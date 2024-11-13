<?php
include("dbcon.php");
require '../vendor/autoload.php'; // Make sure PHPMailer is installed

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
$showOtpModal = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // OTP Verification Logic
    if (isset($_POST['verify_otp'])) {
        $enteredOtp = isset($_POST['otp']) ? $_POST['otp'] : null;

        if ($enteredOtp == $_SESSION['otp']) {
            // Retrieve form data from session
            $formData = $_SESSION['formData'];
            $hashedPassword = password_hash($formData['password'], PASSWORD_DEFAULT);

            // Insert data into employeeuser table
            $stmt = $conn->prepare("INSERT INTO employeeuser (userTypeID, organizationID, employeeNumber, name, email, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissss", $formData['employeeType'], $formData['organizations'], $formData['employeeNumber'], $formData['name'], $formData['email'], $hashedPassword);

            if ($stmt->execute()) {
                echo '<script>alert("Registration successful!"); window.location.href = "homepage.php";</script>';
                exit();
            } else {
                echo "Error signing up user: " . $conn->error;
            }
            $stmt->close();
            session_destroy();
        } else {
            // Invalid OTP, show OTP modal again
            echo '<script>alert("Invalid OTP! Please try again.");</script>';
            $showOtpModal = true;
        }
    }
    // Resend OTP Logic
    elseif (isset($_POST['resend_otp'])) {
        $otp = rand(100000, 999999); // Generate new OTP
        $_SESSION['otp'] = $otp;

        $email = $_SESSION['formData']['email']; // Get email from session data
        $mail = new PHPMailer(true);
        try {
            // Set up email
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'notifications.aduevent@gmail.com';
            $mail->Password = 'mylh wdkv ufqt lncq';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email content
            $mail->setFrom('notifications.aduevent@gmail.com', 'AdUEvent Notifications');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Your Resent OTP for PIN Setup';
            $mail->Body = "Your new OTP code is <strong>$otp</strong>. Please enter this code in the application to proceed.";
            $mail->AltBody = "Your new OTP code is $otp. Please enter this code in the application to proceed.";

            $mail->send();
            echo '<script>alert("New OTP has been sent to your email.");</script>';
            $showOtpModal = true; // Keep OTP modal open after resend
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
    // Registration Logic
    else {
        $userTypeID = isset($_POST['employeeType']) ? $_POST['employeeType'] : null;
        $organizationID = isset($_POST['organizations']) ? $_POST['organizations'] : null;
        $name = isset($_POST['name']) ? $_POST['name'] : null;
        $email = isset($_POST['email']) ? $_POST['email'] : null;
        $password = isset($_POST['password']) ? $_POST['password'] : null;
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : null;

        if ($password !== $confirmPassword) {
            echo "Passwords do not match.";
        } else {
            $otp = rand(100000, 999999); // Generate OTP
            $_SESSION['otp'] = $otp;
            $_SESSION['formData'] = $_POST; // Store form data in session

            $mail = new PHPMailer(true);
            try {
                // Set up email
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'notifications.aduevent@gmail.com';
                $mail->Password = 'mylh wdkv ufqt lncq';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Email content
                $mail->setFrom('notifications.aduevent@gmail.com', 'AdUEvent Notifications');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Your OTP for PIN Setup';
                $mail->Body = "Your OTP code is <strong>$otp</strong>. Please enter this code in the application to proceed.";
                $mail->AltBody = "Your OTP code is $otp. Please enter this code in the application to proceed.";

                $mail->send();
                $showOtpModal = true; // Show OTP modal after sending
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <!-- Link your CSS and Bootstrap properly -->
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
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #signup-container {
            width: 38%;
            padding: 20px;
            box-sizing: border-box;
            background-color: rgba(245, 245, 245, 0.7);
            opacity: 0.9;
            border-radius: 10px;
        }

        .form-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100%;
        }

        .form-group {
            display: grid;
            grid-template-columns: 150px auto;
            align-items: center;
            margin-bottom: 15px;
            width: 100%;
        }

        label {
            text-align: left;
            font-weight: bold;
        }

        input, select {
            border-radius: 50px;
            padding: 10px 15px;
            border: 1px solid #ccc;
            width: 100%;
        }

        /* Centering the form button */
        .btn-submit {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        /* Responsive Design */
        @media only screen and (max-width: 768px) {
            #signup-container {
                width: 85%; /* Full width for small screens */
                height: auto;
                padding: 15px;
            }

            .form-group {
                grid-template-columns: 100px auto; /* Reduce label width */
            }

            label {
                font-size: 14px;
            }
        }

        @media only screen and (max-width: 480px) {
            #signup-container {
                width: 95%;
                padding: 10px;
            }

            .form-group {
                grid-template-columns: 1fr; /* Stack labels on top for very small screens */
            }

            label, input, select {
                width: 100%;
                text-align: center; /* Center labels */
            }

            .btn-submit {
                margin-top: 10px;
            }
        }
        #otpModal {
            display: none;
            position: fixed;
            z-index: 1000;
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
        }
        .otp-modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 30%;
            border-radius: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div id="signup-container">
        <div class="form-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <a href="homepage.php" class="back-link"><i class="bi bi-arrow-left"></i> Back</a>
                <div id="login-logo" class="text-center">
                    <img src="systemlogoo.png" alt="Logo" class="img-fluid" width="45%">
                </div>
                <div class="form-group">
                    <label for="employeeType">Select Employee Type:</label>
                    <select id="employeeType" name="employeeType">
                        <option value="3">Organizational Adviser</option>
                        <option value="4">Chairperson</option>
                        <option value="5">OSA Personnel</option>
                        <option value="7">College Dean</option>
                        <option value="8">Student Development Section</option>
                        <option value="9">Integrated Community External Affairs</option>
                        <option value="10">Campus Ministry Office</option>
                        <option value="11">Vice President for Student Affairs</option>
                        <option value="12">Vice President for Financial Affairs</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="organizations">Select Organization:</label>
                    <select id="organizations" name="organizations">
                        <?php
                        $query = "SELECT organizationID, organizationName FROM organization";
                        $result = mysqli_query($conn, $query);
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value=\"{$row['organizationID']}\">{$row['organizationName']}</option>";
                            }
                            mysqli_free_result($result);
                        } else {
                            echo "<option value=\"\">No options available</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="employeeNumber">Employee Number:</label>
                    <input type="text" id="employeeNumber" name="employeeNumber" required>
                </div>
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="btn-submit">
                    <button type="submit" class="btn btn-primary" style="background-color: #000080; border-radius: 50px; border: none;">Sign Up</button>
                </div>
            </form>
        </div>
    </div>
    <div id="otpModal">
        <div class="otp-modal-content">
            <h3 style="color: #000080;">Enter OTP</h3>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="text" name="otp" placeholder="Enter OTP" required style="width: 70%; padding: 10px; margin: 10px auto; display: block; border: 1px solid #ccc; border-radius: 50px; box-sizing: border-box;">
                <button type="submit" name="verify_otp" class="btn btn-success" style="background-color: #000080; border-radius: 50px; border: none;">Verify</button>
            </form>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <button type="submit" name="resend_otp" class="btn btn-link">Resend OTP</button>
        </form>
        </div>
    </div>
<script>
    document.getElementById('employeeType').addEventListener('change', function() {
    var organizations = document.getElementById('organizations');
    if (this.value === '3') {
        organizations.disabled = false;
    } else {
        organizations.disabled = true;
    }
});
function checkPasswords() {
        var password = document.getElementById('password').value;
        var confirmPassword = document.getElementById('confirm_password').value;
    
        if (password !== confirmPassword && confirmPassword !== '') {
            alert('Passwords do not match.');
            document.getElementById('confirm_password').value = '';
        }
    }
    document.addEventListener('DOMContentLoaded', (event) => {
        <?php if ($showOtpModal) { ?>
            document.getElementById("otpModal").style.display = "block";
        <?php } ?>
    });
    document.getElementById('password').addEventListener('change', checkPasswords);
    document.getElementById('confirm_password').addEventListener('change', checkPasswords);
</script>
</body>
</html>
<?php $conn->close(); ?>
