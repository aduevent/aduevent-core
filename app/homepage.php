<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdUEvent</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <style>
        body {
            background-image: url('bground.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            margin: 0;
            padding: 0;
        }
        .tag{
            margin:0;
        }
        .tag h2 {
            font-size: 2.5em;
            color: #000080;
        }
        .content {
            padding: 0;
            height: 60vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        #login-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(255, 255, 255, 0.8); /* Adjust opacity and color */
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body>
    <div class="container text-center">
        <div class="row">
            <div class="col-sm-8 mt-3">
                <img src="logoadu.png" class="img-fluid rounded float-start" alt="Left Logo" width="50%">
            </div>
        </div>
        <div class="container">
            <div id="login-container">
                <div id="login-logo" class="text-center" style="margin-bottom: 10px;">
                    <img src="adu.png" alt="Logo" class="img-fluid" width="25%">
                </div>
            <div class="tag text-center">
                <h2>AdUevent</h2>
                <h3>Welcome, Klasmeyt!</h3>
                <p class="header-text">Welcome to the Adamson's Event Management System!</p>
            </div>
                <p class="header-text"><br> Login as:</p>
            </header>
            <section id="role-selection">
                <div id="role-buttons" class="text-center">
                    <button class="btn btn-primary" style="background-color: #000080; border-radius: 50px; margin-top: 10px; border: none;" onclick="selectRole('employee')">
                        Employee
                    </button>
                    <button class="btn btn-primary" style="background-color: #000080; border-radius: 50px; margin-top: 10px; border: none;" onclick="selectRole('student')">
                        Student
                    </button>
                </div>
            </section>
        </div>
    </div>
    <script src="/node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>
    <script>
    function selectRole(role) {
        if (role === 'employee') {
            window.location.href = 'loginEmployee.php';
        }
        if (role === 'student') {
            window.location.href = 'loginStudent.php';
        }
    }
    </script>
</body>
</html>
