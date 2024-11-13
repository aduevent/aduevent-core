<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Ensure PHPMailer is correctly autoloaded

if (!isset($_SESSION['id'])) {
    header("Location: loginStudent.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include("dbcon.php");

    // Check if the OTP verification form was submitted
    if (isset($_POST['verify_otp'])) {
        $enteredOtp = $_POST['otp'];
        if ($enteredOtp == $_SESSION['otp']) {
            // OTP is correct; proceed to save the PIN
            $pin = $_SESSION['pin'];
            $studentID = $_SESSION['id'];
            $hashedPin = password_hash($pin, PASSWORD_DEFAULT);
            $query = "UPDATE studentuser SET pin = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $hashedPin, $studentID);
            if ($stmt->execute()) {
                echo "<script>alert('PIN set successfully.'); window.location.href='rsoProfileViewing.php';</script>";
            } else {
                echo "<script>alert('Error setting PIN.');</script>";
            }
            $stmt->close();
            // Clear session variables after successful operation
            unset($_SESSION['otp']);
            unset($_SESSION['pin']);
        } else {
            echo "<script>alert('Invalid OTP. Please try again.');</script>";
        }
    }

    // Check if the PIN setup form was submitted
    elseif (isset($_POST['set_pin'])) {
        $pin = $_POST['pin'];
        $confirm_pin = $_POST['confirm_pin'];
        if ($pin === $confirm_pin) {
            $_SESSION['pin'] = $pin; // Temporarily store PIN in session

            // Generate OTP
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp; // Store OTP in session

            // Fetch recipient email
            $studentID = $_SESSION['id'];
            $emailQuery = "SELECT email FROM studentuser WHERE id = ?";
            $stmt = $conn->prepare($emailQuery);
            $stmt->bind_param("i", $studentID);
            $stmt->execute();
            $stmt->bind_result($recipientEmail);
            $stmt->fetch();
            $stmt->close();

            if ($recipientEmail) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'notifications.aduevent@gmail.com';
                    $mail->Password = 'mylh wdkv ufqt lncq'; // Use app password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Recipients
                    $mail->setFrom('notifications.aduevent@gmail.com', 'AdUEvent Notifications');
                    $mail->addAddress($recipientEmail);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Your OTP for PIN Setup';
                    $mail->Body = "Your OTP code is <strong>$otp</strong>. Please enter this code in the application to proceed.";
                    $mail->AltBody = "Your OTP code is $otp. Please enter this code in the application to proceed.";

                    $mail->send();
                    // Show OTP modal after sending email
                    echo "<script>
                            alert('OTP sent to your email.');
                            document.getElementById('otpModal').style.display = 'block'; // Show OTP modal
                          </script>";
                } catch (Exception $e) {
                    echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
                }
            }
        } else {
            echo "<script>alert('PINs do not match.');</script>";
        }
    }
}
?>