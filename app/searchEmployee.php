<?php
include("dbcon.php");

$searchTermEmployee = isset($_POST['searchEmployee']) ? "%" . $_POST['searchEmployee'] . "%" : "%";

// Prepare the SQL query
$empQuery = "SELECT e.id, e.name, e.email, o.organizationName, ut.userTypeDescription
             FROM employeeuser e
             LEFT JOIN organization o ON e.organizationID = o.organizationID
             JOIN usertype ut ON e.userTypeID = ut.userTypeID
             WHERE e.userTypeID BETWEEN 3 AND 12
             AND e.name LIKE ?";

$empStmt = $conn->prepare($empQuery);
$empStmt->bind_param("s", $searchTermEmployee);
$empStmt->execute();
$empResult = $empStmt->get_result();

// Display results as HTML
if ($empResult->num_rows > 0) {
    echo '<table class="table table-striped table-bordered">
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
            <tbody>';
    while ($empData = $empResult->fetch_assoc()) {
        echo '<tr>
                <td>' . $empData['id'] . '</td>
                <td>' . $empData['name'] . '</td>
                <td>' . $empData['email'] . '</td>
                <td>' . $empData['organizationName'] . '</td>
                <td>' . $empData['userTypeDescription'] . '</td>
                <td><a href="editEmployee.php?id=' . $empData['id'] . '" class="btn-sm edit-btn-logo" title="Edit Employee"><i class="fas fa-edit"></i></a></td>
                <td><a href="deleteEmployee.php?id=' . $empData['id'] . '" class="btn-sm delete-btn-logo" title="Delete Employee" onclick="return confirm(\'Are you sure you want to delete this employee?\');"><i class="fas fa-trash-alt"></i></a></td>
              </tr>';
    }
    echo '</tbody></table>';
} else {
    echo '<p>No employees found.</p>';
}
?>
