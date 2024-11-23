<?php
include "dbcon.php";

// Get filter values from the request
$organizationID = isset($_GET["organization"]) ? $_GET["organization"] : "";
$startDate = isset($_GET["startDate"]) ? $_GET["startDate"] : "";
$endDate = isset($_GET["endDate"]) ? $_GET["endDate"] : "";
$pointSystemCategoryID = isset($_GET["pointSystemCategory"])
    ? $_GET["pointSystemCategory"]
    : "";

// Build the query with filters, group by organizationID, and filter by eventStatus and eventDate
$query = "SELECT e.*, o.organizationName, p.pointSystemCategoryDescription
          FROM event e
          JOIN organization o ON e.organizationID = o.organizationID
          JOIN pointSystemCategory p ON e.pointSystemCategoryID = p.pointSystemCategoryID
          WHERE e.eventStatus = 1 AND e.eventDate < NOW()";

// Apply additional filters based on user input
if (!empty($organizationID)) {
    $query .= " AND e.organizationID = '$organizationID'";
}

// Filter by the event date range
if (!empty($startDate) && !empty($endDate)) {
    $query .= " AND e.eventDate BETWEEN '$startDate' AND '$endDate'";
}

if (!empty($pointSystemCategoryID)) {
    $query .= " AND e.pointSystemCategoryID = '$pointSystemCategoryID'";
}

$query .=
    " GROUP BY e.organizationID, e.eventID ORDER BY o.organizationName, e.eventDate";

$result = mysqli_query($conn, $query);

// Generate the table with filtered data
if (mysqli_num_rows($result) > 0) {
    echo '<div style="text-align: center; margin-bottom: 20px;">
            <img src="reportheader.png" alt="Report Header" style="max-width: 40%; height: auto;">
            <h2 style="font-weight: bold; margin-top: 7px;">Event Accomplishment Report</h2>
          </div>';
    echo '<table border="1">
            <tr>
                <th>Event Title</th>
                <th>Organization</th>
                <th>Event Date</th>
                <th>Category</th>
            </tr>';

    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>
                <td>' .
            $row["eventTitle"] .
            '</td>
                <td>' .
            $row["organizationName"] .
            '</td>
                <td>' .
            $row["eventDate"] .
            '</td>
                <td>' .
            $row["pointSystemCategoryDescription"] .
            '</td>
              </tr>';
    }

    echo "</table>";
} else {
    echo "<p>No results found.</p>";
}
?>
