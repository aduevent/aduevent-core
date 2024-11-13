<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include("dbcon.php");
    $pin = $_POST['pin'];
    $confirm_pin = $_POST['confirm_pin'];
    $response = array();

    if ($pin === $confirm_pin) {
        $hashedPin = password_hash($pin, PASSWORD_DEFAULT);
        $studentID = $_SESSION['id'];
        $query = "UPDATE employeeuser SET pin = ? WHERE id = ?";
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            $response['status'] = 'error';
            $response['message'] = 'SQL Error: ' . $conn->error;
        } else {
            $stmt->bind_param("si", $hashedPin, $studentID);
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'PIN set successfully.';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Execution Error: ' . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'PINs do not match.';
    }
    echo json_encode($response);
}
?>
