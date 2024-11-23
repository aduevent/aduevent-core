<?php
session_start();

if (!isset($_SESSION["id"]) || !isset($_SESSION["access"])) {
    header("Location: loginEmployee.php");
    exit();
}

include "dbcon.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require "../vendor/autoload.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pin = $_POST["pin"];
    $employeeID = $_SESSION["id"];

    // Prepare and execute the query to fetch the hashed PIN from the database
    $query = "SELECT pin FROM employeeuser WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $employeeID);
    $stmt->execute();
    $stmt->bind_result($hashedPin);
    $stmt->fetch();
    $stmt->close();

    // Check if the entered PIN matches the hashed PIN from the database
    if (password_verify($pin, $hashedPin)) {
        // Proceed with event approval process if the PIN is correct
        if (isset($_POST["eventID"])) {
            $eventID = $_POST["eventID"];
            $projectLeadName = $_POST["projectLeadName"];

            // Get current timestamp
            $timestamp = date("Y-m-d H:i:s");

            // Prepare the approval message with the timestamp
            $approvalMessage = "Approved by $projectLeadName on $timestamp";

            // Fetch organization and event details
            $eventDetailsQuery = "SELECT e.eventTitle, o.organizationName
                                  FROM event e
                                  JOIN organization o ON e.organizationID = o.organizationID
                                  WHERE e.eventID = ?";
            $stmt = $conn->prepare($eventDetailsQuery);
            $stmt->bind_param("i", $eventID);
            $stmt->execute();
            $stmt->bind_result($eventName, $orgName);
            $stmt->fetch();
            $stmt->close();

            if (!$eventName || !$orgName) {
                echo "Error fetching event details.";
                exit();
            }

            $updateQuery =
                "UPDATE event SET osaSign = ?, eventStatus = 1 WHERE eventID = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("si", $approvalMessage, $eventID);
            $updateResult = $stmt->execute();
            $stmt->close();

            if ($updateResult) {
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
                        $mail->isSMTP(); // Set mailer to use SMTP
                        $mail->Host = "smtp.gmail.com"; // Specify main and backup SMTP servers
                        $mail->SMTPAuth = true; // Enable SMTP authentication
                        $mail->Username = "notifications.aduevent@gmail.com"; // SMTP username
                        $mail->Password = "mylh wdkv ufqt lncq"; // SMTP password (use an app password if 2FA is enabled)
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption, PHPMailer::ENCRYPTION_SMTPS also accepted
                        $mail->Port = 587; // TCP port to connect to

                        // Recipients
                        $mail->setFrom(
                            "notifications.aduevent@gmail.com",
                            "AdUEvent Notifications"
                        );
                        $mail->addAddress($recipientEmail); // Add a recipient

                        // Content
                        $mail->isHTML(true); // Set email format to HTML
                        $mail->Subject = "Event Approval Notification";
                        $mail->Body = "Hello $projectLeadName,<br><br>
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
                        // echo 'Notification email has been sent';
                        header("Location: approverEventApproval.php");
                        exit();
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                } else {
                    echo "Error fetching recipient email.";
                }

                header("Location: approverEventApproval.php");
                exit();
            } else {
                echo "Error updating event table.";
            }
        }
    } else {
        echo "<script>
                alert('Incorrect PIN. Please try again.');
                window.location.href = 'osaEventApproval.php';
            </script>";
        exit();
    }
} else {
    // Handle unauthorized access
    echo "Unauthorized access!";
    exit();
}
?>
