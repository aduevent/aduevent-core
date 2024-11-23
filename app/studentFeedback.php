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
require "./libraries/fpdf.php"; // Include FPDF library for PDF generation

$userId = $_SESSION["id"];
$feedbackSuccess = false;
$userQuery = "SELECT name, email, profilePicture FROM studentuser WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$userName = $userData["name"];
$email = $userData["email"];
$dp = $userData["profilePicture"];

if (isset($_GET["eventID"])) {
    $eventID = $_GET["eventID"];
    $feedbackQuery = "SELECT f.question1, f.question2, f.question3, f.question4, f.question5,
                         f.question6, f.question7, f.question8, f.question9, f.question10,
                         e.eventTitle
                  FROM feedback f
                  JOIN event e ON f.eventID = e.eventID
                  WHERE f.eventID = ?";
    $stmt = $conn->prepare($feedbackQuery);
    $stmt->bind_param("i", $eventID);
    $stmt->execute();
    $feedbackResult = $stmt->get_result();
    if ($feedbackResult->num_rows > 0) {
        $feedback = $feedbackResult->fetch_assoc(); // Fetch questions and eventTitle
        $eventTitle = $feedback["eventTitle"]; // Store the event title
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $eventID = $_POST["eventID"];
    $userID = $_POST["userID"];
    $response = !empty($_POST["response"]) ? $_POST["response"] : null;
    $ratings = [];

    for ($i = 1; $i <= 10; $i++) {
        $ratings[$i] = isset($_POST["rating"][$i])
            ? $_POST["rating"][$i]
            : null;
    }
    $query = "INSERT INTO feedbackresponse (eventID, id, rating1, rating2, rating3, rating4, rating5, rating6, rating7, rating8, rating9, rating10, response)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "iiissssssssss",
        $eventID,
        $userID,
        $ratings[1],
        $ratings[2],
        $ratings[3],
        $ratings[4],
        $ratings[5],
        $ratings[6],
        $ratings[7],
        $ratings[8],
        $ratings[9],
        $ratings[10],
        $response
    );

    if ($stmt->execute()) {
        $feedbackSuccess = true;

        // Fetch event details and organization info
        $eventQuery = "
            SELECT e.eventTitle, e.eventDate, e.eventVenue, e.organizationID,
                   o.organizationName, o.organizationLogo
            FROM event e
            JOIN organization o ON e.organizationID = o.organizationID
            WHERE e.eventID = ?";
        $eventStmt = $conn->prepare($eventQuery);
        $eventStmt->bind_param("i", $eventID);
        $eventStmt->execute();
        $eventStmt->bind_result(
            $eventTitle,
            $eventDate,
            $eventVenue,
            $organizationID,
            $organizationName,
            $organizationLogo
        );
        $eventStmt->fetch();
        $eventStmt->close();

        $emailQuery = "SELECT email, name FROM studentuser WHERE id = ?";
        $emailStmt = $conn->prepare($emailQuery);
        $emailStmt->bind_param("i", $userID);
        $emailStmt->execute();
        $emailStmt->bind_result($recipientEmail, $userName);
        $emailStmt->fetch();
        $emailStmt->close();

        class PDF extends FPDF
        {
            // Path to the logo image
            protected $watermarkImage = "adueventwatermark.png";
            protected $backgroundImage = "certificate.png";

            // Override header function to add background and watermark to each page
            function Header()
            {
                // Add background image first
                $this->Image($this->backgroundImage, 0, 0, 297, 210);

                // Get page dimensions
                $width = $this->GetPageWidth();
                $height = $this->GetPageHeight();

                // Set watermark size and position to be centered and half the page width
                $imageWidth = $width / 3;
                $imageHeight = $height / 2;
                $xPosition = ($width - $imageWidth) / 2;
                $yPosition = ($height - $imageHeight) / 2;

                // Add watermark image on top of the background
                $this->Image(
                    $this->watermarkImage,
                    $xPosition,
                    $yPosition,
                    $imageWidth,
                    $imageHeight
                );
            }
        }

        $pdf = new PDF("L", "mm", "A4");
        $pdf->SetMargins(20, 20, 20);
        $pdf->AddPage();
        //$backgroundPath = 'C:/xampp/htdocs/capstone/certificate.png';
        //$pdf->Image($backgroundPath, 0, 0, 297, 210); // Fill the page with the background

        // Define the yellow line margins based on measurement
        $yellowLineLeftMargin = 35; // Adjust based on measurement
        $yellowLineRightMargin = 35; // Adjust based on measurement
        $yellowLineTopMargin = 25; // Adjust based on measurement
        $yellowLineBottomMargin = 25; // Adjust based on measurement

        // Set margins based on yellow line area
        $pdf->SetMargins(
            $yellowLineLeftMargin,
            $yellowLineTopMargin,
            $yellowLineRightMargin
        );
        $pdf->SetAutoPageBreak(true, $yellowLineBottomMargin);

        // Set initial cursor position within the yellow area
        $pdf->SetXY($yellowLineLeftMargin, $yellowLineTopMargin + 5); // Adjust as needed

        // Now start adding content within the margins

        // Organization logo on the left
        $orgLogoSize = 30;
        $aduLogoPath = "./adu.png"; // Path to adu.png
        $pdf->Image(
            $organizationLogo,
            $yellowLineLeftMargin,
            $yellowLineTopMargin + 10,
            $orgLogoSize
        );

        // Adamson University text centered between the logos
        $pdf->SetFont("Arial", "B", 20);
        $pdf->SetXY($yellowLineLeftMargin, $yellowLineTopMargin + 15); // Adjust Y to align with logo height
        $pdf->Cell(
            297 - $yellowLineLeftMargin - $yellowLineRightMargin,
            10,
            "Adamson University",
            0,
            1,
            "C"
        );

        // ADU logo on the right
        $pdf->Image(
            $aduLogoPath,
            297 - $yellowLineRightMargin - $orgLogoSize,
            $yellowLineTopMargin + 10,
            $orgLogoSize
        );

        $pdf->Ln(20); // Space below logos and university name

        // Title section
        $pdf->SetFont("Arial", "B", 24);
        $pdf->Cell(0, 10, "Certificate of Participation", 0, 1, "C");
        $pdf->SetTextColor(232, 175, 12);
        $pdf->SetFont("Arial", "I", 16);
        $pdf->Cell(0, 10, "This certificate is awarded to", 0, 1, "C");

        // Participant's name
        $pdf->SetTextColor(25, 25, 112);
        $pdf->SetFont("Arial", "B", 28);
        $pdf->Cell(0, 20, $userName, 0, 1, "C"); // Display user's name

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont("Arial", "I", 16);
        $pdf->MultiCell(
            0,
            10,
            "for attending and participating in $eventTitle conducted by $organizationName on $eventDate at $eventVenue.\nGiven on $eventDate.",
            0,
            "C"
        );

        // Fetch President, Adviser, and Chairperson from event table
        $orgDetailsQuery =
            "SELECT president, adviser, chairperson FROM event WHERE eventID = ?";
        $orgDetailsStmt = $conn->prepare($orgDetailsQuery);
        $orgDetailsStmt->bind_param("i", $eventID);
        $orgDetailsStmt->execute();
        $orgDetailsStmt->bind_result($president, $adviser, $chairperson);
        $orgDetailsStmt->fetch();
        $orgDetailsStmt->close();

        $pdf->Ln(5); // Space before organization roles

        // Display President and Adviser

        $pdf->SetXY($yellowLineLeftMargin, $pdf->GetY() + 10); // Adjust Y position as needed

        if ($president || $adviser) {
            // President and Adviser Names in a row
            $pdf->SetFont("Arial", "B", 12);

            $cellWidth =
                (297 - $yellowLineLeftMargin - $yellowLineRightMargin) / 3;

            // President's Name
            $pdf->Cell($cellWidth, 5, $president, 0, 0, "C");
            $pdf->Cell($cellWidth, 5, "", 0, 0); // Spacer cell
            $pdf->Cell($cellWidth, 5, $adviser, 0, 1, "C"); // Adviser's Name

            // Titles below President and Adviser
            $pdf->SetFont("Arial", "I", 10);
            $pdf->Cell($cellWidth, 5, "Organization President", 0, 0, "C"); // President's Title
            $pdf->Cell($cellWidth, 5, "", 0, 0); // Spacer cell
            $pdf->Cell($cellWidth, 5, "Organization Adviser", 0, 1, "C"); // Adviser's Title
        }

        $pdf->Ln(5); // Space below President and Adviser section

        // Display Chairperson within the yellow line margins if present
        if ($chairperson) {
            $pdf->SetFont("Arial", "B", 12);

            // Center Chairperson Name within yellow line area
            $pdf->Cell(0, 5, $chairperson, 0, 1, "C");

            // Title for Chairperson
            $pdf->SetFont("Arial", "I", 10);
            $pdf->Cell(0, 5, "College Chairperson", 0, 1, "C");
        }

        // Save the certificate
        // $certificateFilePath = "certificates/" . $userName . "_certificate.pdf";
        // $pdf->Output($certificateFilePath, "F");

        // output the PDF data as a string instead
        // (note that under the hood, this is binary)
        $certificateData = $pdf->Output("", "S");

        // then define the file metadata
        $certificateFilename = $userName . "_certificate.pdf";
        $mimeType = "application/pdf";

        // then save the generated PDF file in the files table
        // (edit) removed it since there seems to be no need
        /*
        $save_pdf_stmt = $conn->prepare("
            INSERT INTO files (filename, data) VALUES (?, ?)
        ");
        $save_pdf_stmt->bind_param(
            "sb",
            $certificateFilename,
            $certificateData
        );

        if ($save_pdf_stmt->execute()) {
            echo "Failed to save the PDF File: " . $save_pdf_stmt->error;
            exit();
        }

        $save_pdf_stmt->close();
        */

        // removed eventTitle from condition, since for some reason,
        // some events have empty names
        if ($recipientEmail) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = "smtp.gmail.com";
                $mail->SMTPAuth = true;
                $mail->Username = "notifications.aduevent@gmail.com";
                $mail->Password = "mylh wdkv ufqt lncq";
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom(
                    "notifications.aduevent@gmail.com",
                    "AdUEvent Notifications"
                );
                $mail->addAddress($recipientEmail);

                $mail->isHTML(true);
                $mail->Subject =
                    "Certificate of Participation for " . $eventTitle;
                $mail->Body = "Hello $userName,<br><br>
                                  Thank you for participating in the event <strong>$eventTitle</strong>.<br>
                                  Please find your attached certificate.<br><br>
                                  Best regards,<br>
                                  AdUEvent Team";
                $mail->AltBody = "Hello $userName,\n\n
                                  Thank you for participating in the event $eventTitle.\n
                                  Please find your attached certificate.\n\n
                                  Best regards,\n
                                  AdUEvent Team";

                // Attach the PDF certificate
                // $mail->addAttachment($certificateFilePath);

                // we then attach the PDF certificate in base64
                // encoding, but this time we're reading from
                // memory instead of the local file system
                $mail->addStringAttachment(
                    $certificateData,
                    $certificateFilename,
                    "base64",
                    "application/pdf"
                );

                $mail->send();
                echo "<script>
    alert('Thank you! The email with the certificate has been sent.');
    window.location.href = 'studentIndex.php';
</script>";
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error fetching recipient email or event title.";
        }
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Feedback System</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <?php
    include "studentNavbar.php";
    $activePage = "studentFeedback";
    ?>
    <style>
        .survey-container {
            background-color: #ffffff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .rating {
            display: inline-flex;
            direction: rtl;
            font-size: 1.5em;
        }
        .rating input {
            display: none;
        }
        .rating label {
            color: #ddd;
            cursor: pointer;
        }
        .rating input:checked ~ label {
            color: #f5c518;
        }
        .rating label:hover,
        .rating label:hover ~ label {
            color: #f5c518;
        }
        .table-rating td {
            vertical-align: middle;
        }
        .table-rating .question {
            width: 80%;
        }
        .table-rating .rating-stars {
            width: 40%;
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container" style="margin-left: 5px;">
        <div class="survey-container">
        <a href="studentIndex.php" class="btn btn-link"><i class="bi bi-arrow-left"></i> Back</a>
            <h2><?php echo $eventTitle; ?></h2>
            <?php if ($feedbackSuccess) { ?>
                <div class="alert alert-success" role="alert">
                    Feedback submitted successfully!
                </div>
            <?php } ?>

            <!-- Feedback Form -->
            <?php if (isset($feedback)) { ?>
                <form method="POST" action="">
                    <table class="table table-rating">
                        <tbody>
                            <?php for ($i = 1; $i <= 10; $i++) {
                                $questionKey = "question" . $i;
                                if (!empty($feedback[$questionKey])) { ?>
                                    <tr>
                                        <td class="question"><?php echo $feedback[
                                            $questionKey
                                        ]; ?></td>
                                        <td class="rating-stars">
                                            <!-- Star Rating -->
                                            <div class="rating">
                                                <input type="radio" id="star5-q<?php echo $i; ?>" name="rating[<?php echo $i; ?>]" value="5">
                                                <label for="star5-q<?php echo $i; ?>" title="5 stars">&#9733;</label>

                                                <input type="radio" id="star4-q<?php echo $i; ?>" name="rating[<?php echo $i; ?>]" value="4">
                                                <label for="star4-q<?php echo $i; ?>" title="4 stars">&#9733;</label>

                                                <input type="radio" id="star3-q<?php echo $i; ?>" name="rating[<?php echo $i; ?>]" value="3">
                                                <label for="star3-q<?php echo $i; ?>" title="3 stars">&#9733;</label>

                                                <input type="radio" id="star2-q<?php echo $i; ?>" name="rating[<?php echo $i; ?>]" value="2">
                                                <label for="star2-q<?php echo $i; ?>" title="2 stars">&#9733;</label>

                                                <input type="radio" id="star1-q<?php echo $i; ?>" name="rating[<?php echo $i; ?>]" value="1">
                                                <label for="star1-q<?php echo $i; ?>" title="1 star">&#9733;</label>
                                            </div>
                                        </td>
                                    </tr>
                                <?php }
                            } ?>
                        </tbody>
                    </table>

                    <!-- Comment Input -->
                    <div class="form-group mb-4">
                        <label for="response">Additional Comments</label>
                        <textarea id="response" name="response" class="form-control" rows="4" placeholder="Leave your comment here (optional)"></textarea>
                    </div>

                    <!-- Hidden Fields -->
                    <input type="hidden" name="eventID" value="<?php echo $eventID; ?>">
                    <input type="hidden" name="userID" value="<?php echo $userId; ?>">

                    <button type="submit" class="btn btn-primary" style="border-radius: 50px; background-color: #000080; border: none">Submit</button>
                </form>
            <?php } ?>
        </div>
    </div>

    <script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>
</body>
</html>
