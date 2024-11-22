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
    // Redirect or show an error message if eventID is not provided
    header("Location: rsoIndex.php");
    exit();
}

$eventID = $_GET['eventID'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"])) {
    $image = $_FILES["image"];
    $caption = $_POST["caption"];
    $president = mysqli_real_escape_string($conn, $_POST["president"]);
    $adviser = mysqli_real_escape_string($conn, $_POST["adviser"]);
    $chairperson = !empty($_POST["chairperson"]) ? mysqli_real_escape_string($conn, $_POST["chairperson"]) : NULL;

    // Check if the file is an image
    $imageFileType = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));
    $allowedTypes = array("jpg", "jpeg", "png", "gif");
    if (!in_array($imageFileType, $allowedTypes)) {
        $error_message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
    } else {
        // Move the uploaded image to the appropriate directory
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($image["name"]);
        if (move_uploaded_file($image["tmp_name"], $targetFile)) {
            // Update the event record in the database with the new fields
            $update_query = "UPDATE event SET 
                                eventPhoto = '$targetFile', 
                                eventDescription = '$caption', 
                                president = '$president', 
                                adviser = '$adviser', 
                                chairperson = ".($chairperson ? "'$chairperson'" : "NULL")." 
                             WHERE eventID = '$eventID'";
            if (mysqli_query($conn, $update_query)) {
                $success_message = "Image, caption, and organization roles uploaded successfully.";
            } else {
                $error_message = "Error updating promotion details: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Error uploading image.";
        }
    }
}
// Fetch event details
$query = "SELECT eventTitle FROM event WHERE eventID = '$eventID'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$eventTitle = $row['eventTitle'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Promotion</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <?php include 'rsoNavbar.php';
    $activePage = "rsoEditPromotion";?>
    <style>
        .edit-event {
            background-color: #ffffff; /* White background */
            border-radius: 15px; /* Rounded corners */
            margin-bottom: 20px; /* Spacing between event previews */
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px">
    <div class="container" style="margin=left: 5px;">
    <div class="edit-event">
    <button onclick="window.location.href='rsoEventHub.php';" class="btn btn-light d-flex justify-content-center align-items-center" 
                style="width: 40px; height: 40px; border-radius: 50%; padding: 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #f9f9f9;">
            <i class="bi bi-arrow-left-circle" style="color: #000080; font-size: 20px;"></i> <!-- Bootstrap icon with blue color -->
        </button>
        <h5 class="mb-0" style="text-align: center; margin-bottom: 5px;">Edit Promotion for</h5>
<h4 style="text-align: center; color: #000080; margin-top: 0; margin-bottom: 10px;"><?php echo $eventTitle; ?></h4>
<?php
        if (isset($error_message)) {
            echo '<div class="alert alert-danger" role="alert">' . $error_message . '</div>';
        }
        if (isset($success_message)) {
            echo '<div class="alert alert-success" role="alert">' . $success_message . '</div>';
        }
        ?>
        <div class="card" style="border-radius: 20px; border: none; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); background-color: #d3d3d3;">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data">
        <div class="form-group">
                <label for="president" style="display: block; text-align: center;">Organization President:</label>
                <input type="text" class="form-control" id="president" name="president" maxlength="100" style="border-radius: 50px; border: none; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); width: 60%; margin: 0 auto;" required>
            </div>
            <div class="form-group">
                <label for="adviser" style="display: block; text-align: center;">Organization Adviser:</label>
                <input type="text" class="form-control" id="adviser" name="adviser" maxlength="100" style="border-radius: 50px; border: none; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); width: 60%; margin: 0 auto;" required>
            </div>
            <div class="form-group">
                <label for="chairperson" style="display: block; text-align: center;">College Chairperson (Optional):</label>
                <input type="text" class="form-control" id="chairperson" name="chairperson" maxlength="100" style="border-radius: 50px; border: none; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); width: 60%; margin: 0 auto;">
            </div>
            <div class="row justify-content-center">
            <div class="col-md-12">
    <div class="form-group">
        <label for="caption" style="display: block; text-align: center;">Caption:</label>
        <textarea class="form-control" id="caption" name="caption" style="min-height: 250px; resize: vertical; border-radius: 50px; border: none; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);"></textarea>
    </div>
</div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="image" style="display: block; text-align: center;">Upload Image:</label>
                        <input type="file" class="form-control-file" id="image" name="image" accept="image/*" required>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success" style="border-radius: 20px; background-color: #000080; width: 100%;">Upload</button>
                </div>
            </div>
        </form>
    </div>
</div>

            </div>
        </div>
    </div>
</body>
</html>
<script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>

<?php
mysqli_close($conn); // Close the MySQL connection
?>
