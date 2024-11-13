<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginStudent.php");
    exit;
}
include("dbcon.php");
$userId = $_SESSION['id'];
$userQuery = "SELECT su.name, su.email, o.organizationLogo as profilePicture
                FROM studentuser su
                JOIN organization o ON su.organizationID = o.organizationID
                WHERE su.id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture'];

if (!isset($_GET['eventID']) || empty($_GET['eventID'])) {
    header("Location: rsoIndex.php");
    exit();
}

$eventID = $_GET['eventID'];
$sql = "SELECT eventTitle FROM event WHERE eventID = '$eventID'";
$result = $conn->query($sql);
$title = "";
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $title = $row['eventTitle'];
} else {
    echo "No event found with the provided eventID.";
}

$feedbackQuery = "SELECT COUNT(*) as feedbackCount FROM feedback WHERE eventID = ?";
$stmt = $conn->prepare($feedbackQuery);
$stmt->bind_param('i', $eventID);
$stmt->execute();
$feedbackResult = $stmt->get_result();
$feedbackData = $feedbackResult->fetch_assoc();
$count = $feedbackData['feedbackCount'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSO Feedback Form Creator</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" crossorigin="anonymous">
    <?php include 'rsoNavbar.php'; 
    $activePage = "rsoFeedbackFormCreation"; ?>
    <style>
        .form-group {
            margin-bottom: 5px;
            display: flex;
            flex-direction: row;
            align-items: center;
        }
        .form-label {
            background-color: #000080; 
            color: white; 
            padding: 10px 20px;
            border-radius: 50px;
            white-space: nowrap;
            flex-shrink: 0;
            height: 45px;
            margin-right: 5px;
        }
        .form-control {
            flex: 1; 
            border-radius: 50px;
            padding: 10px;
            border: 1px solid #ced4da;
            height: 45px;
            max-width: 70%;
        }
        .button-container {
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container event-container" style="margin-left: 5px;">
        <ul class="list-inline" style="margin-bottom: 10px;">
            <li class="list-inline-item"><button onclick="window.location.href='rsoEventHub.php';"
                class="btn btn-light d-flex justify-content-center align-items-center"
                style="width: 40px; height: 40px; border-radius: 50%; padding: 0;">
                <i class="bi bi-arrow-left-circle" style="color: #000080; font-size: 20px;"></i></button>
            </li>
            <li class="list-inline-item">
                <div class="d-flex justify-content-center align-items-center" style="border: 2px solid #000080; border-radius: 50px; padding: 5px 15px;">
                    <p class="academic-year-display mb-0" style="color: #000080;">Event Title: <?= htmlspecialchars($title); ?></p>
                </div>
            </li>
        </ul>
        <?php
        if ($count > 0) {
            echo "<p><i>Feedback questions for <strong>" . htmlspecialchars($title) . "</strong> have already been submitted.</i></p>";
        } else {
            echo '<p><i>This is the Feedback Creation Portal. You may create up to 10 questions related to <strong>' . htmlspecialchars($title) . '.</strong> Questions should be solely rating-based for optimal event analysis.</i></p>';
            echo '<form action="saveFeedbackForm.php" method="POST" id="feedbackForm">
            <input type="hidden" name="eventID" value="' . htmlspecialchars($eventID) . '">
            <div class="form-container" id="questionContainer">';
                for ($i = 1; $i <= 5; $i++) {
                    echo '<div class="form-group" id="questionGroup'.$i.'">
                    <label for="question'.$i.'" class="form-label">Question '.$i.'</label>
                    <input type="text" id="question'.$i.'" name="question'.$i.'" class="form-control" placeholder="Write your question here..." onclick="lockQuestion('.$i.')">
                    <button type="button" class="btn btn-secondary rounded-circle editBtn" onclick="editQuestion('.$i.')"><i class="bi bi-pencil"></i></button>
                    <button type="button" class="btn btn-danger rounded-circle deleteBtn" onclick="deleteQuestion('.$i.')"><i class="bi bi-trash"></i></button>
            </div>';
                }
                echo '</div>
                <div class="d-flex flex-column justify-content-center align-items-center mt-3">
                <button type="button" class="btn btn-primary d-flex align-items-center justify-content-center" onclick="addQuestion()" style=" border: 2px solid #000080; background-color: transparent; color: #000080; border-radius: 50%; width: 40px; height: 40px; position: relative;"><i class="bi bi-plus" style="font-size: 20px;"></i></button><br>
                <button type="submit" class="btn btn-primary" style="background-color: #000080; border-radius: 50px;" onclick="return validateForm();">Submit</button>
                </div></form>';
        }
        ?>
<script>
    let questionCount = 5;
    function addQuestion() { 
        if (questionCount < 10) {
            questionCount++;
            const questionContainer = document.getElementById('questionContainer');
            const newQuestion = `
            <div class="form-group" id="questionGroup${questionCount}">
                <label for="question${questionCount}" class="form-label">Question ${questionCount}</label>
                <input type="text" id="question${questionCount}" name="question${questionCount}" class="form-control" placeholder="Write your question here..." onclick="lockQuestion(${questionCount})">
                <button type="button" class="btn btn-secondary rounded-circle editBtn" onclick="editQuestion(${questionCount})"><i class="bi bi-pencil"></i></button>
                <button type="button" class="btn btn-danger rounded-circle deleteBtn" onclick="deleteQuestion(${questionCount})"><i class="bi bi-trash"></i></button>
            </div>`;
            questionContainer.insertAdjacentHTML('beforeend', newQuestion);
        } else {
            alert('You can only add up to 10 questions.');
        }
    }
    function lockQuestion(questionNumber) {
        const questionInput = document.getElementById('question' + questionNumber);
        if (questionInput.value.trim() !== '') {
            questionInput.disabled = true; // Disable input after text is entered
        }
    }
    function editQuestion(questionNumber) {
        const questionInput = document.getElementById('question' + questionNumber);
        questionInput.disabled = false; // Re-enable input for editing
        questionInput.focus(); // Focus the input for easier editing
    }
    function deleteQuestion(questionNumber) {
        const questionGroup = document.getElementById('questionGroup' + questionNumber);
        questionGroup.remove();
    }
    function validateForm() {
        let filledQuestions = 0;
        for (let i = 1; i <= questionCount; i++) {
            const questionInput = document.getElementById('question' + i);
            if (questionInput && questionInput.value.trim() !== '') {
                filledQuestions++;
            }
        }
        if (filledQuestions < 5) {
            alert('Please fill in at least 5 questions.');
            return false;
        }
        return true;
    }
</script>
</body>
</html>