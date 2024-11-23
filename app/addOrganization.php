<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: loginEmployee.php");
    exit();
}

include "dbcon.php";
$userId = $_SESSION["id"];
$userQuery =
    "SELECT name, email, profilePicture, userTypeID FROM employeeuser WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();

$_SESSION["access"] = $userData["userTypeID"];
$userName = $userData["name"];
$email = $userData["email"];
$dp = $userData["profilePicture"];
$access = $_SESSION["access"];

if ($access != 6) {
    echo "Access Denied.";
    exit();
}

include "adminNavbar.php";
$activePage = "addOrganization";

// Fetch organization types for dropdown
$orgTypeQuery =
    "SELECT organizationTypeID, organizationTypeName FROM organizationtype";
$orgTypeResult = $conn->query($orgTypeQuery);

// Initialize a success message variable
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $organizationName = $_POST["organizationName"];
    $organizationTypeID = $_POST["organizationTypeID"];
    $organizationEmail = $_POST["organizationEmail"];
    $logoPath = null; // Initialize logoPath

    // temporary variable for storing the newly inserted logo file data
    $insertedLogoID = null;

    // Handle file upload (if logo is uploaded)
    if (
        isset($_FILES["organizationLogo"]) &&
        $_FILES["organizationLogo"]["error"] == 0
    ) {
        $validMimetypes = ["image/jpeg", "image/png", "image/gif"];
        $file = $_FILES["organizationLogo"];

        if (in_array($file["type"], $validMimetypes)) {
            // buffer
            $null = null;
            $fileContents = file_get_contents($file["tmp_name"]);

            $file_store_stmt = $conn->prepare(
                "INSERT INTO files (filename, data) VALUES (?, ?)"
            );
            $file_store_stmt->bind_param("sb", $file["name"], $null);
            $file_store_stmt->send_long_data(1, $fileContents);

            if ($file_store_stmt->execute()) {
                $insertedLogoID = $conn->insert_id;
            } else {
                echo "Failed to upload file: " . $file_store_stmt->error;
                $logoPath = null;
            }
        } else {
            echo "Invalid file type. Please upload a JPEG, PNG, or GIF image.";
            $logoPath = null;
        }
    }

    if (!empty($insertedLogoID)) {
        $stmt = $conn->prepare(
            "INSERT INTO organization (organizationName, organizationTypeID,
            logoFileReference, organizationEmail) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssis",
            $organizationName,
            $organizationTypeID,
            $insertedLogoID,
            $organizationEmail
        );

        if ($stmt->execute()) {
            // Set success message
            $successMessage = "Organization added successfully!";
            // Redirect to adminIndex.php after a delay
            echo "<script>
                alert('$successMessage');
                window.location.href='adminIndex.php';
            </script>";
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Organization</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            margin-left: 20%;
            padding-top: 10px;
        }

        .form-container {
            margin-top: 20px; /* Space between profile header and form */
            margin-left: 15px;
            margin-right: 15px;
            padding: 20px;
            background-color: #f8f9fa; /* Light background for the form container */
            border-radius: 5px; /* Rounded corners */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Shadow effect */
        }
        .my-button {
            margin-top: 10px; /* Adjust the value as needed */
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add Organization</h2>
        <form method="POST" action="" enctype="multipart/form-data"> <!-- Added enctype for file upload -->
            <div class="form-group">
                <label for="organizationName">Organization Name</label>
                <input type="text" class="form-control" id="organizationName" name="organizationName" required>
            </div>
            <div class="form-group">
                <label for="organizationTypeID">Organization Type</label>
                <select class="form-control" id="organizationTypeID" name="organizationTypeID" required>
                    <option value="">Select Organization Type</option>
                    <?php while (
                        $orgTypeData = $orgTypeResult->fetch_assoc()
                    ) { ?>
                        <option value="<?php echo $orgTypeData[
                            "organizationTypeID"
                        ]; ?>"><?php echo $orgTypeData[
    "organizationTypeName"
]; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="organizationLogo">Organization Logo (Required)</label>
                <input type="file" class="form-control" id="organizationLogo" name="organizationLogo" accept="image/*" required> <!-- Removed required attribute -->
            </div>
            <div class="form-group">
                <label for="organizationEmail">Organization Email</label>
                <input type="email" class="form-control" id="organizationEmail" name="organizationEmail" required>
            </div>
            <button type="submit" class="btn btn-primary my-button">Add Organization</button>
            <a href="adminOrganizationList.php" class="btn btn-secondary my-button">Cancel</a>
        </form>
    </div>
</body>
</html>
