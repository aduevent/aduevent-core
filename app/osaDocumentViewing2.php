<?php
session_start();
if (!isset($_SESSION["id"]) || !isset($_SESSION["access"])) {
    header("Location: loginEmployee.php");
    exit();
}
include "dbcon.php";
$userId = $_SESSION["id"];
$userQuery =
    "SELECT name, email, profilePicture FROM employeeuser WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc(); // Fetch once and store the result
$userName = $userData["name"];
$email = $userData["email"];
$dp = $userData["profilePicture"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["eventID"])) {
        $eventID = $_POST["eventID"];
        $query = "SELECT event.*, organization.organizationName , organization.organizationLogo
                  FROM event
                  INNER JOIN organization ON event.organizationID = organization.organizationID
                  WHERE event.eventID = $eventID";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $submissionDate = $row["eventProposalDate"];
            $organizationName = $row["organizationID"];
            $orgType = $row["organizationTypeID"];
            $activityTitle = $row["eventTitle"];
            $eventCategory = $row["pointSystemCategoryID"];
            $proposedDate = $row["eventDate"];
            $timeStart = $row["eventTimeStart"];
            $timeEnd = $row["eventTimeEnd"];
            $venue = $row["eventVenue"];
            $venueType = $row["eventVenueCategory"];
            $participant = $row["participantCount"];
            $organizationPartner = $row["partnerOrganization"];
            $orgFundAmount = $row["organizationFund"];
            $solShareAmount = $row["solidarityShare"];
            $regFeeAmount = $row["registrationFee"];
            $ausgSubAmount = $row["ausgSubsidy"];
            $sponsorValue = $row["sponsorship"];
            $ticketSellingAmount = $row["ticketSelling"];
            $controlNumber = $row["ticketControlNumber"];
            $others = $row["others"];
            $leadSign = $row["leadSign"];
            $designation = $row["designation"];
            $adviserSign = $row["adviserSign"];
            $chairpersonSign = $row["chairpersonSign"];
            $deanSign = $row["deanSign"];
            $icesSign = $row["icesSign"];
            $ministrySign = $row["ministrySign"];
            $sdsSign = $row["sdsSign"];
            $osaSign = $row["osaSign"];
            $vpsaSign = $row["vpsaSign"];
            $vpfaSign = $row["vpfaSign"];
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
    <?php
    include "navbar.php";
    $activePage = "osaEventApproval";
    ?>
    <style>
        .btn {
            color: white;
            border-radius: 50px;
            border: none;
            padding: 5px 10px;
            font-size: 12px;
        }
        .signature-box {
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #rejectPinModal .modal-content {
            border-radius: 10px;
            padding: 20px;
        }
        #rejectPinModal .modal-title strong {
            font-size: 1.25rem;
        }
        #rejectPinModal .modal-header {
            border-bottom: none;
            padding-bottom: 0;
        }
        .pin-input {
            width: 140px;
            height: 40px;
            border: 1px solid #ced4da;
            border-radius: 20px;
            font-size: 18px;
            text-align: center;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }
        .modal-header {
            border-bottom: none;
        }
        .approve-btn {
            background-color: #000080;
            border-color: #000080;
        }
        .approve-btn:hover {
            background-color: #000070;
            border-color: #000070;
        }
    </style>
</head>
<body style="margin-left: 20%; padding-top: 5px;">
<div class="container" style="margin-left: 5px;">
<button onclick="window.location.href='osaEventApproval.php';" class="btn btn-light d-flex justify-content-center align-items-center"
                style="width: 40px; height: 40px; border-radius: 50%; padding: 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #f9f9f9;">
            <i class="bi bi-arrow-left-circle" style="color: #000080; font-size: 20px;"></i> <!-- Bootstrap icon with blue color -->
        </button>
    <div class="row justify-content-center align-items-center">
        <div class="rounded-container d-flex justify-content-center align-items-center" style="background-color: #F9F9F9; margin-top: 8px; border-radius: 25px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); width: 90%; text-align: center;">
            <div style="display: flex; flex-direction: column; align-items: center;">
                <div style="font-size: 1.7em; font-weight: bold; margin-bottom: 0; text-align:center;">
                    <?php echo $activityTitle; ?>
                </div>
                <div style="font-size: .7em; color: #666; margin-top: 0; border-top: 1px solid #000;">
                    <?php switch ($eventCategory) {
                        case "1":
                            echo "Organizational Related";
                            break;
                        case "4":
                            echo "Environmental";
                            break;
                        case "5":
                            echo "Organizational Development";
                            break;
                        case "3":
                            echo "Spiritual Enrichment";
                            break;
                        case "2":
                            echo "Community Involvement";
                            break;
                        case "6":
                            echo "Others: " . $otherEventCategory;
                            break;
                        default:
                            echo "Unknown";
                            break;
                    } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center align-items-center" style="margin-top: 20px;">
    <div class="col-lg-7"><h4>Proposal Details</h4></div>
    <div class="col-lg-5"><h4>Financial Details</h4></div>
    <div class="col-lg-7 d-flex align-items-stretch">
        <div class="rounded-container" style="background-color: #F9F9F9; border-radius: 15px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); width: 100%; margin: 0 auto; height: 100%;">
            <form id="myForm" method="post" action="osaDocumentProcess.php">
                <input type="hidden" name="eventID" value="<?php echo $eventID; ?>">
                <?php
                $resultOrganization = mysqli_query(
                    $conn,
                    "SELECT * FROM organization WHERE organizationID = '$organizationName'"
                );
                if ($resultOrganization) {
                    $row_org = mysqli_fetch_assoc($resultOrganization);
                    $organizationLogo = $row_org["organizationLogo"];
                    $organizationName = htmlspecialchars(
                        $row_org["organizationName"]
                    );
                    $organizationType = "";
                    switch ($orgType) {
                        case "1":
                            $organizationType = "Academic";
                            break;
                        case "2":
                            $organizationType = "Co-Academic";
                            break;
                        case "3":
                            $organizationType = "Socio-civic";
                            break;
                        case "4":
                            $organizationType = "Religious";
                            break;
                        case "5":
                            $organizationType = "College Council";
                            break;
                        case "6":
                            $organizationType = "AUSG";
                            break;
                        case "7":
                            $organizationType = "Chronicle";
                            break;
                        default:
                            $organizationType = "Unknown";
                            break;
                    }
                    echo "<img src='$organizationLogo' alt='Organization Logo' style='height: 50px; width: 50px; border-radius: 50%;'>";
                    echo "<div style='display: inline-block; vertical-align: middle; margin-left: 10px;'>";
                    echo "<div style='font-size: 1.2em; font-weight: bold;'>$organizationName</div>";
                    echo "<div style='font-size: .7em; color: #666;'>$organizationType</div>";
                    echo "</div>";
                } else {
                    echo "Error fetching organization: " . mysqli_error($conn);
                }
                ?>
            <ul class="list-inline" style="margin-top: 10px;">
                <li class="list-inline-item" style="text-align: right; width: 150px;"><b>Proposed Date:</b></li>
                <li class="list-inline-item"><?php echo $proposedDate; ?></li>
            </ul>
            <ul class="list-inline" style="margin-top: 0;">
                <li class="list-inline-item" style="text-align: right; width: 150px;"><b>Time:</b></li>
                <li class="list-inline-item"><?php echo date(
                    "h:i A",
                    strtotime($timeStart)
                ) .
                    " to " .
                    date("h:i A", strtotime($timeEnd)); ?></li>
            </ul>
            <ul class="list-inline" style="margin-top: 0;">
                <li class="list-inline-item" style="text-align: right; width: 150px;"><b>Venue:</b></li>
                <li class="list-inline-item"><?php echo $venue; ?></li>
            </ul>
            <ul class="list-inline" style="margin-top: 0;">
                <li class="list-inline-item" style="text-align: right; width: 150px;"><b>Venue Type:</b></li>
                <li class="list-inline-item">
                <?php switch ($venueType) {
                    case "1":
                        echo "On-Campus";
                        break;
                    case "2":
                        echo "Off-Campus";
                        break;
                    case "3":
                        echo "Online";
                        break;
                    default:
                        echo "Unknown";
                        break;
                } ?>
                </li>
            </ul>
            <ul class="list-inline" style="margin-top: 0;">
                <li class="list-inline-item" style="text-align: right; width: 150px;"><b>No. of Participants:</b></li>
                <li class="list-inline-item"><?php echo $participant; ?></li>
            </ul>
            <ul class="list-inline" style="margin-top: 0;">
                <li class="list-inline-item" style="text-align: right; width: 150px;"><b>Partner Organization:</b></li>
                <li class="list-inline-item"><?php echo $organizationPartner; ?></li>
            </ul>
        </div>
    </div>
    <div class="col-lg-5 d-flex align-items-stretch">
        <div class="rounded-container" style="background-color: #F9F9F9; border-radius: 15px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); width: 100%; margin: 0 auto; height: 100%;">
            <ul class="list-inline" style="margin-top: 0;">
                <li class="list-inline-item"><b>Organization Fund:</b></li>
                <li class="list-inline-item"><?php echo $orgFundAmount
                    ? "Php " . $orgFundAmount
                    : "N/A"; ?></li>
            </ul>
            <ul class="list-inline" style="margin-top: 0;">
                <li class="list-inline-item"><b>Solidarity Share:</b></li>
                <li class="list-inline-item"><?php echo $solShareAmount
                    ? "Php " . $solShareAmount
                    : "N/A"; ?></li>
            </ul>
            <ul class="list-inline" style="margin-top: 0;">
                <li class="list-inline-item"><b>Registration Fee:</b></li>
                <li class="list-inline-item"><?php echo $regFeeAmount
                    ? "Php " . $regFeeAmount
                    : "N/A"; ?></li>
            </ul>
            <ul class="list-inline" style="margin-top: 0;">
                <li class="list-inline-item"><b>AUSG Subsidy:</b></li>
                <li class="list-inline-item"><?php echo $ausgSubAmount
                    ? "Php " . $ausgSubAmount
                    : "N/A"; ?></li>
            </ul>
            <ul class="list-inline" style="margin-top: 0;">
                <li class="list-inline-item"><b>Sponsorship:</b></li>
                <li class="list-inline-item"><?php echo $sponsorValue
                    ? $sponsorValue
                    : "N/A"; ?></li>
            </ul>
            <ul class="list-inline" style="margin-top: 0;">
                <li class="list-inline-item"><b>Ticket Selling:</b></li>
                <li class="list-inline-item"><?php echo $ticketSellingAmount
                    ? "Php " . $ticketSellingAmount
                    : "N/A"; ?></li>
            </ul>
            <ul class="list-inline" style="margin-top: 0;">
                <li class="list-inline-item"><b>Control Number:</b></li>
                <li class="list-inline-item"><?php echo $controlNumber
                    ? $controlNumber
                    : "N/A"; ?></li>
            </ul>
            <ul class="list-inline" style="margin-top: 0;">
                <li class="list-inline-item"><b>Others:</b></li>
                <li class="list-inline-item"><?php echo $others
                    ? $others
                    : "N/A"; ?></li>
            </ul>
        </div>
    </div>
</div>
<div class="row" style="margin-top: 20px;"><h4>Project Lead Details</h4></div>
    <div class="rounded-container" style="background-color: #F9F9F9; border-radius: 15px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); width: 100%; margin: 0 auto;">
        <div class="row text-center">
            <div class="col-4">
                <?php echo !empty($leadSign)
                    ? "<p style='margin-bottom: 0;'>$leadSign</p>"
                    : "<p style='margin-bottom: 0;'>N/A</p>"; ?>
            </div>
            <div class="col-4">
                <?php echo !empty($designation)
                    ? "<p style='margin-bottom: 0;'>$designation</p>"
                    : "<p style='margin-bottom: 0;'>N/A</p>"; ?>
            </div>
            <div class="col-4">
                <p style="margin-bottom: 0;"><?php echo !empty($submissionDate)
                    ? $submissionDate
                    : "N/A"; ?></p>
            </div>
        </div>
        <div class="row text-center">
            <div class="col-4">
                <label for="leadSign" style="font-size: 0.7em; margin-top: 0; border-top: 1px solid #000;">Project Lead Name</label>
            </div>
            <div class="col-4">
                <label for="designation" style="font-size: 0.7em; margin-top: 0; border-top: 1px solid #000;">Designation</label>
            </div>
            <div class="col-4">
                <label for="submissionDate" style="font-size: 0.7em; margin-top: 0; border-top: 1px solid #000;">Date Submitted</label>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 20px;"><h4>Approval Section</h4></div>
        <div class="rounded-container" style="background-color: #F9F9F9; border-radius: 15px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); width: 100%; margin: 0 auto;">
            <div class="row text-center">
                <div class="col-4">
                    <div class="signature-box">
                        <?php echo !empty($adviserSign)
                            ? "<p style='margin-bottom: 0;'>$adviserSign</p>"
                            : ""; ?>
                    </div>
                    <label for="adviserSign" style="font-size: 0.7em; margin-top: 0; border-top: 1px solid #000;">Organization Adviser</label>
                </div>
            <?php if ($orgType == 1): ?>
                <div class="col-4">
                    <div class="signature-box">
                        <?php echo !empty($chairpersonSign)
                            ? "<p style='margin-bottom: 0;'>$chairpersonSign</p>"
                            : ""; ?>
                    </div>
                    <label for="chairpersonSign" style="font-size: 0.7em; margin-top: 0; border-top: 1px solid #000;">College Chairperson</label>
                </div>
                <div class="col-4">
                    <div class="signature-box">
                        <?php echo !empty($deanSign)
                            ? "<p style='margin-bottom: 0;'>$deanSign</p>"
                            : ""; ?>
                    </div>
                    <label for="deanSign" style="font-size: 0.7em; margin-top: 0; border-top: 1px solid #000;">College Dean</label>
                </div>
            <?php else: ?>
                <div class="col-4"></div>
                <div class="col-4"></div>
            <?php endif; ?>
        </div>
        <div class="row text-center">
            <?php if ($eventCategory == 2): ?>
                <div class="col-6">
                    <div class="signature-box">
                        <?php echo !empty($icesSign)
                            ? "<p style='margin-bottom: 0;'>$icesSign</p>"
                            : ""; ?>
                    </div>
                    <label for="icesSign" style="font-size: 0.7em; margin-top: 0; border-top: 1px solid #000;">Integrated Community External Services</label>
                </div>
            <?php elseif ($eventCategory == 3): ?>
                <div class="col-6">
                    <div class="signature-box">
                        <?php echo !empty($ministrySign)
                            ? "<p style='margin-bottom: 0;'>$ministrySign</p>"
                            : ""; ?>
                    </div>
                    <label for="ministrySign" style="font-size: 0.7em; margin-top: 0; border-top: 1px solid #000;">Campus Ministry Office</label>
                </div>
            <?php else: ?>
                <div class="col-6"></div>
            <?php endif; ?>
                <div class="col-6"></div>
        </div>
        <div class="row text-center">
            <div class="col-6">
                <div class="signature-box">
                    <?php echo !empty($sdsSign)
                        ? "<p style='margin-bottom: 0;'>$sdsSign</p>"
                        : ""; ?>
                </div>
                <label for="sdsSign" style="font-size: 0.7em; margin-top: 0; border-top: 1px solid #000;">Student Development Section</label>
            </div>
            <div class="col-6">
                <div class="signature-box">
                    <?php echo !empty($osaSign)
                        ? "<p style='margin-bottom: 0;'>$osaSign</p>"
                        : ""; ?>
                </div>
                <label for="osaSign" style="font-size: 0.7em; margin-top: 0; border-top: 1px solid #000;">Office of Student Affairs</label>
            </div>
        </div>
        <div class="row text-center">
            <?php if ($venueType == 2): ?>
                <div class="col-6">
                    <div class="signature-box">
                        <?php echo !empty($vpsaSign)
                            ? "<p style='margin-bottom: 0;'>$vpsaSign</p>"
                            : ""; ?>
                    </div>
                    <label for="vpsaSign" style="font-size: 0.7em; margin-top: 0; border-top: 1px solid #000;">Vice President for Student Affairs</label>
                </div>
            <?php endif; ?>
            <?php if (
                !empty($ticketSellingAmount) ||
                !empty($sponsorValue) ||
                !empty($regFeeAmount)
            ): ?>
                <div class="col-6">
                    <div class="signature-box">
                        <?php echo !empty($vpfaSign)
                            ? "<p style='margin-bottom: 0;'>$vpfaSign</p>"
                            : ""; ?>
                    </div>
                    <label for="vpfaSign" style="font-size: 0.7em; margin-top: 0; border-top: 1px solid #000;">Vice President for Financial Affairs</label>
                </div>
            <?php endif; ?>
            </div>
        </div>
    <div class="options-bar d-flex justify-content-center align-items-center" style="width: 80%; margin-top: 20px; margin-bottom: 10px; margin-left: 10%;">
    <div class="container">
        <div class="row justify-content-center">
            <?php if (empty($osaSign) && $_SESSION["access"] == 5): ?>
                <div class="col-4">
                    <button type="submit" name="approve" class="btn btn-success btn-block" style="width: 100%;" onclick="showPinModal(event)">Approve as Office of Student Affairs</button>
                </div>
            <?php endif; ?>
            <?php if (
                !empty($leadSign) ||
                !empty($adviserSign) ||
                !empty($chairpersonSign) ||
                !empty($deanSign) ||
                !empty($icesSign) ||
                !empty($ministrySign) ||
                !empty($vpsaSign) ||
                !empty($vpfaSign)
            ): ?>
                <div class="col-4">
                    <button type="button" class="btn btn-info btn-block" style="width: 100%;" data-toggle="modal" data-target="#commentModal" onclick="showCommentModal(event)">Comment</button>
                </div>
            <?php endif; ?>
            <div class="col-4">
                <button type="submit" name="reject" class="btn btn-danger btn-block" style="width: 100%;" onclick="showRejectPinModal(event)">Reject</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="pinModal" tabindex="-1" role="dialog" aria-labelledby="pinModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <h5 class="modal-title text-center">
                    <strong><?php echo htmlspecialchars($userName); ?></strong>
                    <div class="small text-center">Authorization required for this action</div>
                </h5>
            </div>
            <div class="modal-body">
                <form action="osaApprovalProcess.php" method="POST">
                    <input type="hidden" name="projectLeadName" value="<?php echo htmlspecialchars(
                        $userName
                    ); ?>">
                    <input type="hidden" name="eventID" value="<?php echo $eventID; ?>">
                    <div class="form-group text-center">
                        <label for="pin" class="d-block">PIN</label>
                        <input type="password" id="pin" name="pin" class="pin-input" required>
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary approve-btn">Approve</button>
                        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal" onclick="$('#pinModal').modal('hide')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Reject PIN Modal -->
<div class="modal fade" id="rejectPinModal" tabindex="-1" role="dialog" aria-labelledby="rejectPinModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <h5 class="modal-title text-center">
                    <strong><?php echo $userName; ?></strong>
                    <div class="small text-center">Authorization required to proceed with this action</div>
                </h5>
            </div>
            <div class="modal-body">
                <form action="osaRejectionProcess.php" method="POST">
                    <input type="hidden" id="projectLeadName" name="projectLeadName" value="<?php echo $userName; ?>">
                    <input type="hidden" id="eventID" name="eventID" value="<?php echo $eventID; ?>">
                    <div class="form-group text-center">
                        <label for="rejectPin" class="d-block">PIN</label>
                        <input type="password" id="rejectPin" name="pin" class="pin-input" required>
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-danger">Reject</button>
                        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal" onclick="$('#rejectPinModal').modal('hide')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Comment Modal -->
<div class="modal fade" id="commentModal" tabindex="-1" role="dialog" aria-labelledby="commentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <h5 class="modal-title text-center">
                    <div class="small text-center">Leave a comment</div>
                </h5>
            </div>
            <div class="modal-body">
                <?php
                include "dbcon.php";
                $eventID = $_POST["eventID"];

                $query =
                    "SELECT name, comment, createdAt FROM eventcomments WHERE eventID = ? ORDER BY createdAt DESC";

                if ($stmt = $conn->prepare($query)) {
                    $stmt->bind_param("i", $eventID);
                    $stmt->execute();
                    $stmt->bind_result($name, $comment, $createdAt);
                    echo '<div class="mb-3"><strong>Comments:</strong></div>';
                    while ($stmt->fetch()) {
                        echo '<div class="mb-1">';
                        echo "<strong>" .
                            htmlspecialchars($name) .
                            ":</strong> " .
                            htmlspecialchars($comment) .
                            "<br>";
                        echo '<small class="text-muted">' .
                            date("Y-m-d H:i", strtotime($createdAt)) .
                            "</small>";
                        echo "</div>";
                    }
                    $stmt->close();
                } else {
                    echo '<div class="text-danger">Error fetching comments.</div>';
                }
                $conn->close();
                ?>
                <form method="post" action="osaAddComment.php">
                    <input type="hidden" id="projectLeadName" name="projectLeadName" value="<?php echo htmlspecialchars(
                        $userName
                    ); ?>">
                    <input type="hidden" name="eventID" value="<?php echo htmlspecialchars(
                        $eventID
                    ); ?>">
                    <div class="form-group">
                        <label for="comment">Comment:</label>
                        <textarea id="comment" name="comment" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary approve-btn">Submit Comment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showPinModal(event) {
        event.preventDefault();
        $('#pinModal').modal('show');
    }
    function showRejectPinModal(event) {
        event.preventDefault();
        $('#rejectPinModal').modal('show');
    }
    function showCommentModal(event) {
        event.preventDefault();
        $('#commentModal').modal('show');
    }
</script>
</body>
</html>
