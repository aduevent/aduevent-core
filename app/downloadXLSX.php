<?php
// Database connection
include('dbcon.php');

// Get eventID from URL
$eventID = $_GET['eventID'];

// Query to fetch the event title based on eventID from the event table
$eventQuery = "SELECT eventTitle FROM event WHERE eventID = ?";
$stmtEvent = $conn->prepare($eventQuery);
$stmtEvent->bind_param("i", $eventID);
$stmtEvent->execute();
$eventResult = $stmtEvent->get_result();
$eventRow = $eventResult->fetch_assoc();
$eventTitle = $eventRow['eventTitle'];

// Sanitize eventTitle to remove any special characters that might not be allowed in filenames
$sanitizedTitle = preg_replace('/[^a-zA-Z0-9-_]/', '_', $eventTitle);

// SQL query to fetch data from the eventregistration table
$query = "SELECT studentNumber, name, attended, attendTimestamp, registrationTimestamp 
          FROM eventregistration 
          WHERE eventID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $eventID);
$stmt->execute();
$result = $stmt->get_result();

// Set headers to force download of CSV file with the event title in the filename
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $sanitizedTitle . '_registration.csv"');

// Open a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Output the column headings in your desired order
fputcsv($output, ['Registration Timestamp', 'Student Number', 'Name', 'Attended', 'Attend Timestamp']);

// Output each row of data in the desired column order
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['registrationTimestamp'],  // Registration Timestamp
        $row['studentNumber'],          // Student Number
        $row['name'],                   // Name
        $row['attended'] == 1 ? 'Yes' : 'No',  // Attended (Yes/No)
        $row['attendTimestamp']         // Attend Timestamp
    ]);
}

// Close the output stream
fclose($output);

// Close the database connections
$stmtEvent->close();
$stmt->close();
$conn->close();
exit;
