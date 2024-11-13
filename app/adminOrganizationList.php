<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: loginEmployee.php");
    exit;
}

include("dbcon.php");

// Retrieve user information
$userId = $_SESSION['id'];
$userQuery = "SELECT name, email, profilePicture, userTypeID FROM employeeuser WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();

$userName = $userData['name'];
$email = $userData['email'];
$_SESSION['access'] = $userData['userTypeID'];

// Ensure the user is an admin
if ($_SESSION['access'] != 6) {
    echo "Access Denied.";
    exit;
}

include("adminNavbar.php");
$activePage = "adminOrganizationList";

// Fetch all organizations and set up search functionality
$orgQuery = "SELECT o.organizationID, o.organizationName, ot.organizationTypeName, o.organizationEmail FROM organization o JOIN organizationtype ot ON o.organizationTypeID = ot.organizationTypeID";
$searchTerm = isset($_POST['search']) ? "%" . $_POST['search'] . "%" : "";
if (!empty($searchTerm)) {
    $orgQuery .= " WHERE o.organizationName LIKE ?";
}
$stmt = $conn->prepare($orgQuery);
if (!empty($searchTerm)) {
    $stmt->bind_param("s", $searchTerm);
}
$stmt->execute();
$orgResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Organization List</title>
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
            background:none;
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
            text-decoration: none;
        }
        .delete-btn-logo {  
            color: #dc3545; /* Red text color for delete button */
        }
        .add-btn-logo:hover, .edit-btn-logo:hover, .delete-btn-logo:hover { 
            opacity: 0.8; 
        }
        

    </style>
    <script>
        function openDeleteModal(orgId) {
            document.getElementById('deleteOrgId').value = orgId;
            $('#deleteModal').modal('show');
        }

        function confirmDelete() {
            const orgId = document.getElementById('deleteOrgId').value;
            const pin = document.getElementById('deletePin').value;

            if (pin) {
                // Redirect to the delete script with the organization ID and PIN
                window.location.href = `deleteOrganization.php?id=${orgId}&pin=${pin}`;
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
                <h5 class="card-title text-center">Registered Student Organization</h5>
            </div>
        </div>

        <!-- Display session message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info alert-dismissible fade show text-center mt-4" role="alert">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']); // Clear the message after displaying
                ?>
            </div>
        <?php endif; ?>

        <!-- Organization List Section -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <a href="addOrganization.php" class="add-btn-logo" title="Add Organization">
                        <i class="fas fa-plus"></i>
                    </a>
                    <div class="d-flex justify-content-center" style="margin-bottom: 15px;">
                        <div class="input-group w-100" style="max-width: 600px;">
                            <form method="POST" class="d-flex">
                                <input type="text" class="form-control form-control-sm" name="search" placeholder="Search by Organization Name" value="<?php echo isset($_POST['search']) ? $_POST['search'] : ''; ?>">
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
                                <th>Organization Name</th>
                                <th>Organization Type</th>
                                <th>Email</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($orgData = $orgResult->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $orgData['organizationID']; ?></td>
                                <td><?php echo $orgData['organizationName']; ?></td>
                                <td><?php echo $orgData['organizationTypeName']; ?></td>
                                <td><?php echo $orgData['organizationEmail']; ?></td>
                                <td>
                                    <a href="editOrganization.php?id=<?php echo $orgData['organizationID']; ?>" class="btn-sm edit-btn-logo" data-toggle="tooltip" title="Edit Organization">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                                <td>
                                    <button class="btn-sm delete-btn-logo" data-toggle="tooltip" title="Delete Organization" onclick="openDeleteModal(<?php echo $orgData['organizationID']; ?>);">
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
                <input type="hidden" id="deleteOrgId">
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
