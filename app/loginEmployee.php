<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Make sure to have PHPMailer installed via Composer
date_default_timezone_set('Asia/Manila');

include("dbcon.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $employeeEmail = $_POST['email'];
    $employeePassword = $_POST['password'];

    if (!empty($employeeEmail) && !empty($employeePassword)) {
        $query = "SELECT * FROM employeeuser WHERE email = '$employeeEmail' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $employeeData = mysqli_fetch_assoc($result);
            $hashedPassword = $employeeData['password']; // Retrieve hashed password from database
            if (password_verify($employeePassword, $hashedPassword)) { // Verify entered password with hashed password
                $_SESSION['id'] = $employeeData['id'];
                $_SESSION['access'] = $employeeData['userTypeID'];
                $_SESSION['name'] = $employeeData['name'];

                // Check if the user is an admin (userTypeID 6)
                if ($employeeData['userTypeID'] == 6) {
                    // Send email notification
                    sendLoginNotification($employeeData['email']);
                    header("Location: adminIndex.php?id=" . $employeeData['id']);
                    exit;
                } elseif ($employeeData['userTypeID'] == 5) {
                    header("Location: osaIndex.php?id=" . $employeeData['id']);
                    exit;
                } elseif (in_array($employeeData['userTypeID'], [3, 4, 7, 8, 9, 10, 11, 12])) {
                    header("Location: approverIndex.php?id=" . $employeeData['id']);
                    exit;
                }
            } else {
                echo "Invalid email address or password";
            }
        } else {
            echo "Invalid email address or password";
        }
    } else {
        echo "Please enter valid information";
    }
}

function sendLoginNotification($recipientEmail) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'notifications.aduevent@gmail.com';
        $mail->Password = 'mylh wdkv ufqt lncq'; // Use environment variables in production
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('notifications.aduevent@gmail.com', 'AdUEvent Notifications');
        $mail->addAddress($recipientEmail);

        $mail->isHTML(true);
        $currentDateTime = date('Y-m-d H:i:s'); // Get current date and time
        $mail->Subject = 'Admin Login Notification';
        $mail->Body = "An admin has logged in on $currentDateTime.";

        $mail->send();
    } catch (Exception $e) {
        echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
        }

        .signup {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="post" class="text-center">
        <a href="homepage.php" class="back-link"><i class="bi bi-arrow-left"></i> Back</a>
            <h2 class="mb-0" style="margin-bottom: 0; color: #000080; padding-bottom: 0;">AdUevent</h2>
            <h4 class="mb-4" style="margin-top: 0; padding-top: 0;">Log-in as an Employee</h4>
            <div class="form-group">
                <label for="email">EMAIL ADDRESS:</label>
                <input type="text" class="form-control" id="email" name="email">
            </div>
            <div class="form-group">
                <label for="password">PASSWORD:</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <button type="submit" name="login" class="btn btn-primary" style="background-color: #000080; border-radius: 50px; margin-top: 10px">Login</button>
            <div class="signup">
                Don't have an account? <a href="signupEmployee.php">Signup now</a>
                | <a href="employeeForgotPassword.php">Forgot Password</a>
            </div>
        </form>
    </div>
</body>
</html>
