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

$currentYear = date('Y');
$currentMonth = date('n');

// Determine the academic year
$academicYear = ($currentMonth >= 1 && $currentMonth <= 7) ? ($currentYear - 1) . '-' . $currentYear : $currentYear . '-' . ($currentYear + 1);

$currentOrgID = $_GET['organizationID'] ?? null;

// Fetch organization information securely using prepared statements
$orgInfoQuery = "SELECT organizationLogo, organizationName FROM organization WHERE organizationID = ?";
$orgInfoStmt = $conn->prepare($orgInfoQuery);
$orgInfoStmt->bind_param("i", $currentOrgID);
$orgInfoStmt->execute();
$orgInfoResult = $orgInfoStmt->get_result();

if ($orgInfoResult && $orgInfoResult->num_rows > 0) {
    $orgInfo = $orgInfoResult->fetch_assoc();
    $organizationLogo = $orgInfo['organizationLogo'];
    $organizationName = $orgInfo['organizationName'];
}

// Check if the organization has already been graded for the current academic year
$ratingCheckQuery = "SELECT COUNT(*) as count FROM grading WHERE organizationID = ? AND academicYear = ? AND pointSystemID = 2";
$ratingCheckStmt = $conn->prepare($ratingCheckQuery);
$ratingCheckStmt->bind_param("is", $currentOrgID, $academicYear);
$ratingCheckStmt->execute();
$ratingCheckResult = $ratingCheckStmt->get_result();
$ratingCheck = $ratingCheckResult->fetch_assoc();
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
    $activePage = "osaParticipationCompliance"; ?>
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
            background-color: #011b6e;
        }
        .submit-container {
            display: flex;
            justify-content: center;
            margin-top: 15px;
        }
        .organization-logo {
            max-width: 100px;
            border-radius: 100px;
            margin-right: 10px;
            z-index: 1;
        }
        .grading-container {
            background-color: #f9f9f9;
            border-radius: 50px;
            padding: 15px;
            margin: 15px auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            text-align: center;
            width: 90%;
        }
        .participation-description {
            font-size: 1em;
            color: #000080;
            font-weight: bold;
            margin: 0 0 10px 0;
        }
        .grade-input {
            height: 60px; 
            width: 100px; 
            padding: 5px; 
            margin-top: 10px; 
            border-radius: 20px; 
            border: none; 
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
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
                 style="border: 2px solid #000080; border-radius: 50px; padding: 5px 15px;">
                <p class="academic-year-display mb-0" style="color: #000080;">
                    <?= htmlspecialchars($academicYear); ?>
                </p>
                <input type="hidden" id="academicYear" name="academicYear" value="<?= htmlspecialchars($academicYear); ?>">
            </div>
        </li>
        <li class="list-inline-item">
            <button onclick="window.location.href='osaActivitiesOperations.php?organizationID=<?= htmlspecialchars($currentOrgID); ?>';"
                    class="btn btn-secondary justify-content-center" 
                    style="width: 300px; height: 40px; border-radius: 50px; padding: 0 15px; border: 1px solid #808080; background-color: transparent;">
                <span style="color: #000000; font-weight: bold;">Activities and Operations</span>
            </button>
        </li>
        <li class="list-inline-item"> 
            <button onclick="javascript:void(0);" 
                    class="btn btn-secondary justify-content-center" 
                    style="width: 300px; height: 40px; border-radius: 50px; padding: 0 15px; border: none; background-color: #f1f1f1;">
                <span style="color: #808080; font-weight: bold;">Participation and Compliance</span>
            </button>
        </li>
    </ul>
    <?php if ($ratingCheck['count'] > 0): ?>
        <div class="alert alert-info text-center" style="margin-top: 15px;" role="alert">
            This organization has already been graded.
        </div>
    <?php else: ?>
        <form id="gradingForm" action="osaGradingProcess2.php" method="post">
            <input type="hidden" id="pointSystemID" name="pointSystemID" value="2">
            <input type="hidden" id="organizationID" name="organizationID" value="<?= htmlspecialchars($currentOrgID); ?>">
            <input type="hidden" id="academicYear" name="academicYear" value="<?= htmlspecialchars($academicYear); ?>">

            <div class="d-flex justify-content-center align-items-center" style="margin-top: 10px;">
                <li class="list-inline-item"><img src="<?= htmlspecialchars($organizationLogo); ?>" alt="Organization Logo" class="organization-logo"></li>
                <li class="list-inline-item"><h2 class="organization-name"><?= htmlspecialchars($organizationName); ?></h2></li>  
            </div>
            
            <?php
            $participationQuery = "SELECT participationDescription, participationValue FROM matrixparticipation ORDER BY participationValue ASC";
            $participationResult = $conn->query($participationQuery);

            if ($participationResult->num_rows > 0) {
                while ($row = $participationResult->fetch_assoc()) {
                    $description = htmlspecialchars($row['participationDescription']);
                    $value = htmlspecialchars($row['participationValue']);
                    
                    // Container for each participation description and input
                    echo '<div class="grading-container">';
                    echo '<p class="participation-description">' . $description . '</p>';
                    
                    echo '<label style="display: block; text-align: center; margin-bottom: 0;">Enter rating:</label>';
                    echo '<input type="number" name="grade[]" class="grade-input" max="' . $value . '" required>';
                    echo '</div>';
                }
            } else {
                echo '<div class="alert alert-warning">No participation records found.</div>';
            }
            ?>
            
            <div class="submit-container">
                <button class="submit-button" type="submit">Submit</button>
            </div>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
