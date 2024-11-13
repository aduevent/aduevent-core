<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginEmployee.php");
    exit;
}
include("dbcon.php");

if (isset($_GET['organizationID'])) {
    $organizationID = $_GET['organizationID'];
} else {
    header("Location: approverIndex.php");
    exit;
}

$orgQuery = "SELECT organizationName, organizationLogo FROM organization WHERE organizationID = ?";
$stmt = $conn->prepare($orgQuery);
$stmt->bind_param("i", $organizationID);
$stmt->execute();
$orgResult = $stmt->get_result();
$orgData = $orgResult->fetch_assoc();
$organizationName = $orgData['organizationName'];
$organizationLogo = $orgData['organizationLogo'];

$userId = $_SESSION['id'];
$userQuery = "SELECT name, email, profilePicture, userTypeID, organizationID FROM employeeuser WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture'];
$access = $userData['userTypeID'];

include("approverNavbar.php");
$activePage = "approverRsoList";

$eventQuery = "SELECT eventID, eventTitle, eventDate, eventVenueCategory FROM event WHERE organizationID = ? AND eventDate > CURDATE() AND eventStatus = 1";
$stmt = $conn->prepare($eventQuery);
$stmt->bind_param("i", $organizationID);
$stmt->execute();
$eventResult = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($organizationName); ?>'s Event Line Up</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <style>
        .organization-logo {
            max-width: 100px;
            border-radius: 100px;
            margin-right: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #ddd;
        }
        tr {
            height: 30px;
        }
        td:not(:first-child), th:not(:first-child) {
            text-align: center;
            vertical-align: middle;
        }
        td:first-child {
            text-align: left;
        }
        td:last-child {
            width: 10%;
        }
        .btn-circle {
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            background-color: #000080;
            color: white;
            border: none;
            font-size: 16px; /* Adjust icon size */
            cursor: pointer;
        }
        span[class^="venue-"] {
            padding: 5px 10px;
            border-radius: 15px;
            color: white;
            display: inline-block;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
        }
        .venue-on-campus {
            background-color: #000080;
        }
        .venue-off-campus {
            background-color: #28a745;
        }
        .venue-online {
            background-color: #6c757d;
        }
        .btn-circle i {
            margin: 0; /* Ensure the icon is centered */
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container" style="margin-left: 5px;">
    <ul class="list-inline" style="margin-bottom: 0;">
        <li class="list-inline-item">
            <button onclick="window.location.href='approverIndex.php';" 
                    class="btn btn-light d-flex justify-content-center align-items-center" 
                    style="width: 40px; height: 40px; border-radius: 50%; padding: 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #f9f9f9;">
                <i class="bi bi-arrow-left-circle" style="color: #000080; font-size: 20px;"></i>
            </button>
        </li>
        <li class="list-inline-item">
        <div class="d-flex justify-content-center align-items-center" style="margin-top: 10px;">
    <li class="list-inline-item"><img src="<?php echo $organizationLogo; ?>" alt="Organization Logo" class="organization-logo"></li>
    <li class="list-inline-item"><h2 class="organization-name"><?php echo $organizationName; ?>'s Event Line Up</h2></li>  
</div>
    </li>
    <ul>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Event Title & Schedule</th>
            <th>Event Venue</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($event = $eventResult->fetch_assoc()): ?>
        <tr>
            <td>
                <!-- Form wrapped around the event title -->
                <form action="approverDetailViewing.php" method="POST" style="display: inline;">
                    <input type="hidden" name="eventID" value="<?= htmlspecialchars($event['eventID']); ?>">
                    <button type="submit" style="background: none; border: none; color: #007bff; text-decoration: underline; padding: 0; cursor: pointer;">
                        <strong><?= htmlspecialchars($event['eventTitle']); ?></strong>
                    </button>
                </form>
                <span style="font-size: 12px; color: #555; display: block; margin-top: 0;"><?= htmlspecialchars(date('F d, Y', strtotime($event['eventDate']))); ?></span>
            </td>
            <td>
                <?php
                $venueClass = '';
                $venueText = '';
                switch ($event['eventVenueCategory']) {
                    case 1: $venueClass = 'venue-on-campus'; $venueText = "On-campus"; break;
                    case 2: $venueClass = 'venue-off-campus'; $venueText = "Off-campus"; break;
                    case 3: $venueClass = 'venue-online'; $venueText = "Online"; break;
                    default: $venueClass = 'venue-unknown'; $venueText = "Unknown"; break;
                }
                ?>
                <span class="<?= htmlspecialchars($venueClass); ?>"><?= htmlspecialchars($venueText); ?></span>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</body>
</html>
