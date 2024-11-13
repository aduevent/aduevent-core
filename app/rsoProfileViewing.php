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
include("dbcon.php");
$userId = $_SESSION['id'];
$userQuery = "SELECT name, email, profilePicture FROM studentuser WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();

// Fetch organization details
$query = " SELECT su.name, su.email, o.organizationLogo AS profilePicture, o.organizationName, o.organizationLogo, su.pin 
            FROM studentuser su JOIN organization o ON su.organizationID = o.organizationID 
            WHERE su.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($userName, $email, $dp, $organizationName, $organizationLogo, $userPin);
$stmt->fetch();
$stmt->close();

function isPinSet($pin) {
    return !empty($pin);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['verify_otp'])) {
        $enteredOtp = $_POST['otp'];
        if ($enteredOtp == $_SESSION['otp']) {
            $pin = $_SESSION['pin'];
            $studentID = $_SESSION['id'];
            $hashedPin = password_hash($pin, PASSWORD_DEFAULT);
            $query = "UPDATE studentuser SET pin = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $hashedPin, $studentID);
            if ($stmt->execute()) {
                echo "<script>alert('PIN set successfully.');</script>";
            } else {
                echo "<script>alert('Error setting PIN.');</script>";
            }
            $stmt->close();
            unset($_SESSION['otp']);
            unset($_SESSION['pin']);
        } else {
            echo "<script>alert('Invalid OTP. Please try again.');</script>";
        }
    }

    elseif (isset($_POST['set_pin'])) {
        $pin = $_POST['pin'];
        $confirm_pin = $_POST['confirm_pin'];
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
    
    // Handle Resend OTP
    elseif (isset($_POST['resend_otp'])) {
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
                echo "<script>
                        alert('OTP resent to your email.');
                        document.getElementById('otpModal').style.display = 'block'; // Show OTP modal
                      </script>";
            } catch (Exception $e) {
                echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
            }
        }
    }
}
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['upload'])) {
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($_FILES["image"]["name"]);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check !== false) {
                $uploadOk = 1;
            } else {
                echo "<script>alert('File is not an image.');</script>";
                $uploadOk = 0;
            }
        
            if ($_FILES["image"]["size"] > 5000000) {
                echo "<script>alert('Sorry, your file is too large.');</script>";
                $uploadOk = 0;
            }
        
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');</script>";
                $uploadOk = 0;
            }
            if ($uploadOk == 0) {
                echo "<script>alert('Sorry, your file was not uploaded.');</script>";
            } else {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    $updateQuery = "UPDATE studentuser SET profilePicture = ? WHERE id = ?";
                    $stmt = $conn->prepare($updateQuery);
                    $stmt->bind_param("si", $targetFile, $studentID);
                    if ($stmt->execute()) {
                        echo "<script>alert('Organization logo uploaded successfully.');</script>";
                        $dp = $targetFile; 
                    } else {
                        echo "<script>alert('Error updating organization logo in the database.');</script>";
                    }
                    $stmt->close();
                } else {
                    echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
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
    <title>Organization Profile</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php include 'rsoNavbar.php'; $activePage = "rsoProfileViewing"; ?>
    <style>
        .card{
            border: none;
            border-radius: 30px;
            background-color: #F9F9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: center; /* Horizontal alignment */
            align-items: center; /* Vertical alignment */
            text-align: center;
        }
        .form-control {
            border-radius: 15px; /* Round edges for input */
            border: 1px solid #ccc; /* Optional: Set border */
            padding: 10px; /* Optional: Add padding for better spacing */
        }
        .btn-primary {
            background-color: #000080;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            color: white;
            margin-top: 10px; 
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: #000099;
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container" style="margin-left: 5px;">
        <div class="card">
            <div class="card-body text-center">
                <h2><?php echo htmlspecialchars($organizationName); ?></h2>
                <?php if ($organizationLogo): ?>
                    <img src="<?php echo htmlspecialchars($organizationLogo); ?>" alt="Organization Logo" class="img-fluid" style="max-width: 200px; border-radius: 50%; border: 2px solid #212121;">
                <?php else: ?>
                    <img src="defaultavatar.jpg" alt="Default Profile Picture" class="img-fluid rounded-circle" style="max-width: 200px; box-shadow: 0 0 0 2px gray;">
                <?php endif; ?>
                <div id="profile-pic-container" style="text-align: center;">
                    
                </div>
                <h3><?php echo htmlspecialchars($email); ?></h3>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6"> 
                <div class="card w-100">
                    <div class="card-body">
                        <h3>Set Up PIN</h3>
                        <div id="message-container"></div>
                        <?php if (isPinSet($userPin)): ?>
                            <p>PIN is already set up.</p>
                            <a href="#" id="forgotPinLink" style="color: #007bff; margin-right: 10px;">Forgot PIN</a>
                            <a href="#" id="changePinLink" style="color: #007bff;">Change PIN</a>
                        <?php else: ?>
                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                                <div class="form-group">
                                    <label for="pin">Enter PIN</label>
                                    <input type="password" id="pin" name="pin" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_pin">Confirm PIN</label>
                                    <input type="password" id="confirm_pin" name="confirm_pin" class="form-control" required>
                                </div>
                                <button type="submit" name="set_pin" class="btn btn-primary">Set PIN</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6"> <!-- Change Password -->
                <div class="card w-100">
                    <div class="card-body">
                        <h3>Change Password</h3>
                        <form action="rsoChangePassword.php" method="POST">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_new_password">Confirm New Password</label>
                                <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary" name="change_password" style="width: 100%; margin-bottom: 10px;">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Forgot PIN Modal -->
    <div id="forgotPinModal" class="modal" tabindex="-1" role="dialog" style="display: none;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Forgot PIN</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Please enter your registered email to reset your PIN.</p>
                    <form action="rsoChangePin.php" method="POST">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <button type="submit" name="forgot_pin" class="btn btn-primary">Proceed with PIN Reset</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Change PIN Modal -->
    <div id="changePinModal" class="modal" tabindex="-1" role="dialog" style="display: none;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change PIN</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="rsoChangePinProcess.php" method="POST">
                        <div class="form-group">
                            <label for="current_pin">Current PIN</label>
                            <input type="password" id="current_pin" name="current_pin" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="new_pin">New PIN</label>
                            <input type="password" id="new_pin" name="new_pin" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_new_pin">Confirm New PIN</label>
                            <input type="password" id="confirm_new_pin" name="confirm_new_pin" class="form-control" required>
                        </div>
                        <button type="submit" name="change_pin" class="btn btn-primary">Change PIN</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- OTP Modal -->
    <div id="otpModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:30px; border-radius:8px; width:350px; text-align:center; position: relative;">
        <h3 style="margin-bottom: 20px;">Verify OTP</h3>
        
        <!-- Close button -->
        <button onclick="document.getElementById('otpModal').style.display='none'" style="position: absolute; top: 10px; right: 10px; border: none; background: transparent; font-size: 18px; cursor: pointer;">&times;</button>
        
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="otp">Enter OTP</label>
                <input type="text" id="otp" name="otp" class="form-control" style="text-align: center; border-radius: 50px;" required>
            </div>
            <button type="submit" name="verify_otp" class="btn btn-primary" style="width: 100%; margin-bottom: 10px;">Verify OTP</button>
        </form>
        <a href="#" onclick="document.getElementById('resendOtpForm').submit();" style="display: block; margin-top: 10px; color: #007bff;">Resend OTP</a>
        <form id="resendOtpForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" style="display: none;">
            <input type="hidden" name="resend_otp">
        </form>
    </div>
</div>

   

    <!-- OTP Modal for Change Password -->
    <div id="changePasswordOtpModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:20px; border-radius:8px; width:350px; text-align:center;">
            <h3 style="margin-bottom: 20px;">Verify OTP</h3>
            <button onclick="document.getElementById('changePasswordOtpModal').style.display='none'" style="position: absolute; top: 10px; right: 10px; border: none; background: transparent; font-size: 18px; cursor: pointer;">&times;</button>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="otp_password">Enter OTP</label>
                    <input type="text" id="otp_password" name="otp_password" class="form-control" style="text-align: center; border-radius: 50px;" required>
                </div>
                <button type="submit" name="verify_otp_password" class="btn btn-primary" style="width: 100%; margin-bottom: 10px;">Verify OTP</button>
            </form>
            <a href="#" onclick="document.getElementById('resendPasswordOtpForm').submit();" style="display: block; margin-top: 10px; color: #007bff;">Resend OTP</a>
            <form id="resendPasswordOtpForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" style="display: none;">
                <input type="hidden" name="resend_otp_password">
            </form>
        </div>
    </div>
</body>

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-KyZXEAg3QhqLMpG8r+Knujsl5/5hb7J5gN5ht5VLUuvvLQkl9ryBFSI75W3OzP59" crossorigin="anonymous"></script>
<script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (<?php echo isset($_SESSION['otp']) ? 'true' : 'false'; ?>) {
            document.getElementById('otpModal').style.display = 'block';
        }
    });
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('otpSent')) {
    // If otpSent is true, show the modal
    document.getElementById('changePasswordOtpModal').style.display = 'block';
    alert('OTP sent to your email.');
}
document.getElementById('forgotPinLink').addEventListener('click', function(event) {
    event.preventDefault();
    document.getElementById('forgotPinModal').style.display = 'block';
  });

  // Change PIN modal
  document.getElementById('changePinLink').addEventListener('click', function(event) {
    event.preventDefault();
    document.getElementById('changePinModal').style.display = 'block';
  });

  // Close modals when 'x' button is clicked
  document.querySelectorAll('.close').forEach(button => {
    button.addEventListener('click', function() {
      this.closest('.modal').style.display = 'none';
    });
    
  });
  
</script>
</body>
</html>