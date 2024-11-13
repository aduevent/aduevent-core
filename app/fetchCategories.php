<?php
include("dbcon.php");

if (isset($_POST['organizationID']) && isset($_POST['pointSystemID']) && isset($_POST['academicYear'])) {
    $organizationID = $_POST['organizationID'];
    $pointSystemID = $_POST['pointSystemID'];
    $academicYear = $_POST['academicYear'];

    if ($pointSystemID == 1) {
        $queryCategory = "SELECT * FROM pointSystemCategory 
                          WHERE pointSystemID='$pointSystemID' 
                          AND pointSystemCategoryID NOT IN 
                          (SELECT pointSystemCategoryID FROM grading WHERE organizationID='$organizationID' AND academicYear = '$academicYear')";
        
        $resultCategory = mysqli_query($conn, $queryCategory);

        if ($resultCategory) {
            $options = "<option value=''>Select Event Category</option>";
            while ($row_event_category = mysqli_fetch_assoc($resultCategory)) {
                $options .= "<option value='" . $row_event_category['pointSystemCategoryID'] . "'>" . $row_event_category['pointSystemCategoryDescription'] . "</option>";
            }
            echo $options;
        } else {
            echo "Error fetching event categories: " . mysqli_error($conn);
        }
    } elseif ($pointSystemID == 2) {
        $hasExistingGrade = false;
        $queryExistingGrades = "SELECT * FROM grading WHERE organizationID='$organizationID' AND pointSystemCategoryID BETWEEN 6 AND 9 AND academicYear = '$academicYear'";
        $resultExistingGrades = mysqli_query($conn, $queryExistingGrades);

        if (mysqli_num_rows($resultExistingGrades) > 0) {
            $hasExistingGrade = true;
        }

        if ($hasExistingGrade) {
            echo "This organization has existing grades for this criteria.";
            exit;
        } else {
            echo "Error fetching criteria: " . mysqli_error($conn);
        }
    }
}
?>
