<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginEmployee.php");
    exit;
}
$employeeID = $_SESSION['id'];
include("dbcon.php");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

if (isset($_POST['verify_password_otp'])) {
        $enteredOtp = $_POST['otp'];
    if ($enteredOtp == $_SESSION['change_password_otp']) {
        $newPassword = $_SESSION['new_password'];
        $userID = $_SESSION['id'];
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the new password in the database
        $query = "UPDATE employeeuser SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $hashedPassword, $userID);
        if ($stmt->execute()) {
            echo "<script>
                    alert('Password changed successfully.');
                    window.location.href = 'osaProfile.php';
                  </script>";
        } else {
            echo "<script>alert('Error changing password.');</script>";
        }
        $stmt->close();

        // Clear session variables
        unset($_SESSION['change_password_otp']);
        unset($_SESSION['new_password']);
    } else {
        echo "<script>
    alert('Invalid OTP. Please try again.');
    window.location.href = 'osaProfile.php?otpSent=true';
</script>";
}
}
elseif (isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmNewPassword = $_POST['confirm_new_password'];

    $query = "SELECT password FROM employeeuser WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $employeeID);
        $stmt->execute();
        $stmt->bind_result($hashedCurrentPassword);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($currentPassword, $hashedCurrentPassword)) {
            // Check if the new password is different from the current password
            if ($newPassword === $currentPassword) {
                echo "<script>alert('You already set this password. Please use a different password.');</script>";
            } else {
    // Validate the new password and confirm password
    if ($newPassword === $confirmNewPassword) {
        $_SESSION['new_password'] = $newPassword;

        // Step 2: Generate OTP and send it via email
        $otp = rand(100000, 999999);
        $_SESSION['change_password_otp'] = $otp;

        $userID = $_SESSION['id'];
        $emailQuery = "SELECT email FROM employeeuser WHERE id = ?";
        $stmt = $conn->prepare($emailQuery);
        $stmt->bind_param("i", $userID);
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
                $mail->Password = 'mylh wdkv ufqt lncq';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('notifications.aduevent@gmail.com', 'AdUEvent Notifications');
                $mail->addAddress($recipientEmail);

                $mail->isHTML(true);
                $mail->Subject = 'Your OTP for Password Change';
                $mail->Body = "Your OTP code is <strong>$otp</strong>. Please enter this code in the application to proceed.";
                $mail->AltBody = "Your OTP code is $otp. Please enter this code in the application to proceed.";

                $mail->send();
                header("Location: osaProfile.php?otpSent=true");
        exit;
            } catch (Exception $e) {
                echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
            }
        }

    } else {
        echo "<script>
    alert('Password did not match. Please try again.');
    window.location.href = 'osaProfile.php';
</script>";}
}
} else {
    echo "<script>
    alert('Current password is incorrect. Please try again.');
    window.location.href = 'osaProfile.php';
</script>";
}
}elseif (isset($_POST['resend_change_password_otp'])) {
    $otp = rand(100000, 999999);
    $_SESSION['change_password_otp'] = $otp;

    $userID = $_SESSION['id'];
    $emailQuery = "SELECT email FROM employeeuser WHERE id = ?";
    $stmt = $conn->prepare($emailQuery);
    $stmt->bind_param("i", $userID);
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
            $mail->Password = 'mylh wdkv ufqt lncq';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('notifications.aduevent@gmail.com', 'AdUEvent Notifications');
            $mail->addAddress($recipientEmail);

            $mail->isHTML(true);
            $mail->Subject = 'Your OTP for Password Change';
            $mail->Body = "Your OTP code is <strong>$otp</strong>. Please enter this code in the application to proceed.";
            $mail->AltBody = "Your OTP code is $otp. Please enter this code in the application to proceed.";

            $mail->send();
            echo "<script>alert('OTP resent to your email.');</script>";
            header("Location: osaProfile.php?otpSent=true");
        } catch (Exception $e) {
            echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
        }
    }
}
}
?>