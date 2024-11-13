<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginStudent.php");
    exit;
}
include("dbcon.php");
$userId = $_SESSION['id'];
$userQuery = "
    SELECT 
        su.name, 
        su.email, 
        o.organizationLogo as profilePicture
    FROM 
        studentuser su
    JOIN 
        organization o 
    ON 
        su.organizationID = o.organizationID
    WHERE 
        su.id = ?";

// Prepare and execute the statement
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);  // Corrected to bind "i" (integer) for one variable
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc(); // Fetch once and store the result

// Extract the data
$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture'];
$organizationID = $_SESSION['organizationID'];

$query = "SELECT event.eventID, event.eventTitle, event.eventProposalDate, 
                 organization.organizationName, organization.organizationLogo, 
                 organization.organizationTypeID, event.eventStatus, event.pointSystemCategoryID, 
                 event.eventVenueCategory, event.sponsorship, event.ticketSelling, event.registrationFee, 
                 event.adviserSign, event.sdsSign, event.osaSign, event.chairpersonSign, event.deanSign, 
                 event.icesSign, event.ministrySign, event.vpsaSign, event.vpfaSign 
          FROM event 
          INNER JOIN organization ON event.organizationID = organization.organizationID 
          WHERE organization.organizationID = '$organizationID'
          ORDER BY eventStatus"; // Order by eventStatus to group events by status

$result = mysqli_query($conn, $query);

$events = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSO Feed</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <?php include 'rsoNavbar.php';
    $activePage = "rsoEventHub"; ?>
    <style>
        .event {
            background-color: #F9F9f9;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .event-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
            flex-grow: 1;
        }

        .event-title {
            font-weight: bold;
            font-size: 1.5em;
            color: #000000;
            margin: 0;
            text-align: left;
        }

        .event-status {
            font-size: 0.9em;
            margin: 5px 0;
            text-align: left;
        }

        .event-status.approved {
            color: #008000; /* Green */
        }

        .event-status.rejected {
            color: #FF0000; /* Red */
        }

        .progress-bar {
            width: 50%;
            height: 7px;
            background-color: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            margin-top: 5px;
        }

        .progress-bar-fill {
            height: 100%;
            background-color: #99cc00;
            border-radius: 5px;
            transition: width 0.3s ease;
        }

        .button-group {
            display: flex;
            gap: 10px;
        }

        .btn-circle {
            width: 40px;
            height: 40px;
            padding: 8px;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            color: #FFFFFF;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-blue {
            background-color: #02248A;
        }

        .btn-gray {
            background-color: #6c757d;
        }

        .btn-red {
            background-color: #FF0000;
        }

        .btn-green {
            background-color: #28a745; /* Bootstrap's default green */
        }

        .btn-circle:hover {
            opacity: 0.8;
        }

        /* Custom styles for the tabs */
        .nav-tabs .nav-link {
            font-weight: bold; /* Make tab text bold */
            color: navy; /* Set tab text color to navy blue */
        }

        .nav-tabs .nav-link.active {
            background-color: #F9F9f9; /* Set background color of active tab to grey */
            color: black; /* Optionally set active tab text color to white for contrast */
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container event-container" style="margin-left: 5px;">
        <!-- Tab navigation -->
        <ul class="nav nav-tabs" id="eventTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="approved-tab" data-bs-toggle="tab" href="#approved" role="tab">Approved Events</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="pending-tab" data-bs-toggle="tab" href="#pending" role="tab">Pending Approval</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="unapproved-tab" data-bs-toggle="tab" href="#unapproved" role="tab">Unapproved Events</a>
            </li>
        </ul>
        
        <!-- Tab content -->
        <div class="tab-content" id="eventTabsContent">
            <div class="tab-pane fade show active" id="approved" role="tabpanel">
                <?php
                foreach ($events as $row) {
                    if ($row['eventStatus'] == 1) { 
                        // Display the event
                        echo '<div class="event">';  
                        echo '<div class="event-info">';
                        echo '<p class="event-title">' . $row["eventTitle"] . '</p>';
                        echo '<p class="event-status approved">Approval Complete</p>';
                        echo '</div>'; // Close event-info

                        echo '<div class="button-group">';
                        echo '<a href="rsoRegistration.php?eventID=' . $row["eventID"] . '" class="btn btn-circle btn-blue"><i class="bi bi-eye"></i></a>';
                        echo '<a href="rsoEditPromotion.php?eventID=' . $row["eventID"] . '" class="btn btn-circle btn-gray"><i class="bi bi-pencil"></i></a>';
                        echo '<a href="rsoFeedbackFormCreation.php?eventID=' . $row["eventID"] . '" class="btn btn-circle btn-green"><i class="bi bi-chat-text"></i></a>';
                        echo '</div>'; // Close button-group
                        echo '</div>'; // Close event
                    }
                }
                ?>
            </div>

            <div class="tab-pane fade" id="pending" role="tabpanel">
                <?php
                foreach ($events as $row) {
                    if ($row['eventStatus'] == 0) { // Pending Approval
                        $requiredSignatures = ['adviserSign', 'sdsSign', 'osaSign'];
                        $signedCount = 0;

                        if ($row['organizationTypeID'] == 1) {
                            $requiredSignatures[] = 'chairpersonSign';
                            $requiredSignatures[] = 'deanSign';
                        }

                        if ($row['pointSystemCategoryID'] == 2) {
                            $requiredSignatures[] = 'icesSign';
                        } elseif ($row['pointSystemCategoryID'] == 3) {
                            $requiredSignatures[] = 'ministrySign';
                        }

                        if ($row['eventVenueCategory'] == 2) {
                            $requiredSignatures[] = 'vpsaSign';
                        }

                        if (!is_null($row['sponsorship']) || !is_null($row['ticketSelling']) || !is_null($row['registrationFee'])) {
                            $requiredSignatures[] = 'vpfaSign';
                        }

                        foreach ($requiredSignatures as $signature) {
                            if (!is_null($row[$signature])) {
                                $signedCount++;
                            }
                        }

                        $progressPercentage = ($signedCount / count($requiredSignatures)) * 100;


                        // Display the event
                        echo '<div class="event">';  
                        echo '<div class="event-info">';
                        echo '<p class="event-title">' . $row["eventTitle"] . '</p>';
                        echo '<p class="event-status">Date Submitted: ' . $row["eventProposalDate"] . '</p>';
                            echo '<div class="progress-bar"><div class="progress-bar-fill" style="width: ' . $progressPercentage . '%;"></div></div>';
                        echo '</div>'; // Close event-info

                        echo '<div class="button-group">';
                        echo '<a href="rsoDocumentViewing.php?eventID=' . $row["eventID"] . '" class="btn btn-circle btn-blue">';
                        echo '<i class="bi bi-eye"></i>';
                        echo '</a>';
                        echo '</div>'; // Close button-group
                        echo '</div>'; // Close event
                    }
                }
                ?>
            </div>

            <div class="tab-pane fade" id="unapproved" role="tabpanel">
                <?php
                foreach ($events as $row) {
                    if ($row['eventStatus'] == 2) {
                        // Display the event
                        echo '<div class="event">';  
                        echo '<div class="event-info">';
                        echo '<p class="event-title">' . $row["eventTitle"] . '</p>';
                        echo '<p class="event-status unapproved">Unapproved</p>';
                        echo '</div>'; // Close event-info

                        echo '<div class="button-group">';
                        echo '<a href="rsoEditProposal.php?eventID=' . $row["eventID"] . '" class="btn btn-circle btn-gray">';
                        echo '<i class="bi bi-pencil"></i>';
                        echo '</a>';
                        echo '</div>'; // Close button-group
                        echo '</div>'; // Close event
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>
</body>
</html>
