<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginEmployee.php");
    exit;
}
include("dbcon.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eventID'])) {
    $userTypeID = $_SESSION['access'];
    $eventID = $_POST['eventID'];
    $projectLeadName = $_POST['projectLeadName'];

    // Get current timestamp
    $timestamp = date("Y-m-d H:i:s");

    // Prepare the approval message with the timestamp
    $approvalMessage = "Approved by $projectLeadName on $timestamp";

    // Initialize $signColumn based on the access level
    $signColumn = '';

    if ($userTypeID == 5) {
        $signColumn = 'osaSign';
    } else {
        exit("Invalid userTypeID");
    }

    if ($signColumn) {
        $updateQuery = "UPDATE event SET $signColumn = '$approvalMessage', 
                    eventStatus = '1'  WHERE eventID = $eventID";
        $updateResult = mysqli_query($conn, $updateQuery);

        if ($updateResult) {
            header("Location: osaEventApproval.php");
            exit();
        } else {
            echo "Error updating event table: " . mysqli_error($conn);
        }
    }
}
?>
