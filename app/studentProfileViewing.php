<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require "../vendor/autoload.php";
if (!isset($_SESSION["id"]) || !isset($_SESSION["access"])) {
    header("Location: loginStudent.php");
    exit();
}
$studentID = $_SESSION["id"];
include "dbcon.php";

$userId = $_SESSION["id"];

$userQuery = "SELECT
        studentuser.name,
        studentuser.email,
        studentuser.profilePicture,
        files.id AS profilePictureFileReference,
        files.filename AS profilePictureFileName,
        files.data AS profilePictureFileData
    FROM
        studentuser
    LEFT JOIN
        files
    ON
        studentuser.profilePictureFileReference = files.id
    WHERE
        studentuser.id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$userName = $userData["name"];
$email = $userData["email"];
// $dp = $userData["profilePicture"];
$stmt->close();

// use the stored asset as the default
$dp = "defaultavatar.jpg";

$encoded_dp = null;
$encoded_dp_mimetype = null;

if (!empty($userData["profilePictureFileReference"])) {
    $fileExtension = pathinfo(
        $userData["profilePictureFileReference"],
        PATHINFO_EXTENSION
    );

    $encoded_dp_mimeType = match (strtolower($fileExtension)) {
        "jpg", "jpeg" => "image/jpeg",
        "png" => "image/png",
        "gif" => "image/gif",
        default => "application/octet-stream",
    };

    // base 64 encoding
    $encoded_dp = base64_encode($userData["profilePictureFileData"]);
}

if (isset($_POST["upload"])) {
    $validMimetypes = ["image/jpeg", "image/png", "image/gif"];
    $file = $_FILES["image"];
    $uploadOk = 1;

    if ($file["size"] > 5000000) {
        echo "<script>alert('Sorry, your file is too large.');</script>";
        $uploadOk = 0;
    }

    if (!in_array($file["type"], $validMimetypes)) {
        echo "<script>alert('File is not an image.');</script>";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        echo "<script>alert('Sorry, your file was not uploaded.');</script>";
    } else {
        // upload file to files table
        $null = null;
        $fileContents = file_get_contents($file["tmp_name"]);
        $isUploaded = false;
        $insertedFileID = null;

        $file_store_stmt = $conn->prepare(
            "INSERT INTO files (filename, data) VALUES (?, ?)"
        );
        $file_store_stmt->bind_param("sb", $file["name"], $null);
        $file_store_stmt->send_long_data(1, $fileContents);

        if ($file_store_stmt->execute()) {
            $isUploaded = true;
            $insertedFileID = $conn->insert_id;
        } else {
            echo "Failed to upload file: " . $file_store_stmt->error;
        }

        if ($isUploaded == true && !empty($insertedFileID)) {
            $stmt = $conn->prepare(
                "UPDATE studentuser SET profilePictureFileReference = ? WHERE id = ?"
            );
            $stmt->bind_param("is", $insertedFileID, $studentID);

            if ($stmt->execute()) {
                echo "<script>alert('Profile picture uploaded successfully.');</script>";

                $fileExtension = pathinfo($insertedFileID, PATHINFO_EXTENSION);
                $encoded_dp_mimeType = match (strtolower($fileExtension)) {
                    "jpg", "jpeg" => "image/jpeg",
                    "png" => "image/png",
                    "gif" => "image/gif",
                    default => "application/octet-stream",
                };

                // base 64 encoding
                $encoded_dp = base64_encode($fileContents);
            } else {
                echo "<script>alert('Error updating profile picture in the database.');</script>";
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
    <title>Student Profile</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php
    include "studentNavbar.php";
    $activePage = "studentProfileViewing.php";
    ?>
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
                            <?php if (!empty($encoded_dp)): ?>
                                <img src="<?php echo "data:" .
                                    $encoded_dp_mimetype .
                                    ";base64," .
                                    $encoded_dp; ?>" alt="Profile Picture" class="img-fluid rounded-circle" style="width: 200px; height: 200px; box-shadow: 0 0 0 2px gray; object-fit: cover;">
                                <?php else: ?>
                                    <img src="defaultavatar.jpg" alt="Default Profile Picture" class="img-fluid rounded-circle" style="max-width: 200px; box-shadow: 0 0 0 2px gray;">
                                    <?php endif; ?>
                                    <div id="profile-pic-container" style="text-align: center; margin-top: 8px;">
                                        <form action="<?php echo htmlspecialchars(
                                            $_SERVER["PHP_SELF"]
                                        ); ?>" method="POST" enctype="multipart/form-data">
                                            <label for="image" class="btn" style="background-color: #02248A; color: white; padding: 6px 12px; font-size: 12px; border-radius: 30px;">
                                                <i class="bi bi-pencil" style="font-size: 12px; margin-right: 4px;"></i> Upload Profile Picture
                                            </label>
                                            <input type="file" id="image" name="image" accept="image/*" style="display: none;" onchange="this.form.submit();">
                                            <input type="hidden" name="upload" value="1">
                                        </form>
                                    </div>
                                </div>
                                <h2 style="margin: 8px 0;">
                                    <?php echo htmlspecialchars($userName); ?>
                                </h2>
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
