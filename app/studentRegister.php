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

if (isset($_GET['eventID'])) {
    $eventID = $_GET['eventID'];

    $query = "SELECT event.*, organization.organizationName, organization.organizationLogo 
              FROM event 
              INNER JOIN organization ON event.organizationID = organization.organizationID 
              WHERE event.eventID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $eventID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        $ticketSelling = $event['ticketSelling'];
    } else {
        echo "Event not found.";
        exit;
    }
} else {
    echo "Event ID not provided.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $eventID = $_GET['eventID'];
    $studentNumber = $_POST['studentNumber'];
    $name = $_POST['name'];
    $ticketNumber = isset($_POST['ticketNumber']) ? $_POST['ticketNumber'] : '';

    if (empty($studentNumber) || empty($name)) {
        echo "Student number and name are required.";
    } else {
        // Prepare the base query
        $checkQuery = "SELECT * FROM eventregistration WHERE eventID = ? AND studentNumber = ?";
        
        // If ticketNumber is provided, add it to the query
        if (!empty($ticketNumber)) {
            $checkQuery .= " AND ticketNumber = ?";
        }

        $stmt = $conn->prepare($checkQuery);

        // Bind parameters based on whether ticketNumber is provided
        if (!empty($ticketNumber)) {
            $stmt->bind_param("iss", $eventID, $studentNumber, $ticketNumber);
        } else {
            $stmt->bind_param("is", $eventID, $studentNumber);
        }
        $stmt->execute();
        $checkResult = $stmt->get_result();

        if ($checkResult->num_rows > 0) {
            // If exists, update attended column to 1
            if (!empty($ticketNumber)) {
                // If ticketNumber is provided
                $updateQuery = "UPDATE eventregistration SET attended = 1, attendTimestamp = NOW() WHERE eventID = ? AND studentNumber = ? AND ticketNumber = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("iss", $eventID, $studentNumber, $ticketNumber);
            } else {
                // If ticketNumber is not provided
                $updateQuery = "UPDATE eventregistration SET attended = 1, attendTimestamp = NOW() WHERE eventID = ? AND studentNumber = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("is", $eventID, $studentNumber);
            }
            if ($stmt->execute()) {
                echo "<div class='text-success'>Attendance updated successfully with timestamp!</div>";
            } else {
                echo "<div class='text-danger'>Error updating attendance: " . $conn->error . "</div>";
            }
        } else {
            // Insert registration data into the database
            $insertQuery = "INSERT INTO eventregistration (eventID, studentNumber, name, ticketNumber, attended) 
                            VALUES (?, ?, ?, ?, 1)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("isss", $eventID, $studentNumber, $name, $ticketNumber);

            if ($stmt->execute()) {
                $registrationSuccess = true;
            } else {
                echo "Error: " . $insertQuery . "<br>" . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $event['eventTitle']; ?> Details</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <?php include 'studentNavbar.php';
    $activePage = "studentIndex.php";?>
    <style>
        #signup-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(200, 200, 200, 0.8);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
<div class="container">
    <div id="signup-container" class="bg-light p-4 rounded shadow" style="text-align: center;">
    <a href="javascript:history.back()" class="btn btn-link"><i class="bi bi-arrow-left"></i> Back</a>
    <h2 class="mb-0" style="margin-bottom: 0; color: #000080; padding-bottom: 0; text-align: center;">Event Registration</h2>
    <?php
        $eventQuery = "SELECT eventTitle FROM event WHERE eventID = ?";
        $stmt = $conn->prepare($eventQuery);
        $stmt->bind_param("i", $eventID);
        $stmt->execute();
        $eventResult = $stmt->get_result();
        $event = $eventResult->fetch_assoc();
    ?>
    <div style="margin-top: 0; text-align: center;">
        <p><b><?php echo $event['eventTitle']; ?></b></p>
    </div>
    <div style="text-align: center;">
    <form method="post">
        <input type="hidden" name="eventID" value="<?php echo $_GET['eventID']; ?>">
        <?php if (!is_null($ticketSelling) && $ticketSelling != 0) { ?>
        <!-- Show Ticket Number if ticketSelling is not null -->
        <label for="ticketNumber">Ticket Number</label><br>
        <input type="text" id="ticketNumber" name="ticketNumber" required><br>
    <?php } ?>
        <label for="studentNumber">Student Number</label><br>
        <input type="text" id="studentNumber" name="studentNumber" required><br>
        <label for="name">Name</label><br>
        <input type="text" id="name" name="name" required><br><br>
        <input type="submit" value="Register" style="border-radius: 50px; background-color: #000080; color: white; padding: 10px 20px; border: none; cursor: pointer;">
        <?php if (isset($registrationSuccess) && $registrationSuccess) { ?>
            <div class="text-success">Registration successful!</div>
        <?php } ?>
    </form>
</div>
</body>
</html>

<?php
mysqli_close($conn);
?>
