<?php
include("dbcon.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['eventID'])) {
        $eventID = $_POST['eventID'];
        
        $sql = "UPDATE event SET eventStatus = 2 WHERE eventID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eventID);

        if ($stmt->execute()) {
            header("Location: {$_SERVER['HTTP_REFERER']}?message=Event%20was%20declined!");
            exit();
        } else {
            http_response_code(500);
            echo "Error updating event status.";
        }
    } else {
        http_response_code(400);
        echo "Missing eventID.";
    }
} else {
    http_response_code(405);
    echo "Method Not Allowed";
}
?>
