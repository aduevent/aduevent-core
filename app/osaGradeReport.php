<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginEmployee.php");
    exit;
}
include 'dbcon.php';

$userId = $_SESSION['id'];
$userQuery = "SELECT name, email, profilePicture FROM employeeuser WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture'];

$academicYearQuery = "SELECT DISTINCT academicYear FROM grading ORDER BY academicYear DESC";
$academicYearResult = mysqli_query($conn, $academicYearQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Accomplishment Report</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <?php include 'navbar.php'; 
    $activePage = "osaGradeReport"; ?>
    <style>
    .container {
        display: flex;
    }
    button {
        margin-top: 20px;
        padding: 10px 15px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .filter-column {
        flex: 0 0 30%;
        padding: 20px;
        background-color: #f8f9fa;
        position: sticky;
        top: 0;
        height: 87vh;
        border-radius: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }
    .table-column {
        flex: 1;
        padding: 20px;
        overflow: auto;
        height: 87vh;
    }
    table {
        width: 95%;
        border-collapse: collapse;
    }
    table th, table td {
        padding: 8px;
        border: 1px solid #ddd;
        text-align: center;
    }
    .filter-header {
        margin-bottom: 20px; 
    }
    .filter-header h2 {
        margin: 0;
        font-size: 1.5em;
    }
    .filter-header p {
        font-size: 1em;
        color: #333;
    }
    </style>
    <script>
        const userName = <?php echo json_encode($userName); ?>;
    </script>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container">
        <div class="filter-column">
            <div class="filter-header">
                <h2 style="color: #000080;"><strong>Filter by Academic Year</strong></h2>
                <p>Select academic year to customize the displayed results.</p>
            </div>
            <form id="filtersForm">
                <div class="form-group">
                    <label for="academicYear">Select Academic Year:</label>
                    <select id="academicYear" name="academicYear" class="form-control" style="border-radius: 50px;">
                        <option value="">All Academic Years</option>
                        <?php
                        while ($yearRow = mysqli_fetch_assoc($academicYearResult)) {
                            echo '<option value="' . htmlspecialchars($yearRow['academicYear']) . '">' . htmlspecialchars($yearRow['academicYear']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="border-radius: 50px; background-color: #000080;">Apply Filters</button>
            </form>
            <button id="printBtn" class="btn btn-secondary mt-3" style="border-radius: 50px;">
                <i class="fas fa-file-pdf" style="margin-right: 5px;"></i> Download PDF
            </button>
            <a href="osaReport.php" class="btn btn-link" style="color: #000080; text-decoration: underline;">&larr; Back to Event Accomplishment Report</a>
            </div>
            <div class="table-column">
                <div id="tablePreview">
            </div>
        </div>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Function to handle form submission and dynamically update table preview
    $('#filtersForm').on('submit', function(e) {
        e.preventDefault();  // Prevent the form from submitting the traditional way

        // Get filter values
        var academicYear = $('#academicYear').val();

        $.ajax({
            url: 'osaFilteredGrade.php',
            type: 'GET',
            data: {
                academicYear : academicYear
            },
            success: function(response) {
                $('#tablePreview').html(response);
            },
            error: function() {
                $('#tablePreview').html('<p>An error occurred while fetching data.</p>');
            }
        });
    });
    $('#printBtn').on('click', function() {
    var academicYear = $('#academicYear').val();
    window.location.href = 'osaGradePdf.php?academicYear=' + academicYear + '&userName=' + encodeURIComponent(userName);
});
</script>