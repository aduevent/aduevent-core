<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginStudent.php");
    exit;
}
include("dbcon.php");
$userId = $_SESSION['id'];
$userQuery = "
    SELECT su.name, su.email, o.organizationLogo as profilePicture
    FROM studentuser su
    JOIN organization o 
    ON su.organizationID = o.organizationID
    WHERE su.id = ?";

$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();

$userName = $userData['name'];
$email = $userData['email'];
$dp = $userData['profilePicture'];
$organizationID = $_SESSION['organizationID'];
$queryOrganization = "SELECT * FROM organization";
$resultOrganization = mysqli_query($conn, $queryOrganization);
if (!$resultOrganization) {
    echo "Error fetching organizations: " . mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Document Viewer</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <?php include 'rsoNavbar.php';
    $activePage = "rsoActivityForm"; ?>
    <style>
        .legal-document {
            width: 8.5in;
            height: 14.5in;
            margin-left: 5px;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            overflow-y: auto;
        }
        .button {
            background-color: #000080;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 12px;
            margin: 2px 5px;
            cursor: pointer;
            border-radius: 25px;
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
    <div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="legal-document" style="margin-bottom: 30px;">
                <div class="row">
                    <div class="col"><img src="logodoc.png" alt="logoHeader" style="max-height: 60px;"></div>
                    <div class="col" style="text-align: right;"><img src="header2.png" alt="textHeader" style="max-height: 60px;"></div>
                </div>
                <h4 class="title text-center" style="margin-bottom: 0;">STUDENT ORGANIZATION PROPOSAL FORM</h4>
                <h6 class="text-center" style="margin-top: 0;">(Extra-curricular Activities)</h6>
                <form id="myForm" method="post" action="rsoActivityFormProcess.php">
                <ul class="list-inline">
    <li class="list-inline-item">Date:</li>
    <li class="list-inline-item">
        <input type="date" class="form-control" id="date" name="submissionDate" value="<?php echo date('Y-m-d'); ?>" readonly>
    </li>
</ul>
                <ul class="list-inline" style="margin-bottom: 0;">
                    <li class="list-inline-item"><b>1. Organization:</b></li>
                    <li class="list-inline-item">
                    <select class="form-select" id="organizationName" name="organizationName" style="width: 630px;">
                            <option value="">Select Organization</option>
                            <?php
                            $selectedOrganizationID = $_SESSION['organizationID'];
                            $querySelectedOrganization = "SELECT * FROM organization WHERE organizationID = $selectedOrganizationID";
                            $resultSelectedOrganization = mysqli_query($conn, $querySelectedOrganization);
                            $selectedOrganization = mysqli_fetch_assoc($resultSelectedOrganization);
                            ?>
                            <option value="<?php echo $selectedOrganization['organizationID']; ?>" selected><?php echo $selectedOrganization['organizationName']; ?></option>
                        </select>
                    </li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item"><label><input type="radio" name="option1" value="1"> Academic</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option1" value="2"> Co-Academic</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option1" value="3"> Socio-civic</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option1" value="4"> Religous</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option1" value="5"> College Council</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option1" value="6"> AUSG</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option1" value="7"> Chronicle</label></li>
                </ul>
                <ul class="list-inline" style="margin-bottom: 0;">
                    <li class="list-inline-item"><b>2. Activity Title:</b></li>
                    <li class="list-inline-item"><input type="text" class="form-control" id="activity" name="activityTitle" style="width: 620px;"></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; margin-bottom: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item"><label><input type="radio" name="option2" value="1"> Organizational Related</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option2" value="4"> Environmental</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option2" value="5"> Organizational Development</label></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; margin-bottom: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item"><label><input type="radio" name="option2" value="3"> Spiritual Enrichment</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option2" value="2"> Community Involvement</label></li>
                    <li class="list-inline-item" style="display: flex; align-items: center;"><label><input type="radio" name="option2" value="6"></label><input type="text" class="form-control" id="organization" placeholder="Others(Specify)"></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; margin-bottom: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item">Date:</li>
                    <li class="list-inline-item"><input type="date" class="form-control" id="eventdate" name="proposedDate"></li>
                    <li class="list-inline-item">Time:</li>
                    <li class="list-inline-item"><input type="time" class="form-control" id="timestart" name="timeStart"></li>
                    <li class="list-inline-item">to</li>
                    <li class="list-inline-item"><input type="time" class="form-control" id="timened" name="timeEnd"></li>
                </ul>
                <ul class="list-inline" style="margin-bottom: 0;">
                    <li class="list-inline-item">Venue:</li>
                    <li class="list-inline-item"><input type="text" class="form-control" id="venue" name="venue"></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; margin-bottom: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item"><label><input type="radio" name="option3" value="1"> On-Campus</label></li>
                    <li class="list-inline-item"><label><input type="radio" name="option3" value="2"> Off-Campus (Compliance with requirements of CMO #63 S 2017 is mandatory)</label></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; margin-bottom: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item"><label><input type="radio" name="option3" value="3"> Online</label></li>
                </ul>
                <ul class="list-inline" style="display: flex; justify-content: space-between;">
                    <li class="list-inline-item">No. of Participants:</li>
                    <li class="list-inline-item"><input type="number" class="form-control" id="participant" name="participant"></li>
                    <li class="list-inline-item">Partner Organization (if any):</li>
                    <li class="list-inline-item"><input type="text" class="form-control" id="orgpartner" name="organizationPartner"></li>
                </ul>
                <ul class="list-inline" style="margin-bottom: 0;">
                    <li class="list-inline-item"><b>3. Source of Fund:</b></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; margin-bottom: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item"><label>Org Fund: Php</label></li>
                    <li class="list-inline-item"><input type="number" class="form-control" id="orgFundAmount" name="orgFundAmount"></li>
                    <li class="list-inline-item"><label>Solidarity Share: Php</label></li>
                    <li class="list-inline-item"><input type="number" class="form-control" id="solShareAmount" name="solShareAmount"></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; margin-bottom: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item"><label>Reg Fee: Php</label></li>
                    <li class="list-inline-item"><input type="number" class="form-control" id="regFeeAmount" name="regFeeAmount"></li>
                    <li class="list-inline-item"><label>AUSG Subsidy: Php</label></li>
                    <li class="list-inline-item"><input type="number" class="form-control" id="ausgSubAmount" name="ausgSubAmount"></li>
                </ul>
                <ul>
                    <li class="list-inline-item"><label>Sponsorship (Identify Sponsors):</label></li>
                    <li class="list-inline-item"><input type="text" class="form-control" id="sponsor" name="sponsorValue"></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; margin-bottom: 0; display: flex; justify-content: space-between;">
                    <li class="list-inline-item"><label>Ticket Selling: Php</label></li>
                    <li class="list-inline-item"><input type="number" class="form-control" id="ticketSelling" name="ticketSellingAmount"></li>
                    <li class="list-inline-item"><label>Control #:</label></li>
                    <li class="list-inline-item"><input type="number" class="form-control" id="controlNum" name="controlNumber"></li>
                </ul>
                    <ul class="list-inline" style="margin-top: 0; margin-bottom: 0;">
                    <li class="list-inline-item"><label>Others (Specify):</label></li>
                    <li class="list-inline-item"><input type="text" class="form-control" id="others" name="othersValue"></li>
                </ul>
                <ul class="list-inline" style="margin-top: 0; text-align: center;">
                    <li class="list-inline-item">Note: Observed Centralized Collection Policy through the Finance Office</li>
                </ul>
                <ul class="list-inline" style="margin-top: 0;">
            <li class="list-inline-item"><b>Project Lead</b></li>
        </ul>
        <div class="row" style="margin-top: 0;">
    <div class="col">
        <div class="text-center" style="display: flex; justify-content: center; align-items: center;">
        <div id="leadPhotoContainer" class="photo-container" style="width: 240px; height: 45px; border: .5px solid transparent;">
                    <button class="button confirm" onclick="showPinModal()">Confirm</button></div>
                </div>
    </div>
    <div class="col">
        <div class="text-center"><input type="text" class="form-control-file" id="designation" name="designation" style="border: none; border-bottom: .5px solid #D3D3D3;"></div>
    </div><div class="w-100"></div>
            <div class="col">
                <div class="text-center">Signature Over Printed Name</div>
            </div>
            <div class="col">
                <div class="text-center">Designation</div>
            </div>
        </div>
                <div class="row" style="margin-top: 0; margin-bottom: 0;">
                    <div class="col-sm-4"><b>Noted by:</b></div>
                    <div class="col-sm-8"><b>Endorsed by:</b></div>
                </div>
                <div class="container">
    <div class="row">
        <div class="col">
            <div class="text-center">
                <div id="adviserPhotoContainer" class="photo-container" style="width: 180px; height: 45px; border-bottom: .5px solid #D3D3D3;"></div>
            </div>
        </div>
        <div class="col">
            <div class="text-center">
                <div id="chairpersonPhotoContainer" class="photo-container" style="width: 180px; height: 45px; border-bottom: .5px solid #D3D3D3;"></div>
            </div>
        </div>
        <div class="col">
            <div class="text-center">
                <div id="deanPhotoContainer" class="photo-container" style="width: 180px; height: 45px; border-bottom: .5px solid #D3D3D3;"></div>
            </div>
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
        <div class="text-center">
        <div id="icesPhotoContainer" class="photo-container" style="width: 210px; height: 45px; border-bottom: .5px solid #D3D3D3;"></div>
        </div>
    </div>
    <div class="col-4">
        <div class="text-center">
        <div id="ministryPhotoContainer" class="photo-container" style="width: 210px; height: 45px; border-bottom: .5px solid #D3D3D3;"></div>
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
        <div class="text-center">
        <div id="sdsPhotoContainer" class="photo-container" style="width: 210px; height: 45px; border-bottom: .5px solid #D3D3D3;"></div>
        </div>
    </div>
    <div class="col-4">
        <div class="text-center">
        <div id="osaPhotoContainer" class="photo-container" style="width: 210px; height: 45px; border-bottom: .5px solid #D3D3D3;"></div>
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
    <div class="text-center">
    <div id="vpsaPhotoContainer" class="photo-container" style="width: 210px; height: 45px; border-bottom: .5px solid #D3D3D3;"></div>
        </div>
    </div>
    <div class="col-4">
        <div class="text-center">
        <div id="vpfaPhotoContainer" class="photo-container" style="width: 210px; height: 45px; border-bottom: .5px solid #D3D3D3;"></div>
        </div>
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
                <form id="pinForm" action="verifyPin.php" method="POST">
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
                window.location.href = 'rsoActivityFormProcess.php?' + $('#myForm').serialize();
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

</script></body>
</html>
