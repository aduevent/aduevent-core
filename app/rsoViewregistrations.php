<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginStudent.php");
    exit;
}
include("dbcon.php");
$userId = $_SESSION['id'];
$userQuery = "
    SELECT su.name, su.email, o.organizationLogo as profilePicture
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

$count_query = "SELECT COUNT(*) AS registered_students FROM eventregistration WHERE eventID = '$eventID'";
$count_result = mysqli_query($conn, $count_query);

if (!$count_result) {
    die("Error: " . mysqli_error($conn));
}

$count_row = mysqli_fetch_assoc($count_result);
$registered_students = $count_row['registered_students'];

$query = "SELECT studentNumber, name 
          FROM eventregistration 
          WHERE eventID = '$eventID'";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error: " . mysqli_error($conn));
}
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
    $activepage = "rsoViewRegistrations"; ?>
    <style>
        .registration-preview {
            background-color: #ffffff;
            border-radius: 15px;
            padding: 10px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column; /* To align items vertically */
            align-items: center; /* To center items horizontally */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .rounded-box {
            background-color: #f0f0f0; /* Light gray color */
            border-radius: 10px; /* Round edges */
            padding: 10px; /* Padding inside the box */
            display: inline-block; /* Display as inline-block to fit content */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000080; /* Table border color */
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #000080; /* Table border color */
        }
        tbody tr:nth-child(even) {
            background-color: #f2f2f2; /* Alternate gray background */
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container" style="margin-left: 5px;">
        <div class="registration-preview">
        <button onclick="window.location.href='rsoEventHub.php';" class="btn btn-light d-flex justify-content-center align-items-center" 
                style="width: 40px; height: 40px; border-radius: 50%; padding: 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #f9f9f9;">
            <i class="bi bi-arrow-left-circle" style="color: #000080; font-size: 20px;"></i> <!-- Bootstrap icon with blue color -->
        </button>
        <h5 style="color: #000080;">Registration Monitoring Portal</h5>
        <p style="margin-bottom: 0; padding-bottom: 0;">Number of students registered for the event: </p>
        <div class="rounded-box">
            <h2><?php echo $registered_students; ?></h2>
        </div>
        <h6>List of Registered Students</h6>
        <table>
    <thead>
        <tr>
            <th>Ticket Number</th>
            <th>Student Number</th>
            <th>Name</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $query = "SELECT ticketNumber, StudentNumber, name FROM eventregistration WHERE eventID = '$eventID'";
        $result = mysqli_query($conn, $query);

        if (!$result) {
            die("Error: " . mysqli_error($conn));
        }

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>{$row['ticketNumber']}</td>";
            echo "<td>{$row['StudentNumber']}</td>";
            echo "<td>{$row['name']}</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

    </div>
</body>
</html>
<script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>
<?php
mysqli_close($conn);
?>
