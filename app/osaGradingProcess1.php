<?php
// Include the database connection
include('dbcon.php');

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve posted values
    $pointSystemID = mysqli_real_escape_string($conn, $_POST['pointSystemID']);
    $organizationID = mysqli_real_escape_string($conn, $_POST['organizationID']);
    $academicYear = mysqli_real_escape_string($conn, $_POST['academicYear']);
    $eventID = mysqli_real_escape_string($conn, $_POST['eventID']);

    // Initialize variables for the total rating and rating counter
    $totalRating = 0;
    $ratingCounter = 1;

    // Loop through ratings and add to totalRating
    while (isset($_POST['rating' . $ratingCounter])) {
        $rating = (float)$_POST['rating' . $ratingCounter];
        $totalRating += $rating;
        $ratingCounter++;
    }

    // Retrieve the pointSystemCategoryID from the event table based on the eventID
    $sqlCategory = "SELECT pointSystemCategoryID FROM event WHERE eventID = ?";
    $stmt = $conn->prepare($sqlCategory);
    $stmt->bind_param("i", $eventID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $pointSystemCategoryID = $row['pointSystemCategoryID'];

        // Insert the grading data into the grading table
        $sqlInsert = "INSERT INTO grading (organizationID, eventID, pointSystemID, pointSystemCategoryID, rating, academicYear) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("iiiids", $organizationID, $eventID, $pointSystemID, $pointSystemCategoryID, $totalRating, $academicYear);

        if ($stmtInsert->execute()) {
            echo "Grading data has been successfully saved.";
        } else {
            echo "Error: " . $stmtInsert->error;
        }

        $stmtInsert->close();
    } else {
        echo "Error: Event not found.";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
