<?php
session_start();
include("dbcon.php");

if (isset($_GET['note']) && isset($_GET['date'])) {
    $note = $_GET['note'];
    $noteDate = $_GET['date'];
    $createdBy = $_SESSION['id'];

    // Insert the note into the database
    $insertNoteQuery = "INSERT INTO eventnotes (eventID, noteContent, noteDate, createdBy, type) VALUES (NULL, ?, ?, ?, 2)";
    $stmt = $conn->prepare($insertNoteQuery);
    $stmt->bind_param("ssi", $note, $noteDate, $createdBy);
    $stmt->execute();

    header("Location: osaCalendarView.php?date=" . $noteDate);
    exit;
}
?>
