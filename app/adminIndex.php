<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: loginEmployee.php");
    exit;
}

include("dbcon.php");
$userId = $_SESSION['id'];
$userQuery = "SELECT name, email, profilePicture, userTypeID, organizationID FROM employeeuser WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();

$_SESSION['access'] = $userData['userTypeID'];
$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture'];
$access = $_SESSION['access'];
$organizationID = $userData['organizationID'];

if ($_SESSION['access'] != 6) {
    echo "Access Denied.";
    exit;
}

include("adminNavbar.php");
$activePage = "adminIndex";

// Fetch organization count
$orgCountQuery = "SELECT COUNT(*) AS totalOrganizations FROM organization";
$orgCountResult = $conn->query($orgCountQuery);
$orgCount = $orgCountResult->fetch_assoc()['totalOrganizations'];

// Fetch employee count
$empCountQuery = "SELECT COUNT(*) AS totalEmployees FROM employeeuser";
$empCountResult = $conn->query($empCountQuery);
$empCount = $empCountResult->fetch_assoc()['totalEmployees'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            margin-left: 20%;
            padding-top: 20px;
        }

        .db-section {
            margin-bottom: 20px;
        }
        .card-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            color: white;
            background-color: #007bff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .icon-btn:hover {
            opacity: 0.8;
        }
        .icon-add {
            background-color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cards Section -->
        <div class="card-container">
            <div class="card text-white bg-primary mb-3" style="max-width: 18rem;">
                <div class="card-header">Total Organizations</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $orgCount; ?></h5>
                    <p class="card-text">Total number of organizations registered.</p>
                </div>
            </div>
            <div class="card text-white bg-success mb-3" style="max-width: 18rem;">
                <div class="card-header">Total Employees</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $empCount; ?></h5>
                    <p class="card-text">Total number of employees registered.</p>
                </div>
            </div>
        </div>

        <!-- Registered Organization List Section -->
        <div class="db-section card">
            <div class="card-body">
                <div class="section-title">
                    <h5 class="card-title">Registered Organization List</h5>
                    <div>
                        <button class="icon-btn icon-add" onclick="window.location.href='addOrganization.php'" title="Add Organization">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="icon-btn" onclick="window.location.href='adminOrganizationList.php'" title="Edit Organization List">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee List Section -->
        <div class="db-section card">
            <div class="card-body">
                <div class="section-title">
                    <h5 class="card-title">Employee List</h5>
                    <div>
                        <button class="icon-btn icon-add" onclick="window.location.href='addEmployee.php'" title="Add Employee">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="icon-btn" onclick="window.location.href='adminEmployeeList.php'" title="Edit Employee List">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matrix Section -->
        <div class="db-section card">
            <div class="card-body">
                <div class="section-title">
                    <h5 class="card-title">Matrix</h5>
                    <button class="icon-btn" onclick="window.location.href='adminMatrix.php'" title="Edit Matrix">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
