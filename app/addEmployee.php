<?php
session_start();
if (!isset($_SESSION['id'])) {
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

$_SESSION['access'] = $userData['userTypeID'];

$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture'];
$access = $_SESSION['access'];
$organizationID = $userData['organizationID'];

if ($_SESSION['access'] != 6) {
    echo "Access Denied.";
    exit;
}

include("adminNavbar.php");  // Include the navbar
$activePage = "addEmployee";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userTypeID = $_POST['userTypeID'];
    $organizationID = in_array($userTypeID, [5, 6, 8, 9, 10, 11, 12]) ? null : $_POST['organizationID'];

    if (!in_array($userTypeID, [5, 6, 8, 9, 10, 11, 12]) && empty($organizationID)) {
        echo "Organization is required for this user type.";
        exit;
    }

    $employeeNumber = $_POST['employeeNumber'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $pin = $_POST['pin'];

    $insertQuery = "INSERT INTO employeeuser (organizationID, employeeNumber, name, email, password, userTypeID) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);

    if (!$insertStmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $insertStmt->bind_param("issssi", $organizationID, $employeeNumber, $name, $email, $password, $userTypeID);

    if ($insertStmt->execute()) {
        echo "<script>alert('Successfully added to the database'); window.location.href='adminIndex.php';</script>";
        exit;
    } else {
        echo "Error: " . $insertStmt->error;
    }
}

$orgQuery = "SELECT organizationID, organizationName FROM organization";
$orgResult = $conn->query($orgQuery);

$userTypeQuery = "SELECT userTypeID, userTypeDescription FROM usertype";
$userTypeResult = $conn->query($userTypeQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            margin-left: 20%;
            padding-top: 10px;
        }
        .form-container {
            margin-top: 20px;
            margin-left: 15px;
            margin-right: 15px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .form-control {
            width: 100%;
        }
        .my-button {
            margin-top: 10px;
        }
    </style>
    <script>
        function toggleOrganizationField() {
            const userType = document.getElementById("userTypeID").value;
            const orgField = document.getElementById("organizationID");

            if (["5", "6", "8", "9", "10", "11", "12"].includes(userType)) {
                orgField.disabled = true;
                orgField.required = false;
                orgField.value = "";
            } else {
                orgField.disabled = false;
                orgField.required = true;
            }
        }
    </script>
</head>
<body>
    <div class="form-container">
        <h2>Add Employee</h2>
        <form method="POST">
            <div class="form-group">
                <label for="organizationID">Organization</label>
                <select name="organizationID" id="organizationID" class="form-control">
                    <option value="">Select Organization</option>
                    <?php while ($orgData = $orgResult->fetch_assoc()) { ?>
                        <option value="<?php echo $orgData['organizationID']; ?>"><?php echo $orgData['organizationName']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="employeeNumber">Employee Number</label>
                <input type="text" class="form-control" name="employeeNumber" id="employeeNumber" required>
            </div>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" name="name" id="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="userTypeID">User Type</label>
                <select name="userTypeID" id="userTypeID" class="form-control" required onchange="toggleOrganizationField()">
                    <option value="">Select User Type</option>
                    <?php while ($userTypeData = $userTypeResult->fetch_assoc()) { ?>
                        <option value="<?php echo $userTypeData['userTypeID']; ?>"><?php echo $userTypeData['userTypeDescription']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="pin">PIN</label>
                <input type="text" class="form-control" name="pin" id="pin" required>
            </div>
            <button type="submit" class="btn btn-primary my-button">Add Employee</button>
            <a href="adminEmployeeList.php" class="btn btn-secondary my-button">Cancel</a>
        </form>
    </div>
</body>
</html>
