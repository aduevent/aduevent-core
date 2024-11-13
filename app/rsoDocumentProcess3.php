<?php
include("dbcon.php");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submissionDate = $_POST['submissionDate'];
    $organizationName = $_POST['organizationName'];
    $orgType = $_POST['option1'];
    $activityTitle = $_POST['activityTitle'];
    $eventCategory = $_POST['option2'];
    $proposedDate = $_POST['proposedDate'];
    $timeStart = $_POST['timeStart'];
    $timeEnd = $_POST['timeEnd'];
    $venue = $_POST['venue'];
    $venueType = $_POST['option3'];
    $participant = $_POST['participant'];
    $organizationPartner = $_POST['organizationPartner'];
    $orgFundAmount = $_POST['orgFundAmount'];
    $solShareAmount = $_POST['solShareAmount'];
    $regFeeAmount = $_POST['regFeeAmount'];
    $ausgSubAmount = $_POST['ausgSubAmount'];
    $sponsorValue = $_POST['sponsorValue'];
    $ticketSellingAmount = $_POST['ticketSellingAmount'];
    $controlNumber = $_POST['controlNumber'];
    $others = $_POST['othersValue'];
    $designation = $_POST['designation'];
    $leadSgn = $_POST['leadSign'];

    $stmt = $conn->prepare("INSERT INTO event (eventTitle, organizationID, organizationTypeID, eventProposalDate, pointSystemCategoryID, eventVenue, eventVenueCategory, eventDate, eventTimeStart, eventTimeEnd, organizationFund, solidarityShare, registrationFee, ausgSubsidy, sponsorship, ticketSelling, ticketControlNumber, others, participantCount, partnerOrganization, designation, leadSign) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssssssssssssssssssss", $activityTitle, $organizationName, $orgType, $submissionDate, $eventCategory, $venue, $venueType, $proposedDate, $timeStart, $timeEnd, $orgFundAmount, $solShareAmount, $regFeeAmount, $ausgSubAmount, $sponsorValue, $ticketSellingAmount, $controlNumber, $others, $participant, $organizationPartner, $designation, $leadSign);

    if ($stmt->execute()) {
        echo "Event details stored successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
