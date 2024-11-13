<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginEmployee.php");
    exit;
}

include("dbcon.php");
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
$organizationID = $userData['organizationID'];
include("approverNavbar.php");
$activePage = "approverIndex";

// Determine semester based on the current month
$currentMonth = date('n');
$semester = ($currentMonth >= 8 && $currentMonth <= 12) ? '1st' : '2nd';
$currentYear = date('Y');

// Fetch the organization name if access is 3, 4, or 7
$organizationName = '';
if (in_array($access, [3, 4, 7])) {
    $orgQuery = "SELECT o.organizationName FROM organization o WHERE o.organizationID = ?";
    $orgStmt = $conn->prepare($orgQuery);
    $orgStmt->bind_param("i", $organizationID);
    $orgStmt->execute();
    $orgResult = $orgStmt->get_result();
    if ($orgResult->num_rows > 0) {
        $orgData = $orgResult->fetch_assoc();
        $organizationName = $orgData['organizationName'];
    }
}

// Define greeting message based on access level
$greetingMessage = "";
$eventQuery = "SELECT eventID, eventTitle, organizationName, organizationLogo, eventPhoto, eventDate 
               FROM event 
               INNER JOIN organization ON event.organizationID = organization.organizationID 
               WHERE eventStatus = '1' AND YEAR(eventDate) = ?";

switch ($access) {
    case 3:
    case 4:
    case 7:
        $greetingMessage = "Here is the lineup of approved events by $organizationName for the $semester semester of $currentYear.";
        $eventQuery .= " AND event.organizationID = ?";
        $params = [$currentYear, $organizationID];
        break;
    case 9:
        $greetingMessage = "Here is the lineup of Community Involved Events for the $semester semester of $currentYear.";
        $eventQuery .= " AND event.pointSystemCategoryID = 2";
        $params = [$currentYear];
        break;
    case 10:
        $greetingMessage = "Here is the lineup of Spiritual Enrichment events for the $semester semester of $currentYear.";
        $eventQuery .= " AND event.pointSystemCategoryID = 3";
        $params = [$currentYear];
        break;
    case 11:
        $greetingMessage = "Here is the lineup of off-campus events for the $semester semester of $currentYear.";
        $eventQuery .= " AND event.eventVenueCategory = 2";
        $params = [$currentYear];
        break;
    case 12:
        $greetingMessage = "Here is the lineup of sponsored events for the $semester semester of $currentYear.";
        $eventQuery .= " AND (event.ticketSelling IS NOT NULL OR event.sponsorship IS NOT NULL OR event.registrationFee IS NOT NULL)";
        $params = [$currentYear];
        break;
    default:
        $greetingMessage = "Here is the lineup of events for the $semester semester of $currentYear.";
        $params = [$currentYear];
        break;
}

// Execute the event query
$eventStmt = $conn->prepare($eventQuery);
$eventStmt->bind_param(str_repeat('i', count($params)), ...$params);
$eventStmt->execute();
$eventResult = $eventStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Feed</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
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
        .event {
            width: 100%;
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
        .organization-logo {
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            width: 100%;
            height: auto;
        }
        .event-details {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 60%;
            background-color: rgba(128, 128, 128, 0.8);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }
        .event-name {
            font-weight: bold;
            text-align: center;
            color: #FFFFFF;
            margin-bottom: 5px;
        }
        .view-details-button {
            background-color: #02248A;
            color: #FFFFFF;
            border: none;
            border-radius: 30px;
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 5px;
        }
        .view-details-button:hover {
            background-color: #0156B1;
        }
        .card {
            flex: 1 0 30%;
            margin: 0 10px;
        }
        .table-container {
            max-height: 400px;
            overflow-y: auto; 
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        .table th, .table td {
            font-size: 12px;
            padding: 5px;
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
        }
        .table th {
            background-color: #f0f0f0;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #d3d3d3;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
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
        .search-bar {
            border: none;
            background-color: #dcdcdc;
            border-top-left-radius: 50px;
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
<div class="container" style="margin-left: 5px;">
<div class="d-flex justify-content-center" style="margin-bottom: 10px;">
    <div class="input-group w-75" style="max-width: 600px;">
        <form action="approverSearchResult.php" method="GET" class="d-flex w-100">
            <input type="text" class="form-control search-bar" name="query" placeholder="Search for events or organizations..." style="flex-grow: 1;">
            <button class="btn search-btn" type="submit">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>
</div>
    <h5 class="mb-4"><?php echo $greetingMessage; ?></h5>
        <div id="eventCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
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
                                                <form action="approverDetailViewing.php" method="POST">
                                                    <input type="hidden" name="eventID" value="<?php echo $eventRow["eventID"]; ?>">
                                                    <button type="submit" class="btn btn-primary">View</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>                        
                    <?php
                        $counter++;
                        if ($counter % 3 == 0 || $counter == $eventResult->num_rows) {
                            echo '</div>'; // Close row
                            echo '</div>'; // Close carousel-item
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
            <button class="carousel-control-prev" type="button" data-bs-target="#eventCarousel" data-bs-slide="prev" style="background-color: #000080;width: 40px; height: 40px; border-radius: 50%; top: 50%;">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#eventCarousel" data-bs-slide="next" style="background-color: #000080;width: 40px; height: 40px; border-radius: 50%; top: 50%;">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    <h5 style="margin-top: 10px;"><?php echo $userName; ?>, don’t miss out! See what our RSOs are up to this semester!</h5>
        <div class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th scope="col">Organization Name</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $query = "SELECT organizationID, organizationName FROM organization";
                            $result = mysqli_query($conn, $query);
                            while ($organization = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($organization['organizationName']); ?></td>
                        <td>
                            <form action="approverRsoList.php" method="GET" style="display: inline;">
                                <input type="hidden" name="organizationID" value="<?= htmlspecialchars($organization['organizationID']); ?>">
                                <button type="submit" class="btn">
                                    <i class="fas fa-eye"></i> <!-- Font Awesome eye icon -->
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
    <script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
