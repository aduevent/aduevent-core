<?php
session_start();
if (!isset($_SESSION["id"]) || !isset($_SESSION["access"])) {
    header("Location: loginStudent.php");
    exit();
}
include "dbcon.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submissionDate = $_POST["submissionDate"];
    $organizationName = $_POST["organizationName"];
    $orgType = $_POST["option1"];
    $activityTitle = $_POST["activityTitle"];
    $eventCategory = $_POST["option2"];
    $proposedDate = $_POST["proposedDate"];
    $timeStart = $_POST["timeStart"];
    $timeEnd = $_POST["timeEnd"];
    $venue = $_POST["venue"];
    $venueType = $_POST["option3"];
    $participant = $_POST["participant"];
    $organizationPartner = $_POST["organizationPartner"];
    $orgFundAmount = $_POST["orgFundAmount"];
    $solShareAmount = $_POST["solShareAmount"];
    $regFeeAmount = $_POST["regFeeAmount"];
    $ausgSubAmount = $_POST["ausgSubAmount"];
    $sponsorValue = $_POST["sponsorValue"];
    $ticketSellingAmount = $_POST["ticketSellingAmount"];
    $controlNumber = $_POST["controlNumber"];
    $others = $_POST["othersValue"];
    $designation = $_POST["designation"];

    $userID = $_SESSION["id"];

    $pin = $_POST["pin"];
    $projectLeadName = $_POST["projectLeadName"];
    $stmt = $conn->prepare(
        "INSERT INTO event (eventTitle, organizationID, organizationTypeID, eventProposalDate, pointSystemCategoryID, eventVenue, eventVenueCategory, eventDate, eventTimeStart, eventTimeEnd, organizationFund, solidarityShare, registrationFee, ausgSubsidy, sponsorship, ticketSelling, ticketControlNumber, others, participantCount, partnerOrganization, designation, leadSign) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "ssssssssssddddsdisisss",
        $activityTitle,
        $organizationName,
        $orgType,
        $submissionDate,
        $eventCategory,
        $venue,
        $venueType,
        $proposedDate,
        $timeStart,
        $timeEnd,
        $orgFundAmount,
        $solShareAmount,
        $regFeeAmount,
        $ausgSubAmount,
        $sponsorValue,
        $ticketSellingAmount,
        $controlNumber,
        $others,
        $participant,
        $organizationPartner,
        $designation,
        $projectLeadName
    );
    if ($stmt->execute()) {
        header("Location: rsoIndex.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
