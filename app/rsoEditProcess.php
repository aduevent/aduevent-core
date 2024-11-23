<?php
session_start();

if (!isset($_SESSION["id"]) || !isset($_SESSION["access"])) {
    header("Location: loginStudent.php");
    exit();
}

include "dbcon.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require "../vendor/autoload.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pin = $_POST["pin"];
    $studentID = $_SESSION["id"];

    // Prepare and execute the query to fetch the hashed PIN from the database
    $query = "SELECT pin FROM studentuser WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $studentID);
    $stmt->execute();
    $stmt->bind_result($hashedPin);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($pin, $hashedPin)) {
        if (isset($_POST["eventID"])) {
            $eventID = $_POST["eventID"];
            $projectLeadName = $_POST["projectLeadName"];

            // Capture the current timestamp
            $timestamp = date("Y-m-d H:i:s");

            // Prepare the approval message
            $approvalMessage = "Submitted by $projectLeadName on $timestamp";

            // Retrieve event and organization details
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

            // Check if event and organization details were successfully retrieved
            if (!$eventName || !$orgName) {
                echo "Error fetching event details.";
                exit();
            }

            // Fetch updated details from POST request
            $activityTitle = $_POST["activityTitle"];
            $eventCategory = $_POST["option2"];
            $proposedDate = $_POST["proposedDate"];
            $timeStart = $_POST["timeStart"];
            $timeEnd = $_POST["timeEnd"];
            $venue = $_POST["venue"];
            $venueType = $_POST["option3"];
            $participant = $_POST["participant"];
            $organizationPartner = $_POST["organizationPartner"];
            $orgFundAmount = $_POST["orgFundAmount"];
            $solShareAmount = $_POST["solShareAmount"];
            $regFeeAmount = $_POST["regFeeAmount"];
            $ausgSubAmount = $_POST["ausgSubAmount"];
            $sponsorValue = $_POST["sponsorValue"];
            $ticketSellingAmount = $_POST["ticketSellingAmount"];
            $controlNumber = $_POST["controlNumber"];
            $others = $_POST["othersValue"];
            $designation = $_POST["designation"];

            // Update event details and the approval message in leadSign
            $updateQuery = "UPDATE event
                            SET eventTitle = ?, pointSystemCategoryID = ?, eventVenue = ?, eventVenueCategory = ?,
                                eventDate = ?, eventTimeStart = ?, eventTimeEnd = ?, organizationFund = ?,
                                solidarityShare = ?, registrationFee = ?, ausgSubsidy = ?, sponsorship = ?,
                                ticketSelling = ?, ticketControlNumber = ?, others = ?, participantCount = ?,
                                partnerOrganization = ?,  designation = ?, leadSign = ?, adviserSign = NULL,
                    chairpersonSign = NULL, deanSign = NULL, icesSign = NULL, ministrySign = NULL,
                    vpsaSign = NULL, vpfaSign = NULL, sdsSign = NULL
                            WHERE eventID = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param(
                "sssssssssddddssssssi",
                $activityTitle,
                $eventCategory,
                $venue,
                $venueType,
                $proposedDate,
                $timeStart,
                $timeEnd,
                $orgFundAmount,
                $solShareAmount,
                $regFeeAmount,
                $ausgSubAmount,
                $sponsorValue,
                $ticketSellingAmount,
                $controlNumber,
                $others,
                $participant,
                $organizationPartner,
                $designation,
                $approvalMessage,
                $eventID
            );

            // Execute the update query
            if ($stmt->execute()) {
                echo "Event details updated successfully.";
            } else {
                echo "Error updating event details.";
            }
            $stmt->close();

            // Sending notification email
            $emailQuery = "SELECT email FROM studentuser WHERE id = ?";
            $stmt = $conn->prepare($emailQuery);
            $stmt->bind_param("i", $studentID);
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
                    $mail->Subject =
                        "Event Successful Modification Notification";
                    $mail->Body = "Hello $projectLeadName,<br><br>
                                     This is to notify you that an event submitted by <strong>$orgName</strong> titled <strong>$eventName</strong> on <strong>$timestamp</strong> has been successfully modified.<br><br>
                                     If you have any further actions or changes to make, please log in to the system.<br><br>
                                     Thank you for your prompt action.<br><br>
                                     Best regards,<br>
                                     AdUEvent Team";
                    $mail->AltBody = "Hello $projectLeadName,\n\n
                                     This is to notify you that an event submitted by $orgName titled $eventName on $timestamp has been successfully modified.\n\n
                                     If you have any further actions or changes to make, please log in to the system.\n\n
                                     Thank you for your prompt action.\n\n
                                     Best regards,\n
                                     AdUEvent Team";

                    $mail->send();
                    echo "Notification email has been sent";
                } catch (Exception $e) {
                    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                echo "Error fetching recipient email.";
            }

            header("Location: rsoEventHub.php");
            exit();
        }
    } else {
        echo "<script>
                alert('Incorrect PIN. Please try again.');
                window.location.href = 'osaEventHub.php';
            </script>";
        exit();
    }
} else {
    // Handle unauthorized access
    echo "Unauthorized access!";
    exit();
}
?>
