<?php
$activePage = basename($_SERVER["PHP_SELF"], ".php");
include "dbcon.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Navigation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 20%;
            background-color: #000080;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px 10px;
        }
        .sidebar .logo {
            text-align: center;
            margin-bottom: -20px;
        }
        .sidebar .logo img {
            max-width: 100%;
            height: auto;
        }
        .sidebar .nav-options {
            flex-grow: 1;
        }
        .sidebar .nav-item {
            margin-bottom: 3px; /* Reduced space between nav items */
        }
        .sidebar .nav-link {
            color: #F8F8F8; /* Default link color */
            transition: color 0.3s;
        }
        .sidebar .nav-link.active {
            color: #000080; /* Dark blue color when active */
            background-color: white; /* White background on active */
            border-radius: 50px; /* Rounded edges */
        }
        .sidebar .nav-link:hover {
            color: black; /* Black on hover */
        }
        .nav-label {
            color: #F8F8F8; /* Default gray color */
            font-weight: bold;
            font-size: 14px; /* Adjust font size as needed */
        }
        .notification-badge {
            position: absolute;
            top: 10px;
            right: 15px;
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 50%;
            font-size: 12px;
        }
        .logout {
            margin-top: auto;
        }
        .logout a {
            color: #fff; /* Default color for logout link */
            text-decoration: none;
            transition: color 0.3s;
        }
        .logout a:hover {
            color: black; /* Change to black on hover */
        }
        .search-bar {
            border: none; /* No border */
            background-color: #dcdcdc; /* White background */
            border-radius: 50px;
        }
        .search-bar:focus {
            box-shadow: none; /* Remove focus shadow */
        }
        .search-btn {
            background-color: #dcdcdc; /* Dark blue background */
            color: #02248A; /* White icon */
            border-radius: 50px;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <!-- Logo Section -->
    <div class="logo">
        <img src="systemlogoo.png" alt="Event Logo">
    </div>

    <!-- Navigation Options -->
    <div class="nav-options">
    <ul class="nav flex-column">
        <li class="nav-item mt-3">
        <span class="nav-label">Menu</span>
        <hr class="mt-1 mb-2" style="border-top: 1px solid gray; opacity: 0.5;"/>
    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php if (
                            $activePage == "rsoIndex" ||
                            $activePage == "rsoSearchResult" ||
                            $activePage == "rsoDetailViewing"
                        ) {
                            echo "active";
                        } ?>" href="rsoIndex.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if (
                            $activePage == "rsoEventHub" ||
                            $activePage == "rsoDocumentViewing" ||
                            $activePage == "rsoFeedbackFormCreation" ||
                            $activePage == "rsoViewRegistrations" ||
                            $activePage == "rsoEditPromotion" ||
                            $activePage == "rsoEditProposal" ||
                            $activePage == "rsoEventPerformance" ||
                            $activePage == "rsoRegistration"
                        ) {
                            echo "active";
                        } ?>" href="rsoEventHub.php">Events Hub</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if (
                            $activePage == "rsoActivityForm"
                        ) {
                            echo "active";
                        } ?>" href="rsoActivityForm.php">Activity Form</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if (
                            $activePage == "rsoDashboard"
                        ) {
                            echo "active";
                        } ?>" href="rsoDashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if (
                            $activePage == "rsoCalendarView" ||
                            $activePage == "rsoEventCalendar"
                        ) {
                            echo "active";
                        } ?>" href="rsoCalendarView.php">Calendar</a>
                    </li>
                    <li class="nav-item mt-3">
                    <li class="nav-item mt-3">
        <span class="nav-label">Customize</span>
        <hr class="mt-1 mb-2" style="border-top: 1px solid gray; opacity: 0.5;"/>
    </li>

    <!-- Profile Nav Link -->
    <li class="nav-item">
        <a class="nav-link <?php if ($activePage == "rsoProfileViewing") {
            echo "active";
        } ?>" href="rsoProfileViewing.php">Profile</a>
                    </li>
        </ul>
    </div>
        <div class="logout">
    <a href="#" onclick="confirmLogout()">
        <i class="bi bi-box-arrow-right" style="font-size: 1rem; margin-right: 5px;"></i> <!-- Bootstrap icon for logout -->
        Logout
    </a>
</div>
    </div>
<div>
<div class="container">
        <div class="row mb-4 align-items-center">
            <div class="col-auto d-flex align-items-center">
                <!-- Display Picture -->
                <?php // Check if profile picture exists; if not, use default profile picture
                $profilePic = !empty($dp) ? $dp : "defaultavatar.jpg"; ?>
                <img src="<?php echo $profilePic; ?>" alt="Profile Picture" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">

                <!-- Name and Email -->
                <div>
                    <div style="font-weight: bold; font-size: 1rem;"><?php echo $userName; ?></div>
                    <div style="font-size: 0.7rem; color: gray;"><?php echo $email; ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</div>

<!-- Bootstrap JS (Optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmLogout() {
        if (confirm("Are you sure you want to logout?")) {
            window.location.href = "logout.php";
        }
    }
</script>
</body>
</html>

</nav>
