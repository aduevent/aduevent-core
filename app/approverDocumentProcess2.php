<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginEmployee.php");
    exit;
}
include("dbcon.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

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

    // Get current timestamp
    $timestamp = date("Y-m-d H:i:s");

    // Prepare the approval message with the timestamp
    $approvalMessage = "Approved by $projectLeadName on $timestamp";

    // Fetch organization and event details
    $eventDetailsQuery = "SELECT e.eventTitle, o.organizationName 
                          FROM event e 
                          JOIN organization o ON e.organizationID = o.organizationID 
                          WHERE e.eventID = $eventID";
    $eventDetailsResult = mysqli_query($conn, $eventDetailsQuery);

    if ($eventDetailsResult && mysqli_num_rows($eventDetailsResult) > 0) {
        $eventDetails = mysqli_fetch_assoc($eventDetailsResult);
        $eventName = $eventDetails['eventTitle'];
        $orgName = $eventDetails['organizationName'];
    } else {
        echo "Error fetching event details: " . mysqli_error($conn);
        exit();
    }

    // Update the event table with the approval message
    $updateQuery = "UPDATE event SET $signColumn = '$approvalMessage' WHERE eventID = $eventID";
    $updateResult = mysqli_query($conn, $updateQuery);

    if ($updateResult) {
        // Fetch the recipient's email based on session ID
        $emailQuery = "SELECT email FROM employeeuser WHERE id = $employeeID";
        $emailResult = mysqli_query($conn, $emailQuery);

        if ($emailResult && mysqli_num_rows($emailResult) > 0) {
            $row = mysqli_fetch_assoc($emailResult);
            $recipientEmail = $row['email'];

            // Send notification email
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();                                            // Set mailer to use SMTP
                $mail->Host       = 'smtp.gmail.com';                       // Specify main and backup SMTP servers
                $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                $mail->Username   = 'notifications.aduevent@gmail.com';     // SMTP username
                $mail->Password   = 'mylh wdkv ufqt lncq';                  // SMTP password (use an app password if 2FA is enabled)
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption, `PHPMailer::ENCRYPTION_SMTPS` also accepted
                $mail->Port       = 587;                                    // TCP port to connect to

                //Recipients
                $mail->setFrom('notifications.aduevent@gmail.com', 'AdUEvent Notifications');
                $mail->addAddress($recipientEmail);                         // Add a recipient

                // Content
                $mail->isHTML(true);                                        // Set email format to HTML
                $mail->Subject = 'Event Approval Notification';
                $mail->Body    = "Hello $projectLeadName,<br><br>
                                 This is to notify you that you have approved an event submitted by <strong>$orgName</strong> titled <strong>$eventName</strong> on <strong>$timestamp</strong>.<br><br>
                                 If you have any further actions or changes to make, please log in to the system.<br><br>
                                 Thank you for your prompt action.<br><br>
                                 Best regards,<br>
                                 AdUEvent Team";
                $mail->AltBody = "Hello $projectLeadName,\n\n
                                 This is to notify you that you have approved an event submitted by $orgName titled $eventName on $timestamp.\n\n
                                 If you have any further actions or changes to make, please log in to the system.\n\n
                                 Thank you for your prompt action.\n\n
                                 Best regards,\n
                                 AdUEvent Team";

                $mail->send();
                echo 'Notification email has been sent';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error fetching recipient email: " . mysqli_error($conn);
        }

        header("Location: approverEventApproval.php");
        exit();
    } else {
        echo "Error updating event table: " . mysqli_error($conn);
    }
}
?>
