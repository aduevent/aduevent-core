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

$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'prev') {
        $month--;
        if ($month < 1) {
            $month = 12;
            $year--;
        }
    } elseif ($_GET['action'] === 'next') {
        $month++;
        if ($month > 12) {
            $month = 1;
            $year++;
        }
    }
}

// Fetch events from the database for the current month and year
$query = "SELECT eventTitle, eventDate FROM event WHERE eventStatus = 1 AND MONTH(eventDate) = '$month' AND YEAR(eventDate) = '$year'";
$result = mysqli_query($conn, $query);

$events = [];
while ($row = mysqli_fetch_assoc($result)) {
    $events[$row['eventDate']][] = $row['eventTitle']; // Group events by date
}

// Determine how many days are in the current month
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// First day of the current month (used for aligning the calendar)
$firstDayOfMonth = date('N', strtotime("$year-$month-01"));
$clickedDate = isset($_GET['date']) ? $_GET['date'] : null;

// Fetch notes for the selected date
$notesQuery = "SELECT noteContent FROM eventnotes WHERE noteDate = '$clickedDate' AND createdBy ='$userId' AND type ='2'";
$notesResult = mysqli_query($conn, $notesQuery);

$notes = [];
while ($row = mysqli_fetch_assoc($notesResult)) {
    $notes[] = $row;
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
    <?php include 'approverNavbar.php';
    $activePage = "approverCalendarView"; ?>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .contained {
            display: flex;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }
        .calendar-container {
            flex: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-right: 1px solid #ddd;
            padding-right: 20px;
        }
        .nav-buttons {
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 600px;
        }
        .nav-buttons a {
            text-decoration: none;
            color: #000;
            font-size: 24px;
        }
        table {
            width: 100%;
            max-width: 600px;
            border-collapse: collapse;
        }
        th {
            background-color: #000080;
            color: white;
            padding: 10px;
        }
        td {
            width: 14.28%; /* 7 columns for 7 days */
            height: 100px;
            vertical-align: top;
            padding: 5px;
            border: 1px solid #ddd;
        }
        td.event {
            background-color: #f0f8ff;
        }
        .event-title {
            background-color: #000080;
            color: white;
            padding: 2px;
            border-radius: 4px;
            font-size: 12px;
        }
        .notes-container {
            flex: 1;
            padding-left: 20px;
        }
        .notes-header {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .note-item {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 15px;
            margin-bottom: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
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
            <button onclick="window.location.href='approverCalendar.php';"
                    class="btn btn-secondary justify-content-center" 
                    style="width: 300px; height: 40px; border-radius: 50px; padding: 0 15px; border: 1px solid #808080; background-color: transparent;">
                <span style="color: #000000; font-weight: bold;">List View</span>
            </button>
        </li>
        <li class="list-inline-item"> 
            <button onclick="javascript:void(0);" 
                    class="btn btn-secondary justify-content-center" 
                    style="width: 300px; height: 40px; border-radius: 50px; padding: 0 15px; border: none; background-color: #f1f1f1;">
                <span style="color: #808080; font-weight: bold;">Calendar View</span>
            </button>
        </li>
    </ul>
    <div class="contained">
        <div class="calendar-container">
            <div class="nav-buttons">
                <a href="?month=<?php echo $month; ?>&year=<?php echo $year; ?>&action=prev">&#9664;</a>
                <span><?php echo date('F Y', strtotime("$year-$month-01")); ?></span>
                <a href="?month=<?php echo $month; ?>&year=<?php echo $year; ?>&action=next">&#9654;</a>
            </div>

            <table>
                <tr>
                    <th>Sun</th>
                    <th>Mon</th>
                    <th>Tue</th>
                    <th>Wed</th>
                    <th>Thu</th>
                    <th>Fri</th>
                    <th>Sat</th>
                </tr>
                <tr>
                    <?php
                    // Add empty cells before the first day of the month
                    for ($i = 1; $i < $firstDayOfMonth; $i++) {
                        echo "<td></td>";
                    }

                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $currentDate = "$year-$month-".str_pad($day, 2, '0', STR_PAD_LEFT); // Format the date as YYYY-MM-DD
                    
                        echo "<td";
                        
                        if (array_key_exists($currentDate, $events)) {
                            echo ' class="event"';
                        }
                    
                        echo ">";
                        echo "<a href='?date=$currentDate'>$day</a>"; // Link the date to the current page with the date parameter
                    
                        if (array_key_exists($currentDate, $events)) {
                            foreach ($events[$currentDate] as $eventTitle) {
                                echo "<div class='event-title'>$eventTitle</div>";
                            }
                        }
                    
                        echo "</td>";
                        
                        // Move to the next row after Saturday (7th day of the week)
                        if (($day + $firstDayOfMonth - 1) % 7 == 0) {
                            echo "</tr><tr>";
                        }
                    }

                    // Fill the remaining cells of the last week with empty cells
                    $remainingDays = (7 - ($daysInMonth + $firstDayOfMonth - 1) % 7) % 7;
                    for ($i = 0; $i < $remainingDays; $i++) {
                        echo "<td></td>";
                    }
                    ?>
                </tr>
            </table>
        </div>

        <!-- Notes Column -->
        <div class="notes-container">
    <div class="notes-header">Notes for <?php echo $clickedDate ? date('F j, Y', strtotime($clickedDate)) : 'Selected Date'; ?></div>
    
    <?php if (count($notes) > 0): ?>
        <?php foreach ($notes as $note): ?>
            <div class="note-item"><?php echo $note['noteContent']; ?></div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="note-item">No notes for this date.</div>
    <?php endif; ?>

    <!-- Add a Note Button -->
    <button style="background-color: #000080; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); color: white; border: none; border-radius: 50px; padding: 10px 20px;" onclick="addNote()">+ Add Note</button>
</div>
    </div>
    <script>
    function addNote() {
        let note = prompt("Enter your note:");
        if (note) {
            // Send the note to the server using AJAX or a form submission
            window.location.href = `approverAddNote.php?date=<?php echo $clickedDate; ?>&note=${encodeURIComponent(note)}`;
        }
    }
</script>
</body>
</html>
