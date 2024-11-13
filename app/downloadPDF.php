<?php
require('libraries/fpdf.php');
include('dbcon.php');

// Check if the eventID is set
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['eventID'])) {
    $eventID = $_GET['eventID'];
    
    // Fetch event details from the database
    $query = "SELECT event.*, organization.organizationName 
              FROM event 
              INNER JOIN organization ON event.organizationID = organization.organizationID 
              WHERE event.eventID = $eventID";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Store the fetched values
        $submissionDate = $row['eventProposalDate'];
        $organizationName = $row['organizationName'];  // Use organization name instead of ID
        $orgType = $row['organizationTypeID'];
        $activityTitle = $row['eventTitle'];
        $eventCategory = $row['pointSystemCategoryID'];
        $proposedDate = $row['eventDate'];
        $timeStart = $row['eventTimeStart'];
        $timeEnd = $row['eventTimeEnd'];
        $venue = $row['eventVenue'];
        $venueType = $row['eventVenueCategory'];
        $participant = $row['participantCount'];
        $organizationPartner = $row['partnerOrganization'];
        $orgFundAmount = $row['organizationFund'];
        $solShareAmount = $row['solidarityShare'];
        $regFeeAmount = $row['registrationFee'];
        $ausgSubAmount = $row['ausgSubsidy'];
        $sponsorValue = $row['sponsorship'];
        $ticketSellingAmount = $row['ticketSelling'];
        $controlNumber = $row['ticketControlNumber'];
        $others = $row['others'];
        $leadSign = $row['leadSign'];
        $designation = $row['designation'];
        $adviserSign = $row['adviserSign'];
        $chairpersonSign = $row['chairpersonSign'];
        $deanSign = $row['deanSign'];
        $icesSign = $row['icesSign'];
        $ministrySign = $row['ministrySign'];
        $sdsSign = $row['sdsSign'];
        $osaSign = $row['osaSign'];
        $vpsaSign = $row['vpsaSign'];
        $vpfaSign = $row['vpfaSign'];
    } else {
        die("Event not found.");
    }
    $organizationType = ''; // Default empty string

// Determine the organization type based on the organizationTypeID
switch ($orgType) {
    case 1:
        $organizationType = 'Academic';
        break;
    case 2:
        $organizationType = 'Co-Academic';
        break;
    case 3:
        $organizationType = 'Socio-Civic';
        break;
    default:
        $organizationType = ''; // Default empty if not found
        break;
}

// Format the organization name with type if available
$organizationDisplay = $organizationName;
if (!empty($organizationType)) {
    $organizationDisplay .= ' - ' . $organizationType; // Add the type with a preceding dash
}
class PDF extends FPDF
{
    // Path to the logo image
    protected $watermarkImage = 'adueventwatermark.png';

    // Override header function to add background and watermark to each page
    function Header()
    {
        // Get page dimensions
        $width = $this->GetPageWidth();
        $height = $this->GetPageHeight();

        // Define a larger width for the watermark image
        $desiredWidth = $width * 0.8; // Increase the size by using a larger fraction

        // Calculate X and Y position to center the image
        $xPosition = ($width - $desiredWidth) / 2;
        $yPosition = ($height - ($desiredWidth / 2)) / 2; // Adjust yPosition based on aspect ratio

        // Add watermark image and let height auto-scale based on width
        $this->Image($this->watermarkImage, $xPosition, $yPosition, $desiredWidth);
    }
}

    // Create instance of FPDF class
    $pdf = new PDF('P', 'mm', array(210, 350));
    $pdf->AddPage();

    // Set header
    $pdf->Image('logodoc.png', 10, 5, 0, 10); // Image 1: left side
        $pdf->Image('header2.png', 140, 5, 0, 10); // Image 2: right side
        $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(190, 6, 'STUDENT ORGANIZATION PROPOSAL FORM', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(190, 5, '(Extra-curricular Activities)', 0, 1, 'C');
    $pdf->Ln(5);

    // Date
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 7, 'Date:', 0, 0);
    $pdf->SetFont('Arial', 'U', 10);
    $pdf->Cell(50, 7, date('F d, Y', strtotime($submissionDate)), 0, 1);

    // Organization
    $pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 7, 'Organization:', 0, 0);
$pdf->SetFont('Arial', 'U', 10);
$pdf->Cell(50, 7, $organizationDisplay, 0, 1);

    $eventCategoryType = ''; // Default empty string

// Determine the event category type based on the pointSystemCategoryID
switch ($eventCategory) {
    case 1:
        $eventCategoryType = 'Organizational-Related Project';
        break;
    case 2:
        $eventCategoryType = 'Community Involvement Project';
        break;
    case 3:
        $eventCategoryType = 'Spiritual Enrichment Project';
        break;
    case 4:
        $eventCategoryType = 'Environmental Project';
        break;
    case 5:
        $eventCategoryType = 'Organizational Development Project';
        break;
    default:
        $eventCategoryType = ''; // Default empty if not found
        break;
}

// Format the activity title with category if available
$activityDisplay = $activityTitle;
if (!empty($eventCategoryType)) {
    $activityDisplay .= ' - ' . $eventCategoryType; // Add the type with a preceding dash
}

// Add it to the PDF output
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 7, 'Activity Title:', 0, 0);
$pdf->SetFont('Arial', 'U', 10);
$pdf->Cell(50, 7, $activityDisplay, 0, 1);

    // Proposed Date and Time
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 7, 'Proposed Date:', 0, 0);
    $pdf->SetFont('Arial', 'U', 10);
    $pdf->Cell(50, 7, date('F d, Y', strtotime($proposedDate)), 0, 1);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 7, 'Time:', 0, 0);
    $pdf->SetFont('Arial', 'U', 10);
    $pdf->Cell(50, 7, $timeStart . ' - ' . $timeEnd, 0, 1);

    // Venue
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 7, 'Venue:', 0, 0);
    $pdf->SetFont('Arial', 'U', 10);
    $pdf->Cell(50, 7, $venue, 0, 1);

    // No. of Participants
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 7, 'No. of Participants:', 0, 0);
    $pdf->SetFont('Arial', 'U', 10);
    $pdf->Cell(50, 7, $participant, 0, 1);

    // Partner Organization
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 7, 'Partner Organization:', 0, 0);
    $pdf->SetFont('Arial', 'U', 10);
    $pdf->Cell(50, 7, $organizationPartner, 0, 1);

    // Source of Fund
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(50, 7, 'Source of Fund', 0, 1);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(75, 7, 'Org Fund: Php ' . number_format($orgFundAmount, 2)); // Org Fund
$pdf->Cell(75, 7, 'Solidarity Share: Php ' . number_format($solShareAmount, 2), 0, 1); // Solidarity Share (same line as Org Fund)

$pdf->Cell(75, 7, 'Registration Fee: Php ' . number_format($regFeeAmount, 2)); // Registration Fee
$pdf->Cell(75, 7, 'AUSG Subsidy: Php ' . number_format($ausgSubAmount, 2), 0, 1); // AUSG Subsidy (same line as Registration Fee)

$pdf->Cell(140, 7, 'Sponsorship: ' . $sponsorValue, 0, 1); // Sponsorship alone in one line

$pdf->Cell(75, 7, 'Ticket Selling: Php ' . number_format($ticketSellingAmount, 2)); // Ticket Selling
$pdf->Cell(75, 7, 'Control Number: ' . $controlNumber, 0, 1); // Control Number (same line as Ticket Selling)

$pdf->Cell(140, 7, 'Others: ' . $others, 0, 1); // Others alone in one line

    
    // Start by setting up the necessary variables
$pageWidth = $pdf->GetPageWidth(); // Get the total width of the page
$margin = 10; // Set a margin to account for some spacing from the edges

// Define cell width and total width for centering calculations
$cellWidth = 60;
$totalWidth = 2 * $cellWidth + 10; // Only two cells for this row
$centerX = ($pageWidth - $totalWidth) / 2;

// Line break before the signatures
$pdf->Ln(6);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'Project Lead', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);

// Fourth Row: SDS and OSA
$pdf->Ln(7);
$centerX = ($pageWidth - $totalWidth) / 2; // Recenter for the two cells
$pdf->SetX($centerX);
$pdf->MultiCell($cellWidth, 7, $leadSign, 0, 'C');
$pdf->SetXY($centerX + $cellWidth + 10, $pdf->GetY() - 7);
$pdf->MultiCell($cellWidth, 7, $designation, 0, 'C');

$pdf->Ln(2);
$currentY = $pdf->GetY();
$pdf->Line($centerX, $currentY - 3, $centerX + $cellWidth, $currentY - 3);             // SDS underline
$pdf->Line($centerX + $cellWidth + 10, $currentY - 3, $centerX + 2 * $cellWidth + 10, $currentY - 3); // OSA underline

$pdf->SetX($centerX);
$pdf->Cell($cellWidth, 7, 'Project Lead Name', 0, 0, 'C');
$pdf->Cell($cellWidth, 7, 'Designation', 0, 1, 'C');

$cellWidth = 60;
$totalWidth = 3 * $cellWidth + 2 * 10; // Total width of three cells with gaps
$centerX = ($pageWidth - $totalWidth) / 2; // Calculate the centered X position

$pdf->Ln(3);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'Noted and Endorsed by:', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);

// Second Row: Adviser, Chairperson, and Dean
// Define starting X position for centered alignment and a base Y for uniform height
$pdf->Ln(7);
$baseY = $pdf->GetY();  // Capture the current Y position before adding signatures

// Use the same Y position for all MultiCell elements
$pdf->SetXY($centerX, $baseY);
$pdf->MultiCell($cellWidth, 7, $adviserSign, 0, 'C');

$pdf->SetXY($centerX + $cellWidth + 10, $baseY);  // Keep same Y coordinate for Chairperson
$pdf->MultiCell($cellWidth, 7, $chairpersonSign, 0, 'C');

$pdf->SetXY($centerX + 2 * $cellWidth + 20, $baseY);  // Keep same Y coordinate for Dean
$pdf->MultiCell($cellWidth, 7, $deanSign, 0, 'C');

// Draw the underlines below the signatures
$pdf->Ln(2);
$currentY = $pdf->GetY();
$pdf->Line($centerX, $currentY - 3, $centerX + $cellWidth, $currentY - 3);  // Adviser underline
$pdf->Line($centerX + $cellWidth + 10, $currentY - 3, $centerX + 2 * $cellWidth + 10, $currentY - 3); // Chairperson underline
$pdf->Line($centerX + 2 * $cellWidth + 20, $currentY - 3, $centerX + 3 * $cellWidth + 20, $currentY - 3); // Dean underline

// Add labels below the lines, centered under each column
$pdf->SetXY($centerX, $currentY);
$pdf->Cell($cellWidth, 7, 'Adviser', 0, 0, 'C');
$pdf->Cell($cellWidth, 7, 'Chairperson', 0, 0, 'C');
$pdf->Cell($cellWidth, 7, 'College Dean', 0, 1, 'C');

// Line break before the next row
$pdf->Ln(7);

// Third Row: ICES and Ministry
$totalWidth = 2 * $cellWidth + 10; // Only two cells for this row
$centerX = ($pageWidth - $totalWidth) / 2;
$baseY = $pdf->GetY(); // Capture the current Y position for uniformity

// Use the same Y position for all MultiCell elements
$pdf->SetXY($centerX, $baseY);
$pdf->MultiCell($cellWidth, 7, $icesSign, 0, 'C');
$icesEndY = $pdf->GetY(); // Capture the ending Y position for this cell

$pdf->SetXY($centerX + $cellWidth + 10, $baseY); // Keep same Y coordinate for Ministry
$pdf->MultiCell($cellWidth, 7, $ministrySign, 0, 'C');
$ministryEndY = $pdf->GetY(); // Capture the ending Y position for this cell
$pdf->Ln(2);
$lineY = max($icesEndY, $ministryEndY) + 2; // Add extra spacing below the signatures
$pdf->Line($centerX, $lineY, $centerX + $cellWidth, $lineY); // ICES underline
$pdf->Line($centerX + $cellWidth + 10, $lineY, $centerX + 2 * $cellWidth + 10, $lineY); // Ministry underline

// Add labels below the lines
$pdf->SetXY($centerX, $lineY + 2); // Adjust label position based on line Y
$pdf->Cell($cellWidth, 7, 'Integrated Community External Services', 0, 0, 'C');
$pdf->Cell($cellWidth, 7, 'Campus Ministry Office', 0, 1, 'C');

// Line break before the next row
$pdf->Ln(6);

// Section Header: "APPROVAL"
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'APPROVAL', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);

// Fourth Row: SDS and OSA
$pdf->Ln(7);
$baseY = $pdf->GetY(); // Capture the new starting Y position for uniformity
$centerX = ($pageWidth - $totalWidth) / 2; // Recenter for the two cells
$pdf->SetXY($centerX, $baseY);
$pdf->MultiCell($cellWidth, 7, $sdsSign, 0, 'C');

$pdf->SetXY($centerX + $cellWidth + 10, $baseY); // Keep same Y coordinate for OSA
$pdf->MultiCell($cellWidth, 7, $osaSign, 0, 'C');

// Draw the underlines below the signatures
$pdf->Ln(2);
$currentY = $pdf->GetY();
$pdf->Line($centerX, $currentY - 3, $centerX + $cellWidth, $currentY - 3);  // SDS underline
$pdf->Line($centerX + $cellWidth + 10, $currentY - 3, $centerX + 2 * $cellWidth + 10, $currentY - 3); // OSA underline

// Add labels below the lines
$pdf->SetXY($centerX, $currentY);
$pdf->Cell($cellWidth, 7, 'Student Development Section', 0, 0, 'C');
$pdf->Cell($cellWidth, 7, 'Office for Student Affairs', 0, 1, 'C');

$pdf->Ln(7);

// Fifth Row: VPSA and VPFA
$baseY = $pdf->GetY(); // Capture the new starting Y position for uniformity
$centerX = ($pageWidth - $totalWidth) / 2;

$pdf->SetXY($centerX, $baseY);
$pdf->MultiCell($cellWidth, 7, $vpsaSign, 0, 'C');
$vpsaEndY = $pdf->GetY();

$pdf->SetXY($centerX + $cellWidth + 10, $baseY); // Keep same Y coordinate for VPFA
$pdf->MultiCell($cellWidth, 7, $vpfaSign, 0, 'C');
$pdf->Ln(2);
$vpfaEndY = $pdf->GetY();
// Adjust line position based on the maximum Y coordinate of the signatures
$lineY = max($vpsaEndY, $vpfaEndY) + 2; // Add extra spacing below the signatures
$pdf->Line($centerX, $lineY, $centerX + $cellWidth, $lineY); // VPSA underline
$pdf->Line($centerX + $cellWidth + 10, $lineY, $centerX + 2 * $cellWidth + 10, $lineY); // VPFA underline

// Add labels below the lines
$pdf->SetXY($centerX, $lineY + 2);
$pdf->Cell($cellWidth, 7, 'Vice President for Student Affairs', 0, 0, 'C');
$pdf->Cell($cellWidth, 7, 'Vice President for Financial Affairs', 0, 1, 'C');

    // Output the PDF
    $pdf->Output('D', 'Event_Proposal_Form_' . $eventID . '.pdf'); // Downloads the PDF
} else {
    echo "Event ID is missing!";
}
?>
