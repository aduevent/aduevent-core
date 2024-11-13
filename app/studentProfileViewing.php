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
$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture'];
$stmt->close();

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
                echo "<script>alert('Profile picture uploaded successfully.');</script>";
                $dp = $targetFile; 
            } else {
                echo "<script>alert('Error updating profile picture in the database.');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php include 'studentNavbar.php'; 
    $activePage = "studentprofileViewing.php"; ?>
    <style>
        .card {
            border: none;
            border-radius: 30px;
            background-color: #F9F9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .form-control {
            border-radius: 15px;
            padding: 10px;
        }
        .btn-primary {
            background-color: #000080;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            color: white;
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: #000099;
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container" style="margin-left: 5px;">
        <div class="row">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex justify-content-center flex-column align-items-center">
                        <div style="position: relative; display: inline-block;">
                            <?php if ($dp): ?>
                                <img src="<?php echo htmlspecialchars($dp); ?>" alt="Profile Picture" class="img-fluid rounded-circle" style="width: 200px; height: 200px; box-shadow: 0 0 0 2px gray;">
                                <?php else: ?>
                                    <img src="defaultavatar.jpg" alt="Default Profile Picture" class="img-fluid rounded-circle" style="max-width: 200px; box-shadow: 0 0 0 2px gray;">
                                    <?php endif; ?>
                                    <div id="profile-pic-container" style="text-align: center;">
                                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                                            <label for="image" class="btn" style="background-color: #02248A; color: white; padding: 6px 12px; font-size: 12px; border-radius: 30px;">
                                                <i class="bi bi-pencil" style="font-size: 12px; margin-right: 4px;"></i> Upload Profile Picture
                                            </label>
                                            <input type="file" id="image" name="image" accept="image/*" style="display: none;" onchange="this.form.submit();">
                                            <input type="hidden" name="upload" value="1">
                                        </form>
                                    </div>
                                </div>
                                <h2><?php echo htmlspecialchars($userName); ?></h2>
                                <p><?php echo htmlspecialchars($email); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card w-100">
                            <div class="card-body">
                                <h3>Change Password</h3>
                                <form action="change_password.php" method="POST">
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
                                    <button type="submit" class="btn btn-primary" style="margin-bottom: 15px; margin-top: 15px;">Change Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>