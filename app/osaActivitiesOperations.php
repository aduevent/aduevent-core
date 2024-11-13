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

$currentOrgID = $_GET['organizationID'] ?? null;
$currentYear = date('Y');
$currentMonth = date('n');

if ($currentMonth >= 1 && $currentMonth <= 7) {
    $academicYear = ($currentYear - 1) . '-' . $currentYear;
} else {
    $academicYear = $currentYear . '-' . ($currentYear + 1);
}

$academicYearStartDate = ($currentMonth >= 1 && $currentMonth <= 7) ? ($currentYear - 1) . '-08-01' : $currentYear . '-08-01';
$academicYearEndDate = ($currentMonth >= 1 && $currentMonth <= 7) ? $currentYear . '-07-31' : ($currentYear + 1) . '-07-31';

$orgInfoQuery = "SELECT organizationLogo, organizationName FROM organization WHERE organizationID = $currentOrgID";
$orgInfoResult = $conn->query($orgInfoQuery);
if ($orgInfoResult && $orgInfoResult->num_rows > 0) {
    $orgInfo = $orgInfoResult->fetch_assoc();
    $organizationLogo = $orgInfo['organizationLogo'];
    $organizationName = $orgInfo['organizationName'];
}

$eventQuery = "SELECT event.eventID, event.eventTitle, event.pointSystemCategoryID 
               FROM event
               WHERE event.organizationID = ? 
               AND event.eventStatus = 1 
               AND event.eventDate BETWEEN ? AND ?
               AND NOT EXISTS (
                   SELECT 1 FROM grading 
                   WHERE grading.eventID = event.eventID
               )";
$stmt = $conn->prepare($eventQuery);
$stmt->bind_param("iss", $currentOrgID, $academicYearStartDate, $academicYearEndDate);
$stmt->execute();
$eventResult = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OSA Grading Portal</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <?php include 'navbar.php'; 
    $activePage = "osaActivitiesOperations"; ?>
    <style>
        .submit-button {
            background-color: #000080;
            color: #FFFFFF;
            border: none;
            border-radius: 30px;
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .submit-button:hover {
            background-color: #011b6e; /* Slightly darker on hover */
        }
        .submit-container {
            display: flex;
            justify-content: center; /* Center horizontally */
            margin-top: 15px; /* Optional spacing from above content */
        }
        .organization-logo {
            max-width: 100px;
            border-radius: 100px;
            margin-right: 10px;
            z-index: 1;
        }
        .form-select {
            border-radius: 50px; /* Rounds the edges */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3); /* Adds a subtle shadow */
            border: 1px solid #ccc; /* Light border to define the edges */
            padding: 8px;
            transition: box-shadow 0.3s ease; /* Smooth transition for the shadow effect */
        }
        .ul-container {
            display: flex;
            justify-content: center; /* Centers the <ul> horizontally */
            margin-top: 20px; /* Optional: adjust spacing as needed */
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
<div class="container mt-5">
    <div class="col-md-12">
    <ul class="list-inline" style="margin-bottom: 0;">
        <li class="list-inline-item">
            <button onclick="window.location.href='osaIndex.php';" 
                    class="btn btn-light d-flex justify-content-center align-items-center" 
                    style="width: 40px; height: 40px; border-radius: 50%; padding: 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #f9f9f9;">
                <i class="bi bi-arrow-left-circle" style="color: #000080; font-size: 20px;"></i>
            </button>
        </li>
        <li class="list-inline-item">
            <div class="d-flex justify-content-center align-items-center" 
                 style="border: 2px solid #000080; border-radius: 15px; padding: 5px 15px;">
                <p class="academic-year-display mb-0" style="color: #000080;">
                    <?= htmlspecialchars($academicYear); ?>
                </p>
            </div>
        </li>
        <li class="list-inline-item">
            <button onclick="javascript:void(0);" 
                    class="btn btn-secondary justify-content-center" 
                    style="width: 300px; height: 40px; border-radius: 50px; padding: 0 15px; border: none; background-color: #f1f1f1;">
                <span style="color: #000000; font-weight: bold;">Activities and Operations</span>
            </button>
        </li>
        <li class="list-inline-item">
        <button onclick="window.location.href='osaParticipationCompliance.php?organizationID=<?php echo htmlspecialchars($currentOrgID); ?>';" 
            class="btn justify-content-center" 
            style="width: 300px; height: 40px; border-radius: 50px; padding: 0 15px; border: 1px solid #808080; background-color: transparent;">
        <span style="color: #808080; font-weight: bold;">Participation and Compliance</span>
    </button>
</li>
</ul>
<div class="d-flex justify-content-center align-items-center" style="margin-top: 10px;">
    <li class="list-inline-item"><img src="<?php echo $organizationLogo; ?>" alt="Organization Logo" class="organization-logo"></li>
    <li class="list-inline-item"><h2 class="organization-name"><?php echo $organizationName; ?></h2></li>  
</div>
<form id="gradingForm" form action="osaGradingProcess1.php" method="post">
<input type="hidden" id="pointSystemID" name="pointSystemID" value="1">
<input type="hidden" id="organizationID" name="organizationID" value="<?= htmlspecialchars($currentOrgID); ?>">
<input type="hidden" id="academicYear" name="academicYear" value="<?= htmlspecialchars($academicYear); ?>">
<div class="ul-container">
    <ul class="list-inline" style="margin: 0; padding: 0;">
    <li class="list-inline-item">
        <label for="events">Events:</label>
    </li>
    <li class="list-inline-item">
        <select class="form-select" id="events" name="eventID">
            <option value="">Select an Event</option>
            <?php if ($eventResult && $eventResult->num_rows > 0): ?>
                <?php while ($event = $eventResult->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($event['eventID']); ?>">
                <?php echo htmlspecialchars($event['eventTitle']); ?>
            </option>
                <?php endwhile; ?>
            <?php else: ?>
                <option value="">No events available</option>
            <?php endif; ?>
        </select>
    </li>
</li>
</ul>
</div>
<?php
$queryAreas = "SELECT areaID, areaDescription FROM matrixarea";
$resultAreas = mysqli_query($conn, $queryAreas);

if (mysqli_num_rows($resultAreas) > 0) {    
    $ratingCounter = 1;
    $areaIndex = 1;

    while ($rowArea = mysqli_fetch_assoc($resultAreas)) {
        $areaID = $rowArea['areaID'];
        $areaDescription = $rowArea['areaDescription'];

        // Wrap each area inside a section
        echo '<div class="area-section" id="areaSection' . $areaIndex . '" style="display: none; text-align: center; margin-top: 10px;>';
        echo '<strong style="color: #000080; font-size: 1.5em; margin-bottom: 0; display: block;">' . $areaDescription . '</strong><br>';


        // Check if there are criteria for this areaID in matrixcriteria
        $queryCriteria = "SELECT criteriaID, criteriaDescription FROM matrixcriteria WHERE areaID = $areaID";
        $resultCriteria = mysqli_query($conn, $queryCriteria);

        if (mysqli_num_rows($resultCriteria) > 0) {
            // Loop through criteria
            while ($rowCriteria = mysqli_fetch_assoc($resultCriteria)) {
                $criteriaID = $rowCriteria['criteriaID'];
                $criteriaDescription = $rowCriteria['criteriaDescription'];

                // Get point basis data for this criteriaID
                $queryPointBasis = "SELECT value, pointBasisDescription FROM matrixpointbasis WHERE criteriaID = $criteriaID";
                $resultPointBasis = mysqli_query($conn, $queryPointBasis);

                // Start container for criteria
                echo '<div style="box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3); border-radius: 30px; background-color: #f9f9f9; padding: 10px; margin: 0 auto 20px auto; text-align: center; width: 90%;">';
                echo '<span style="color: #000080; font-weight: bold;">' . $criteriaDescription . '</span><br>';

                echo '<div style="display: flex; gap: 10px;">'; // Flex container for horizontal boxes

                // Loop through point basis
                while ($rowPointBasis = mysqli_fetch_assoc($resultPointBasis)) {
                    $value = $rowPointBasis['value'];
                    $pointBasisDescription = $rowPointBasis['pointBasisDescription'];

                    // Display each value-pointBasisDescription pair
                    echo '<div style="flex-grow: 1; text-align: center; border: 1px solid lightgray; border-radius: 10px; padding: 10px;">';
                    echo $value . ' - ' . $pointBasisDescription;
                    echo '</div>';
                }
                echo '</div>';

                // Add rating input
                echo '<label for="rating' . $ratingCounter . '" style="display: block; text-align: center; margin-bottom: 0;">Enter rating:</label>';
echo '<input type="number" name="rating' . $ratingCounter . '" id="rating' . $ratingCounter . '" style="height: 60px; width: 100px; padding: 5px; margin-top: 10px; border-radius: 20px; border: none; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">';
echo '</div>';
                $ratingCounter++;
            }
        } else {
            // No criteria, use areaID to fetch point basis data
            $queryPointBasis = "SELECT value, pointBasisDescription FROM matrixpointbasis WHERE criteriaID IS NULL AND areaID = $areaID";
            $resultPointBasis = mysqli_query($conn, $queryPointBasis);

            // Start container for this areaID
            echo '<div style="box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); border-radius: 30px; background-color: #F9F6EE; padding: 10px; margin: 0 auto 20px auto; text-align: center; width: 90%;">';
            echo '<span style="color: #000080; font-weight: bold;">' . $areaDescription . '</span><br>';
            
            echo '<div style="display: flex; gap: 10px;">'; // Flex container for horizontal boxes

            // Loop through point basis results
            while ($rowPointBasis = mysqli_fetch_assoc($resultPointBasis)) {
                $value = $rowPointBasis['value'];
                $pointBasisDescription = $rowPointBasis['pointBasisDescription'];
                
                // Display each value - pointBasisDescription pair as a box
                echo '<div style="flex-grow: 1; text-align: center; border: 1px solid lightgray; border-radius: 10px; padding: 10px;">';
                echo $value . ' - ' . $pointBasisDescription;
                echo '</div>';
            }

            echo '</div><br>'; // Close flex container
            
            // Add rating input field
            echo '<label for="rating' . $ratingCounter . '" style="display: block; text-align: center; margin-bottom: 0;">Enter rating:</label>';
echo '<input type="number" name="rating' . $ratingCounter . '" id="rating' . $ratingCounter . '" style="height: 60px; width: 100px; padding: 5px; margin-top: 10px; border-radius: 20px; border: none; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">';
echo '</div>';
            $ratingCounter++;
        }

        // Close section
        echo '</div>';
        $areaIndex++;
    }

    // Add navigation buttons
    echo '<div id="formNavigation" style="text-align: center; margin-top: 20px; margin-bottom: 20px;">';
    echo '<button type="button" id="prevButton" style="display: none; background-color: #000080; color: white; padding: 10px 20px; border: none; border-radius: 50px; margin-right: 10px;" onclick="prevSection()">Previous</button>';
    echo '<button type="button" id="nextButton" style="background-color: #000080; color: white; padding: 10px 20px; border: none; border-radius: 50px; margin-right: 10px;" onclick="nextSection()">Next</button>';
    echo '<button type="submit" id="submitButton" class="submit-button" style="display: none; background-color: #000080; color: white; padding: 10px 20px; border: none; border-radius: 50px;">Submit</button>'; // Added ID here
    echo '</div>';

    echo '</form>';
}
?>

<script>
let currentSection = 1;
const totalSections = <?= $areaIndex - 1; ?>;

document.getElementById('areaSection1').style.display = 'block'; // Show the first section

function nextSection() {
    if (currentSection < totalSections) {
        document.getElementById('areaSection' + currentSection).style.display = 'none'; // Hide current section
        currentSection++;
        document.getElementById('areaSection' + currentSection).style.display = 'block'; // Show next section

        // If we're on the last section, hide "Next" and show "Submit"
        if (currentSection === totalSections) {
            document.getElementById('nextButton').style.display = 'none';
            document.getElementById('submitButton').style.display = 'inline';
        }

        // Show "Previous" button if not on the first section
        if (currentSection > 1) {
            document.getElementById('prevButton').style.display = 'inline';
        }
    }
}

function prevSection() {
    if (currentSection > 1) {
        document.getElementById('areaSection' + currentSection).style.display = 'none'; // Hide current section
        currentSection--;
        document.getElementById('areaSection' + currentSection).style.display = 'block'; // Show previous section

        // If we're on the first section, hide "Previous"
        if (currentSection === 1) {
            document.getElementById('prevButton').style.display = 'none';
        }

        // Show "Next" button if not on the last section
        if (currentSection < totalSections) {
            document.getElementById('nextButton').style.display = 'inline';
            document.getElementById('submitButton').style.display = 'none';
        }
    }
}
</script>
