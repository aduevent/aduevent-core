<?php
require('libraries/fpdf.php');
include('dbcon.php');

// Fetch and display data
$academicYear = isset($_GET['academicYear']) ? $_GET['academicYear'] : '';
$userName = isset($_GET['userName']) ? $_GET['userName'] : '';

class PDF extends FPDF 
{
    // Path to the watermark image
    protected $watermarkImage = 'adueventwatermark.png';

    // Add watermark function
    function AddWatermark()
    {
        $pageWidth = $this->GetPageWidth();
        $pageHeight = $this->GetPageHeight();

        // Set the desired watermark width (half the page width)
        $imageWidth = $pageWidth / 2;

        // Calculate the aspect ratio of the watermark image
        list($originalWidth, $originalHeight) = getimagesize($this->watermarkImage);
        $aspectRatio = $originalHeight / $originalWidth;

        // Adjust the height based on the width to maintain the aspect ratio
        $imageHeight = $imageWidth * $aspectRatio;

        // Center the watermark on the page
        $xPosition = ($pageWidth - $imageWidth) / 2;
        $yPosition = ($pageHeight - $imageHeight) / 2;

        // Add the watermark image, specifying only width to maintain aspect ratio
        $this->Image($this->watermarkImage, $xPosition, $yPosition, $imageWidth);
    }
    // Override header function to add watermark to each page
    function Header()
    {
        $this->AddWatermark();
    }
}

// Instantiate PDF instead of FPDF
$pdf = new PDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Image('reportheader.png', $pdf->GetPageWidth() / 2 - 40, 10, 80); // Adjust the x, y, and width accordingly

// Move the cursor down to avoid overlapping the image with the title
$pdf->Ln(20);
// Add a title
$pdf->Cell(0, 10, $academicYear . ' Organizational Performance Report', 0, 1, 'C');

// Table header
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(90, 10, 'Organization Name', 1, 0, 'C');
$pdf->Cell(27, 10, 'Org-Related', 1, 0, 'C');
$pdf->Cell(27, 10, 'Community', 1, 0, 'C');
$pdf->Cell(27, 10, 'Spiritual', 1, 0, 'C');
$pdf->Cell(27, 10, 'Environmental', 1, 0, 'C');
$pdf->Cell(27, 10, 'Org Dev', 1, 0, 'C');
$pdf->Cell(25, 10, 'Participation', 1, 0, 'C');
$pdf->Cell(20, 10, 'Average', 1, 1, 'C'); // End of row

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

    // Calculate Average
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

// Display sorted rows
$pdf->SetFont('Arial', '', 9);
foreach ($organizationData as $org) {
    // Truncate organization name if it exceeds a certain character length
    $orgName = strlen($org['name']) > 60 ? substr($org['name'], 0, 47) . '...' : $org['name'];

    // Organization name with truncation, no wrapping
    $pdf->Cell(90, 9, $orgName, 1);

    // Other columns
    $pdf->Cell(27, 9, $org['ratings']['1'], 1, 0, 'C');
    $pdf->Cell(27, 9, $org['ratings']['2'], 1, 0, 'C');
    $pdf->Cell(27, 9, $org['ratings']['3'], 1, 0, 'C');
    $pdf->Cell(27, 9, $org['ratings']['4'], 1, 0, 'C');
    $pdf->Cell(27, 9, $org['ratings']['5'], 1, 0, 'C');
    $pdf->Cell(25, 9, $org['ratings']['Participation'], 1, 0, 'C');

    // Average score
    $averageText = number_format($org['average'], 2);
    $pdf->Cell(20, 9, $averageText, 1, 1, 'C');
}
$pdf->Ln(10);  // Add space below the table

// "Prepared by" text, center aligned
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 25, 'Prepared by', 0, 1, 'C');

// userName, center aligned, bold, and underlined
$pdf->SetFont('Arial', 'BU', 10);
$pdf->Cell(0, 7, $userName, 0, 1, 'C');

// "Office for Student Affairs" label, center aligned with minimal spacing
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'Office for Student Affairs', 0, 1, 'C');

// Output the PDF
$pdf->Output('I', 'Organizational_Performance_Report.pdf');
?>
