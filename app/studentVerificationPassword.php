<?php 
include("dbcon.php");
session_start();

// PHPMailer use statements should be outside of any function/block
require '../vendor/autoload.php'; // Ensure PHPMailer is installed
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$showOtpModal = true; // Initially show OTP modal
$showResetPasswordModal = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verify OTP
    if (isset($_POST['verify_otp'])) {
        $enteredOtp = $_POST['otp'];

        if ($enteredOtp == $_SESSION['otp']) {
            $showOtpModal = false; // Hide OTP modal
            $showResetPasswordModal = true; // Show password reset form
        } else {
            echo '<script>alert("Invalid OTP! Please try again.");</script>';
        }
    }

    // Resend OTP logic
    if (isset($_POST['resend_otp'])) {
        $otp = rand(100000, 999999); // Generate a new OTP
        $_SESSION['otp'] = $otp;
        $email = $_SESSION['email'];

        // Send the new OTP via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'notifications.aduevent@gmail.com';
            $mail->Password = 'mylh wdkv ufqt lncq'; // Use your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('notifications.aduevent@gmail.com', 'AdUEvent Notifications');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP for Password Reset';
            $mail->Body = "Your new OTP code is <strong>$otp</strong>. Please enter this code to proceed.";
            $mail->AltBody = "Your new OTP code is $otp. Please enter this code to proceed.";

            $mail->send();
            echo '<script>alert("OTP has been resent!");</script>';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    // Reset password logic
    if (isset($_POST['reset_password'])) {
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($password !== $confirmPassword) {
            echo '<script>alert("Passwords do not match.");</script>';
        } else {
            $email = $_SESSION['email'];
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE studentuser SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashedPassword, $email);

            if ($stmt->execute()) {
                echo '<script>alert("Password updated successfully!"); window.location.href = "loginStudent.php";</script>';
                session_destroy();
                exit();
            } else {
                echo "Error updating password: " . $conn->error;
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP & Reset Password</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <style>
        body {
            background-image: url('bground.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            margin: 0;
            padding: 0;
        }
        .container {
            margin-top: 50px;
            max-width: 500px;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h3 {
            color: #000080;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .btn {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            border-radius: 50px;
        }
        .btn-primary {
            background-color: #000080;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="otpModal" style="display: none;">
            <h3>Enter OTP</h3>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="text" name="otp" class="form-control" placeholder="Enter OTP" required>
                <button type="submit" name="verify_otp" class="btn btn-primary" style="background-color: #000080; border-radius: 50px;">Verify</button> 
            </form>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <button type="submit" name="resend_otp" class="btn btn-link">Resend OTP</button>
            </form>
        </div>
        <div id="resetPasswordModal" style="display: none;">
            <h3>Reset Password</h3>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="password">New Password:</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" class="form-control" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary" name="reset_password">Reset Password</button>
            </form>
        </div>
    </div>
    <script>
        window.onload = function() {
            var showOtpModal = <?php echo $showOtpModal ? 'true' : 'false'; ?>;
            var showResetPasswordModal = <?php echo $showResetPasswordModal ? 'true' : 'false'; ?>;
            
            var otpModal = document.getElementById('otpModal');
            var resetPasswordModal = document.getElementById('resetPasswordModal');
            
            if (showResetPasswordModal) {
                resetPasswordModal.style.display = 'block'; // Show reset password modal
                otpModal.style.display = 'none'; // Hide OTP modal
            } else if (showOtpModal) {
                otpModal.style.display = 'block'; // Show OTP modal
                resetPasswordModal.style.display = 'none'; // Hide reset password modal
            }
        };
    </script>
</body>
</html>
<?php $conn->close(); ?>
