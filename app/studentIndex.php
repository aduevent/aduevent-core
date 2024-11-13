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
$query = "SELECT eventID, eventTitle, organizationName, organizationLogo FROM event INNER JOIN organization ON event.organizationID = organization.organizationID WHERE eventStatus = '1'";
$result = mysqli_query($conn,$query);      

$query = "SELECT organizationID, organizationName FROM organization";
$result = mysqli_query($conn, $query);

if (!$result) {
    die('Error fetching organizations: ' . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student feed</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <?php include 'studentNavbar.php';
    $activePage = "studentIndex.php";?>
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
        .organization-container {
            padding: 20px;
        }
        .organization {
            width: 250px;
            margin: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            overflow: wrap;
        }
        .organization-logo {
            width: 100%;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .organization-details {
            padding: 10px;
            color: #333333;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: auto;
            box-sizing: border-box;
        }
        .organization-name {
            font-weight: bold;
            text-align: center;
            color: #333333;
            width: 250px;
            text-wrap: balance;
            margin: 0;
        }
        .view-details-button {
            background-color: #02248A;
            color: #FFFFFF;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .grading-button{
            background-color: #66FF00;
            color: #FFFFFF;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .event-photo {
            height: 150px; /* Fixed height */
            width: 100%; /* Full width */
            object-fit: cover; /* Ensure the photo fits the rectangle */
            z-index: 1;
        }
        .card-title, .card-text {
            margin-bottom: 5px; /* Minimal margin to reduce space */
            white-space: nowrap; /* Prevent wrapping */
            overflow: hidden;
            text-overflow: ellipsis; /* Add ellipsis for overflow */
        }
        .btn-circle {
            width: 40px;
            height: 40px;
            padding: 6px 0;
            border-radius: 50%;
            text-align: center;
            font-size: 18px;
            line-height: 1.42857;
        }
        .table-container {
            max-height: 400px; /* Set a maximum height to make the table scrollable */
            overflow-y: auto;  /* Enable vertical scrolling */
            border: 1px solid #ccc; /* Light gray border */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Add a subtle shadow */
            margin-top: 20px; /* Space above the table */
        }
        .table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        .table th, .table td {
            font-size: 12px; 
            padding: 8px; 
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
            outline: none; /* Remove focus outline */
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="d-flex justify-content-center" style="margin-bottom: 15px;">
        <div class="input-group w-75" style="max-width: 600px;">
            <form action="studentSearchResult.php" method="GET" class="d-flex w-100">
                <input type="text" class="form-control search-bar" name="query" placeholder="Search for events or organizations..." style="flex-grow: 1;">
                <button class="btn search-btn" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>
    <div class="container">
        <?php
            $currentMonth = date('F');
            $currentYear = date('Y');
        ?>
        <div class="row mb-5" style="margin-left: 5px;">
            <div class="col">
                <h5>Here is the line-up of events for <?php echo $currentMonth . ', ' . $currentYear; ?> you might be interested in</h5>
                    <div id="eventsCarousel" class="carousel slide" data-bs-ride="carousel">
                    <button class="carousel-control-prev" type="button" data-bs-target="#eventsCarousel" data-bs-slide="prev" style="background-color: #000080; width: 40px; height: 40px; border-radius: 50%; top: 50%;">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
                        <div class="carousel-inner">
                            <?php
                                $monthNumber = date('m'); // Month as a number
                                $eventQuery = "SELECT e.eventID, e.eventTitle, e.eventDate, e.eventPhoto, o.organizationName 
                                    FROM event e JOIN organization o ON e.organizationID = o.organizationID 
                                    WHERE MONTH(e.eventDate) = ? AND YEAR(e.eventDate) = ? AND eventStatus = '1'
                                    ORDER BY e.eventDate ASC";
                                $stmt = $conn->prepare($eventQuery);
                                $stmt->bind_param("ii", $monthNumber, $currentYear);
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
                                                <form action="studentEventDetails.php" method="GET">
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
</div>
<h5><?php echo $userName; ?>, don’t miss out! See what our RSOs are up to this semester!</h5>
<div class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th scope="col">Organization Name</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($organization = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($organization['organizationName']); ?></td>
                        <td>
                            <form action="studentRsoList.php" method="GET" style="display: inline;">
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