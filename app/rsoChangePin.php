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

$showOtpModal = false; // Default flag for OTP modal visibility

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forgot_pin'])) {
    $email = $_POST['email'];

    $query = "SELECT * FROM studentuser WHERE email = ? and id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $email, $studentID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset PIN</title>
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
            <?php include 'rsoNavbar.php'; ?>
        </head>
        <body style="margin-left: 20%; padding-top: 5px;">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card mt-5">
                            <div class="card-header">
                                <h4>Reset Your PIN</h4>
                            </div>
                            <div class="card-body">
                                <form action="rsoProcessNewPin.php" method="POST">
                                    <div class="form-group">
                                        <label for="pin">Enter PIN</label>
                                        <input type="password" id="pin" name="pin" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm_pin">Confirm PIN</label>
                                        <input type="password" id="confirm_pin" name="confirm_pin" class="form-control" required>
                                    </div>
                                    <button type="submit" name="set_pin" class="btn btn-primary">Reset PIN</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "<script>
            alert('Inputted email is incorrect');
            window.location.href = 'rsoProfileViewing.php';
        </script>";
    }
    $stmt->close();
    $conn->close();
}
?>
