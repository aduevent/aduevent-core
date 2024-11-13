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

$currentMonth = date('n');
$semester = ($currentMonth >= 8 && $currentMonth <= 12) ? '1st' : '2nd';
$currentYear = date('Y');
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
    $activePage = "rsoIndex"; ?>
    <style>
        .card {
            position: relative;
            overflow: hidden;
            z-index: 0;
            border: none;
            align-items: center;
            text-align:center;
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); 
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 3;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .card:hover .overlay {
            opacity: 1;
        }
        .btn-primary {
            margin: 0;
            padding: 10px 20px;
            background-color: #000080;
            border-radius: 50px;
            border: none;
        }
        .event-photo {
            height: 150px;
            width: 100%;
            object-fit: cover;
            z-index: 1;
        }
        .card-title, .card-text {
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .table-container {
            max-height: 400px;
            overflow-y: auto; 
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
            margin-right: 30px;
        }
        .table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }
        .table th {
            background-color: #f0f0f0; /* Light gray background for header */
            font-size: 14px;            /* Medium font size */
            font-weight: bold;          /* Bold text */
            padding: 10px;              /* Padding for header cells */
            text-align: left;           /* Align header text to the left */
            border-bottom: 2px solid #ccc; /* Bottom border for header */
            position: sticky;           /* Sticky header when scrolling */
            top: 0;                     /* Sticky position */
            z-index: 10;                /* Ensure header stays on top */
        }
        .table td {
            font-size: 12px;           /* Smaller font size for rows */
            padding: 10px;             /* Padding for table cells */
            border-top: 1px solid #ccc; /* Top border for rows */
            text-align: left;          /* Align text to the left */
            overflow: hidden;          /* Hide overflowing content */
            text-overflow: ellipsis;   /* Show ellipsis for long content */
            white-space: nowrap;       /* Prevent text wrapping */
        }
        .table th:nth-child(1), .table td:nth-child(1) { 
            width: 15%; /* Date column */
        }
        .table th:nth-child(2), .table td:nth-child(2) {
            width: 50%; /* Event Title column */
        }
        .table th:nth-child(3), .table td:nth-child(3) {
            width: 20%; /* Category column */
        }
        .table th:nth-child(4), .table td:nth-child(4) {
            width: 15%; /* Action column */
            text-align: center; /* Center align for buttons */
        }
        .table tbody tr:nth-child(odd) {
            background-color: #f9f9f9; /* Light gray for odd rows */
        }
        .table tbody tr:nth-child(even) {
            background-color: #ffffff;
        }
        .table tbody tr:hover {
            background-color: #e0e0e0;
        }
        .btn:not(.btn-primary) {
            color: #000080 !important;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
        }
        .btn:focus {
            outline: none;
        }
        .fa-eye {
            font-size: 16px;
        }
        .search-bar {
            border: none;
            background-color: #dcdcdc;
            border-top-left-radius;
            border-bottom-left-radius: 50px;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .search-bar:focus {
            box-shadow: none;
        }
        .search-btn {
            background-color: #dcdcdc;
            color: #02248A;
            border-top-right-radius: 50px;
            border-bottom-right-radius: 50px;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            padding: 8px;
        }
</style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
<div class="d-flex justify-content-center" style="margin-bottom: 15px;">
            <div class="input-group w-75" style="max-width: 600px;">
            <form action="rsoSearchResult.php" method="GET" class="d-flex w-100">
                <input type="text" class="form-control search-bar" name="query" placeholder="Search for events or organizations..." style="flex-grow: 1;">
                <button class="btn search-btn" type="submit"><i class="bi bi-search"></i></button>
            </form>
            </div>
        </div>
        <div class="row mb-5" style="margin-left: 5px;">
    <div class="col">
        <h5>Here is your organization's lineup of events for the <?php echo $semester ?> semester of <?php echo $currentYear?>.</h5>
        <div id="eventsCarousel" class="carousel slide" data-bs-ride="carousel">
                    <button class="carousel-control-prev" type="button" data-bs-target="#eventsCarousel" data-bs-slide="prev" style="background-color: #000080; width: 40px; height: 40px; border-radius: 50%; top: 50%;">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
<div class="carousel-inner">
                <?php
                $currentYear = date('Y');
                $currentMonth = date('n');
                
                // Determine semester
                if ($currentMonth >= 1 && $currentMonth <= 7) {
                    $semester = '2nd';
                    $semesterStartMonth = 1; // January
                    $semesterEndMonth = 7;   // July
                    $yearForSemester = $currentYear; // Still in the same academic year
                } else {
                    $semester = '1st';
                    $semesterStartMonth = 8;  // August
                    $semesterEndMonth = 12;   // December
                    $yearForSemester = $currentYear; // First semester of current academic year
                }
                
                // Adjust academic year based on semester
                $defaultAcademicYear = ($semester === '1st') ? $currentYear . '-' . ($currentYear + 1) : ($currentYear - 1) . '-' . $currentYear;
                
                // Set start and end dates based on the current semester
                $academicYearStartDate = $yearForSemester . '-' . str_pad($semesterStartMonth, 2, '0', STR_PAD_LEFT) . '-01';
                $academicYearEndDate = $yearForSemester . '-' . str_pad($semesterEndMonth, 2, '0', STR_PAD_LEFT) . '-31';
                
                // SQL query to fetch events based on the semester
                $eventQuery = "SELECT e.eventID, e.eventTitle, e.eventDate, e.eventPhoto, o.organizationName 
                               FROM event e 
                               JOIN organization o ON e.organizationID = o.organizationID 
                               WHERE MONTH(e.eventDate) BETWEEN ? AND ? 
                               AND YEAR(e.eventDate) = ? 
                               AND eventStatus = '1' 
                               AND e.organizationID = ? 
                               ORDER BY e.eventDate ASC";
                               
                $stmt = $conn->prepare($eventQuery);
                $stmt->bind_param("iiii", $semesterStartMonth, $semesterEndMonth, $yearForSemester, $orgId);
                $stmt->execute();
                $eventResult = $stmt->get_result();
                

                if ($eventResult->num_rows > 0) {
                    $activeClass = 'active';
                    $counter = 0;
                    while ($eventRow = $eventResult->fetch_assoc()) {
                        if ($counter % 3 == 0) {
                            echo '<div class="carousel-item ' . $activeClass . '">';
                            echo '<div class="row">';
                            $activeClass = '';
                        }
?>
                        <div class="col-md-4">
                                    <div class="card">
                                        <img src="<?php echo $eventRow["eventPhoto"]; ?>" class="card-img-top img-fluid event-photo" alt="Event Image">
                                            <div class="card-body p-2">
                                                <h5 class="card-title text-truncate mb-1"><?php echo $eventRow["eventTitle"]; ?></h5>
                                                <p class="card-text text-truncate mb-1"><?php echo date('F d, Y', strtotime($eventRow["eventDate"])); ?></p>
                                                <p class="card-text text-truncate mb-1"><small class="text-muted"><?php echo $eventRow["organizationName"]; ?></small></p>
                                            </div>
                                            <div class="overlay">
                                                <form action="rsoEventPerformance.php" method="GET">
                                                    <input type="hidden" name="eventID" value="<?php echo $eventRow["eventID"]; ?>">
                                                    <button type="submit" class="btn btn-primary">View</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>                        
                    <?php
                        $counter++;
                            if ($counter % 3 == 0 || $counter == $eventResult->num_rows) {
                                echo '</div>';
                                echo '</div>';
                        }
                    }
                } else {
                    echo '<div class="carousel-item active">';
                    echo '<div class="d-flex justify-content-center align-items-center" style="height: 200px;">';
                    echo '<h5>No Event Coming Up this Month</h5>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
            <button class="carousel-control-next" type="button" data-bs-target="#eventsCarousel" data-bs-slide="next" style="background-color: #000080;width: 40px; height: 40px; border-radius: 50%; top: 50%;">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span></button>
        </div>
    </div>
<?php
$archiveQuery = "
    SELECT e.eventID, e.eventTitle, e.eventDate, e.pointSystemCategoryID, o.organizationName
    FROM event e
    JOIN organization o ON e.organizationID = o.organizationID
    WHERE e.eventDate < CURDATE() 
    AND e.organizationID = ?
    ORDER BY e.pointSystemCategoryID, e.eventDate DESC";

$stmt = $conn->prepare($archiveQuery);
$stmt->bind_param("i", $orgId);
$stmt->execute();
$archiveResult = $stmt->get_result();
?>
<h5 class="mt-5"><?php echo $userData['organizationName']; ?>'s Events Archive</h5>

<div class="table-container" style="margin-left: 15px;">
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Event Title</th>
                <th>Category</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch and display archived events based on categories
            $categories = [
                1 => 'Organizational Related',
                2 => 'Community Involvement',
                3 => 'Spiritual Enrichment',
                4 => 'Environmental',
                5 => 'Organizational Development'
            ];

            while ($event = $archiveResult->fetch_assoc()) {
                $categoryID = $event['pointSystemCategoryID'];
                $categoryName = isset($categories[$categoryID]) ? $categories[$categoryID] : 'Unknown';
                ?>
                <tr>
                    <td><?php echo date('F d, Y', strtotime($event['eventDate'])); ?></td>
                    <td style="width: 60%;"><?php echo $event['eventTitle']; ?></td>
                    <td><?php echo $categoryName; ?></td>
                    <td>
                        <form action="rsoEventPerformance.php" method="GET">
                            <input type="hidden" name="eventID" value="<?php echo $event['eventID']; ?>">
                            <button type="submit" class="btn">
                                <i class="fa fa-eye"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</div>

</div>
