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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pin = $_POST['pin'];
    $employeeID = $_SESSION['id'];

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
        // Proceed with event rejection process if the PIN is correct
        if (isset($_POST['eventID'])) {
            $eventID = $_POST['eventID'];
            $projectLeadName = $_POST['projectLeadName'];

            // Get current timestamp
            $timestamp = date("Y-m-d H:i:s");

            // Prepare the rejection message with the timestamp
            $rejectionMessage = "Rejected by $projectLeadName on $timestamp";

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

            // Update the event table with the rejection message in osaSign and set eventStatus to 2
            $updateQuery = "UPDATE event 
                            SET osaSign = ?, eventStatus = 2 
                            WHERE eventID = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("si", $rejectionMessage, $eventID);
            $updateResult = $stmt->execute();
            $stmt->close();

            if ($updateResult) {
                // Fetch the recipient's email based on session ID
                $emailQuery = "SELECT email FROM employeeuser WHERE id = ?";
                $stmt = $conn->prepare($emailQuery);
                $stmt->bind_param("i", $employeeID);
                $stmt->execute();
                $stmt->bind_result($recipientEmail);
                $stmt->fetch();
                $stmt->close();

                if ($recipientEmail) {
                    // Send notification email
                    $mail = new PHPMailer(true);
                    try {
                        // Server settings
                        $mail->isSMTP();                                            // Set mailer to use SMTP
                        $mail->Host       = 'smtp.gmail.com';                       // Specify main and backup SMTP servers
                        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                        $mail->Username   = 'notifications.aduevent@gmail.com';     // SMTP username
                        $mail->Password   = 'mylh wdkv ufqt lncq';                  // SMTP password (use an app password if 2FA is enabled)
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption, PHPMailer::ENCRYPTION_SMTPS also accepted
                        $mail->Port       = 587;                                    // TCP port to connect to

                        // Recipients
                        $mail->setFrom('notifications.aduevent@gmail.com', 'AdUEvent Notifications');
                        $mail->addAddress($recipientEmail);                         // Add a recipient

                        // Content
                        $mail->isHTML(true);                                        // Set email format to HTML
                        $mail->Subject = 'Event Rejection Notification';
                        $mail->Body    = "Hello $projectLeadName,<br><br>
                                         This is to notify you that you rejected an event submitted by <strong>$orgName</strong> titled <strong>$eventName</strong> on <strong>$timestamp</strong>.<br><br>
                                         If you have any further actions or changes to make, please log in to the system.<br><br>
                                         Thank you for your understanding.<br><br>
                                         Best regards,<br>
                                         AdUEvent Team";
                        $mail->AltBody = "Hello $projectLeadName,\n\n
                                         This is to notify you that you rejected an event submitted by $orgName titled $eventName on $timestamp.\n\n
                                         If you have any further actions or changes to make, please log in to the system.\n\n
                                         Thank you for your understanding.\n\n
                                         Best regards,\n
                                         AdUEvent Team";

                        $mail->send();
                        echo 'Rejection notification email has been sent';
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                } else {
                    echo "Error fetching recipient email.";
                }

                header("Location: osaEventApproval.php");
                exit();
            } else {
                echo "Error updating event table.";
            }
        }
    } else {
        // PIN is incorrect, show an alert and go back to the previous page
        echo "<script>
                alert('Incorrect PIN. Please try again.');
                window.location.href = 'osaEventApproval.php'; // Redirects to osaEventApproval.php
            </script>";
        exit();
    }
} else {
    // Handle unauthorized access
    echo "Unauthorized access!";
    exit();
}
?>
