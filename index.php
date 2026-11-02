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
        $errors[] = "Invalid username or password.";
    }

}   
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
</head>
<body>
    <h1>Baranggay Resident Record System</h1>
    <h2>Sign In To Your Account</h2>
    <br>
    <?php foreach($errors as $e): ?>
        <p class="notification" style="color:red;"><?= h($e) ?></p>
    <?php endforeach;?>

    <form action="" method="post">
        <label for="">Username: </label>
        <input type="text" name="uname" id="" placeholder="Enter Username" required autofocus>
        <br><br>
        <label for="">Password: </label>
        <input type="password" name="password" id="password" placeholder="Enter Password" required>
        <button type="button" onclick="togglePassword('password', this)">Show</button>
        <br><br>
        <input type="submit" value="Sign In">

    </form>
    <br><br>
    <p>No account?<a href="register.php">Register</a> </p>
        

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





