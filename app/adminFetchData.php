<?php
include 'dbcon.php';

if (isset($_POST['areaID']) && isset($_POST['areaDescription'])) {
    $areaID = $_POST['areaID'];
    $areaDescription = $_POST['areaDescription'];

    // Begin form to handle edits
    echo '<form method="POST" action="adminSaveChanges.php">';
    echo '<input type="hidden" name="areaID" value="' . htmlspecialchars($areaID) . '">';
    echo '<h3>' . htmlspecialchars($areaDescription) . '</h3>';
    echo '<div class="label-container">';
    echo '<div class="point-basis-container">';
    echo '<label>Criteria: </label><input type="text" name="areaDescription" value="' . htmlspecialchars($areaDescription) . '"><br><br>';
    echo '</div>';
    echo '</div>';
    // Check if it's the special "Compliance Review" area
    if ($areaID == 999) {
        // Fetch participation data from matrixparticipation table for Compliance Review
        $participationQuery = "SELECT participationID, participationDescription, participationValue FROM matrixparticipation ORDER BY participationValue ASC";
        $stmt = $conn->prepare($participationQuery);
        $stmt->execute();
        $participationResult = $stmt->get_result();

        if ($participationResult->num_rows > 0) {
            echo '<div class="criteria-container">';
            while ($participationRow = $participationResult->fetch_assoc()) {
                echo '<div class="point-basis-container">';
                echo '<input type="hidden" name="participationID[]" class="narrow-input" value="' . htmlspecialchars($participationRow['participationID']) . '">';
                echo '<input type="number" name="participationValue[]" class="narrow-input" value="' . htmlspecialchars($participationRow['participationValue']) . '">';
                echo '<input type="text" name="participationDescription[]" value="' . htmlspecialchars($participationRow['participationDescription']) . '"><br>';
                echo '</div>';
            }
        } else {
            echo 'No records found in matrixparticipation.';
        }
        echo '</div>';
    } else {
        // Fetch criteria details from matrixcriteria table if it's a standard area
        $criteriaQuery = "SELECT criteriaID, criteriaDescription FROM matrixcriteria WHERE areaID = ?";
        $stmt = $conn->prepare($criteriaQuery);
        $stmt->bind_param("i", $areaID);
        $stmt->execute();
        $criteriaResult = $stmt->get_result();

        if ($criteriaResult->num_rows > 0) {
            // Display each criteria
            while ($criteriaRow = $criteriaResult->fetch_assoc()) {
                $criteriaID = $criteriaRow['criteriaID'];
                echo '<div class="criteria-container">';
                echo '<div class="point-basis-container">';
                echo '<label>Sub-criteria: </label>';
                echo '<input type="hidden" name="criteriaID[]" value="' . htmlspecialchars($criteriaID) . '">';
                echo '<input type="text" name="criteriaDescription[]" value="' . htmlspecialchars($criteriaRow['criteriaDescription']) . '">';
                echo '</div>';

                // Fetch matrix point basis details for each criteriaID
                $pointQuery = "SELECT pointBasisID, value, pointBasisDescription FROM matrixpointbasis WHERE criteriaID = ? ORDER BY value ASC";
                $pointStmt = $conn->prepare($pointQuery);
                $pointStmt->bind_param("i", $criteriaID);
                $pointStmt->execute();
                $pointResult = $pointStmt->get_result();
            
                if ($pointResult->num_rows > 0) {
                    // Display each point basis in a single line
                    while ($pointRow = $pointResult->fetch_assoc()) {
                        echo '<div class="point-basis-container">';
                        echo '<label>' . htmlspecialchars($pointRow['value']) . ' - </label>';
                        echo '<input type="hidden" name="pointBasisID[]" value="' . htmlspecialchars($pointRow['pointBasisID']) . '">';
                        echo '<input type="text" name="pointBasisDescription[]" value="' . htmlspecialchars($pointRow['pointBasisDescription']) . '">';
                        echo '</div>'; // Close point-basis-container
                    }
                }
                echo '</div>'; // Close criteria-container
                $pointStmt->close();
            }
        } else {
            // If no criteria in matrixcriteria, show matrixpointbasis based on areaID
            $pointQuery = "SELECT pointBasisID, value, pointBasisDescription FROM matrixpointbasis WHERE areaID = ? ORDER BY value ASC";
            $pointStmt = $conn->prepare($pointQuery);
            $pointStmt->bind_param("i", $areaID);
            $pointStmt->execute();
            $pointResult = $pointStmt->get_result();

            if ($pointResult->num_rows > 0) {
                echo '<div class="criteria-container">';
                while ($pointRow = $pointResult->fetch_assoc()) {
                    echo '<div class="point-basis-container">';
                        echo '<label>' . htmlspecialchars($pointRow['value']) . ' - </label>';
                        echo '<input type="hidden" name="pointBasisID[]" value="' . htmlspecialchars($pointRow['pointBasisID']) . '">';
                        echo '<input type="text" name="pointBasisDescription[]" value="' . htmlspecialchars($pointRow['pointBasisDescription']) . '">';
                        echo '</div>';
                    }
            } else {
                echo 'No records found in matrixcriteria or matrixpointbasis for this area.';
            }
            echo '</div>';
            $pointStmt->close();
        }
        $stmt->close();
    }

    // Add Save Changes button
    echo '<br><button type="submit" class="submit-button">Save Changes</button>';
    echo '</form>';
}
$conn->close();
?>
