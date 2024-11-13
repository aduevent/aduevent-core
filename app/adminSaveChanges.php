<?php
// Include database connection
include 'dbcon.php';

// Retrieve areaID and other details from the form submission
$areaID = $_POST['areaID'];
$areaDescription = $_POST['areaDescription'];
$criteriaIDs = $_POST['criteriaID'] ?? [];
$criteriaDescriptions = $_POST['criteriaDescription'] ?? [];
$pointBasisIDs = $_POST['pointBasisID'] ?? [];
$pointBasisDescriptions = $_POST['pointBasisDescription'] ?? [];

// New variables for matrixparticipation table
$participationIDs = $_POST['participationID'] ?? [];
$participationDescriptions = $_POST['participationDescription'] ?? [];
$participationValues = $_POST['participationValue'] ?? [];

// Update areaDescription in matrixarea table
$updateAreaQuery = "UPDATE matrixarea SET areaDescription = ? WHERE areaID = ?";
$stmt = $conn->prepare($updateAreaQuery);
$stmt->bind_param("si", $areaDescription, $areaID);
$stmt->execute();
$stmt->close();

// Update criteria descriptions in matrixcriteria table
foreach ($criteriaIDs as $index => $criteriaID) {
    $criteriaDescription = $criteriaDescriptions[$index];
    $updateCriteriaQuery = "UPDATE matrixcriteria SET criteriaDescription = ? WHERE criteriaID = ?";
    $stmt = $conn->prepare($updateCriteriaQuery);
    $stmt->bind_param("si", $criteriaDescription, $criteriaID);
    $stmt->execute();
    $stmt->close();
}

// Update point basis descriptions in matrixpointbasis table
foreach ($pointBasisIDs as $index => $pointBasisID) {
    $pointBasisDescription = $pointBasisDescriptions[$index];
    $updatePointBasisQuery = "UPDATE matrixpointbasis SET pointBasisDescription = ? WHERE pointBasisID = ?";
    $stmt = $conn->prepare($updatePointBasisQuery);
    $stmt->bind_param("si", $pointBasisDescription, $pointBasisID);
    $stmt->execute();
    $stmt->close();
}

// Update participation descriptions and values in matrixparticipation table
foreach ($participationIDs as $index => $participationID) {
    $participationDescription = $participationDescriptions[$index];
    $participationValue = $participationValues[$index];
    $updateParticipationQuery = "UPDATE matrixparticipation SET participationDescription = ?, participationValue = ? WHERE participationID = ?";
    $stmt = $conn->prepare($updateParticipationQuery);
    $stmt->bind_param("sii", $participationDescription, $participationValue, $participationID);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

// Redirect back to matrix.php with a success message
header("Location: adminMatrix.php?message=Changes saved successfully");
exit();
?>
