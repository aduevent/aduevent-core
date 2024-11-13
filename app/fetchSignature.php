<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginEmployee.php");
    exit;
}

include("dbcon.php");

// Get the logged-in user's employeeID
$employeeID = $_SESSION['id'];

// SQL query to fetch signatures for the logged-in user
$sql = "SELECT signatureData FROM signature WHERE employeeID = $employeeID";

$result = $mysqli->query($sql);

// Check if there are any results
if ($result->num_rows > 0) {
    $signatures = array();
    // Fetch data and push to the $signatures array
    while($row = $result->fetch_assoc()) {
        $signatures[] = $row['signatureData'];
    }
    // Return the signatures as JSON
    echo json_encode($signatures);
} else {
    // If no signatures found, return an empty array
    echo json_encode([]);
}

// Close database connection
$mysqli->close();
?>
