<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginEmployee.php");
    exit;
}
include("dbcon.php");
include("navbar.php"); 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
if(isset($_POST['eventID'])) {
    $eventID = $_POST['eventID'];
    $query = "SELECT event.*, organization.organizationName 
              FROM event 
              INNER JOIN organization ON event.organizationID = organization.organizationID 
              WHERE event.eventID = $eventID";
    $result = mysqli_query($conn, $query);
    if(mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $submissionDate = $row['eventProposalDate'];
        $organizationName = $row['organizationID'];
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

        // Define user type ID
    $userTypeID = $_SESSION['access'] ?? null;

    // Define HTML for signature display
    function getSignHtml($sign, $buttonHtml = '') {
        $fontFamily = 'Dancing Script, cursive'; // Use 'Snell Roundhand' if available or similar font
    
        if (!empty($sign)) {
            // Display italic text for non-empty signatures
            return '<div class="text-center" style="width: 180px; height: 45px; font-family: \'' . $fontFamily . '\'; font-size: 18px; font-style: italic; text-decoration: underline; display: flex; justify-content: center; align-items: center;">
                        ' . htmlspecialchars($sign) . '
                    </div>';
        } else {
            // Show buttons for empty signature fields if $buttonHtml is provided
            return '<div class="text-center" style="width: 180px; height: 45px; display: flex; justify-content: center; align-items: center;">
                        ' . $buttonHtml . '
                    </div>';
        }
    }
    
    // Generate HTML based on user type and signature status
    $adviserSignHtml = $chairpersonSignHtml = $deanSignHtml = $sdsSignHtml = $icesSignHtml = $ministrySignHtml = $osaSignHtml = $vpsaSignHtml = $vpfaSignHtml = '';
    
    $adviserSignHtml = getSignHtml($adviserSign);
    $chairpersonSignHtml = getSignHtml($chairpersonSign);
    $deanSignHtml = getSignHtml($deanSign);
    $icesSignHtml = getSignHtml($icesSign);
    $ministrySignHtml = getSignHtml($ministrySign);
    $sdsSignHtml = getSignHtml($sdsSign);
    
    $osaSignHtml = getSignHtml(
        $osaSign,
        '<button class="button confirm" onclick="showPinModal()">APPROVE</button> <button class="btn btn-danger" onclick="decline()">DECLINE</button>'
    );
    
    $vpsaSignHtml = getSignHtml($vpsaSign);
    $vpfaSignHtml = getSignHtml($vpfaSign);
    
} else {
    echo "Event not found";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OSA Document View</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .legal-document {
            width: 8.5in;
            height: 15in;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }
        body {
            background-color: #D3D3D3;
        }
        .custom-file-input {
            border: 2px solid #ccc;
            border-radius: 8px;
            padding: 8px 12px;
            width: 100%;
            box-sizing: border-box;
        }
        .options-bar {
            background-color: #f8f9fa; 
            border: 1px solid #dee2e6; 
            padding: 10px;
        }
        .signature-canvas {
        border: .5px solid #D3D3D3;
        }
        button {
            background-color: #000080;
            color: white;
            border-radius: 50px;
            border: none;
            padding: 5px 10px;
            font-size: 12px;
        }
        .btn {
            background-color: #d3d3d3;
            color: red;
            border: none;
            border-radius: 50px;
            padding: 5px 10px;
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="container" style="padding-top: 70px;">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="legal-document" style="margin-bottom: 30px;">
                <div class="row">
                    <div class="col"><img src="logodoc.png" alt="logoHeader" style="max-height: 60px;"></div>
                    <div class="col" style="text-align: right;"><img src="header2.png" alt="textHeader" style="max-height: 60px;"></div>
                </div>
                <h4 class="title text-center" style="margin-bottom: 0;">STUDENT ORGANIZATION PROPOSAL FORM</h4>
                <h6 class="text-center" style="margin-top: 0;">(Extra-curricular Activities)</h6>
                <form id="myForm" method="post" action="osaDocumentProcess.php">
                <input type="hidden" name="eventID" value="<?php echo $eventID; ?>">
                <ul class="list-inline">
                    <li class="list-inline-item">Date:</li>
                    <li class="list-inline-item"><input type="date" class="form-control" id="date" name="submissionDate" value="<?php echo $submissionDate; ?>" disabled></li>
                </ul>
                <ul class="list-inline" style="margin-bottom: 0;">
                    <li class="list-inline-item"><b>1. Organization:</b></li>
                    <li class="list-inline-item">
                    <select class="form-select" id="organizationName" name="organizationName" disabled>
                        <option value="">Select Organization</option>
                        <?php 
                        $resultOrganization = mysqli_query($conn, "SELECT * FROM organization");
                        if ($resultOrganization) {
                            while ($row_org = mysqli_fetch_assoc($resultOrganization)) { 
                            $selected = ($row_org['organizationID'] == $organizationName) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $row_org['organizationID']; ?>" <?php echo $selected; ?>><?php echo $row_org['organizationName']; ?></option>
                        <?php 
                            } 
                        } else {
                            echo "Error fetching organizations: " . mysqli_error($conn);
                        }
                        ?>
                    </select>
                    </li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item"><label><input type="radio" name="option1" value="1" <?php echo ($orgType == '1') ? 'checked' : ''; ?> disabled> Academic</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option1" value="2" <?php echo ($orgType == '2') ? 'checked' : ''; ?> disabled> Co-Academic</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option1" value="3" <?php echo ($orgType == '3') ? 'checked' : ''; ?> disabled> Socio-civic</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option1" value="4" <?php echo ($orgType == '4') ? 'checked' : ''; ?> disabled> Religious</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option1" value="5" <?php echo ($orgType == '5') ? 'checked' : ''; ?> disabled> College Council</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option1" value="6" <?php echo ($orgType == '6') ? 'checked' : ''; ?> disabled> AUSG</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option1" value="7" <?php echo ($orgType == '7') ? 'checked' : ''; ?> disabled> Chronicle</label></li>
                </ul>
                <ul class="list-inline" style="margin-bottom: 0;">
                    <li class="list-inline-item"><b>2. Activity Title:</b></li>
                    <li class="list-inline-item" style="width: 150px;"><input type="text" class="form-control" id="activity" name="activityTitle" value="<?php echo $activityTitle; ?>" style="width: 620px;" disabled></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; margin-bottom: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item"><label><input type="radio" name="option2" value="1" <?php echo ($eventCategory == '1') ? 'checked' : ''; ?> disabled> Organizational Related</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option2" value="4" <?php echo ($eventCategory == '4') ? 'checked' : ''; ?> disabled> Environmental</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option2" value="5" <?php echo ($eventCategory == '5') ? 'checked' : ''; ?> disabled> Organizational Development</label></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item"><label><input type="radio" name="option2" value="3" <?php echo ($eventCategory == '3') ? 'checked' : ''; ?> disabled> Spiritual Enrichment</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option2" value="2" <?php echo ($eventCategory == '2') ? 'checked' : ''; ?> disabled> Community Involvement</label></li>
                    <li class="list-inline-item" style="display: flex; align-items: center;">
                    <label><input type="radio" name="option2" value="6" <?php echo ($eventCategory == '6') ? 'checked' : ''; ?> disabled></label>
                    <input type="text" class="form-control" id="organization" placeholder="Others(Specify)" readonly></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; margin-bottom: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item">Date:</li>
                    <li class="list-inline-item"><input type="date" class="form-control" id="eventdate" name="proposedDate" value="<?php echo $proposedDate; ?>" disabled></li>
                    <li class="list-inline-item">Time:</li>
                    <li class="list-inline-item"><input type="time" class="form-control" id="timestart" name="timeStart" value="<?php echo $timeStart; ?>" disabled></li>
                    <li class="list-inline-item">to</li>
                    <li class="list-inline-item"><input type="time" class="form-control" id="timened" name="timeEnd" value="<?php echo $timeEnd; ?>" disabled></li>
                </ul>
                <ul class="list-inline" style="margin-bottom: 0;">
                    <li class="list-inline-item">Venue:</li>
                    <li class="list-inline-item"><input type="text" class="form-control" id="venue" name="venue" value="<?php echo $venue; ?>" style="width: 620px;" disabled></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; margin-bottom: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item"><label><input type="radio" name="option3" value="1" value="1" <?php echo ($venueType == '1') ? 'checked' : ''; ?> disabled> On-Campus</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option3" value="2" value="2" <?php echo ($venueType == '2') ? 'checked' : ''; ?> disabled> Off-Campus (Compliance with requirements of CMO #63 S 2017 is mandatory)</label></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; margin-bottom: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item"><label><input type="radio" name="option3" value="3" value="3" <?php echo ($venueType == '3') ? 'checked' : ''; ?> disabled> Online</label></li>
                </ul>
                <ul class="list-inline" style="display: flex; justify-content: space-between;">
                    <li class="list-inline-item">No. of Participants:</li>
                    <li class="list-inline-item"><input type="number" class="form-control" id="participant" name="participant" value="<?php echo $participant; ?>" disabled></li>
                    <li class="list-inline-item">Partner Organization (if any):</li>
                    <li class="list-inline-item"><input type="text" class="form-control" id="orgpartner" name="organizationPartner"  value="<?php echo $organizationPartner; ?>" disabled></li>
                </ul>
                <ul class="list-inline" style="margin-bottom: 0;">
                    <li class="list-inline-item"><b>3. Source of Fund:</b></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; margin-bottom: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item"><label>Org Fund: Php</label></li>
                    <li class="list-inline-item"><input type="number" class="form-control" id="orgFundAmount" name="orgFundAmount" value="<?php echo $orgFundAmount; ?>" disabled></li>
                    <li class="list-inline-item"><label>Solidarity Share: Php</label></li>
                    <li class="list-inline-item"><input type="number" class="form-control" id="solShareAmount" name="solShareAmount" value="<?php echo $solShareAmount; ?>" disabled></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; margin-bottom: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item"><label>Reg Fee: Php</label></li>
                    <li class="list-inline-item"><input type="number" class="form-control" id="regFeeAmount" name="regFeeAmount" value="<?php echo $regFeeAmount; ?>" disabled></li>
                    <li class="list-inline-item"><label>AUSG Subsidy: Php</label></li>
                    <li class="list-inline-item"><input type="number" class="form-control" id="ausgSubAmount" name="ausgSubAmount" value="<?php echo $ausgSubAmount; ?>" disabled></li>
                </ul>
                <ul>
                    <li class="list-inline-item"><label>Sponsorship (Identify Sponsors):</label></li>
                    <li class="list-inline-item"><input type="text" class="form-control" id="sponsor" name="sponsorValue" value="<?php echo $sponsorValue; ?>" disabled></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; margin-bottom: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item"><label>Ticket Selling: Php</label></li>
                    <li class="list-inline-item"><input type="number" class="form-control" id="ticketSelling" name="ticketSellingAmount" value="<?php echo $ticketSellingAmount; ?>" disabled></li>
                    <li class="list-inline-item"><label>Control #:</label></li>
                    <li class="list-inline-item"><input type="number" class="form-control" id="controlNum" name="controlNumber" value="<?php echo $controlNumber; ?>" disabled></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; margin-bottom: 0;">
                    <li class="list-inline-item"><label>Others (Specify):</label></li>
                    <li class="list-inline-item"><input type="text" class="form-control" id="others" name="othersValue" value="<?php echo $others; ?>" disabled></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; text-align: center;">
                    <li class="list-inline-item">Note: Observed Centralized Collection Policy through the Finance Office</li>
                </ul>
                <ul class="list-inline" style="margin-top: 0;">
                    <li class="list-inline-item"><b>Project Lead</b></li>
                </ul>
                <div class="row" style="margin-top: 0;">
                    <div class="col text-center"><div style="width: 180px; height: 45px; font-family: \'' . $fontFamily . '\'; font-size: 18px; font-style: italic; text-decoration: underline; display: flex; justify-content: center; align-items: center;"><?php echo htmlspecialchars($leadSign); ?>
                </div></div>
                    <div class="col"><div class="text-center"><input type="text" class="form-control-file" id="designation" name="designation" value="<?php echo $designation; ?>" disabled>
                </div></div>
                <div class="w-100"></div>
                    <div class="col"><div class="text-center">Signature Over Printed Name</div></div>
                    <div class="col"><div class="text-center">Designation</div></div>
                </div>
                <div class="row" style="margin-top: 0; margin-bottom: 0;">
        <div class="col-sm-4"><b>Noted by:</b></div>
        <div class="col-sm-8"><b>Endorsed by:</b></div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col">
                <?php echo $adviserSignHtml; ?>
            </div>
            <div class="col">
                <?php echo $chairpersonSignHtml; ?>
            </div>
            <div class="col">
                <?php echo $deanSignHtml; ?>
            </div>
        </div>
        <div class="row" style="margin-top: 0;">
            <div class="col"><div class="text-center">Adviser</div></div>
            <div class="col">Department Chairperson</div>
            <div class="col">College Dean</div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-4">If Comm. Involvement Activity:</div>
            <div class="col-4">If Spiritual Enrichment Activity:</div>
        </div>
        <div class="row justify-content-center">
            <div class="col-4">
            <?php echo $icesSignHtml; ?>
            </div>
            <div class="col-4">
            <?php echo $ministrySignHtml; ?>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-4">Integrated Comm. Ext. Services</div>
            <div class="col-4">Campus Ministry Office</div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col"><div class="text-center"><b>APPROVAL</b></div></div>
        </div>
        <div class="row justify-content-center">
            <div class="col-4">
            <?php echo $sdsSignHtml; ?>
            </div>
            <div class="col-4">
            <?php echo $osaSignHtml; ?>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-4">Student Development Section</div>
            <div class="col-4">OSA Director</div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-4">If Off-Campus Activity:</div>
            <div class="col-4">If Sponsorship/Ticket Selling/Registration Fee/Income Generation:</div>
        </div>
        <div class="row justify-content-center">
            <div class="col-4">
            <?php echo $vpsaSignHtml; ?>
            </div>
            <div class="col-4">
            <?php echo $vpfaSignHtml; ?>
                </div>
                <div class="row justify-content-center">
                    <div class="col-4">VPSA</div>
                    <div class="col-4">VPFA/Controller/Treasurer</div>
                </div>
                </div>
                <div class="modal fade" id="pinModal" tabindex="-1" role="dialog" aria-labelledby="pinModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pinModalLabel">Enter PIN</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="pinForm" action="approverVerifyPin.php" method="POST">
                <div class="form-group">
                <label for="projectLeadName">Project Lead Name:</label>
                        <input type="text" class="form-control" id="projectLeadName" name="projectLeadName">
                    </div>
                    <div class="form-group">
                        <label for="pin">PIN</label>
                        <input type="password" id="pin" name="pin" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit PIN</button>
                </form>
                </div>
            </div>
        </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showPinModal(event) {
    event.preventDefault(); // Prevent default form submission
    $('#pinModal').modal('show');
}

$('#pinForm').on('submit', function(event) {
    event.preventDefault();
    var pin = $('#pin').val();
    var projectLeadName = $('#projectLeadName').val(); // Get project lead name from modal form field

    // Submit the PIN and projectLeadName via AJAX for verification
    $.ajax({
        url: 'verifyPin.php',
        type: 'POST',
        data: {
            pin: pin,
            projectLeadName: projectLeadName // Include projectLeadName in the data
        },
        success: function(response) {
            if (response === 'success') {
                $('#pinModal').modal('hide');
                window.location.href = 'osaDocumentProcess.php?' + $('#myForm').serialize();
            } else {
                alert('Invalid PIN or Project Lead Name. Please try again.');
            }
        }
    });
});

// Attach the showPinModal function to the button click
$(document).ready(function() {
    $('.button.confirm').on('click', showPinModal);
});
</script>
</body>
</html>
