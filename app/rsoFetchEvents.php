<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginStudent.php");
    exit;
}
include("dbcon.php");
$userId = $_SESSION['id'];
$userQuery = "
    SELECT su.name, su.email, su.organizationID, o.organizationName, o.organizationLogo as profilePicture
    FROM studentuser su JOIN organization o ON su.organizationID = o.organizationID
    WHERE su.id = ?";

$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture'];
$orgId = $userData['organizationID'];


if (isset($_GET['categoryID']) && isset($_GET['academicYear'])) {
    $categoryID = $_GET['categoryID'];
    $academicYear = $_GET['academicYear'];

    // Parse the academic year to get the start and end dates
    list($startYear, $endYear) = explode('-', $academicYear);
    $academicYearStartDate = $startYear . '-08-01';
    $academicYearEndDate = $endYear . '-07-31';

    // Fetch the events for the selected category and academic year
    $sql = "SELECT eventTitle, eventDate, eventID FROM event 
            WHERE pointSystemCategoryID = $categoryID 
            AND organizationID = $orgId 
            AND eventDate BETWEEN '$academicYearStartDate' AND '$academicYearEndDate' AND eventStatus = '1'";

    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $eventTitle = $row['eventTitle'];
            $eventDate = $row['eventDate'];
            $eventID = $row['eventID'];

            // Fetch the registration count for the current event
            $regCountQuery = "SELECT COUNT(*) AS totalRegistrations FROM eventregistration WHERE eventID = $eventID";
            $regCountResult = $conn->query($regCountQuery);
            $regCount = $regCountResult->fetch_assoc()['totalRegistrations'];

            // Fetch the previous event details based on the eventDate
            $prevEventQuery = "SELECT eventID, eventDate FROM event 
                               WHERE eventDate < '$eventDate' 
                               AND organizationID = 12 
                               AND pointSystemCategoryID = $categoryID 
                               ORDER BY eventDate DESC LIMIT 1";
            $prevEventResult = $conn->query($prevEventQuery);
            $prevEventID = $prevEventResult->num_rows > 0 ? $prevEventResult->fetch_assoc()['eventID'] : null;

            $prevRegCount = 0;
            if ($prevEventID) {
                $prevRegCountQuery = "SELECT COUNT(*) AS totalRegistrations FROM eventregistration WHERE eventID = $prevEventID";
                $prevRegCountResult = $conn->query($prevRegCountQuery);
                $prevRegCount = $prevRegCountResult->fetch_assoc()['totalRegistrations'];
            }

            // Calculate the registration difference
            $regDifference = $regCount - $prevRegCount;
            $regDifferenceText = $regDifference > 0 ? "+$regDifference" : ($regDifference < 0 ? "$regDifference" : "0.00");
            $regColor = $regDifference > 0 ? 'green' : ($regDifference < 0 ? 'red' : 'gray');

            // Fetch the count of attendees who marked 'attended'
            $attendedCountQuery = "SELECT COUNT(*) AS attendedCount FROM eventregistration WHERE eventID = $eventID AND attended = 1";
            $attendedCountResult = $conn->query($attendedCountQuery);
            $attendedCount = $attendedCountResult->fetch_assoc()['attendedCount'];

            $prevAttendedCount = 0;
            if ($prevEventID) {
                $prevAttendedCountQuery = "SELECT COUNT(*) AS attendedCount FROM eventregistration WHERE eventID = $prevEventID AND attended = 1";
                $prevAttendedCountResult = $conn->query($prevAttendedCountQuery);
                $prevAttendedCount = $prevAttendedCountResult->fetch_assoc()['attendedCount'];
            }

            // Calculate the attended difference
            $attendedDifference = $attendedCount - $prevAttendedCount;
            $attendedDifferenceText = $attendedDifference > 0 ? "+$attendedDifference" : ($attendedDifference < 0 ? "$attendedDifference" : "0.00");
            $attendedColor = $attendedDifference > 0 ? 'green' : ($attendedDifference < 0 ? 'red' : 'gray');

            // Assign a color for the card based on the category
            $colors = [
                1 => 'rgba(255, 99, 132, 0.5)', // Organizational-Related Project
                2 => 'rgba(54, 162, 235, 0.5)', // Environmental Project
                3 => 'rgba(255, 206, 86, 0.5)', // Community Involvement Project
                4 => 'rgba(75, 192, 192, 0.5)', // Spiritual Enrichment Project
                5 => 'rgba(153, 102, 255, 0.5)'  // Organizational Development Project
            ];
            $cardColor = $colors[$categoryID];

            // Fetch the feedback data for average ratings
            $sqlFeedback = "SELECT 
                AVG(rating1) AS avg_rating1, 
                AVG(rating2) AS avg_rating2, 
                AVG(rating3) AS avg_rating3, 
                AVG(rating4) AS avg_rating4, 
                AVG(rating5) AS avg_rating5, 
                AVG(rating6) AS avg_rating6, 
                AVG(rating7) AS avg_rating7, 
                AVG(rating8) AS avg_rating8, 
                AVG(rating9) AS avg_rating9, 
                AVG(rating10) AS avg_rating10
                FROM feedbackresponse
                WHERE eventID = $eventID";

            $resultFeedback = $conn->query($sqlFeedback);
            $feedbackData = $resultFeedback->fetch_assoc();

            // Calculate the average rating only if there are valid ratings
            $validRatings = array_filter($feedbackData);
            $totalValidRatings = count($validRatings);
            $avgRating = $totalValidRatings > 0 ? array_sum($validRatings) / $totalValidRatings : 0.0; // Avoid division by zero
            $maxRating = 5.00;

            // Calculate the percentage of attendees
            $percentageAttended = ($regCount > 0) ? ($attendedCount / $regCount) * 100 : 0;

            $ratingQuery = "SELECT rating FROM grading WHERE eventID = $eventID AND academicYear = '$academicYear'";
            $ratingResult = $conn->query($ratingQuery);
            
            if ($ratingResult->num_rows > 0) {
                $rating = $ratingResult->fetch_assoc()['rating'];
                $osaRating = ($rating * 2) . "%";
            } else {
                $osaRating = "Not yet graded";
            }

            $responseQuery = "SELECT response FROM feedbackresponse WHERE eventID = $eventID";
            $responseResult = $conn->query($responseQuery);

            // Output the event details
            echo "<div class='card' style='background-color: $cardColor; color: white; margin-bottom: 10px; position: relative;'>"; // Added position: relative;
            echo "<h3>$eventTitle</h3>";
            echo "<p>Date: $eventDate</p>";

            echo '<div class="button-group" style="position: absolute; right: 10px; top: 10px;">'; // Positioned the button to the right
            echo '<a href="downloadPDF.php?eventID=' . $row["eventID"] . '" class="custom-circle-btn" style="display: inline-block; background-color: white; color: black; padding: 8px; border-radius: 50%; text-align: center; text-decoration: none;">'; // White filled button
            echo '<i class="bi bi-file-earmark-pdf" style="font-size: 18px;"></i>'; // PDF icon with font size
            echo '</a>';
            echo '</div>'; // End button group
            echo "</div>"; // End card
            
            echo "<div style='display: flex; flex-wrap: wrap; justify-content: space-between;'>";
            
            echo "<div class='card' style='width: 49%; margin-bottom: 20px;'>"; 
            echo "<h2 style='font-weight: bold;'>". number_format($avgRating, 2) ."/<span style='font-size: 12px; color: gray;'>$maxRating</span></h2>";
            echo "<p style='font-size: 12px;'>Average Rating <span style='font-size: 20px;'>⭐</span></p>";
            echo "</div>";
            
            echo "<div class='card' style='width: 49%; margin-bottom: 20px;'>";
            echo "<h2 style='font-weight: bold;'>". number_format($percentageAttended, 2) ."%</h2>";
            echo "<p style='font-size: 12px;'>Percentage of Attendees</p>";
            echo "</div>";
            
            echo "<div class='card' style='width: 49%; margin-bottom: 20px;'>";
            echo "<h2 style='font-weight: bold;'>$regCount</h2>";
            echo "<p style='font-size: 12px;'>Total Registrations</p>";
            echo "<div style='font-size: 14px;'><span style='color: $regColor;'>&#x25CF;</span> $regDifferenceText</div>";
            echo "</div>";
            
            echo "<div class='card' style='width: 49%; margin-bottom: 20px;'>";
            echo "<h2 style='font-weight: bold;'>$attendedCount</h2>";
            echo "<p style='font-size: 12px;'>Total Attended</p>";
            echo "<div style='font-size: 14px;'><span style='color: $attendedColor;'>&#x25CF;</span> $attendedDifferenceText</div>";
            echo "</div>";
            
            echo "<div class='card' style='width: 100%; margin-bottom: 20px;'>"; // Adjust width if needed
            echo "<h2 style='font-weight: bold;'>$osaRating</h2>";
            echo "<p style='font-size: 12px;'>OSA Given Rating</p>";
            echo "</div>";
            
            echo "</div>";
            
            echo "<div class='card' style='width: 100%; margin-bottom: 20px;'>";
            if ($responseResult->num_rows > 0) {
                echo "<h3>Feedback Responses</h3>";
                
                // Display each response in a separate card
                while ($responseRow = $responseResult->fetch_assoc()) {
                    $response = $responseRow['response'];
        
                    echo "<p style='margin: 0;'><strong>Anonymous:</strong> $response</p>";

                }
            } else {
                echo "<p>No feedback responses found for this event.</p>";
            }
            echo "</div>";
        }
    } else {
        echo "<p>No events found for this category and academic year.</p>";
    }
} else {
    echo "<p>Category ID and Academic Year are required.</p>";
}
?>
