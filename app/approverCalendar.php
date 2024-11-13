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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdUEvent</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <?php include 'approverNavbar.php';
    $activePage = "approverCalendar"; ?>
    <style>
        .event-preview {
            background-color: #F9F9F9;
            border-radius: 15px;
            padding: 10px;
            margin-bottom: 20px;
            display: flex;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        .event-date-container {
            flex: 1;
            text-align: center;
            background-color: #000080; 
            color: #ffffff;
            border-top-left-radius: 15px;
            border-bottom-left-radius: 15px;
            padding: 0;
            max-width: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .event-date-content {
            padding: 5px;
        }
        .event-date-month {
            font-size: 14px;
        }
        .event-date-day {
            font-size: 12px;
        }
        .event-details {
            flex: 2;
            padding-left: 20px;
        }
        .organization-name {
            margin-bottom: 5px;
        }
        .button-column {
            flex: 1;
            text-align: right;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container">
    <ul class="list-inline" style="margin-bottom: 10px;">
        <li class="list-inline-item">
            <button onclick="window.location.href='approverIndex.php';" 
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
            <button onclick="window.location.href='approverCalendarView.php';"
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
                    echo '<form action="approverDetailViewing.php" method="post">';
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
