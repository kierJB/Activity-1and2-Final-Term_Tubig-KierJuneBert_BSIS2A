<?php 
    require_once "config.php";

    function h($str){
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }

    $mess = [];
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        $uname = $_POST['uname'];
        $password = $_POST['password'];
        $password2 = $_POST['password2'];

        if ($password === $password2) {
            $check = $connect->prepare("SELECT id FROM users WHERE username = ?");
            $check->bind_param('s', $uname);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows > 0) {
                $errors[] = "Username is already taken.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $connect->prepare(
                    "INSERT INTO users (username, password) VALUES (?, ?)"
                );
                $stmt->bind_param('ss', $uname, $hashedPassword);

                if ($stmt->execute()) {
                    $mess[] = "Registered successfully. Please wait for an admin to activate your account.";
                } else {
                    $errors[] = "Something went wrong. Please try again later.";
                }

                $stmt->close();
            }

            $check->close();
        } else {
            $errors[] = "Passwords do not match.";
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
    <h2>Create your Account</h2>
    <br>
    <?php foreach ($errors as $e): ?>
    <p class="notification" style="color: red;"><?= h($e) ?></p>
    <?php endforeach; ?>
    <br>    
    <?php foreach ($mess as $m): ?>
        <p class="notification" style="color: green;"><?= h($m) ?></p>
    <?php endforeach; ?>

    <form action="" method="post">
        <label for="">Username: </label>
        <input type="text" name="uname" id="" placeholder="Enter Username" required autofocus>
        <br>
        <br>
        <label for="">Password: </label>
        <input type="password" name="password" id="password" placeholder="Enter Password" required>
        <button type="button" onclick="togglePassword('password', this)">Show password</button>
        <br>
        <br>
        <label for="">Re-enter password: </label>
        <input type="password" name="password2" id="password2" placeholder="Re-enter Password" required>
        <button type="button" onclick="togglePassword('password2', this)">Show password</button>
        <br>
        <br>

        <input type="submit" value="Register">

    </form>
    <br><br>
    <p>Already have an account?<a href="index.php">Sign in</a> </p>

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




