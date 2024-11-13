<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginStudent.php");
    exit;
}
include("dbcon.php");
$userId = $_SESSION['id'];
$userQuery = "
    SELECT su.name, su.email, su.organizationID, o.organizationName, o.organizationLogo as profilePicture
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
$orgId = $userData['organizationID'];

$currentYear = date('Y');
$currentMonth = date('n');

if ($currentMonth >= 1 && $currentMonth <= 7) {
    $defaultAcademicYear = ($currentYear - 1) . '-' . $currentYear;
} else {
    $defaultAcademicYear = $currentYear . '-' . ($currentYear + 1);
}

// Set the default start and end dates for the academic year
$academicYearStartDate = ($currentMonth >= 1 && $currentMonth <= 7) ? ($currentYear - 1) . '-08-01' : $currentYear . '-08-01';
$academicYearEndDate = ($currentMonth >= 1 && $currentMonth <= 7) ? $currentYear . '-07-31' : ($currentYear + 1) . '-07-31';

// If an academic year is selected from the dropdown, update the dates accordingly
if (isset($_GET['academicYear'])) {
    list($startYear, $endYear) = explode('-', $_GET['academicYear']);
    $academicYearStartDate = $startYear . '-08-01';
    $academicYearEndDate = $endYear . '-07-31';
    $defaultAcademicYear = $_GET['academicYear'];
}

// SQL query to fetch event data for organizationID = 12 within the selected academic year
$sql = "SELECT pointSystemCategoryID, COUNT(*) AS eventCount FROM event 
        WHERE organizationID = $orgId AND eventDate BETWEEN '$academicYearStartDate' AND '$academicYearEndDate' AND eventStatus = '1'
        GROUP BY pointSystemCategoryID";
$result = $conn->query($sql);

$eventCounts = array_fill(1, 5, 0);  // Initialize event counts for all categories
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $eventCounts[$row['pointSystemCategoryID']] = $row['eventCount'];
    }
}
$registrationSql = "SELECT e.eventID, e.eventTitle, COUNT(er.eventID) AS registrationCount 
                    FROM event e
                    LEFT JOIN eventregistration er ON e.eventID = er.eventID
                    WHERE e.organizationID = $orgId AND e.eventDate BETWEEN '$academicYearStartDate' AND '$academicYearEndDate' AND eventStatus = '1'
                    GROUP BY e.eventID, e.eventTitle
                    ORDER BY e.eventDate";
$registrationResult = $conn->query($registrationSql);
$eventTitles = [];
$registrationCounts = [];
if ($registrationResult) {
    while ($row = $registrationResult->fetch_assoc()) {
        $eventTitles[] = $row['eventTitle'];
        $registrationCounts[] = $row['registrationCount'];
    }
}

// Mapping Point System Categories to Labels and Colors
$categoryLabels = [
    1 => 'Organizational-Related',
    2 => 'Community Involvement',
    3 => 'Spiritual Enrichment',
    4 => 'Environmental',
    5 => 'Organizational Development'
];
$categoryColors = [
    1 => 'rgba(255, 99, 132, 0.5)',
    2 => 'rgba(54, 162, 235, 0.5)',
    3 => 'rgba(255, 206, 86, 0.5)',
    4 => 'rgba(75, 192, 192, 0.5)',
    5 => 'rgba(153, 102, 255, 0.5)'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include 'rsoNavbar.php';
    $activePage = "rsoDashboard"; ?>
    <style>
        .container {
            display: flex;
            justify-content: space-between;
        }
        .col-left, .col-right {
            width: 49%;
        }
        .donut-chart-container {
            width: 90%; /* Adjusted size for smaller donut */
            height: 49%;
            margin: auto;
            border-radius: 50px;
            margin-bottom: 15px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: center; /* Center horizontally */
            align-items: center;     /* Center vertically */
        }
        .line-chart-container {
            width: 90%; /* Adjusted size for smaller donut */
            height: 49%;
            margin: auto;
            border-radius: 50px;
            margin-bottom: 15px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: center; /* Center horizontally */
            align-items: center;     /* Center vertically */
        }
        .card {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
        }
        .card-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .comparison-circle {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .truncate-text {
            display: block;
            width: 150px; /* Adjust this value to control the max width */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
<div class="container">
    <div class="col-left">
        <div style="display: flex; justify-content: center; align-items: center; height: 100px;"> 
            <form method="GET" action="" style="display: inline-block;">
                <select name="academicYear" onchange="this.form.submit()" style="border-radius: 50px; box-shadow: 0px 4px 8px rgba(0,0,0,0.2); padding: 8px 12px; font-size: 14px; margin-bottom: 5px; border: 1px solid #ccc; background-color: #fff;">
                    <?php for ($year = 2018; $year <= $currentYear + 1; $year++): ?>
                    <?php $displayYear = $year . '-' . ($year + 1); ?>
                    <option value="<?= $displayYear ?>" <?= $displayYear == $defaultAcademicYear ? 'selected' : '' ?>>
                        <?= $displayYear ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
        <div class="donut-chart-container">
            <canvas id="donutChart"></canvas>
        </div>
        <div class="line-chart-container">
            <canvas id="lineChart"></canvas>
        </div>
    </div><div class="col-right">
        <h2>Event Details</h2>
        <div id="eventDetails">
            Click on a segment of the donut chart to see event details.
        </div>
    </div>
</div>
<script>
// Donut Chart Setup
var ctx = document.getElementById('donutChart').getContext('2d');
var donutChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_values($categoryLabels)) ?>,
        datasets: [{
            label: 'Event Count',
            data: <?= json_encode(array_values($eventCounts)) ?>,
            backgroundColor: <?= json_encode(array_values($categoryColors)) ?>,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            },
            title: {
                display: true,
                text: 'Event Distribution by Category'
            }
        },
        onClick: function (evt, item) {
            if (item.length > 0) {
                var index = item[0].index;  // Get the clicked segment index
                var category = Object.keys(<?= json_encode($categoryLabels) ?>)[index];  // Get the category ID
                showEventDetails(category);  // Call the function to show event details
            }
        }
    }
});

// Function to show event details for the selected category
function showEventDetails(categoryID) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "rsofetchEvents.php?categoryID=" + categoryID + "&academicYear=<?= $defaultAcademicYear ?>", true);
    xhr.onload = function () {
        if (this.status === 200) {
            document.getElementById('eventDetails').innerHTML = this.responseText;
        }
    };
    xhr.send();
}
function truncateTitle(title, maxLength) {
    return title.length > maxLength ? title.substring(0, maxLength) + '...' : title;
}

// Get truncated event titles
var truncatedEventTitles = <?= json_encode($eventTitles) ?>.map(function(title) {
    return truncateTitle(title, 15);  // Adjust '15' to control max title length
});

// Line Chart Setup with truncated titles
var lineCtx = document.getElementById('lineChart').getContext('2d');
var lineChart = new Chart(lineCtx, {
    type: 'line',
    data: {
        labels: truncatedEventTitles,  // Use the truncated titles
        datasets: [{
            label: 'Registrations',
            data: <?= json_encode($registrationCounts) ?>,
            borderColor: 'rgba(54, 162, 235, 1)',
            fill: false,
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Event Registration Counts'
            }
        },
        scales: {
            x: {
                ticks: {
                    callback: function(value, index, values) {
                        // Add a truncate class for long labels in the X-axis
                        return this.getLabelForValue(value).length > 15 ? truncateTitle(this.getLabelForValue(value), 15) : this.getLabelForValue(value);
                    }
                }
            }
        }
    }
});
</script>
</body>
</html>