<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginStudent.php");
    exit;
}

include("dbcon.php");
$userId = $_SESSION['id'];

// Fetch user information
$userQuery = "SELECT su.name, su.email, o.organizationLogo as profilePicture
    FROM studentuser su JOIN organization o ON su.organizationID = o.organizationID
    WHERE su.id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture'];
$currentMonth = date('F'); // Full month name
$currentYear = date('Y');  // Full year

if (!isset($_GET['eventID']) || empty($_GET['eventID'])) {
    header("Location: rsoIndex.php");
    exit();
}

$eventID = $_GET['eventID'];
$query = "SELECT eventTitle FROM event WHERE eventID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $eventID);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the event title from the result
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $eventTitle = $row['eventTitle'];
} else {
    echo "No event found with the specified ID.";
}

// Count registered students
$count_query = "SELECT COUNT(*) AS registered_students FROM eventregistration WHERE eventID = ?";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("i", $eventID);
$count_stmt->execute();
$count_stmt->bind_result($registered_students);
$count_stmt->fetch();
$count_stmt->close();

$registered_query = "SELECT studentNumber, name, attended, attendTimestamp, registrationTimestamp FROM eventregistration WHERE eventID = ?";
$registered_stmt = $conn->prepare($registered_query);
$registered_stmt->bind_param("i", $eventID);
$registered_stmt->execute();
$registered_result = $registered_stmt->get_result();

// Fetch attended students
$attended_query = "SELECT studentNumber, name FROM eventregistration WHERE eventID = ? AND attended = 1";
$attended_stmt = $conn->prepare($attended_query);
$attended_stmt->bind_param("i", $eventID);
$attended_stmt->execute();
$attended_result = $attended_stmt->get_result();
$attended_count = $attended_result->num_rows;

// Calculate percentage of attended
$attended_percentage = $registered_students > 0 ? ($attended_count / $registered_students) * 100 : 0;

// Fetch feedback for the event
$feedback_query = "
    SELECT 
        (
            COALESCE(NULLIF(rating1, ''), 0) + 
            COALESCE(NULLIF(rating2, ''), 0) + 
            COALESCE(NULLIF(rating3, ''), 0) + 
            COALESCE(NULLIF(rating4, ''), 0) + 
            COALESCE(NULLIF(rating5, ''), 0) + 
            COALESCE(NULLIF(rating6, ''), 0) + 
            COALESCE(NULLIF(rating7, ''), 0) + 
            COALESCE(NULLIF(rating8, ''), 0) + 
            COALESCE(NULLIF(rating9, ''), 0) + 
            COALESCE(NULLIF(rating10, ''), 0)
        ) / 
        NULLIF(
            (
                (rating1 IS NOT NULL) + 
                (rating2 IS NOT NULL) + 
                (rating3 IS NOT NULL) + 
                (rating4 IS NOT NULL) + 
                (rating5 IS NOT NULL) + 
                (rating6 IS NOT NULL) + 
                (rating7 IS NOT NULL) + 
                (rating8 IS NOT NULL) + 
                (rating9 IS NOT NULL) + 
                (rating10 IS NOT NULL)
            ), 0
        ) AS average_rating, 
        response
    FROM feedbackresponse 
    WHERE eventID = ?
";

$feedback_stmt = $conn->prepare($feedback_query);
$feedback_stmt->bind_param("i", $eventID);
$feedback_stmt->execute();
$feedback_result = $feedback_stmt->get_result();

// Initialize variables
$average_rating = 0;
$responses = [];

// Fetch the first row to get the average rating
if ($feedback = $feedback_result->fetch_assoc()) {
    $average_rating = isset($feedback['average_rating']) ? $feedback['average_rating'] : 0;

    // Fetch all responses
    do {
        if (!empty($feedback['response'])) {
            $responses[] = $feedback['response'];
        }
    } while ($feedback = $feedback_result->fetch_assoc());
}

// Display results
// Prepare the SQL query to count attendees
$attendee_count_query = "SELECT COUNT(*) AS attendee_count 
                         FROM eventregistration 
                         WHERE eventID = ? AND attended = 1";

// Prepare and execute the statement
$stmt = $conn->prepare($attendee_count_query);
$stmt->bind_param("i", $eventID); // Bind the eventID parameter
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Store the attendee count in a variable
$attendee_count = $row['attendee_count'];

$accessQuery = "SELECT eventAccess, feedbackAccess FROM event WHERE eventID = ?";
$accessStmt = $conn->prepare($accessQuery);
$accessStmt->bind_param("i", $eventID);
$accessStmt->execute();
$accessResult = $accessStmt->get_result();
$accessData = $accessResult->fetch_assoc();

$eventAccess = $accessData['eventAccess'];
$feedbackAccess = $accessData['feedbackAccess'];

// Check if a form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action == 'toggleAttendance') {
        // Toggle eventAccess
        $query = "UPDATE event SET eventAccess = IF(eventAccess IS NULL, 1, NULL) WHERE eventID = ?";
    } elseif ($action == 'toggleFeedback') {
        // Toggle feedbackAccess
        $query = "UPDATE event SET feedbackAccess = IF(feedbackAccess IS NULL, 1, NULL) WHERE eventID = ?";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $eventID);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Refresh the page to update button states
        header("Location: " . $_SERVER['PHP_SELF'] . "?eventID=" . $eventID);
        exit();
    } else {
        echo "Failed to update";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Registrations</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <?php include 'rsoNavbar.php'; 
    $activePage = "rsoEventPerformance"; ?>
    <style>
        .feedback-card {
            border: none;
            border-radius: 30px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .conversation-bubbles {
            padding: 10px;
        }
        .bubble {
            background-color: #f1f1f1; /* Light gray background for the bubble */
            border: 1px solid #ddd;    /* Border color for the bubble */
            border-radius: 30px;       /* Rounded corners for the bubble */
            padding: 10px 15px;        /* Padding inside the bubble */
            margin-bottom: 10px;       /* Space between bubbles */
            position: relative;        /* Position relative for the arrow */
            max-width: 80%;            /* Limit the width of the bubble */
            word-wrap: break-word;     /* Wrap text inside the bubble */
        }
        .bubble:before {
            content: "";
            position: absolute;
            bottom: 100%;
            left: 20px;
            border-width: 10px;
            border-style: solid;
            border-color: transparent transparent #f1f1f1 transparent;
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container" style="margin-left: 5px;">
        <ul class="list-inline" style="margin-bottom: 0;">
            <li class="list-inline-item">
                <button onclick="window.history.back();"
                        class="btn btn-light d-flex justify-content-center align-items-center" 
                        style="width: 40px; height: 40px; border-radius: 50%; padding: 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #f9f9f9;">
                    <i class="bi bi-arrow-left-circle" style="color: #000080; font-size: 20px;"></i>
                </button>
            </li>
            <li class="list-inline-item">
            <button onclick="window.location.href='rsoRegistration.php?eventID=<?= htmlspecialchars($eventID); ?>';"
                class="btn btn-secondary justify-content-center" 
                        style="width: 300px; height: 40px; border-radius: 50px; padding: 0 15px; border: 1px solid #808080; background-color: transparent;">
                    <span style="color: #000000; font-weight: bold;">Event Registration</span>
                </button>
            </li>
            <li class="list-inline-item"> 
            <button onclick="javascript:void(0);"
                class="btn btn-secondary justify-content-center" 
                        style="width: 300px; height: 40px; border-radius: 50px; padding: 0 15px; border: none; background-color: #f1f1f1;">
                    <span style="color: #808080; font-weight: bold;">Event Performance Tracking</span>
                </button>
            </li>
        </ul>
        <h2 class="text-center" style="margin-top: 20px;"><strong style="color: #000080"><?php echo $eventTitle ?></strong></h2>
        <div class="d-flex justify-content-center mb-3">
    <form action="" method="post" style="display:inline;">
        <input type="hidden" name="action" value="toggleAttendance">
        <button type="submit" class="btn <?php echo $eventAccess ? 'btn-danger' : 'btn-success'; ?>" style="margin: 3px; border-radius: 50px;">
            <?php echo $eventAccess ? 'Disable Attendance' : 'Enable Attendance'; ?>
        </button>
    </form>

    <!-- Form to enable/disable feedback -->
    <form action="" method="post" style="display:inline;">
        <input type="hidden" name="action" value="toggleFeedback">
        <button type="submit" class="btn <?php echo $feedbackAccess ? 'btn-danger' : 'btn-success'; ?>" style="margin: 3px; border-radius: 50px;">
            <?php echo $feedbackAccess ? 'Disable Feedback' : 'Enable Feedback'; ?>
        </button>
    </form>
</div><div class="row">
            <div class="col-md-6">
            <h4>
            Attended Students (<?php echo $attendee_count; ?>)
            <!-- Download Icon Link with eventID -->
            <a href="downloadXLSX.php?eventID=<?php echo $eventID; ?>" class="btn btn-link" title="Download Excel">
                <i class="fas fa-download"></i> <!-- FontAwesome Icon for download -->
            </a>
        </h4>
                <table class="table table-bordered";>
    <thead>
        <tr>
            <th>Student Number</th>
            <th>Name</th>
            <th>Attendance</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $registered_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['studentNumber']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td style="background-color: 
                    <?php 
                        if ($row['attended'] == 1) {
                            echo 'green'; // Set background to green if attended
                        } else {
                            echo 'gray'; // Set background to gray if not attended
                        } 
                    ?>;">
                    <?php
                        if ($row['attended'] == 1) {
                            echo $row['attendTimestamp']; // Show attendTimestamp if attended
                        } else {
                            echo $row['registrationTimestamp']; // Show registrationTimestamp if not attended
                        }
                    ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

            </div>

            <div class="col-md-6">
    <div class="row">
        <!-- First Row: Average Ratings and Attendee Count Side by Side -->
        <div class="col-md-6">
            <div class="feedback-card" style="background-color: #d3d3d3;">
                <h4 style="color: #000080;">Average Rating</h4>
                <p style="font-size: 30px; font-weight: bold; color: white;">
                    <span style="color: gold;">&#9733;</span> <!-- Yellow Star Icon -->
                    <?php echo number_format($average_rating, 2); ?> 
                    <span style="font-size: 14px; font-weight: normal; color: white;">/5</span> <!-- Smaller /5 -->
                </p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="feedback-card" style="background-color: #000080;">
                <h4 style="color: white;">Attendance Rate</h4>
                <p style="font-size: 30px; font-weight: bold; color: white;">
                    <?php echo number_format($attended_percentage, 1); ?>% <!-- Replace with actual attendee count variable -->
                </p>
            </div>
        </div>
    </div>
    
    <!-- Second Row: Comments -->
    <div class="feedback-card" style="text-align: left; overflow-y: auto; max-height: 300px">
    <h4>Feedback Comments</h4>
    <?php if (!empty($responses)) { ?>
        <div class="conversation-bubbles">
            <?php foreach ($responses as $response) { ?>
                <div class="bubble">
                    <?php echo htmlspecialchars($response); ?>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <p>No feedback comments available.</p>
    <?php } ?>
</div>

</body>
</html>
