<?php 
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginStudent.php");
    exit;
}

$studentID = $_SESSION['id'];
include('dbcon.php');
$userQuery = "SELECT name, email, profilePicture FROM studentuser WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $studentID);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture'];

$showOtpForm = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['verify_otp'])) {
        $enteredOtp = $_POST['otp'];
        if ($enteredOtp == $_SESSION['otp']) {
            $pin = $_SESSION['pin'];
            $hashedPin = password_hash($pin, PASSWORD_DEFAULT);
            $query = "UPDATE studentuser SET pin = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $hashedPin, $studentID);
            if ($stmt->execute()) {
                echo "<script>alert('PIN set successfully.');
                window.location.href = 'rsoProfileViewing.php';</script>";
            } else {
                echo "<script>alert('Error setting PIN.');</script>";
            }
            $stmt->close();
            unset($_SESSION['otp']);
            unset($_SESSION['pin']);
        } else {
            $showOtpForm = true; // Show OTP form on invalid OTP
            echo "<script>alert('Invalid OTP. Please try again.');</script>";
        }
    } elseif (isset($_POST['change_pin'])) {
        $current_pin = $_POST['current_pin'];
        $pin = $_POST['new_pin'];
        $confirm_pin = $_POST['confirm_new_pin'];

        $query = "SELECT pin FROM studentuser WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $studentID);
        $stmt->execute();
        $stmt->bind_result($hashedCurrentPin);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($current_pin, $hashedCurrentPin)) {
        if ($pin === $confirm_pin) {
            $_SESSION['pin'] = $pin;

            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;

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
                    $mail->Password = 'mylh wdkv ufqt lncq';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('notifications.aduevent@gmail.com', 'AdUEvent Notifications');
                    $mail->addAddress($recipientEmail);

                    $mail->isHTML(true);
                    $mail->Subject = 'Your OTP for PIN Setup';
                    $mail->Body = "Your OTP code is <strong>$otp</strong>. Please enter this code in the application to proceed.";
                    $mail->AltBody = "Your OTP code is $otp. Please enter this code in the application to proceed.";

                    $mail->send();
                    $showOtpForm = true; // Show the OTP form on sending OTP
                    echo "<script>alert('OTP sent to your email.');</script>";
                } catch (Exception $e) {
                    echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
                }
            } 
        } else {
            echo "<script>
        alert('PINs do not match.');
        window.location.href = 'rsoProfileViewing.php'; // Redirect to the specified page
    </script>";
        }
    }
        else {
            echo "<script>
    alert('Current Pin does not match.');
    window.location.href = 'rsoProfileViewing.php'; // Redirect to the specified page
</script>";
        }
    } elseif (isset($_POST['resend_otp'])) {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;

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
                $mail->Password = 'mylh wdkv ufqt lncq';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('notifications.aduevent@gmail.com', 'AdUEvent Notifications');
                $mail->addAddress($recipientEmail);

                $mail->isHTML(true);
                $mail->Subject = 'Your OTP for PIN Setup';
                $mail->Body = "Your OTP code is <strong>$otp</strong>. Please enter this code in the application to proceed.";
                $mail->AltBody = "Your OTP code is $otp. Please enter this code in the application to proceed.";

                $mail->send();
                $showOtpForm = true; // Show the OTP form on resend
                echo "<script>alert('OTP resent to your email.');</script>";
            } catch (Exception $e) {
                echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set PIN</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <?php include 'rsoNavbar.php'; 
    $activePage = "rsoChangePinProcess";?>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
<div class="container" style="display: flex; justify-content: center; align-items: center; min-height: 70vh; padding-top: 50px;">
    <?php if ($showOtpForm): ?>
        <div id="otpForm" class="mt-4" style="background-color: #f9f9f9; padding: 20px; border-radius: 20px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); width: 350px; text-align: center;">
            <h3 style="color: #000080; text-align: center;">Enter OTP</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="otp">OTP:</label>
                    <input type="text" name="otp" class="form-control" style="border-radius: 30px; text-align: center; margin-bottom: 10px;" required>
                </div>
                <div class="button-container" style="text-align: center; margin-bottom:5px">
                    <button type="submit" name="verify_otp" class="btn btn-success" style="background-color: #000080; border-radius: 30px; padding: 10px 20px;">Verify OTP</button>
                </div>
            </form>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <button type="submit" name="resend_otp" class="btn btn-link">Resend OTP</button>
        </form>
        </div>
    <?php endif; ?>
</div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-KyZXEAg3QhqLMpG8r+Knujsl5/5hb7J5gN5ht5VLUuvvLQkl9ryBFSI75W3OzP59" crossorigin="anonymous"></script>
    <script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>