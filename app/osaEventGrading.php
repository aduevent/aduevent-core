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

$currentOrgID = $_GET['organizationID'] ?? null;
if ($currentOrgID) {
    $queryOrganization = "SELECT * FROM organization WHERE organizationID = '$currentOrgID'";
    $resultOrganization = mysqli_query($conn, $queryOrganization);
    $orgInfoQuery = "SELECT organizationLogo, organizationName FROM organization WHERE organizationID = $currentOrgID";
    $orgInfoResult = $conn->query($orgInfoQuery);
    if ($orgInfoResult && $orgInfoResult->num_rows > 0) {
        $orgInfo = $orgInfoResult->fetch_assoc();
        $organizationLogo = $orgInfo['organizationLogo'];
        $organizationName = $orgInfo['organizationName'];
    }
    if (!$resultOrganization) {
        echo "Error fetching organizations: " . mysqli_error($conn);
    }
} else {
    echo "No organization selected.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OSA RSO Dashboard View</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <?php include 'navbar.php';
    $activePage = "osaIndex"; ?>
    </head>
<body style="margin-left: 20%; padding-top: 5px;">
<div class="container" style="margin-left: 5px;">
    <div class="row">
        <div class="col-md-12">
            <ul class="list-inline" style="margin-bottom: 0;">
            <li class="list-inline-item"><button onclick="window.location.href='osaIndex.php';" class="btn btn-light d-flex justify-content-center align-items-center" 
                style="width: 40px; height: 40px; border-radius: 50%; padding: 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #f9f9f9;">
            <i class="bi bi-arrow-left-circle" style="color: #000080; font-size: 20px;"></i> <!-- Bootstrap icon with blue color -->
        </button></li>
                <li class="list-inline-item"><img src="<?php echo $organizationLogo; ?>" alt="Organization Logo" class="organization-logo"></li>
                <li class="list-inline-item"><h2 class="organization-name"><?php echo $organizationName; ?></h2></li>
            </ul>         
        </div>