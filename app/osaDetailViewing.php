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
$userData = $userResult->fetch_assoc(); // Fetch once and store the result
$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['eventID'])) {
        $eventID = $_POST['eventID'];
        $query = "SELECT eventID, eventTitle, organizationName, organizationLogo, eventDescription, eventPhoto, eventVenue, eventVenueCategory, eventDate, eventTimeStart FROM event INNER JOIN organization ON event.organizationID = organization.organizationID WHERE event.eventID = $eventID";
        $result = mysqli_query($conn, $query);

        if(mysqli_num_rows($result) > 0) {
            while ($eventDetails = mysqli_fetch_assoc($result)) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <?php include 'navbar.php';
    $activePage = "osaDetailViewing"; ?>
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
    padding-right: 20%; /* Content positioned on the dark side of the gradient */
    min-height: 640px; /* Increased minimum height to make the event-preview taller */
    z-index: 2; /* Ensures the preview content is above the overlay */
}

.event-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to right, black, var(--gradient-color), transparent);
    z-index: 1; /* Overlay is below the content */
    pointer-events: none; /* Prevents the overlay from blocking interactions */
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
    z-index: 0; /* Background is at the lowest layer */
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
    position: relative; /* Ensures content is above the overlay */
    z-index: 3; /* Higher than overlay to ensure visibility */
    color: white; /* Text color to stand out against the gradient */
}

.organization-logo {
    max-width: 40px;
    border-radius: 50%;
    margin-right: 3px;
    position: relative; /* Ensures content is above the overlay */
    z-index: 3; /* Higher than overlay */
}

.organization-name {
    font-size: 20px;
    color: white; /* White color for better contrast */
    text-align: center;
    position: relative; /* Ensures content is above the overlay */
    z-index: 3; /* Higher than overlay */
}

.event-description {
    font-size: 16px;
    margin: 10px 0;
    text-align: flex-start;
    position: relative; /* Ensures content is above the overlay */
    z-index: 3; /* Higher than overlay */
    color: white; /* Text color for visibility */
}

.event-details {
    display: flex;
    flex-direction: column; /* Positions items in a row */
    align-items: flex-start; /* Aligns items vertically in the center */
    gap: 20px; /* Adds space between the detail items */
    margin-left: 15px;
}

.detail-item-container {
    display: flex;
    flex-direction: row; /* Stacks label and rectangle vertically within each item */
    align-items: flex-start; /* Aligns label and rectangle to the left */
}

.detail-label {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 2px; /* Adds space between the label and the rounded rectangle */
    color: #fff; /* Label color */
    z-index: 3;
    margin-right: 6px;
    padding-top: 5px;
}

.rounded-rectangle {
    padding: 5px 10px;
    border-radius: 20px;
    text-align: center;
    position: relative; /* Ensures content is above the overlay */
    z-index: 3; /* Higher than overlay */
    max-width: 150px; /* Limits maximum width */
    border: 2px solid white; /* White border */
    box-sizing: border-box; /* Includes padding and border in total width */
}

.ul-container {
    display: flex;
    justify-content: center;
    position: relative; /* Ensures content is above the overlay */
    z-index: 3; /* Higher than overlay */
}

    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container" style="margin-left: 5px;">
    <?php
// Determine venue category display text
$venueCategory = $eventDetails["eventVenueCategory"];
$venueText = "";
if ($venueCategory == 1) {
    $venueText = "On Campus";
} elseif ($venueCategory == 2) {
    $venueText = "Off-Campus";
} elseif ($venueCategory == 3) {
    $venueText = "Online";
}

// Format event time to 12-hour format with AM/PM
$eventTimeFormatted = date("g:iA", strtotime($eventDetails["eventTimeStart"]));

echo '<div class="event-preview event-venue-' . $eventDetails['eventVenueCategory'] . '" style="padding-right: 20%;">';
echo '<div class="event-background" style="background-image: url(\'' . $eventDetails["eventPhoto"] . '\');"></div>';
echo '<div class="event-overlay"></div>';
?>
       <button onclick="window.history.back();" class="btn btn-light d-flex justify-content-center align-items-center" 
        style="width: 40px; height: 40px; border-radius: 50%; padding: 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #fff; position: relative; z-index: 3;">
    <i class="bi bi-arrow-left-circle" style="color: #d3d3d3; font-size: 20px;"></i>
</button>
<?php
echo '<div class="rounded-rectangle white-background text-center" style="margin-top: 5px;">' . htmlspecialchars($venueText) . '</div>';

// Event Title
echo '<div class="row justify-content-center" style="margin-top: 10px;">';
echo '<div class="col text-center">';
echo '<b class="event-title">' . htmlspecialchars($eventDetails["eventTitle"]) . '</b>';
echo '</div>';
echo '</div>';

// Organization Logo and Name
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

// Event Description
echo '<div class="row justify-content-center" style="margin-left: 5px; margin-bottom: 20px; margin-right: 15%;">';
echo '<div class="col">';
echo '<div class="text-left event-description">' . htmlspecialchars($eventDetails["eventDescription"]) . '</div>';
echo '</div>';
echo '</div>';

// Event Details: Venue and Time Start (Inlined)
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
