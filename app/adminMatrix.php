<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: loginEmployee.php");
    exit;
}
include 'dbcon.php';
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

include("adminNavbar.php");
$activePage = "adminMatrix";

$areaQuery = "SELECT areaID, areaDescription FROM matrixarea";
$areaResult = $conn->query($areaQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matrix Configuration</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .container {
            display: flex;
            width: 100%;
        }
        .column-1 {
            flex: 0 0 30%;
            padding: 20px;
            background-color: #d3d3d3;
            position: sticky;
            top: 0;
            height: 87vh;
            border-radius: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        .column-2 {
            flex: 1;
            padding: 20px;
            overflow: auto;
            height: 87vh;
            margin-left: 1%;
            background-color: #f9f9f9;
            border-radius: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        .button {
            display: block;
            width: 100%;
            padding: 10px;
            margin-bottom: 5px;
            background-color: #ffffff;
            color: #696969;
            text-align: center;
            border: none;
            border-radius: 50px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #000080;
        }
        .filter-header h2 {
            margin: 0;
            font-size: 1.5em;
        }
        .filter-header p {
            font-size: 1em;
            color: #333;
        }
        .nav-title {
            color: #000080; /* Default gray color */
            font-weight: bold;
            font-size: 14px; /* Adjust font size as needed */
        }
        h3 {
            color: #000080;
            margin: 0;
            font-size: 1.5em;
            font-weight: bold;
        }
        label {
            color: black;
            font-weight: bold;
        }
        input[type="text"] {
            border-radius: 50px;
            padding: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%; /* Adjust width as needed */
            margin-bottom: 10px; /* Space between inputs */
            font-size: 1em;
            transition: box-shadow 0.3s ease; /* Smooth transition effect */
        }
        input[type="text"]:focus {
            box-shadow: 0px 6px 8px rgba(0, 0, 0, 0.2); /* Slightly stronger shadow on focus */
            border-color: #000080; /* Match h3 color */
        }
        .label-container{
            margin-top: 15px;
            margin-bottom: 0px;
        }
        .criteria-container {
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            margin: 15px 0;
            background-color: #ffffff;
            border: none;
        }
        .criteria-container label {
            color: black;
            font-weight: bold;
            margin-right: 5px;
        }
        .criteria-container input[type="text"] {
            border-radius: 50px;
            padding: 5px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #ccc;
            margin-bottom: 5px;
            font-size: 1em;
        }
        .point-basis-container {
            display: flex;
            align-items: center;
            margin: 5px 0; /* Less space between each point basis */
            gap: 10px;
        }
        .point-basis-container label {
            margin-right: 10px; /* Adds space to the right of the label */
            white-space: nowrap; /* Prevents the label from wrapping to a new line */
            overflow: hidden; /* Hides any overflow */
            text-overflow: ellipsis; /* Adds ellipsis for overflow text (optional) */
            max-width: 150px; /* Set a max width to control label size, adjust as needed */
            min-width: 100px;
        }
        .narrow-input {
            width: 75px; /* Adjust width as needed */
            padding: 5px;
            text-align: center; /* Center-align the digit */
            border: none; /* Optional: Border color */
            border-radius: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Optional: Shadow */
        }
        .narrow-input:focus {
            outline: none;
            border-color: #000080; /* Dark blue on focus */
        }
        .criteria-container input[type="text"]:focus {
            box-shadow: 0px 6px 8px rgba(0, 0, 0, 0.2);
            border-color: #000080;
        }
        .submit-button {
            display: block;
            margin: 20px auto; /* Center the button horizontally */
            padding: 10px 20px;
            background-color: #000080; /* Dark blue background */
            color: #ffffff; /* White text */
            border: none; /* No border */
            border-radius: 50px; /* Rounded edges */
            font-size: 1em;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s ease; /* Optional: transition effect */
        }
        .submit-button:hover {
            background-color: #000099; /* Slightly lighter blue on hover */
        }
    </style>
    <script>
        function loadCriteria(areaID, areaDescription) {
            // AJAX request to load data based on areaID
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "adminFetchData.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById("column-2-content").innerHTML = xhr.responseText;
                }
            };
            xhr.send("areaID=" + areaID + "&areaDescription=" + encodeURIComponent(areaDescription));
        }
    </script>
</head>
<body style="margin-left: 20%; padding-top: 10px;">
    <div class="container">
        <!-- First Column with Buttons -->
        <div class="column-1">
        <div class="filter-header">
            <h2 style="color: #000080;"><strong>Grading Criteria Selection</strong></h2>
            <p>Select grade criteria to configure</p>
        </div>
        <li class="nav-item mt-3">
            <span class="nav-title">Activities and Operations</span>
              <hr class="mt-1 mb-2" style="border-top: 1px solid gray; opacity: 0.5;"/>
            </li>
            <?php
            if ($areaResult->num_rows > 0) {
                while ($row = $areaResult->fetch_assoc()) {
                    echo '<button class="button" onclick="loadCriteria(' . $row['areaID'] . ', \'' . addslashes($row['areaDescription']) . '\')">' . $row['areaDescription'] . '</button>';
                }
            } else {
                echo "No areas available.";
            }
            ?>
            <li class="nav-item mt-3">
            <span class="nav-title">Participation and Compliance</span>
              <hr class="mt-1 mb-2" style="border-top: 1px solid gray; opacity: 0.5;"/>
            </li>
            <button class="button" onclick="loadCriteria(999, 'Compliance Review')">Compliance Review</button>
        </div>

        <div class="column-2" id="column-2-content">
            <h3>Matrix Configuration Field</h3>
        </div>
    </div>
</body>
</html>
