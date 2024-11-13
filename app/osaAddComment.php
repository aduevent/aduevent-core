<?php
session_start();

// Include the database connection file
include("dbcon.php");

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve data from the form submission
    $eventID = $_POST['eventID'];
    $projectLeadName = $_POST['projectLeadName'];
    $comment = $_POST['comment'];

    // Prepare the SQL statement to insert the comment into the database
    $query = "INSERT INTO eventcomments (eventID, name, comment, createdAt) VALUES (?, ?, ?, NOW())";
    if ($stmt = $conn->prepare($query)) {
        // Bind the parameters to the SQL query
        $stmt->bind_param("iss", $eventID, $projectLeadName, $comment);

        // Execute the statement
        if ($stmt->execute()) {
            // Comment was successfully added, redirect to a success page or show a success message
            echo "<script>
                    alert('Comment added successfully.');
                    window.location.href = 'osaEventApproval.php'; // Redirect to the appropriate page
                  </script>";
        } else {
            // Handle error if the execution fails
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        // Handle error if the statement preparation fails
        echo "Error preparing the SQL statement: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
} else {
    // Handle unauthorized access or wrong request method
    echo "Unauthorized access!";
    exit();
}
?>
