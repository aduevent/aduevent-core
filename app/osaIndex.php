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
$activePage = "osaIndex";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OSA Homepage</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <?php include 'navbar.php'; ?>
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
        .btn-circle {
            width: 40px;
            height: 40px;
            padding: 6px 0;
            border-radius: 50%;
            text-align: center;
            font-size: 18px;
            line-height: 1.42857;
        }.search-bar {
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
    <div class="container">
        <div class="d-flex justify-content-center" style="margin-bottom: 15px;">
            <div class="input-group w-75" style="max-width: 600px;">
            <form action="osaSearchResult.php" method="GET" class="d-flex w-100">
                <input type="text" class="form-control search-bar" name="query" placeholder="Search for events or organizations..." style="flex-grow: 1;">
                <button class="btn search-btn" type="submit"><i class="bi bi-search"></i></button>
            </form>
            </div>
        </div>
        <?php
            $currentMonth = date('F');
            $currentYear = date('Y'); 
        ?>
        <div class="row mb-5" style="margin-left: 5px;">
            <div class="col">
                <h5>Here are the line-up of events for <?php echo $currentMonth . ', ' . $currentYear; ?>:</h5>
                    <div id="eventsCarousel" class="carousel slide" data-bs-ride="carousel">
                        <button class="carousel-control-prev" type="button" data-bs-target="#eventsCarousel" data-bs-slide="prev" style="background-color: #000080; width: 40px; height: 40px; border-radius: 50%; top: 50%;">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span>
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
                                                <form action="osaDetailViewing.php" method="POST">
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


<div class="container mt-5" style="margin-left: 5px;">
    <h5>Recognized Student Organizations</h5>
    <h6>Academic Organizations</h6>
    <div id="academicCarousel" class="carousel slide" data-bs-ride="false">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="row">
                    <?php
                    $sql = "SELECT organizationID, organizationName, organizationLogo FROM organization WHERE organizationTypeID = 1";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        $counter = 0;
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="col-md-3">';
                            echo '<div class="card organization">';
                            echo '<img class="card-img-top organization-logo" src="' . $row["organizationLogo"] . '" alt="Organization Logo">';
                            echo '</div>';
                            echo '<div class="card-body organization-details">';
                            echo '<p class="card-title organization-name">' . $row["organizationName"] . '</p>';
                            echo '<div class="d-flex gap-2">';
                            echo '<a href="osaRsoDashboard.php?organizationID=' . $row["organizationID"] . '" class="btn btn-primary btn-circle view-details-button">';
                            echo '<i class="fas fa-eye"></i>'; // Font Awesome Eye Icon for View Details
                            echo '</a>';
                            echo '<a href="osaActivitiesOperations.php?organizationID=' . $row["organizationID"] . '" class="btn btn-secondary btn-circle grading-button">';
                            echo '<i class="fas fa-star"></i>';
                            echo '</a>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                            $counter++;
                            if ($counter % 4 == 0 && $counter != $result->num_rows) {
                                echo '</div>'; // Close row div
                                echo '</div>'; // Close carousel-item div
                                echo '<div class="carousel-item"><div class="row">';
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#academicCarousel" data-bs-slide="prev" style="background-color: #000080;width: 40px; height: 40px; border-radius: 50%; top: 50%;">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#academicCarousel" data-bs-slide="next" style="background-color: #000080;width: 40px; height: 40px; border-radius: 50%; top: 50%;">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <h6 class="mt-5">Co-Academic Organizations</h6>
    <div id="coAcademicCarousel" class="carousel slide" data-bs-ride="false">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="row">
                    <?php
                    $sql = "SELECT organizationID, organizationName, organizationLogo FROM organization WHERE organizationTypeID = 2";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        $counter = 0;
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="col-md-3">';
                            echo '<div class="card organization">';
                            echo '<img class="card-img-top organization-logo" src="' . $row["organizationLogo"] . '" alt="Organization Logo">'; 
                            echo '</div>'; // Close organization-details div
                            echo '<div class="card-body organization-details">';
                            echo '<p class="card-title organization-name">' . $row["organizationName"] . '</p>';
                            echo '<div class="d-flex gap-2">';
                            echo '<a href="osaRsoDashboard.php?organizationID=' . $row["organizationID"] . '" class="btn btn-primary btn-circle view-details-button">';
                            echo '<i class="fas fa-eye"></i>'; // Font Awesome Eye Icon for View Details
                            echo '</a>';
                            echo '<a href="osaActivitiesOperations.php?organizationID=' . $row["organizationID"] . '" class="btn btn-secondary btn-circle grading-button">';
                            echo '<i class="fas fa-star"></i>';
                            echo '</a>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                            $counter++;
                            if ($counter % 4 == 0 && $counter != $result->num_rows) {
                                echo '</div>'; // Close row div
                                echo '</div>'; // Close carousel-item div
                                echo '<div class="carousel-item"><div class="row">';
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#coAcademicCarousel" data-bs-slide="prev" style="background-color: #000080;width: 40px; height: 40px; border-radius: 50%; top: 50%;">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#coAcademicCarousel" data-bs-slide="next" style="background-color: #000080;width: 40px; height: 40px; border-radius: 50%; top: 50%;">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <h6 class="mt-5">Socio-Civic Organizations</h6>
    <div id="socioCivicCarousel" class="carousel slide" data-bs-ride="false">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="row">
                    <?php
                    $sql = "SELECT organizationID, organizationName, organizationLogo FROM organization WHERE organizationTypeID = 3";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        $counter = 0;
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="col-md-3">';
                            echo '<div class="card organization">';
                            echo '<img class="card-img-top organization-logo" src="' . $row["organizationLogo"] . '" alt="Organization Logo">';
                            echo '</div>'; // Close organization-details div
                            echo '<div class="card-body organization-details">';
                            echo '<div class="d-flex gap-2">';
                            echo '<a href="osaRsoDashboard.php?organizationID=' . $row["organizationID"] . '" class="btn btn-primary btn-circle view-details-button">';
                            echo '<i class="fas fa-eye"></i>'; // Font Awesome Eye Icon for View Details
                            echo '</a>';
                            echo '<a href="osaActivitiesOperations.php?organizationID=' . $row["organizationID"] . '" class="btn btn-secondary btn-circle grading-button">';
                            echo '<i class="fas fa-star"></i>';
                            echo '</a>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                            $counter++;
                            if ($counter % 4 == 0 && $counter != $result->num_rows) {
                                echo '</div>'; // Close row div
                                echo '</div>'; // Close carousel-item div
                                echo '<div class="carousel-item"><div class="row">';
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#socioCivicCarousel" data-bs-slide="prev" style="background-color: #000080;width: 40px; height: 40px; border-radius: 50%; top: 50%;">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#socioCivicCarousel" data-bs-slide="next" style="background-color: #000080;width: 40px; height: 40px; border-radius: 50%; top: 50%;">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>   
</div>
<script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>
</body>
</html>
<?php
$conn->close();
?>

