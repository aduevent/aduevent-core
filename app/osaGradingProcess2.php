<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginEmployee.php");
    exit;
}

include("dbcon.php");

// Retrieve posted data
$organizationID = $_POST['organizationID'];
$pointSystemID = $_POST['pointSystemID'];
$academicYear = $_POST['academicYear'];
$grades = $_POST['grade'];

// Calculate the total sum of grades
$totalGrade = array_sum($grades);

try {
    // Prepare and execute the insert query
    $sql = "INSERT INTO grading (organizationID, pointSystemID, academicYear, rating) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisi", $organizationID, $pointSystemID, $academicYear, $totalGrade);

    if ($stmt->execute()) {
        echo "<script>
            alert('Grade successfully recorded');
            window.location.href = 'osaIndex.php';
        </script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
?>
