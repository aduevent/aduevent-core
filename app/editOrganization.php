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
    "SELECT name, email, profilePicture, userTypeID, pin FROM employeeuser WHERE id = ?";
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

$orgId = $_GET["id"] ?? null;
if ($orgId === null) {
    echo "Organization ID is required.";
    exit();
}

// Fetch organization details
$orgQuery =
    "SELECT organizationName, organizationTypeID, organizationLogo, organizationEmail FROM organization WHERE organizationID = ?";
$stmt = $conn->prepare($orgQuery);
$stmt->bind_param("i", $orgId);
$stmt->execute();
$orgResult = $stmt->get_result();
$orgData = $orgResult->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $organizationName = $_POST["organizationName"];
    $organizationTypeID = (int) $_POST["organizationTypeID"];
    $organizationEmail = filter_var(
        $_POST["organizationEmail"],
        FILTER_VALIDATE_EMAIL
    );

    if ($organizationEmail === false) {
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
            "UPDATE organization SET organizationName = ?, organizationTypeID = ?, organizationEmail = ? WHERE organizationID = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param(
            "sisi",
            $organizationName,
            $organizationTypeID,
            $organizationEmail,
            $orgId
        );

        // Check if a new logo has been uploaded
        if ($_FILES["organizationLogo"]["error"] == UPLOAD_ERR_OK) {
            $logoTmpPath = $_FILES["organizationLogo"]["tmp_name"];
            $logoName = $_FILES["organizationLogo"]["name"];
            $logoExt = pathinfo($logoName, PATHINFO_EXTENSION);
            $newLogoName = "logos/logo_" . $orgId . "." . $logoExt;

            $destPath = $_SERVER["DOCUMENT_ROOT"] . "/capstone/" . $newLogoName;
            if (move_uploaded_file($logoTmpPath, $destPath)) {
                // Update organization details with new logo path
                $updateQueryWithLogo =
                    "UPDATE organization SET organizationName = ?, organizationTypeID = ?, organizationLogo = ?, organizationEmail = ? WHERE organizationID = ?";
                $stmt = $conn->prepare($updateQueryWithLogo);
                $stmt->bind_param(
                    "ssssi",
                    $organizationName,
                    $organizationTypeID,
                    $newLogoName,
                    $organizationEmail,
                    $orgId
                );
            } else {
                echo "Error uploading the logo.";
                exit();
            }
        }

        // Execute the prepared statement
        if ($stmt->execute()) {
            echo '<script>
                    alert("Your changes were successfully updated.");
                    window.location.href = "adminIndex.php";
                  </script>';
            exit();
        } else {
            echo "Error updating organization: " . $stmt->error;
        }
    }
}

// Fetch organization types for dropdown
$typeQuery =
    "SELECT organizationTypeID, organizationTypeName FROM organizationtype";
$typeResult = $conn->query($typeQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Organization</title>
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
        <h2>Edit Organization</h2>
        <form id="editOrgForm" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="organizationName">Organization Name</label>
                <input type="text" name="organizationName" id="organizationName" class="form-control" value="<?php echo htmlspecialchars(
                    $orgData["organizationName"]
                ); ?>" required>
            </div>
            <div class="form-group">
                <label for="organizationTypeID">Organization Type</label>
                <select name="organizationTypeID" id="organizationTypeID" class="form-control" required>
                    <?php while ($typeData = $typeResult->fetch_assoc()) { ?>
                        <option value="<?php echo $typeData[
                            "organizationTypeID"
                        ]; ?>" <?php if (
    $orgData["organizationTypeID"] == $typeData["organizationTypeID"]
) {
    echo "selected";
} ?>>
                            <?php echo htmlspecialchars(
                                $typeData["organizationTypeName"]
                            ); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="organizationLogo">Organization Logo</label><br>
                <?php if ($orgData["organizationLogo"]) { ?>
                    <img src="<?php echo htmlspecialchars(
                        $orgData["organizationLogo"]
                    ); ?>?v=<?php echo time(); ?>" alt="Current Logo" style="width: 100px; height: auto;">
                <?php } ?>
                <input type="file" name="organizationLogo" id="organizationLogo" class="form-control mt-2">
            </div>

            <div class="form-group">
                <label for="organizationEmail">Organization Email</label>
                <input type="email" name="organizationEmail" id="organizationEmail" class="form-control" value="<?php echo htmlspecialchars(
                    $orgData["organizationEmail"]
                ); ?>" required>
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
                document.getElementById('editOrgForm').insertAdjacentHTML('beforeend', '<input type="hidden" name="pin" value="' + pinInput + '">');
                $('#pinModal').modal('hide'); // Close the modal
                document.getElementById('editOrgForm').submit(); // Submit the form
            } else {
                alert("Please enter your PIN.");
            }
        }

    </script>
</body>
</html>
