<?php 
require('libraries/fpdf.php');
include('dbcon.php');

// Get filter values from the URL parameters
$organizationID = isset($_GET['organization']) ? $_GET['organization'] : '';
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';
$pointSystemCategoryID = isset($_GET['pointSystemCategory']) ? $_GET['pointSystemCategory'] : '';
$userName = isset($_GET['userName']) ? $_GET['userName'] : '';

// Build the query with filters, group by organizationID, and filter by eventStatus and past events
$query = "SELECT e.*, o.organizationName, p.pointSystemCategoryDescription 
          FROM event e 
          JOIN organization o ON e.organizationID = o.organizationID
          JOIN pointSystemCategory p ON e.pointSystemCategoryID = p.pointSystemCategoryID
          WHERE e.eventStatus = 1 AND e.eventDate < NOW()";

// Apply filters
if (!empty($organizationID)) {
    $query .= " AND e.organizationID = '$organizationID'";
}

// Apply the date range filter
if (!empty($startDate) && !empty($endDate)) {
    $query .= " AND e.eventDate BETWEEN '$startDate' AND '$endDate'";
}

if (!empty($pointSystemCategoryID)) {
    $query .= " AND e.pointSystemCategoryID = '$pointSystemCategoryID'";
}

$query .= " GROUP BY e.organizationID, e.eventID ORDER BY o.organizationName, e.eventDate";

$result = mysqli_query($conn, $query);

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

$pdf = new PDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);

$pdf->Image('reportheader.png', $pdf->GetPageWidth() / 2 - 40, 10, 80); // Adjust the x, y, and width accordingly

// Move the cursor down to avoid overlapping the image with the title
$pdf->Ln(20);
// Add a title
$pdf->Cell(0, 10, 'Event Accomplishment Report', 0, 1, 'C');

// Initialize the first organization
$currentOrganization = "";

// Fetch and display data
$pdf->SetFont('Arial', '', 9);
while ($row = mysqli_fetch_assoc($result)) {
    $orgName = $row['organizationName'];
    
    // Group by organization name: if the organization changes, print a new header for the organization
    if ($currentOrganization !== $orgName) {
        $pdf->Ln(10); // Line break
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 10, "Organization: " . $orgName, 0, 1, 'L');
        $pdf->SetFont('Arial', '', 9);

        // Table headers under each organization
        $pdf->Cell(120, 9, 'Event Title', 1);
        $pdf->Cell(40, 9, 'Event Date', 1);
        $pdf->Cell(60, 9, 'Category', 1);
        $pdf->Ln();
        
        $currentOrganization = $orgName;
    }

    // Add event data
    $pdf->Cell(120, 9, $row['eventTitle'], 1);
    $pdf->Cell(40, 9, $row['eventDate'], 1);
    $pdf->Cell(60, 9, $row['pointSystemCategoryDescription'], 1);
    $pdf->Ln();
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
$pdf->Output('D', 'Filtered_Events_Report.pdf'); // 'D' forces download, 'I' displays in the browser

?>
