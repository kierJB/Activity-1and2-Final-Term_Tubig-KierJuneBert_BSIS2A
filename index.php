<?php 
require_once "config.php";

function h($str){
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

$errors = [];

if($_SERVER['REQUEST_METHOD'] === "POST"){

    $uname = $_POST['uname'];
    $password = $_POST['password'];

    $stmt = $connect->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param('s',$uname);
    $stmt->execute();

    $result = $stmt->get_result();

    $user = $result->fetch_assoc();

    $stmt->close();

    if ($user && password_verify($password, $user['password'])){
        if($user['status'] !== "active"){
            $errors[] = "Your account has not been actived by the admin yet.";
        } else{
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header('location: dashboard.php');

            exit;
        }
    } else{
        $errors[] = "Invalid username or password";
    }

}   
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
        <style>
        *{
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        html, body {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #D3E6C8;
        }


        .nav{
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            background: #8FC96F;
            position: sticky;
            top: 0;           
            z-index: 10;
            padding: 10px;
        }
        .logo{
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 1rem;
            padding-left: 20px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.67);
        }
        .logo img{
            border-radius: 50px;
            box-shadow: 0 4px 5px -2px rgba(0, 0, 0, 0.3);
        }

        /* .text{
            display: flex;
            gap: 30px;
            padding-right: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.67);
        }
        .nav a{
            text-decoration: none;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.67);

        } */
        .nav a:hover{
            text-decoration: underline;
            color: white;
        }
        .main {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px;
            margin: 80px 0 30px 0;
            flex: 1;

        }
        .main-logo{
            border-radius: 100%;
            box-shadow: 0 4px 5px -2px rgba(0, 0, 0, 0.3);
            margin-bottom: 10px;
        }
        #uname , #password{
            padding: 20px;
            width: 240px;
            margin-top: 10px;
            border: 1px solid black;
            box-shadow: 0 4px 5px -2px rgba(0, 0, 0, 0.3);
        }
        .signin, .btn{
            padding: 5px;
            width: 300px;
            margin-bottom: 8px;
            box-shadow: 0 4px 5px -2px rgba(0, 0, 0, 0.3);

        }
        .link{
            text-decoration: none;
            color: darkgreen;
        }
        .btn{
            padding: 5px;
            width: 300px;
        }
        .notification-area {
            width: 300px;
            height: 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .notification {
            color: red;
            margin: 0;
        }
        footer{
            padding: 30px;
            background: #8FC96F;
        }

    </style>

</head>
<body>
    <div class="nav">
        <div class="logo">
            <img src="baranggay.png" alt="Baranggay Logo" width="55px" class="main-logo">
            <h2>Baranggay Resident Record System</h2>
        </div>
       <!-- <div class="text">
         <a href="dashboard.php">Dashboard</a>
        <a href="resident.php">Resident</a>
        <a href="   users.php">User Management</a>
        <a href="logout.php">Logout</a>
       </div> -->
    </div>
    <div class="main">
    <!-- <h1>Baranggay Biringan Resident</h1>
    <br>
    <h2>Sign In To Your Account</h2> -->
    <img src="baranggay.png" alt="main logo png" width="155px" class="main-logo">

    <form action="" method="post">
        <img src="username.png" alt="username png" width="30px">
        <input type="text" name="uname" id="uname" placeholder="Enter Username" required autofocus>
        <br>
        <img src="password.png" alt="username png" width="30px">
        <input type="password" name="password" id="password" placeholder="Enter Password" required>
        <div class="notification-area">
            <?php foreach($errors as $e): ?>
                <p class="notification"><?= h($e) ?></p>
            <?php endforeach; ?>
        </div>
        <input type="submit" value="Sign In" class="signin">
        <br>
        <button type="button" onclick="togglePassword('password', this)" class="btn">Show password</button>

    
    </form>
    <p>Have no account?<a href="register.php" class="link">Register</a> </p>

    </div>

    <footer></footer>
        

         <script>
            function togglePassword(id, showR) {
                const newPass = document.getElementById(id);

                if (newPass.type === "password") {
                    newPass.type = "text";
                    showR.innerHTML = "Hide password";
                } else {
                    newPass.type = "password";
                    showR.innerHTML = "Show password";
                }
            }

            setTimeout(() => {
                document.querySelectorAll('.notification').forEach(notification => {
                    notification.style.display = 'none';
                });
            }, 3000);
        </script>

</body>
</html>
