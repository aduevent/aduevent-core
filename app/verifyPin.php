<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include("dbcon.php");

    $pin = $_POST['pin'];
    $studentID = $_SESSION['id'];

    $query = "SELECT pin FROM students WHERE studentID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $studentID);
    $stmt->execute();
    $stmt->bind_result($hashedPin);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($pin, $hashedPin)) {
        echo 'success';
    } else {
        echo 'failure';
    }
}
?>