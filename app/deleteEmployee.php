<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: loginEmployee.php");
    exit;
}

include("dbcon.php");

// Ensure the user is an admin
$userId = $_SESSION['id'];
$userQuery = "SELECT userTypeID FROM employeeuser WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();

if ($userData['userTypeID'] != 6) {
    $_SESSION['message'] = "Access Denied.";
    $_SESSION['status'] = "error";
    header("Location: adminEmployeeList.php");
    exit;
}

// Validate the employee ID and PIN
if (isset($_GET['id']) && isset($_GET['pin'])) {
    $employeeID = $_GET['id'];
    $pin = $_GET['pin'];

    // Retrieve the user's stored hashed PIN from the database
    $pinQuery = "SELECT pin FROM employeeuser WHERE id = ?";
    $stmt = $conn->prepare($pinQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $pinResult = $stmt->get_result();
    $pinData = $pinResult->fetch_assoc();

    // Verify the entered PIN against the stored hashed PIN
    if (password_verify($pin, $pinData['pin'])) {
        // Proceed with deletion if PIN is verified
        $deleteQuery = "DELETE FROM employeeuser WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $employeeID);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Employee successfully deleted.";
            $_SESSION['status'] = "success";
        } else {
            $_SESSION['message'] = "Error deleting employee.";
            $_SESSION['status'] = "error";
        }
    } else {
        $_SESSION['message'] = "Invalid PIN.";
        $_SESSION['status'] = "error";
    }
} else {
    $_SESSION['message'] = "Invalid request.";
    $_SESSION['status'] = "error";
}

$stmt->close();
$conn->close();

// Redirect back to employee list
header("Location: adminEmployeeList.php");
exit;
?>
