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
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture'];
include("approverNavbar.php");
$activePage = "approverEventApproval";

$access = $_SESSION['access'];
$userId = $_SESSION['id'];
$signatureColumn = '';
switch ($access) {
    case 3:  $signatureColumn = 'adviserSign'; break;
    case 4:  $signatureColumn = 'chairpersonSign'; break;
    case 7:  $signatureColumn = 'deanSign'; break;
    case 8:  $signatureColumn = 'sdsSign'; break;
    case 9:  $signatureColumn = 'icesSign'; break;
    case 10: $signatureColumn = 'ministrySign'; break;
    case 11: $signatureColumn = 'vpsaSign'; break;
    case 12: $signatureColumn = 'vpfaSign'; break;
    default: break;
}
$query = "SELECT DISTINCT event.eventID, event.eventTitle, event.eventProposalDate, 
                 organization.organizationName, organization.organizationTypeID, event.eventStatus, event.pointSystemCategoryID, 
                 event.eventVenueCategory, event.sponsorship, event.ticketSelling, event.registrationFee, 
                 event.adviserSign, event.sdsSign, event.osaSign, event.chairpersonSign, event.deanSign, 
                 event.icesSign, event.ministrySign, event.vpsaSign, event.vpfaSign  
          FROM event 
          INNER JOIN organization ON event.organizationID = organization.organizationID 
          INNER JOIN employeeuser ON event.organizationID = employeeuser.organizationID 
          WHERE eventStatus = '0' AND leadSign IS NOT NULL";

$additionalConditions = "";

switch ($access) { 
    case 3:
        $additionalConditions = " AND event.organizationID = (SELECT organizationID FROM employeeuser WHERE id = '$userId') ";
        break;
    case 4:
    case 7:
        $additionalConditions .= " AND event.organizationID = (SELECT organizationID FROM employeeuser WHERE id = '$userId') AND adviserSign IS NOT NULL AND event.organizationTypeID = '1' ";
        break;
    case 8:
        $additionalConditions .= " AND adviserSign IS NOT NULL ";
        $additionalConditions .= " AND (pointSystemCategoryID != '2' OR (pointSystemCategoryID = '2' AND icesSign IS NOT NULL)) ";
        $additionalConditions .= " AND (pointSystemCategoryID != '3' OR (pointSystemCategoryID = '3' AND ministrySign IS NOT NULL)) ";
        $additionalConditions .= " AND (event.organizationTypeID != '1' OR (chairpersonSign IS NOT NULL AND deanSign IS NOT NULL)) ";
        break;
    case 9:
        $additionalConditions .= " AND adviserSign IS NOT NULL AND pointSystemCategoryID = '2' ";
        $additionalConditions .= " AND (event.organizationTypeID != '1' OR (event.organizationTypeID = '1' AND chairpersonSign IS NOT NULL AND deanSign IS NOT NULL)) ";
        break;
    case 10:
        $additionalConditions .= " AND adviserSign IS NOT NULL AND pointSystemCategoryID = '3' ";
        $additionalConditions .= " AND (event.organizationTypeID != '1' OR (event.organizationTypeID = '1' AND chairpersonSign IS NOT NULL AND deanSign IS NOT NULL)) ";
        break;
    case 11:
        $additionalConditions .= " AND adviserSign IS NOT NULL AND eventVenueCategory = '2' ";
        $additionalConditions .= " AND (pointSystemCategoryID != '2' OR (pointSystemCategoryID = '2' AND icesSign IS NOT NULL)) ";
        $additionalConditions .= " AND (pointSystemCategoryID != '3' OR (pointSystemCategoryID = '3' AND ministrySign IS NOT NULL)) ";
        $additionalConditions .= " AND (sdsSign IS NOT NULL) ";
        break;
    case 12:
        $additionalConditions .= " AND adviserSign IS NOT NULL AND (ticketSelling IS NOT NULL OR sponsorship IS NOT NULL OR registrationFee IS NOT NULL) ";
        $additionalConditions .= " AND (pointSystemCategoryID != '2' OR (pointSystemCategoryID = '2' AND icesSign IS NOT NULL)) ";
        $additionalConditions .= " AND (pointSystemCategoryID != '3' OR (pointSystemCategoryID = '3' AND ministrySign IS NOT NULL)) ";
        $additionalConditions .= " AND (sdsSign IS NOT NULL) ";
        break;
    default:
        break;
}

$query .= $additionalConditions;
$query .= " AND osaSign IS NULL";

$result = mysqli_query($conn, $query);
$events = mysqli_fetch_all($result, MYSQLI_ASSOC);

if (!$result) {
    echo "Error fetching pending events: " . mysqli_error($conn);
}

$actionRequiredEvents = [];
$pendingFinalApprovalEvents = [];

foreach ($events as $event) {
    if (is_null($event[$signatureColumn])) {
        $actionRequiredEvents[] = $event;
    } else {
        $pendingFinalApprovalEvents[] = $event;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdUEvent</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        .event {
            background-color: #f9f9f9;
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
            background-color: #28a745;
        }
        .btn-circle:hover {
            opacity: 0.8;
        }
        .nav-tabs .nav-link {
            font-weight: bold; /* Make tab text bold */
            color: navy; /* Set tab text color to navy blue */
        }

        .nav-tabs .nav-link.active {
            background-color: #f9f9f9; /* Set background color of active tab to grey */
            color: black; /* Optionally set active tab text color to white for contrast */
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
<div class="container" style="margin-left: 5px;">
    <ul class="nav nav-tabs" id="eventTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="action-required-tab" data-toggle="tab" href="#action-required" role="tab" aria-controls="action-required" aria-selected="true">Action Required</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="pending-approval-tab" data-toggle="tab" href="#pending-approval" role="tab" aria-controls="pending-approval" aria-selected="false">Pending Approval</a>
        </li>
    </ul>
    <div class="tab-content" id="eventTabContent">
        <!-- Action Required Tab -->
        <div class="tab-pane fade show active" id="action-required" role="tabpanel" aria-labelledby="action-required-tab">
            <div class="row">
                <div class="col-12">
                <?php
                if (!empty($actionRequiredEvents)) {
                    foreach ($actionRequiredEvents as $row) {
                        $requiredSignatures = ['adviserSign', 'sdsSign', 'osaSign'];
                        $signedCount = 0;

                        if ($row['organizationTypeID'] == 1) {
                            $requiredSignatures[] = 'chairpersonSign';
                            $requiredSignatures[] = 'deanSign';
                        }
                        
                        // Point System Category Signatures
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
                        $totalRequiredSignatures = count($requiredSignatures);
                        $signedPercentage = ($signedCount / $totalRequiredSignatures) * 100;
                        ?>
                        <div class="event">
                            <div class="event-info">
                                <h4 class="event-title"><?php echo $row['eventTitle']; ?></h4>
                                <p class="event-status"><?php echo "Organization: " . $row['organizationName']; ?></p>
                                <p class="event-status"><?php echo "Submitted on: " . $row['eventProposalDate']; ?></p>
                                <div class="progress-bar">
                                    <div class="progress-bar-fill" style="width: <?php echo $signedPercentage; ?>%;"></div>
                                </div>
                            </div>
                            <div class="button-group">
                                <form action="approverDocumentViewing.php" method="POST">
                                    <input type="hidden" name="eventID" value="<?php echo $row['eventID']; ?>">
                                    <input type="hidden" name="organizationID" value="<?php echo $row['organizationTypeID']; ?>">
                                    <input type="hidden" name="organizationTypeID" value="<?php echo $row['organizationTypeID']; ?>">
                                    <input type="hidden" name="pointSystemCategoryID" value="<?php echo $row['pointSystemCategoryID']; ?>">
                                    <input type="hidden" name="eventVenueCategory" value="<?php echo $row['eventVenueCategory']; ?>">
                                    <input type="hidden" name="sponsorship" value="<?php echo $row['sponsorship']; ?>">
                                    <input type="hidden" name="ticketSelling" value="<?php echo $row['ticketSelling']; ?>">
                                    <input type="hidden" name="registrationFee" value="<?php echo $row['registrationFee']; ?>">
                                    <input type="hidden" name="eventProposalDate" value="<?php echo $row['eventProposalDate']; ?>">
                                    <input type="hidden" name="accessLevel" value="<?php echo $access; ?>">
                                    <input type="hidden" name="employeeID" value="<?php echo $userId; ?>">
                                    <button type="submit" class="btn-circle btn-blue"><i class="fas fa-eye"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                <?php 
            } else { ?>
                <p>No events requiring your action at this time.</p>
            <?php } ?>
        </div>
    </div>
</div>
    <div class="tab-pane fade" id="pending-approval" role="tabpanel" aria-labelledby="pending-approval-tab">
            <div class="row">
                <div class="col-12">
                <?php
                if (!empty($pendingFinalApprovalEvents)) {
                    foreach ($pendingFinalApprovalEvents as $row) {
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

                        $totalRequiredSignatures = count($requiredSignatures);
                        $signedPercentage = ($signedCount / $totalRequiredSignatures) * 100;
                        ?>
                        <div class="event">
                            <div class="event-info">
                                <h4 class="event-title"><?php echo $row['eventTitle']; ?></h4>
                                <p class="event-status"><?php echo "Organization: " . $row['organizationName']; ?></p>
                                <p class="event-status"><?php echo "Submitted on: " . $row['eventProposalDate']; ?></p>
                                <div class="progress-bar">
                                    <div class="progress-bar-fill" style="width: <?php echo $signedPercentage; ?>%;"></div>
                                </div>
                            </div>
                            <div class="button-group">
                                <form action="approverDocumentViewing.php" method="POST">
                                    <input type="hidden" name="eventID" value="<?php echo $row['eventID']; ?>">
                                    <input type="hidden" name="organizationID" value="<?php echo $row['organizationTypeID']; ?>">
                                    <input type="hidden" name="organizationTypeID" value="<?php echo $row['organizationTypeID']; ?>">
                                    <input type="hidden" name="pointSystemCategoryID" value="<?php echo $row['pointSystemCategoryID']; ?>">
                                    <input type="hidden" name="eventVenueCategory" value="<?php echo $row['eventVenueCategory']; ?>">
                                    <input type="hidden" name="sponsorship" value="<?php echo $row['sponsorship']; ?>">
                                    <input type="hidden" name="ticketSelling" value="<?php echo $row['ticketSelling']; ?>">
                                    <input type="hidden" name="registrationFee" value="<?php echo $row['registrationFee']; ?>">
                                    <input type="hidden" name="eventProposalDate" value="<?php echo $row['eventProposalDate']; ?>">
                                    <input type="hidden" name="accessLevel" value="<?php echo $access; ?>">
                                    <input type="hidden" name="employeeID" value="<?php echo $userId; ?>">
                                    <button type="submit" class="btn-circle btn-blue"><i class="fas fa-eye"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p>No events pending final approval at this time.</p>
                <?php } ?>
            </div>
        </div>
    </div>
    <script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
