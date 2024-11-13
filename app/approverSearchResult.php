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
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture'];    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdUEvent</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <?php include 'approverNavbar.php';
    $activePage = "approverSearchResult"; ?>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container">
        <h5 style="text-align:center;">Search Results</h5>

    <?php if (isset($_GET['query'])) {
    $searchQuery = htmlspecialchars($_GET['query']);
    $searchTerm = "%" . $searchQuery . "%";
    
    $hasResults = false;
    
    echo "<div style='display: flex; justify-content: center;'>
            <table style='width: 60%; border-collapse: collapse; margin-top: 20px;'>
                <tbody>";

    // Search for events
    $sql_events = "SELECT eventID, eventTitle, eventStatus, organizationID FROM event WHERE eventTitle LIKE ?";
    $stmt_events = $conn->prepare($sql_events);
    $stmt_events->bind_param("s", $searchTerm);
    $stmt_events->execute();
    $events_result = $stmt_events->get_result();

    if ($events_result->num_rows > 0) {
        $hasResults = true; // Flag set to true when events are found
        while ($event = $events_result->fetch_assoc()) {
            // Get the organization name for the event
            $organizationID = $event['organizationID'];
            $org_name_query = $conn->query("SELECT organizationName FROM organization WHERE organizationID = '$organizationID'");
            $organization = $org_name_query->fetch_assoc();

            // Determine the redirect URL based on eventStatus
            $buttonLink = ($event['eventStatus'] == 1) ? "approverDetailViewing.php?eventID=" . $event['eventID'] : "approverDocumentViewing2.php?eventID=" . $event['eventID'];

            // Output the event title and organization name with a button aligned to the right
            echo "<tr style='border-bottom: 1px solid #ddd;'>
        <td style='padding: 10px;'>
            " . $event['eventTitle'] . " - " . $organization['organizationName'] . "
        </td>
        <td style='text-align: right; padding: 10px;'>
            <form action='$buttonLink' method='POST'>
                <input type='hidden' name='eventID' value='" . $event['eventID'] . "'>
                <button type='submit' class='btn btn-primary' style='border-radius: 50%; padding: 10px 12px;'>
                    <i class='bi bi-eye'></i>
                </button>
            </form>
        </td>
      </tr>";
        }
    }

    // Search for organizations
    $sql_orgs = "SELECT organizationID, organizationName FROM organization WHERE organizationName LIKE ?";
    $stmt_orgs = $conn->prepare($sql_orgs);
    $stmt_orgs->bind_param("s", $searchTerm);
    $stmt_orgs->execute();
    $orgs_result = $stmt_orgs->get_result();

    if ($orgs_result->num_rows > 0) {
        $hasResults = true; // Flag set to true when organizations are found
        while ($org = $orgs_result->fetch_assoc()) {
            // Organization button redirect link
            $buttonLink = "approverRsoList.php?organizationID=" . $org['organizationID'];

            // Output the organization name with a button aligned to the right
            echo "<tr style='border-bottom: 1px solid #ddd;'>
                    <td style='padding: 10px;'>
                        " . $org['organizationName'] . "
                    </td>
                    <td style='text-align: right; padding: 10px;'>
                        <a href='$buttonLink'>
                            <button class='btn btn-primary' style='border-radius: 50%; padding: 10px 12px; background-color: #000080; border: none;'>
                                <i class='bi bi-eye'></i>
                            </button>
                        </a>
                    </td>
                  </tr>";
        }
    }

    // If no results are found at all, show a message
    if (!$hasResults) {
        echo "<tr><td colspan='2' style='text-align: center; padding: 10px;'>No events or organizations found for '$searchQuery'.</td></tr>";
    }

    echo "    </tbody>
            </table>
          </div>";
}
?>