<?php

session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginStudent.php");
    exit;
}

include("dbcon.php");

$userId = $_SESSION['id'];

// Fetch user information
$userQuery = "
  SELECT su.name, su.email, o.organizationLogo as profilePicture
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

$currentMonth = date('F'); // Full month name
$currentYear = date('Y'); // Full year

if (!isset($_GET['eventID']) || empty($_GET['eventID'])) {
    header("Location: rsoIndex.php");
    exit();
}

$eventID = $_GET['eventID'];

// Fetch event title
$query = "SELECT eventTitle FROM event WHERE eventID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $eventID);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the event title from the result
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $eventTitle = $row['eventTitle'];
} else {
    echo "No event found with the specified ID.";
}

// Count registered students
$count_query = "SELECT COUNT(*) AS registered_students FROM eventregistration WHERE eventID = ?";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("i", $eventID);
$count_stmt->execute();
$count_stmt->bind_result($registered_students);
$count_stmt->fetch();
$count_stmt->close();

// Count survey responses
$survey_query = "SELECT COUNT(*) AS surveyrespondents FROM feedbackresponse WHERE eventID = ?";
$survey_stmt = $conn->prepare($survey_query);
$survey_stmt->bind_param("i", $eventID);
$survey_stmt->execute();
$survey_stmt->bind_result($surveyrespondents);
$survey_stmt->fetch();
$survey_stmt->close();

// Count attendees
$attendance_query = "SELECT COUNT(*) AS total_attended FROM eventregistration WHERE eventID = ? AND attended = 1";
$attendance_stmt = $conn->prepare($attendance_query);
$attendance_stmt->bind_param("i", $eventID);
$attendance_stmt->execute();
$attendance_stmt->bind_result($total_attended);
$attendance_stmt->fetch();
$attendance_stmt->close();

// Fetch feedback responses and calculate average scores
$feedback_scores_query = "
    SELECT AVG(rating1) AS avg_rating1, AVG(rating2) AS avg_rating2, AVG(rating3) AS avg_rating3,
           AVG(rating4) AS avg_rating4, AVG(rating5) AS avg_rating5, AVG(rating6) AS avg_rating6,
           AVG(rating7) AS avg_rating7, AVG(rating8) AS avg_rating8, AVG(rating9) AS avg_rating9,
           AVG(rating10) AS avg_rating10
    FROM feedbackresponse 
    WHERE eventID = ?";
$feedback_scores_stmt = $conn->prepare($feedback_scores_query);
$feedback_scores_stmt->bind_param("i", $eventID);
$feedback_scores_stmt->execute();
$feedback_scores_result = $feedback_scores_stmt->get_result();
$average_scores = $feedback_scores_result->fetch_assoc();

// Fetch questions from the feedback table
$questions_query = "SELECT question1, question2, question3, question4, question5, question6, question7, question8, question9, question10 FROM feedback WHERE eventID = ? LIMIT 1";
$questions_stmt = $conn->prepare($questions_query);
$questions_stmt->bind_param("i", $eventID);
$questions_stmt->execute();
$questions_result = $questions_stmt->get_result();

if ($questions_result->num_rows > 0) {
    $questions_row = $questions_result->fetch_assoc();
} else {
    echo "No questions found for this event yet.";
}

$questions_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Registrations</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Include Chart.js -->
    <?php include 'rsoNavbar.php'; $activepage = "rsoRegistration"; ?>
    <style>
        .scrollable-table {
            max-height: 400px; /* Adjust height as needed */
            overflow-y: auto;
            border: 1px solid #ddd;
            margin-top: 10px;
        }
        canvas {
            width: 100%;
            height: 400px;
        }
        .graph-container {
            max-width: 600px; 
        }
        .questions-container {
            max-height: 400px; 
            overflow-y: auto; 
            border: 1px solid #ddd;
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container" style="margin-left: 5px;">
        <ul class="list-inline" style="margin-bottom: 0;">
            <li class="list-inline-item">
                <button onclick="window.location.href='rsoDashboard.php';"
                        class="btn btn-light d-flex justify-content-center align-items-center"
                        style="width: 40px; height: 40px; border-radius: 50%; padding: 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #f9f9f9;">
                    <i class="bi bi-arrow-left-circle" style="color: #000080; font-size: 20px;"></i>
                </button>
            </li>
            <li class="list-inline-item">
                <button onclick="javascript:void(0);"
                        class="btn btn-secondary justify-content-center"
                        style="width: 300px; height: 40px; border-radius: 50px; padding: 0 15px; border: none; background-color: #f1f1f1;">
                    <span style="color: #000000; font-weight: bold;">Event Dashboard</span>
                </button>
            </li>
        </ul>
        <h2 class="text-center" style="margin-top: 20px;">Event Title: <strong><?php echo $eventTitle ?></strong></h2>
        <div class="row mt-4 align-items-left justify-content-left">
            <!-- Card to Show Total Registrations -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Registrations</h5>
                        <p class="card-text"><?php echo $registered_students; ?></p>
                    </div>
                </div>
            </div>

            <!-- Card to Show Total Attended -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Attended</h5>
                        <p class="card-text"><?php echo $total_attended; ?></p>
                    </div>
                </div>
            </div>

            <!-- Card to Show Survey Responses -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Survey Responses</h5>
                        <p class="card-text"><?php echo $surveyrespondents; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Container to hold the graph and questions side by side -->
        <div class="row mt-5">
            <!-- Graph Column -->
            <div class="col-md-6 graph-container">
                <h3 class="text-center">Average Scores per Question</h3>
                <div class="card">
                    <div class="card-body">
                        <canvas id="averageScoresChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Questions Column -->
            <div class="col-md-6 questions-container">
                <h3 class="text-center">Questions</h3>
                <div class="scrollable-table">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Question No.</th>
                                <th>Question Text</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            for ($i = 1; $i <= 10; $i++) {
                                if (!empty($questions_row['question' . $i])) {
                                    echo "<tr>";
                                    echo "<td>Q" . $i . "</td>";
                                    echo "<td>" . htmlspecialchars($questions_row['question' . $i]) . "</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('averageScoresChart').getContext('2d');
        const averageScores = [
            <?php 
                echo implode(',', array_map(function($score) { return $score !== null ? round($score, 2) : 0; }, $average_scores)); 
            ?>
        ]; // Average scores
        const questionLabels = ['Q1', 'Q2', 'Q3', 'Q4', 'Q5', 'Q6', 'Q7', 'Q8', 'Q9', 'Q10']; // Question labels

        const myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: questionLabels,
                datasets: [{
                    label: 'Average Score',
                    data: averageScores,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)', // Bar color
                    borderColor: 'rgba(54, 162, 235, 1)', // Bar border color
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                const questionIndex = tooltipItem.dataIndex;
                                return `${questionLabels[questionIndex]}: ${tooltipItem.raw.toFixed(2)}`; // Show question and score
                            }
                        }
                    },
                    legend: {
                        display: false // Hide legend
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Average Score'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
