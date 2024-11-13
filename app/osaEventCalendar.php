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
    $activePage = "osaEventCalendar"; ?>
    <style>
        .event-preview {
            background-color: #f9f9f9;
            border-radius: 15px; /* Rounded corners */
            padding: 10px; /* Padding inside the container */
            margin-bottom: 20px; /* Spacing between event previews */
            display: flex; /* Display flex for horizontal alignment */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        .event-date-container {
            flex: 1; /* Take up one-third of the space */
            text-align: center; /* Center align */
            background-color: #000080; /* Dark blue background */
            color: #ffffff; /* White text color */
            border-top-left-radius: 15px; /* Rounded corner for top-left */
            border-bottom-left-radius: 15px; /* Rounded corner for bottom-left */
            padding: 0; /* Adjust padding as needed */
            max-width: 100px; /* Decrease width */
            display: flex; /* Use flexbox for content alignment */
            justify-content: center; /* Horizontally center content */
            align-items: center; /* Vertically center content */
        }
        .event-date-content {
            padding: 5px; /* Adjust padding as needed */
        }
        .event-date-month {
            font-size: 14px; /* Adjust font size */
        }
        .event-date-day {
            font-size: 12px; /* Adjust font size */
        }
        .event-details {
            flex: 2; /* Take up two-thirds of the space */
            padding-left: 20px; /* Add padding for space */
        }
        .organization-name {
            margin-bottom: 5px; /* Spacing between organization name and event title */
        }
        .button-column {
            flex: 1; /* Take up one-third of the space */
            text-align: right; /* Align button to the right */
            display: flex; /* Use flexbox for content alignment */
            justify-content: flex-end; /* Horizontally align to the end */
            align-items: center; /* Vertically center content */
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container">
    <ul class="list-inline" style="margin-bottom: 10px;">
        <li class="list-inline-item">
            <button onclick="window.location.href='osaIndex.php';" 
                    class="btn btn-light d-flex justify-content-center align-items-center" 
                    style="width: 40px; height: 40px; border-radius: 50%; padding: 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #f9f9f9;">
                <i class="bi bi-arrow-left-circle" style="color: #000080; font-size: 20px;"></i>
            </button>
        </li>
        <li class="list-inline-item"> 
            <button onclick="javascript:void(0);" 
                    class="btn btn-secondary justify-content-center" 
                    style="width: 300px; height: 40px; border-radius: 50px; padding: 0 15px; border: none; background-color: #f1f1f1;">
                <span style="color: #808080; font-weight: bold;">List View</span>
            </button>
        </li>
        <li class="list-inline-item">
            <button onclick="window.location.href='osaCalendarView.php';"
                    class="btn btn-secondary justify-content-center" 
                    style="width: 300px; height: 40px; border-radius: 50px; padding: 0 15px; border: 1px solid #808080; background-color: transparent;">
                <span style="color: #000000; font-weight: bold;">Calendar View</span>
            </button>
        </li>
    </ul>
        <?php
        $query = "SELECT eventID, eventTitle, eventDate, organizationName, eventVenueCategory FROM event INNER JOIN organization ON event.organizationID = organization.organizationID WHERE eventStatus = '1'ORDER BY eventDate ASC";
        $result = mysqli_query($conn, $query);
            if (!$result) {
                echo "Error fetching pending events: " . mysqli_error($conn);
            } else {
                while ($row = mysqli_fetch_assoc($result)) {
                    $eventID = $row['eventID'];
                    $eventTitle = $row['eventTitle'];
                    $eventDate = $row['eventDate'];
                    $organizationName = $row['organizationName'];
                    $eventVenueCategoryID = $row['eventVenueCategory'];

                    // Convert event venue category ID to text
                    $eventVenueCategory = '';
                    switch ($eventVenueCategoryID) {
                        case 1:
                            $eventVenueCategory = 'On-Campus';
                            break;
                        case 2:
                            $eventVenueCategory = 'Off-Campus';
                            break;
                        case 3:
                            $eventVenueCategory = 'Online';
                            break;
                        default:
                            $eventVenueCategory = 'Unknown';
                            break;
                    }

                    // Format event date
                    $eventDateFormatted = date('M', strtotime($eventDate)) . '<br>' . date('d', strtotime($eventDate)) . '<br>' . date('D', strtotime($eventDate));

                    echo '<div class="event-preview">';
                    echo '<div class="event-date-container">';
                    echo '<div class="event-date-content">';
                    echo '<div class="event-date-month">' . date('M', strtotime($eventDate)) . '</div>';
                    echo '<div class="event-date-day"><h3 style="margin-top: 2px; margin-bottom: 2px;">' . date('d', strtotime($eventDate)) . '</h3></div>';
                    echo '<div class="event-date-day">' . date('D', strtotime($eventDate)) . '</div>';
                    echo '</div>'; // event-date-content
                    echo '</div>'; // event-date-container
                    echo '<div class="event-details">';
                    // Organization Name
                    echo "<p class='organization-name'>$organizationName</p>";
                    // Event Title
                    echo "<h2><strong>$eventTitle</strong></h2>";
                    // Event Venue Category
                    echo "<p>Event Venue: <b>$eventVenueCategory</b></p>";
                    echo '</div>'; // event-details
                    echo '<div class="button-column">';
                    // Add your button here
                    echo '<form action="osaDetailViewing.php" method="post">';
                    echo "<input type='hidden' name='eventID' value='$eventID'>";
                    echo '<button type="submit" class="btn btn-primary" style="background-color: #000080; border-radius: 50px;">View Details</button>';
                    echo '</form>';
                    echo '</div>'; // button-column
                    echo '</div>'; // event-preview
                }
            }
        ?>
    </div>
    <script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>
</body>
</html>
