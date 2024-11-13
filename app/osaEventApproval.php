<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginEmployee.php");
    exit;
}
include("dbcon.php");
$userId = $_SESSION['id'];
$userQuery = "SELECT name, email, profilePicture FROM employeeuser WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);  // Corrected to bind "i" (integer) for one variable
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc(); // Fetch once and store the result
$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdUEvent</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <?php include 'navbar.php';
    $activePage = "osaEventApproval"; ?>
    <style>
        .event-preview {
            background-color: #F9F6EE; /* White background */
            border-radius: 15px; /* Rounded corners */
            padding: 20px; /* Padding inside the container */
            margin-bottom: 20px; /* Spacing between event previews */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .event-details {
            display: flex;
        }
        .info-column {
            width: 70%; /* Adjust as needed */
        }
        .button-column {
            width: 30%; /* Adjust as needed */
            text-align: right;
            display: block;
            margin: 0 auto;
        }
        .organization-name {
            margin-bottom: 5px; /* Spacing between organization name and event title */
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container">
        <h3 style="margin-left: 5px; padding-top: 5px;">Action Required</h3>
        <?php
        $query = "
        SELECT eventProposalDate, event.eventID, event.eventTitle, organization.organizationName 
        FROM event 
        INNER JOIN organization ON event.organizationID = organization.organizationID 
        WHERE 
            eventStatus = '0' 
            AND leadSign IS NOT NULL 
            AND adviserSign IS NOT NULL 
            AND sdsSign IS NOT NULL 
            AND (
                (organization.organizationID = 1 AND chairpersonSign IS NOT NULL AND deanSign IS NOT NULL) OR 
                (organization.organizationID != 1)
            )
            AND (
                (pointSystemCategoryID = 2 AND icesSign IS NOT NULL) OR 
                (pointSystemCategoryID = 3 AND ministrySign IS NOT NULL) OR
                (pointSystemCategoryID != 2 AND pointSystemCategoryID != 3)
            )
            AND (
                ((ticketSelling IS NOT NULL OR sponsorship IS NOT NULL OR registrationFee IS NOT NULL) AND vpfaSign IS NOT NULL) OR 
                (ticketSelling IS NULL AND sponsorship IS NULL AND registrationFee IS NULL)
            )
            AND (
                (eventVenueCategory = 2 AND vpsaSign IS NOT NULL) OR 
                (eventVenueCategory != 2)
            )
            AND osaSign IS NULL
        ";
        $result = mysqli_query($conn, $query);
           if (!$result) {
            echo "Error fetching pending events: " . mysqli_error($conn);
        } else {
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $eventID = $row['eventID'];
                    $eventTitle = $row['eventTitle'];
                    $organizationName = $row['organizationName'];
                    $date = $row['eventProposalDate'];

                    echo '<div class="event-preview">';
                    echo '<div class="event-details">';
                    echo '<div class="info-column">';
                    echo "<h2 style='margin: 0 0 5px 0;'><strong>$eventTitle</strong></h2>";
                    echo "<p class='organization-name'>$organizationName</p>";
                    echo "<p class='organization-name' style='font-style: italic;'>Submitted on: $date</p>";
                    echo '</div>';
                    echo '<div class="button-column">';
                    echo '<form action="osaDocumentViewing2.php" method="post">';
                    echo "<input type='hidden' name='eventID' value='$eventID'>";
                    echo '<button type="submit" class="btn btn-primary" style="background-color: #000080; border-radius: 50px;">View Proposal</button>';
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo "<p>You're all caught up!</p>";
            }
        }
    ?>
    </div>
    <script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>
</body>
</html>
