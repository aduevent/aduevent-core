<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginEmployee.php");
    exit;
}
include("dbcon.php");

if(isset($_GET['eventID'])) {
    $eventID = $_GET['eventID'];

    $query = "SELECT event.*, organization.organizationName, organization.organizationLogo 
          FROM event 
          INNER JOIN organization ON event.organizationID = organization.organizationID 
          WHERE event.eventID = '$eventID'";

    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0) {
        $event = mysqli_fetch_assoc($result);
    } else {
        echo "Event not found.";
        exit;
    }
} else {
    echo "Event ID not provided.";
    exit;
}

$pointSystemCategoryID = $event['pointSystemCategoryID'];
$overlayColor = '';

switch($pointSystemCategoryID) {
    case 1:
        $overlayColor = 'rgba(0, 0, 255, 0.7)'; // blue
        break;
    case 2:
        $overlayColor = 'rgba(135, 206, 250, 0.7)'; // sky blue
        break;
    case 3:
        $overlayColor = 'rgba(128, 128, 128, 0.7)'; // gray
        break;
    case 4:
        $overlayColor = 'rgba(0, 128, 0, 0.7)'; // green
        break;
    case 5:
        $overlayColor = 'rgba(255, 0, 0, 0.7)'; // red
        break;
    default:
        $overlayColor = 'rgba(0, 0, 0, 0.7)'; // default black overlay
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $event['eventTitle']; ?> Details</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <?php include 'approverNavbar.php'; ?>
    <style>
        body {
            background-color: #D3D3D3;
            <?php if (!empty($event['eventPhoto'])): ?>
                background-image: url('<?php echo $event['eventPhoto']; ?>');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            <?php endif; ?>
            position: relative;
            z-index: 1;
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: <?php echo $overlayColor; ?>;
            z-index: -1;
        }
        .event-preview {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }
    </style>
</head>
<body>
    <div class="container" style="padding-top: 70px; max-width: 900px;">
        <div class="event-preview">
            <div class="row">
                <ul class="list-inline">
                    <li class="list-inline-item"><img src='<?php echo $event['organizationLogo']; ?>' style="max-width: 100px;"></li>
                    <li class="list-inline-item"><h5 class="card-title"><?php echo $event['organizationName']; ?></h5></li>
                </ul>
                    <div class="row justify-content-center">
                    <div class="col"><div class="text-center"><b><?php echo $event["eventTitle"]; ?></b></div></div></div>
                    <div class="col"><div class="text-left"><?php echo $event["eventDescription"]; ?></div></div></div>
                    <div class="col"><div class="text-left"><?php echo $event["eventVenue"]; ?></div></div>
                    <?php 
                    $venueCategory = $event["eventVenueCategory"];
                    $venue = "";
                    if ($venueCategory == 1) {
                        $venue = "On Campus";
                    } elseif ($venueCategory == 2) {
                        $venue = "Off-Campus";
                    } elseif ($venueCategory == 3) {
                        $venue = "Online";
                    }
                    ?>
                    <div class="col"><div class="text-left"<?php echo $venue ?></div></div>
                    <div class="col"><div class="text-left"><b> Time:</b><?php echo $event["eventTimeStart"]; ?></div></div>
                    <div class="row">
                    <img class="card-img-top" src='<?php echo $event["eventPhoto"]; ?>'</li>
                    </div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>
<?php
mysqli_close($conn);
?>
