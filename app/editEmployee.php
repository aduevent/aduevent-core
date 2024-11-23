<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: loginEmployee.php");
    exit();
}

include "dbcon.php";

// Retrieve user information
$userId = $_SESSION["id"];
$userQuery =
    "SELECT name, email, userTypeID, pin FROM employeeuser WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();

$userName = $userData["name"];
$email = $userData["email"];
$_SESSION["access"] = $userData["userTypeID"];
$storedPin = $userData["pin"]; // Store the user's PIN for comparison

// Ensure the user is an admin
if ($_SESSION["access"] != 6) {
    echo "Access Denied.";
    exit();
}

include "adminNavbar.php";

$empId = $_GET["id"] ?? null;
if ($empId === null) {
    echo "Employee ID is required.";
    exit();
}

// Fetch employee details
$empQuery =
    "SELECT organizationID, employeeNumber, name, email, userTypeID FROM employeeuser WHERE id = ?";
$stmt = $conn->prepare($empQuery);
$stmt->bind_param("i", $empId);
$stmt->execute();
$empResult = $stmt->get_result();
$empData = $empResult->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $organizationID = null;

    if (
        isset($_POST["organizationID"]) &&
        strlen($_POST["organizationID"]) > 0
    ) {
        $organizationID = (int) $_POST["organizationID"];
    }

    $employeeNumber = $_POST["employeeNumber"];
    $name = $_POST["name"];
    $email = filter_var($_POST["email"], FILTER_VALIDATE_EMAIL);
    $userTypeID = (int) $_POST["userTypeID"];

    if ($email === false) {
        echo "Invalid email format.";
        exit();
    }

    // Store the entered PIN
    $enteredPin = $_POST["pin"];

    // Check if the entered PIN matches the stored hashed PIN
    if (!password_verify($enteredPin, $storedPin)) {
        echo '<script>alert("Incorrect PIN. Please try again.");</script>';
    } else {
        // Prepare the update query
        $updateQuery =
            "UPDATE employeeuser SET employeeNumber = ?, name = ?, email = ?, userTypeID = ?";

        // some employees may not have an organizationID
        if (isset($organizationID) || !is_null($organizationID)) {
            $updateQuery .= ", organizationID = ?";
        }

        $updateQuery .= " WHERE id = ?";

        $stmt = $conn->prepare($updateQuery);

        if (isset($organizationID) || !is_null($organizationID)) {
            $stmt->bind_param(
                "issssi",
                $employeeNumber,
                $name,
                $email,
                $userTypeID,
                $organizationID,
                $empId
            );
        } else {
            $stmt->bind_param(
                "ssssi",
                $employeeNumber,
                $name,
                $email,
                $userTypeID,
                $empId
            );
        }

        // Execute the prepared statement
        if ($stmt->execute()) {
            echo '<script>
                    alert("Employee information successfully updated.");
                    window.location.href = "adminEmployeeList.php";
                  </script>';
            exit();
        } else {
            var_dump($organizationID);
            echo "Error updating employee: " . $stmt->error;
        }
    }
}

// Fetch organizations for dropdown
$orgQuery = "SELECT organizationID, organizationName FROM organization";
$orgResult = $conn->query($orgQuery);

// Fetch user types for dropdown
$userTypeQuery = "SELECT userTypeID, userTypeDescription FROM usertype";
$userTypeResult = $conn->query($userTypeQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
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
        .form-group label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .my-button {
            margin-top: 10px;
            padding: 10px 20px; /* Ensure same padding */
            border-radius: 5px; /* Consistent border radius */
        }
        .my-b {
            margin-top: 10px;
            padding: 10px 20px; /* Ensure same padding */
            border-radius: 5px; /* Consistent border radius */
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Employee</h2>
        <form id="editEmpForm" method="POST">
        <div class="form-group">
           <label for="organizationID">Organization</label>
              <select name="organizationID" id="organizationID" class="form-control" required>
                 <option value="" <?php if (empty($empData["organizationID"])) {
                     echo "selected";
                 } ?>>Select organization</option>
                 <?php while ($orgData = $orgResult->fetch_assoc()) { ?>
                 <option value="<?php echo $orgData[
                     "organizationID"
                 ]; ?>" <?php if (
    $empData["organizationID"] == $orgData["organizationID"]
) {
    echo "selected";
} ?>>
                <?php echo htmlspecialchars($orgData["organizationName"]); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="employeeNumber">Employee Number</label>
                <input type="text" name="employeeNumber" id="employeeNumber" class="form-control" value="<?php echo htmlspecialchars(
                    $empData["employeeNumber"]
                ); ?>" required>
            </div>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars(
                    $empData["name"]
                ); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars(
                    $empData["email"]
                ); ?>" required>
            </div>
            <div class="form-group">
                <label for="userTypeID">User Type</label>
                <select name="userTypeID" id="userTypeID" class="form-control" required>
                    <?php while (
                        $userTypeData = $userTypeResult->fetch_assoc()
                    ) { ?>
                        <option value="<?php echo $userTypeData[
                            "userTypeID"
                        ]; ?>" <?php if (
    $empData["userTypeID"] == $userTypeData["userTypeID"]
) {
    echo "selected";
} ?>>
                            <?php echo htmlspecialchars(
                                $userTypeData["userTypeDescription"]
                            ); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <button type="button" class="btn btn-primary my-button" data-toggle="modal" data-target="#pinModal">Save Changes</button>
            <a href="adminEmployeeList.php" class="btn btn-secondary my-button" style="min-width: 140px;">Cancel</a>

        </form>
    </div>

    <!-- PIN Modal -->
    <div class="modal fade" id="pinModal" tabindex="-1" role="dialog" aria-labelledby="pinModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pinModalLabel">Enter Your PIN</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="password" class="form-control" id="modalPinInput" placeholder="Enter PIN" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary my-b" onclick="submitForm()">Confirm</button>
                    <button type="button" class="btn btn-secondary my-b" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function submitForm() {
            var pinInput = document.getElementById('modalPinInput').value;
            if (pinInput) {
                document.getElementById('editEmpForm').insertAdjacentHTML('beforeend', '<input type="hidden" name="pin" value="' + pinInput + '">');
                $('#pinModal').modal('hide'); // Close the modal
                document.getElementById('editEmpForm').submit(); // Submit the form
            } else {
                alert("Please enter your PIN.");
            }
        }
    </script>
</body>
</html>
