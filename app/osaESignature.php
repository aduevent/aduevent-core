<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['access'])) {
    header("Location: loginEmployee.php");
    exit;
}
$employeeID = $_SESSION['id'];
include("dbcon.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature Enrollment</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-..." crossorigin="anonymous">
    <?php include 'navbar.php';
    $activePage = "osaESignature"; ?>
    <style>
        body {
            background-color: #D3D3D3;
        }
        canvas {
            border: 1.5px solid #000080;
            background-color: #ffffff;
        }
        .canvas-container {
            background-color: #ffffff; /* White background */
            border-radius: 15px; /* Rounded corners */
            padding: 20px; /* Padding inside the container */
            margin: 0 auto 20px;  /* Spacing between event previews */
            width: 60%;
        }
        .signature-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center; /* Center items horizontally */
            margin-bottom: 20px; /* Spacing between canvas and buttons */
        }
        .button-wrapper {
            display: flex;
        }
        .clear-button, .save-button {
            padding: 10px 20px;
            border: none;
            border-radius: 20px; /* Rounded edges */
            cursor: pointer;
            margin-top: 10px;
        }
        .clear-button {
            background-color: #888; /* Gray background */
            color: #fff; /* White text */
        }
        .save-button {
            background-color: #000080; /* Dark blue background */
            color: #fff; /* White text */
            margin-left: 10px; /* Spacing between buttons */
        }
    </style>
</head>
<body>
<div class="container" style="padding-top: 70px;">
    <div class="canvas-container">
    <h2 style="color: #000080; text-align: center;">Enroll Your Electronic Signature</h2>    
    <div class="signature-wrapper">
        <canvas id="canvas" width="600" height="130"></canvas>
    <div class="button-wrapper">
    <button class="save-button" id="saveBtn">Save</button>
    <button class="clear-button" id="clearBtn">Clear</button>
    <script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>
    <script>
        // Canvas setup
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        // Event listeners
        canvas.addEventListener('mousedown', (e) => {
            isDrawing = true;
            [lastX, lastY] = [e.offsetX, e.offsetY];
        });

        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', () => isDrawing = false);
        canvas.addEventListener('mouseout', () => isDrawing = false);

        // Drawing function
        function draw(e) {
            if (!isDrawing) return;
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(e.offsetX, e.offsetY);
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2;
            ctx.stroke();
            [lastX, lastY] = [e.offsetX, e.offsetY];
        }

        // Save button event listener
        document.getElementById('saveBtn').addEventListener('click', () => {
    const imageData = canvas.toDataURL('image/png'); // Specify image/png
    saveToDatabase(imageData);
});
// Clear button event listener
document.getElementById('clearBtn').addEventListener('click', clearCanvas);

// Function to clear the canvas
function clearCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

        // Function to save image to database
        function saveToDatabase(imageData) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'osaSaveSignature.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert(xhr.responseText);
                } else {
                    alert('Error: ' + xhr.status);
                }
            };
            xhr.send('image_data=' + encodeURIComponent(imageData));
        }
    </script>
</body>
</html>
