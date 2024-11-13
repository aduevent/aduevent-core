<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginEmployee.php");
    exit;
}
include("dbcon.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eventID'])) {
    $userTypeID = $_SESSION['access'];

    switch ($userTypeID) {
        case 3:
            $signColumn = 'adviserSign';
            break;
        case 4:
            $signColumn = 'chairpersonSign';
            break;
        case 7:
            $signColumn = 'deanSign';
            break;
        case 8:
            $signColumn = 'sdsSign';
            break;
        case 9:
            $signColumn = 'icesSign';
            break;
        case 10:
            $signColumn = 'ministrySign';
            break;
        case 11:
            $signColumn = 'vpsaSign';
            break;
        case 12:
            $signColumn = 'vpfaSign';
            break;
        default:
            exit("Invalid userTypeID");
    }

    $employeeID = $_SESSION['id'];

    $eventID = $_POST['eventID'];

    $pin = $_POST['pin'];
    $projectLeadName = $_POST['projectLeadName'];
    $updateQuery = "UPDATE event SET $signColumn = '$projectLeadName' WHERE eventID = $eventID";
    $updateResult = mysqli_query($conn, $updateQuery);
    if ($updateResult) {
        header("Location: approverEventApproval.php");
        exit();
    } else {
        echo "Error updating event table: " . mysqli_error($conn);
    }    
}
?>
