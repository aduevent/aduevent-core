<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $eventID = $_POST['eventID'];
    $userID = $_POST['userID'];
    $response = !empty($_POST['response']) ? $_POST['response'] : NULL;

    // Initialize ratings
    $ratings = [];
    for ($i = 1; $i <= 10; $i++) {
        $ratings[$i] = isset($_POST['rating'][$i]) ? $_POST['rating'][$i] : NULL;
    }

    // Prepare SQL query
    $query = "INSERT INTO feedbackresponse (eventID, id, rating1, rating2, rating3, rating4, rating5, rating6, rating7, rating8, rating9, rating10, response) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "iiissssssssss",
        $eventID,
        $userID,
        $ratings[1], $ratings[2], $ratings[3], $ratings[4], $ratings[5], 
        $ratings[6], $ratings[7], $ratings[8], $ratings[9], $ratings[10],
        $response
    );

    if ($stmt->execute()) {
        echo "Feedback submitted successfully!";
        // Redirect or display success message
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
