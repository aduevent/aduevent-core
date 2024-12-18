<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginStudent.php");
    exit;
}
include("dbcon.php");

$userId = $_SESSION['id'];
$userQuery = "SELECT name, email, profilePicture FROM studentuser WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId); 
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture'];

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if(isset($_GET['eventID'])) {
        $eventID = $_GET['eventID'];
        $query = "SELECT eventID, eventTitle, organizationName, organizationLogo, eventDescription, eventPhoto, eventVenue, eventVenueCategory, eventDate, eventTimeStart, eventAccess, feedbackAccess FROM event INNER JOIN organization ON event.organizationID = organization.organizationID WHERE event.eventID = $eventID";
        $result = mysqli_query($conn, $query);
        if(mysqli_num_rows($result) > 0) {
            while ($eventDetails = mysqli_fetch_assoc($result)) {
                $eventAccess = $eventDetails['eventAccess'];
                $feedbackAccess = $eventDetails['feedbackAccess'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <?php include 'studentNavbar.php';
    $activePage = "studentEventDetails"; ?>
    <style>
        .event-preview {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 20px;
            margin-top: 5px;
            position: relative;
            overflow: hidden;
            color: white;
            padding-right: 20%;
            min-height: 640px;
            z-index: 2;
        }
        .event-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, black, var(--gradient-color), transparent);
            z-index: 1;
            pointer-events: none;
        }
        .event-venue-1 .event-overlay {
            --gradient-color: darkblue;
        }
        .event-venue-2 .event-overlay {
            --gradient-color: green;
        }
        .event-venue-3 .event-overlay {
            --gradient-color: gray;
        }
        .event-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            z-index: 0;
        }
        .event-photo {
            max-height: 150px;
            width: 100%;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
        }
        .event-title {
            font-size: 32px;
            margin-top: 3px;
            text-align: center;
            position: relative;
            z-index: 3;
            color: white;
        }
        .organization-logo {
            max-width: 40px;
            border-radius: 50%;
            margin-right: 3px;
            position: relative;
            z-index: 3;
        }
        .organization-name {
            font-size: 20px;
            color: white;
            text-align: center;
            position: relative;
            z-index: 3;
        }
        .event-description {
            font-size: 16px;
            margin: 10px 0;
            text-align: flex-start;
            position: relative;
            z-index: 3;
            color: white;
        }
        .event-details {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 20px;
            margin-left: 15px;
        }
        .detail-item-container {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
        }
        .detail-label {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
            color: #fff;
            z-index: 3;
            margin-right: 6px;
            padding-top: 5px;
        }
        .rounded-rectangle {
            padding: 5px 10px;
            border-radius: 20px;
            text-align: center;
            position: relative;
            z-index: 3;
            max-width: 150px;
            border: 2px solid white;
            box-sizing: border-box;
        }
        .ul-container {
            display: flex;
            justify-content: center;
            position: relative;
            z-index: 3;
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container" style="margin-left: 5px;">
    <?php
        $venueCategory = $eventDetails["eventVenueCategory"];
        $venueText = "";
        if ($venueCategory == 1) {
            $venueText = "On Campus";
        } elseif ($venueCategory == 2) {
            $venueText = "Off-Campus";
        } elseif ($venueCategory == 3) {
            $venueText = "Online";
        }
        $eventTimeFormatted = date("g:iA", strtotime($eventDetails["eventTimeStart"]));
        echo '<div class="event-preview event-venue-' . $eventDetails['eventVenueCategory'] . '" style="padding-right: 20%;">';
        echo '<div class="event-background" style="background-image: url(\'' . $eventDetails["eventPhoto"] . '\');"></div>';
        echo '<div class="event-overlay"></div>';
    ?>
    <ul class="list-inline" style="margin-bottom: 0;">
    <li class="list-inline-item">
        <button onclick="window.history.back();" class="btn btn-light d-flex justify-content-center align-items-center" 
                style="width: 40px; height: 40px; border-radius: 50%; padding: 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #fff; position: relative; z-index: 3;">
            <i class="bi bi-arrow-left-circle" style="color: #d3d3d3; font-size: 20px;"></i>
        </button>
    </li>
    <?php if ($eventAccess == 1): ?>
        <li class="list-inline-item">
            <button onclick="window.location.href='studentRegister.php?eventID=<?= htmlspecialchars($eventID); ?>';"
                    class="btn btn-secondary d-flex justify-content-center align-items-center" 
                    style="height: 40px; border-radius: 50px; padding: 0 15px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #000080; color: #fff; font-weight: bold; position: relative; z-index: 3;">Attend
            </button>
        </li>
    <?php endif; ?>
    <?php if ($feedbackAccess == 1): ?>
        <li class="list-inline-item">
            <button onclick="window.location.href='studentFeedback.php?eventID=<?= htmlspecialchars($eventID); ?>';"
                    class="btn btn-secondary d-flex justify-content-center align-items-center" 
                    style="height: 40px; border-radius: 50px; padding: 0 15px; border: none; background-color: #f1f1f1; color: #808080; font-weight: bold; position: relative; z-index: 3;">Feedback
            </button>
        </li>
    <?php endif; ?>
</ul>
<?php
echo '<div class="rounded-rectangle white-background text-center" style="margin-top: 5px;">' . htmlspecialchars($venueText) . '</div>';
echo '<div class="row justify-content-center" style="margin-top: 10px;">';
echo '<div class="col text-center">';
echo '<b class="event-title">' . htmlspecialchars($eventDetails["eventTitle"]) . '</b>';
echo '</div>';
echo '</div>';
echo '<div class="ul-container">';
echo '<ul class="list-inline align-items-center">';
echo '<li class="list-inline-item">';
echo '<img class="organization-logo" src="' . htmlspecialchars($eventDetails["organizationLogo"]) . '" alt="Organization Logo">';
echo '</li>';
echo '<li class="list-inline-item">';
echo '<p class="organization-name">' . htmlspecialchars($eventDetails["organizationName"]) . '</p>';
echo '</li>';
echo '</ul>';
echo '</div>';
echo '<div class="row justify-content-center" style="margin-left: 5px; margin-bottom: 20px; margin-right: 15%;">';
echo '<div class="col">';
echo '<div class="text-left event-description">' . htmlspecialchars($eventDetails["eventDescription"]) . '</div>';
echo '</div>';
echo '</div>';
echo '<div class="event-details">';
echo '<div class="detail-item-container">';
echo '<label class="detail-label">Venue:</label>';
echo '<div class="detail-item rounded-rectangle">' . htmlspecialchars($eventDetails["eventVenue"]) . '</div>';
echo '</div>';
echo '<div class="detail-item-container">';
echo '<label class="detail-label">Start Time:</label>';
echo '<div class="detail-item rounded-rectangle">' . $eventTimeFormatted . '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
?>
</div>
<script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>
</body>
</html>
<?php
            }
        } else {
            echo "Event not found or not approved.";
        }
    }
}
?>