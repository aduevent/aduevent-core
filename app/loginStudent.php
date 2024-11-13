<?php
session_start();
include("dbcon.php");

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $studentEmail = $_POST['email'];
    $studentPassword = $_POST['password'];

    if(!empty($studentEmail) && !empty($studentPassword)){
        $query = "SELECT * FROM studentuser WHERE email = '$studentEmail' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if($result && mysqli_num_rows($result) > 0){
            $studentData = mysqli_fetch_assoc($result);
            $hashedPassword = $studentData['password']; // Retrieve hashed password from database
            if(password_verify($studentPassword, $hashedPassword)){ // Verify entered password with hashed password
                $_SESSION['id'] = $studentData['id'];
                $_SESSION['name'] = $studentData['name'];
                $_SESSION['access'] = $studentData['userTypeID'];
                $_SESSION['organizationID'] = $studentData['organizationID'];
                $_SESSION['organizationTypeID'] = $studentData['organizationTypeID'];
                if($studentData['userTypeID'] == 1){
                    header("Location: studentIndex.php?id=".$studentData['id']);
                    exit;
                } elseif($studentData['userTypeID'] == 2){
                    header("Location: rsoIndex.php?id=".$studentData['id']);
                    exit;
                }
            } else {
                echo "<script>
                    alert('Invalid email address or password');
                    window.location.href = 'loginStudent.php';
                </script>";
            }
        } else {
            echo "<script>
                alert('Invalid email address or password');
                window.location.href = 'loginStudent.php';
            </script>";
        }
    } else {
        echo "<script>
            alert('Please enter valid information');
            window.location.href = 'loginStudent.php';
        </script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
        }

        .signup {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="post" class="text-center">
        <a href="homepage.php" class="back-link"><i class="bi bi-arrow-left"></i> Back</a>
            <h2 class="mb-0" style="margin-bottom: 0; color: #000080; padding-bottom: 0;">AdUevent</h2>
            <h4 class="mb-4" style="margin-top: 0; padding-top: 0;">Log-in as a Student</h4>
            <div class="form-group">
                <label for="email">EMAIL ADDRESS:</label>
                <input type="text" class="form-control" id="email" name="email">
            </div>
            <div class="form-group">
                <label for="password">PASSWORD:</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <button type="submit" name="login" class="btn btn-primary" style="background-color: #000080; border-radius: 50px; margin-top: 10px">Login</button>
            <div class="signup">
                Don't have an account? <a href="signupStudent.php">Signup now</a>
                 | <a href="studentForgotPassword.php">Forgot Password</a>
            </div>
        </form>
    </div>
</body>
</html>
