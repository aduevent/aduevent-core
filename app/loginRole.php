<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
       #login-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(200, 200, 200, 0.8); /* Adjust opacity and color */
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        .back-link {
            color: #02254a;
        }
    </style>  
</head>
<body>
    <div class="container">
        <div id="login-container" class="bg-light p-4 rounded shadow">
            <a href="homepage.php" class="back-link"><i class="bi bi-arrow-left"></i> Back</a>
            <div id="login-logo" class="text-center">
                <img src="adu.png" alt="Logo" class="img-fluid" width="15%">
            </div>
            <header class="text-center mb-4">
                <h1>Welcome, Klasmeyt!</h1>
                <p class="header-text">Welcome to the Adamson's Event Management System!<br> Login as:</p>
            </header>
            <section id="role-selection">
                <div id="role-buttons" class="text-center">
                    <button class="btn btn-primary" onclick="selectRole('employee')">
                        Employee
                    </button>
                    <button class="btn btn-primary" onclick="selectRole('student')">
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