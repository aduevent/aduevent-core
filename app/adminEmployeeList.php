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
$activePage = "adminEmployeeList";

// Employee list query with search functionality
$searchTermEmployee = isset($_POST['searchEmployee']) ? "%" . $_POST['searchEmployee'] . "%" : "";
$empQuery = "SELECT e.id, e.name, e.email, o.organizationName, ut.userTypeDescription
             FROM employeeuser e
             LEFT JOIN organization o ON e.organizationID = o.organizationID
             JOIN usertype ut ON e.userTypeID = ut.userTypeID
             WHERE e.userTypeID BETWEEN 3 AND 12";

if (!empty($searchTermEmployee)) {
    $empQuery .= " AND e.name LIKE ?";
}

$empStmt = $conn->prepare($empQuery);
if (!empty($searchTermEmployee)) {
    $empStmt->bind_param("s", $searchTermEmployee);
}
$empStmt->execute();
$empResult = $empStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee List</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            margin-left: 20%;
            padding-top: 10px;
        }
        .table {
            font-size: 0.85rem;
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
        .btn-sm {
            font-size: 0.75rem;
        }
        .edit-btn-logo, .delete-btn-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            color: #007bff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 16px;
            text-decoration: none;
            background: none;
        }
        .add-btn-logo { 
            background-color: #007bff; 
            margin: 0; 
            padding: 0; 
            text-decoration: none; 
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }
        .delete-btn-logo {  
            color: #dc3545; /* Red text color for delete button */
        }
        .add-btn-logo:hover, .edit-btn-logo:hover, .delete-btn-logo:hover { 
            opacity: 0.8; 
        }
    </style>
    <script>
        function openDeleteModal(empId) {
            document.getElementById('deleteEmpId').value = empId;
            $('#deleteModal').modal('show');
        }

        function confirmDelete() {
            const empId = document.getElementById('deleteEmpId').value;
            const pin = document.getElementById('deletePin').value;

            if (pin) {
                // Redirect to the delete script with the employee ID and PIN
                window.location.href = `deleteEmployee.php?id=${empId}&pin=${pin}`;
            } else {
                alert("Please enter your PIN.");
            }
        }
    </script>
</head>
<body>
    
    <div class="container">
        <!-- Header Section -->
        <div class="card mt-4 mb-3">
            <div class="card-body">
                <h5 class="card-title text-center">Employee Users</h5>
            </div>
        </div>
        <!-- Display Notification -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info alert-dismissible fade show text-center mt-4" role="alert">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']); // Clear the message after displaying
                ?>
            </div>
        <?php endif; ?>

        <!-- Employee List Section -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <a href="addEmployee.php" class="add-btn-logo" title="Add Employee">
                        <i class="fas fa-plus"></i>
                    </a>
                    <div class="d-flex justify-content-center" style="margin-bottom: 15px;">
                        <div class="input-group w-100" style="max-width: 600px;">
                            <form method="POST" class="d-flex">
                                <input type="text" class="form-control form-control-sm" name="searchEmployee" placeholder="Search by Employee Name" value="<?php echo isset($_POST['searchEmployee']) ? $_POST['searchEmployee'] : ''; ?>">
                                <button type="submit" class="btn btn-primary btn-sm" style="margin-left: 10px;">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Organization</th>
                                <th>User Type</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($empData = $empResult->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $empData['id']; ?></td>
                                <td><?php echo $empData['name']; ?></td>
                                <td><?php echo $empData['email']; ?></td>
                                <td><?php echo $empData['organizationName']; ?></td>
                                <td><?php echo $empData['userTypeDescription']; ?></td>
                                <td>
                                    <a href="editEmployee.php?id=<?php echo $empData['id']; ?>" class="btn-sm edit-btn-logo" data-toggle="tooltip" title="Edit Employee">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                                <td>
                                    <button class="btn-sm delete-btn-logo" data-toggle="tooltip" title="Delete Employee" onclick="openDeleteModal(<?php echo $empData['id']; ?>);">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                </div>
                <div class="modal-body">
                    <p>Please enter your PIN to confirm deletion.</p>
                    <input type="password" class="form-control" id="deletePin" placeholder="Enter your PIN">
                    <input type="hidden" id="deleteEmpId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="$('#deleteModal').modal('hide')">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="confirmDelete()">Confirm</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Updated Bootstrap and jQuery for compatibility -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
