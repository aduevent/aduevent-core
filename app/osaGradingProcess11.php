<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginEmployee.php");
    exit;
}

include("dbcon.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form values
    $organizationID = $_POST['organizationID'];
    $eventID = $_POST['eventID'];
    $pointSystemID = $_POST['pointSystemID'];
    $academicYear = $_POST['academicYear'];

    // Collect all ratings
    $ratings = [
        $_POST['rating1'] ?? 0,
        $_POST['rating2'] ?? 0,
        $_POST['rating3'] ?? 0,
        $_POST['rating4'] ?? 0,
        $_POST['rating5'] ?? 0,
        $_POST['rating6'] ?? 0,
        $_POST['rating7'] ?? 0,
        $_POST['rating8'] ?? 0,
        $_POST['rating9'] ?? 0,
        $_POST['rating10'] ?? 0
    ];

    // Calculate total rating
    $totalRating = array_sum($ratings);

    // Fetch pointSystemCategoryID from database based on eventID
    $categoryQuery = "SELECT pointSystemCategoryID FROM event WHERE eventID = ?";
    $categoryStmt = $conn->prepare($categoryQuery);
    $categoryStmt->bind_param("i", $eventID);
    $categoryStmt->execute();
    $categoryResult = $categoryStmt->get_result();

    if ($categoryResult->num_rows > 0) {
        $categoryRow = $categoryResult->fetch_assoc();
        $pointSystemCategoryId = $categoryRow['pointSystemCategoryID'];

        // Now that we have the pointSystemCategoryId, we can proceed with the insert
        $insertQuery = "INSERT INTO grading (organizationID, eventID, pointSystemID, pointSystemCategoryID, rating, academicYear) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);

        // Fix bind_param by adding the correct number of placeholders and matching types
        $stmt->bind_param("iiiiss", $organizationID, $eventID, $pointSystemID, $pointSystemCategoryId, $totalRating, $academicYear);

        if ($stmt->execute()) {
            echo "<script>
            alert('Grade successfully submitted!');
            window.location.href = 'osaSummary.php';
          </script>";
    exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Error: Event not found!";
    }
}

    $stmt->close();

$conn->close();
?>
