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
$userId = $_SESSION['id'];
$userQuery = "SELECT name, email, profilePicture FROM employeeuser WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture'];
// Fetch organization details
$query = "SELECT s.name, s.profilePicture, u.userTypeDescription, s.pin
          FROM employeeuser s
          JOIN usertype u ON s.userTypeID = u.userTypeID
          WHERE s.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $employeeID);
$stmt->execute();
$stmt->bind_result($userName, $profilePicture, $userTypeDescription, $userPin);
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
            $employeeID = $_SESSION['id'];
            $hashedPin = password_hash($pin, PASSWORD_DEFAULT);
            $query = "UPDATE employeeuser SET pin = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $hashedPin, $employeeID);
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

            $employeeID = $_SESSION['id'];
            $emailQuery = "SELECT email FROM employeeuser WHERE id = ?";
            $stmt = $conn->prepare($emailQuery);
            $stmt->bind_param("i", $employeeID);
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

        $employeeID = $_SESSION['id'];
        $emailQuery = "SELECT email FROM employeeuser WHERE id = ?";
        $stmt = $conn->prepare($emailQuery);
        $stmt->bind_param("i", $employeeID);
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
        // Directory for uploads
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($_FILES["image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if image file is an actual image or fake
        if (isset($_FILES["image"]["tmp_name"]) && $_FILES["image"]["tmp_name"] != '') {
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check !== false) {
                $uploadOk = 1;
            } else {
                echo "<script>alert('File is not an image.');</script>";
                $uploadOk = 0;
            }
        } else {
            echo "<script>alert('No file selected or file is invalid.');</script>";
            $uploadOk = 0;
        }

        // Check file size (limit: 5MB)
        if ($_FILES["image"]["size"] > 5000000) {
            echo "<script>alert('Sorry, your file is too large.');</script>";
            $uploadOk = 0;
        }

        // Allow only certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');</script>";
            $uploadOk = 0;
        }

        // Check if upload is allowed
        if ($uploadOk == 0) {
            echo "<script>alert('Sorry, your file was not uploaded.');</script>";
        } else {
            // Try to move the uploaded file
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                // Database connection and query
                $updateQuery = "UPDATE employeeuser SET profilePicture = ? WHERE id = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("si", $targetFile, $employeeID);
                
                if ($stmt->execute()) {
                    echo "<script>alert('Profile picture uploaded successfully.');</script>";
                    $profilePicture = $targetFile; 
                } else {
                    echo "<script>alert('Error updating profile picture in the database.');</script>";
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
    <?php include 'navbar.php';
    $activePage = "osaProfile"; ?>
    <style>
        .card{
            border: none;
            border-radius: 30px;
            background-color: #f9f9f9;
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
        .form-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 5px;
        }
        .form-group label {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
            text-align: center;
        }
        .form-group input {
            border-radius: 50px;
            padding: 10px 20px;
            border: 1px solid #ccc;
            width: 100%;
            max-width: 90%;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            font-size: 14px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #000080; /* Adds blue border on focus */
            box-shadow: 0px 4px 8px rgba(0, 123, 255, 0.2); /* Slight shadow effect on focus */
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
<div class="container" style="margin-left: 5px;">
<form method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-center flex-column align-items-center">
                    <div style="position: relative; display: inline-block;">
                    <img id="profilePicPreview" src="<?php echo isset($profilePicture) && $profilePicture ? htmlspecialchars($profilePicture) : 'defaultavatar.jpg'; ?>" alt="Profile Picture" class="img-fluid rounded-circle" style="max-width: 200px;">
                    <div id="profile-pic-container" style="text-align: center;">
                        <div id="profile-pic-container" style="text-align: center;">
                            <label for="image" class="btn-primary";>
                                <i class="bi bi-pencil" style="font-size: 12px; margin-right: 4px;"></i> Add Profile Picture
                            </label>
                            <input type="file" id="image" name="image" accept="image/*" style="display: none;" onchange="previewImage(event)">
                        </div>
                    </div>
                </div>
                <button type="submit" name="upload" class="btn-primary">Save</button>
                        <h2><?php echo htmlspecialchars($userName); ?></h2>
                        <p><?php echo htmlspecialchars($userTypeDescription); ?></p>
                        
                    </div>
                </div>
            </div>
                        </form>
            <div class="col-md-4">
                <div class="col-12 mb-4 d-flex align-items-center">
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
        <div id="forgotPinModal" class="modal" tabindex="-1" role="dialog" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="justify-content: center; position: relative; border-bottom: none;">
                <h5 class="modal-title" style="tet-align: center; color: #000080;font-weight: bold;">Forgot PIN</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: absolute; right: 10px; top: 10px; color: #000080; font-size: 20px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p style="text-align: center;">Please enter your registered email to reset your PIN.</p>
                <form action="osaChangePin.php" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="button-container" style="text-align: center;">
                    <button type="submit" name="forgot_pin" class="btn btn-primary">Proceed with PIN Reset</button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>
<div id="changePinModal" class="modal" tabindex="-1" role="dialog" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="justify-content: center; position: relative; border-bottom: none;">
        <h5 class="modal-title" style="tet-align: center; color: #000080;font-weight: bold;">Change PIN</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: absolute; right: 10px; top: 10px; color: #000080; font-size: 20px;">
                    <span aria-hidden="true">&times;</span>
                </button>
    </div>
        <div class="modal-body">
        <form action="osaChangePinProcess.php" method="POST">
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
            <div class="button-container" style="text-align: center;">
                <button type="submit" name="change_pin" class="btn btn-primary">Change PIN</button>
            </div>
        </form>
        </div>
    </div>
</div>
</div>
    <div id="otpModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:30px; border-radius:8px; width:350px; text-align:center;">
        <h3 style="margin-bottom: 20px;">Verify OTP</h3>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="otp">Enter OTP</label>
                <input type="text" id="otp" name="otp" class="form-control" style="text-align: center; border-radius: 50px;" required>
            </div>
            <button type="submit" name="verify_otp" class="btn btn-primary" style="width: 100%; margin-bottom: 10px;">Verify OTP</button>
        </form>
        <!-- Resend PIN link -->
        <a href="#" onclick="document.getElementById('resendOtpForm').submit();" style="display: block; margin-top: 10px; color: #007bff;">Resend OTP</a>
<form id="resendOtpForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" style="display: none;">
    <input type="hidden" name="resend_otp">
</form></div>
</div>
<div class="col-12 d-flex align-items-center">
    <div class="card w-100">
        <div class="card-body">
            <h3>Change Password</h3>
            <form action="osaChangePassword.php" method="POST">
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

<!-- OTP Modal for Change Password -->
<div id="changePasswordOtpModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:20px; border-radius:8px; width:300px; text-align:center;">
        <h3>Verify OTP for Password Change</h3>
        <form action="osaChangePassword.php" method="POST">
            <div class="form-group">
                <label for="otp">Enter OTP</label>
                <input type="text" id="otp" name="otp" class="form-control" required>
            </div>
            <button type="submit" name="verify_password_otp" class="btn btn-primary">Verify OTP</button>
        </form>
        <a href="#" onclick="document.getElementById('resendChangePasswordOtpForm').submit();" style="display: block; margin-top: 10px; color: #007bff;">Resend OTP</a>
        <form id="resendChangePasswordOtpForm" action="osaChangePassword.php" method="POST" style="display: none;">
            <input type="hidden" name="resend_change_password_otp">
        </form>
    </div>
</div>
            </div>
        </div>
    </div>
</div>
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
}document.getElementById('forgotPinLink').addEventListener('click', function(event) {
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
  // Function to preview image immediately after selection
  function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilePicPreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
</script>
</body>
</html>