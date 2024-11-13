<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginStudent.php");
    exit;
}

include("dbcon.php");
$userId = $_SESSION['id'];

// Fetch user information
$userQuery = "SELECT su.name, su.email, o.organizationLogo as profilePicture
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
$query = "SELECT eventTitle, ticketSelling FROM event WHERE eventID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $eventID);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the event title from the result
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $eventTitle = $row['eventTitle'];
    $ticketSelling = $row['ticketSelling'];
} else {
    echo "No event found with the specified ID.";
}

// Count registered students
$count_query = "SELECT COUNT(*) AS registered_students FROM eventregistration WHERE eventID = ?";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("i", $eventID);
$count_stmt->execute();
$count_stmt->bind_result($registered_students);
$count_stmt->fetch();
$count_stmt->close();

// Fetch registered students
$query = "SELECT studentNumber, name FROM eventregistration WHERE eventID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $eventID);
$stmt->execute();
$result = $stmt->get_result();

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $studentNumber = $_POST['studentNumber'];
    $ticketNumber = $_POST['ticketNumber'];

    // Insert new registration into the database
    $insertQuery = "INSERT INTO eventregistration (eventID, name, studentNumber, ticketNumber, registrationTimestamp) VALUES (?, ?, ?, ?, NOW())";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("isss", $eventID, $name, $studentNumber, $ticketNumber);
    
    if ($insertStmt->execute()) {
        echo "<script>alert('Student registered successfully.');
                window.location.href = window.location.href;
                </script>";
    } else {
        echo "<script>alert('Failed to register student.');</script>";
    }
    $insertStmt->close();
}
$conn->close();
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
    $activePage = "rsoRegistration"; ?>
    <style>
        .scrollable-table {
            max-height: 400px; /* Adjust height as needed */
            overflow-y: auto;
            border: 1px solid #ddd;
            margin-left: 20px;
            margin-top: 10px;
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container" style="margin-left: 5px;">
        <ul class="list-inline" style="margin-bottom: 0;">
            <li class="list-inline-item">
                <button onclick="window.location.href='rsoEventHub.php';" 
                        class="btn btn-light d-flex justify-content-center align-items-center" 
                        style="width: 40px; height: 40px; border-radius: 50%; padding: 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #f9f9f9;">
                    <i class="bi bi-arrow-left-circle" style="color: #000080; font-size: 20px;"></i>
                </button>
            </li>
            <li class="list-inline-item">
                <button onclick="javascript:void(0);"
                class="btn btn-secondary justify-content-center" 
                        style="width: 300px; height: 40px; border-radius: 50px; padding: 0 15px; border: none; background-color: #f1f1f1;">
                    <span style="color: #000000; font-weight: bold;">Event Registration</span>
                </button>
            </li>
            <li class="list-inline-item"> 
                <button onclick="window.location.href='rsoEventPerformance.php?eventID=<?= htmlspecialchars($eventID); ?>';"
                class="btn btn-secondary justify-content-center" 
                        style="width: 300px; height: 40px; border-radius: 50px; padding: 0 15px; border: 1px solid #808080; background-color: transparent;">
                    <span style="color: #808080; font-weight: bold;">Event Performance Tracking</span>
                </button>
            </li>
        </ul>
        <h2 class="text-center" style="margin-top: 20px;"><strong style="color: #000080;"><?php echo $eventTitle ?></strong></h2>
        <div class="row mt-4 align-items-center justify-content-center">
            <div class="col-md-4 d-flex flex-column align-items-center justify-content-center" style="background-color: #f1f1f1; border-radius: 15px; padding: 10px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);">
                <h3 class="text-center" style="color: #000080;">Pre-register to this event</h3>
                <form action="" method="POST">
                <input type="hidden" name="eventID" value="<?= htmlspecialchars($eventID); ?>">
                <?php if (!is_null($ticketSelling) && $ticketSelling != 0) { ?>
                <div class="mb-3">
                    <label for="ticketNumber" class="form-label">Ticket Number</label>
                    <input type="text" class="form-control" id="ticketNumber" name="ticketNumber" style="border-radius: 50px;" required>
                </div>
                <?php } ?>
                <div class="mb-3">
                    <label for="name" class="form-label text-center">Name</label>
                    <input type="text" class="form-control" id="name" name="name" style="border-radius: 50px;" required>
                </div>
                <div class="mb-3">
                    <label for="studentNumber" class="form-label">Student Number</label>
                    <input type="text" class="form-control" id="studentNumber" name="studentNumber" style="border-radius: 50px;" required>
                </div>
                <button type="submit" class="btn btn-primary mb-2" style="border-radius: 50px; background-color: #000080; border: none;">Register</button>
                </form>
            </div>
            <div class="col-md-4">
                <h3 class="text-center">Registered Students (<?= $registered_students; ?>)</h3>
                <div class="scrollable-table">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($row['name']); ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($row['studentNumber']); ?></small>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
