<?php
session_start();
if (!isset($_SESSION["id"]) || !isset($_SESSION["access"])) {
    header("Location: loginEmployee.php");
    exit();
}
include "dbcon.php";

$userId = $_SESSION["id"];
$userQuery =
    "SELECT name, email, profilePicture FROM employeeuser WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$userName = $userData["name"];
$email = $userData["email"];
$dp = $userData["profilePicture"];
// Fetch organizations for dropdown
$orgQuery = "SELECT organizationID, organizationName FROM organization";
$orgResult = mysqli_query($conn, $orgQuery);

// Fetch point system categories for dropdown
$pointSystemQuery =
    "SELECT pointSystemCategoryID, pointSystemCategoryDescription FROM pointsystemcategory";
$pointSystemResult = mysqli_query($conn, $pointSystemQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Accomplishment Report</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <?php
    include "navbar.php";
    $activePage = "osaReport";
    ?>
    <style>
        .container {
            display: flex;
        }
        .filter-column {
            flex: 0 0 30%; /* 30% width */
            padding: 20px;
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
            height: 87vh;
            border-radius: 20px; /* Adjust the value for desired roundness */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); /* Adds a subtle shadow effect */
        }
        .table-column {
            flex: 1; /* Takes up the remaining space (70%) */
            padding: 20px;
            overflow: auto; /* Enables scrolling both vertically and horizontally */
            height: 87vh;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
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
        button:hover {
            background-color: #0056b3;
        }.filter-header {
            margin-bottom: 20px; /* Space below the header */
        }
        .filter-header h2 {
            margin: 0; /* Remove default margin */
            font-size: 1.5em;
        }
        .filter-header p {
            font-size: 1em; /* Font size for instructions */
            color: #333; /* Dark gray color for the instruction text */
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
            <h2 style="color: #000080;"><strong>Filter by Criteria</strong></h2>
            <p>Select your criteria to customize the displayed results.</p>
        </div>
        <form id="filtersForm">
            <div class="form-group">
                <label for="organization">Select Organization:</label>
                <select id="organization" name="organization" class="form-control" style="border-radius: 50px;">
                    <option value="">All</option>
                    <?php while ($orgRow = mysqli_fetch_assoc($orgResult)) { ?>
                        <option value="<?php echo $orgRow[
                            "organizationID"
                        ]; ?>"><?php echo $orgRow[
    "organizationName"
]; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="startDate">Select Date Range:</label>
                <input type="date" id="startDate" name="startDate" class="form-control" style="border-radius: 50px;"> -
                <input type="date" id="endDate" name="endDate" class="form-control" style="border-radius: 50px;">
            </div>
            <div class="form-group">
                <label for="pointSystemCategory">Select Point System Category:</label>
                <select id="pointSystemCategory" name="pointSystemCategory" class="form-control" style="border-radius: 50px;">
                    <option value="">All</option>
                    <?php while (
                        $pointRow = mysqli_fetch_assoc($pointSystemResult)
                    ) { ?>
                        <option value="<?php echo $pointRow[
                            "pointSystemCategoryID"
                        ]; ?>"><?php echo $pointRow[
    "pointSystemCategoryDescription"
]; ?></option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="border-radius: 50px; background-color: #000080;">Apply Filters</button>
        </form>
        <button id="printBtn" class="btn btn-secondary mt-3" style="border-radius: 50px;">
            <i class="fas fa-file-pdf" style="margin-right: 5px;"></i> Download PDF
        </button>
        <a href="osaGradeReport.php" class="btn btn-link" style="color: #000080; text-decoration: underline;">Proceed to Grade Report &rarr;</a>
    </div>
    <div class="table-column">
        <div id="tablePreview">
        </div>
    </div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#filtersForm').on('submit', function(e) {
        e.preventDefault();  // Prevent the form from submitting the traditional way

        // Get filter values
        var organization = $('#organization').val();
        var startDate = $('#startDate').val();
        var endDate = $('#endDate').val();
        var pointSystemCategory = $('#pointSystemCategory').val();

        // Send AJAX request to fetch filtered data
        $.ajax({
            url: 'fetch_filtered_data.php',  // You will need to create this file to process the filters
            type: 'GET',
            data: {
                organization: organization,
                startDate: startDate,
                endDate: endDate,
                pointSystemCategory: pointSystemCategory
            },
            success: function(response) {
                // Update table preview with the fetched data
                $('#tablePreview').html(response);
            },
            error: function() {
                $('#tablePreview').html('<p>An error occurred while fetching data.</p>');
            }
        });
    });

    // Print button event handler
    $('#printBtn').on('click', function() {
        // Get filter values
        var organization = $('#organization').val();
        var startDate = $('#startDate').val();
        var endDate = $('#endDate').val();
        var pointSystemCategory = $('#pointSystemCategory').val();

        // Redirect to the PHP script that generates the PDF, passing the filters as URL parameters
        window.location.href = 'generate_pdf.php?organization=' + organization +
                                '&startDate=' + startDate +
                                '&endDate=' + endDate +
                                '&pointSystemCategory=' + pointSystemCategory + '&userName=' + encodeURIComponent(userName);
    });
</script>
</body>
</html>
