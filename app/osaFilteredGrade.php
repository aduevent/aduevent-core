<?php
include('dbcon.php');

$academicYear = isset($_GET['academicYear']) ? $_GET['academicYear'] : '';

$orgQuery = "SELECT organizationID, organizationName FROM organization";
$orgResult = $conn->query($orgQuery);

$organizationData = [];

while ($orgRow = $orgResult->fetch_assoc()) {
    $organizationID = $orgRow['organizationID'];
    $organizationName = $orgRow['organizationName'];

    $ratingQuery = "SELECT pointSystemCategoryID, pointSystemID, rating 
                    FROM grading WHERE organizationID = ? AND academicYear = ?";
    $stmt = $conn->prepare($ratingQuery);
    $stmt->bind_param("is", $organizationID, $academicYear);
    $stmt->execute();
    $ratingResult = $stmt->get_result();

    $ratings = [
        '1' => '-', // Organizational-Related
        '2' => '-', // Community Involvement
        '3' => '-', // Spiritual Enrichment
        '4' => '-', // Environmental
        '5' => '-', // Organizational Development
        'Participation' => '-' // Participation and Compliance
    ];

    // Fill the ratings based on query results
    while ($ratingRow = $ratingResult->fetch_assoc()) {
        $categoryID = $ratingRow['pointSystemCategoryID'];
        $pointSystemID = $ratingRow['pointSystemID'];
        $rating = $ratingRow['rating'];

        // Populate appropriate category based on pointSystemID and pointSystemCategoryID
        if ($pointSystemID == 1 && isset($ratings[$categoryID])) {
            $ratings[$categoryID] = $rating;
        } elseif ($pointSystemID == 2) {
            $ratings['Participation'] = $rating;
        }
    }
    $sum = 0;
    for ($i = 1; $i <= 5; $i++) {
        if ($ratings[$i] !== '-') {
            $sum += $ratings[$i] * 2; // Multiply each rating by 2
        }
    }
    
    // Compute component averages
    $activityAverage = ($sum / 5) * 0.60; // Activity component divided by 5
    $participationValue = ($ratings['Participation'] !== '-') ? $ratings['Participation'] * 0.40 : 0; // Participation component
    $finalAverage = $activityAverage + $participationValue;

    // Store data for sorting later
    $organizationData[] = [
        'name' => $organizationName,
        'ratings' => $ratings,
        'average' => $finalAverage
    ];
}

// Sort organizations by average descending
usort($organizationData, function ($a, $b) {
    return $b['average'] <=> $a['average'];
});
echo '<div style="text-align: center; margin-bottom: 20px;">
            <img src="reportheader.png" alt="Report Header" style="max-width: 40%; height: auto;">
            <h2 style="font-weight: bold; margin-top: 7px;">Organizational Performance Report</h2>
          </div>';
// Display the table
echo '<table class="table table-bordered text-center">
        <thead style="font-size: 12px;">
            <tr>
                <th rowspan="2">Organization Name</th>
                <th colspan="5">Activities and Operations (60%)</th>
                <th rowspan="2">Participation and Compliance (40%)</th>
                <th rowspan="2">Average</th>
            </tr>
            <tr>
                <th>Organizational-Related (20%)</th>
                <th>Community Involvement (20%)</th>
                <th>Spiritual Enrichment (20%)</th>
                <th>Environmental (20%)</th>
                <th>Organizational Development (20%)</th>
            </tr>
        </thead>
        <tbody style="font-size: 12px;">';

// Display sorted rows
foreach ($organizationData as $org) {
    echo "<tr>";
    echo "<td>{$org['name']}</td>"; // Organization Name
    echo "<td>{$org['ratings']['1']}</td>"; // Organizational-Related
    echo "<td>{$org['ratings']['2']}</td>"; // Community Involvement
    echo "<td>{$org['ratings']['3']}</td>"; // Spiritual Enrichment
    echo "<td>{$org['ratings']['4']}</td>"; // Environmental
    echo "<td>{$org['ratings']['5']}</td>"; // Organizational Development
    echo "<td>{$org['ratings']['Participation']}</td>"; // Participation and Compliance
    
    // Conditional class for average
    $averageClass = $org['average'] >= 70 ? 'average-green' : 'average-red';
    echo "<td class='{$averageClass}'>" . number_format($org['average'], 2) . "</td>"; // Average with conditional class
    echo "</tr>";
}

echo '</tbody></table>';
?>
