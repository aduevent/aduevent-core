<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginStudent.php");
    exit;
}
include("dbcon.php");

if (isset($_POST['eventID'])) {
    $eventID = $_POST['eventID'];
    $question1 = $_POST['question1'] ?? null;
    $question2 = $_POST['question2'] ?? null;
    $question3 = $_POST['question3'] ?? null;
    $question4 = $_POST['question4'] ?? null;
    $question5 = $_POST['question5'] ?? null;
    $question6 = $_POST['question6'] ?? null;
    $question7 = $_POST['question7'] ?? null;
    $question8 = $_POST['question8'] ?? null;
    $question9 = $_POST['question9'] ?? null;
    $question10 = $_POST['question10'] ?? null;

    $query = "INSERT INTO feedback (eventID, question1, question2, question3, question4, question5, question6, question7, question8, question9, question10) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param( "issssssssss", $eventID, $question1, $question2, $question3, $question4, $question5, $question6, $question7, $question8, $question9, $question10);
    if ($stmt->execute()) {
        header("Location: rsoEventHub.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "No event ID provided!";
}
$conn->close();
?>