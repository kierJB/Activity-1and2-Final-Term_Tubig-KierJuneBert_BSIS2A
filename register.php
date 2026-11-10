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
    <title>Register</title>

    <style>
        * {
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
            background: #D3E6C8;;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            background: #8FC96F;
            position: sticky;
            top: 0;
            z-index: 10;
            padding: 15px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-left: 20px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.67);
        }

        .logo img {
            border-radius: 50px;
            box-shadow: 0 4px 5px -2px rgba(0, 0, 0, 0.3);
            
        }

        .main {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px;
            margin: 50px 0 30px 0;
            flex: 1;
        }

        .main-logo {
            border-radius: 100%;
            box-shadow: 0 4px 5px -2px rgba(0, 0, 0, 0.3);
            margin-bottom: 10px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .field {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .field img {
            width: 30px;
            height: 30px;
            object-fit: contain;
        }

        #uname, #password, #password2 {
            padding: 20px;
            width: 240px;
            margin-top: 10px;
            border: 1px solid black;
            box-shadow: 0 4px 5px -2px rgba(0, 0, 0, 0.3);
        }

        .signin, .btn {
            padding: 5px;
            width: 300px;
            margin-bottom: 8px;
            box-shadow: 0 4px 5px -2px rgba(0, 0, 0, 0.3);
        }
        .link{
            text-decoration: none;
            color: darkgreen;
        }
        .notification-area {
            width: 300px;
            height: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .notification {
            font-size: 13px;
            margin: 2px 0;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }

        .account {
            margin-top: 5px;
        }

        footer {
            padding: 30px;
            background: #8FC96F;
        }
    </style>
</head>

<body>

    <div class="nav">
        <div class="logo">
            <img src="baranggay.png" alt="Barangay Logo" width="55px">
            <h2>Baranggay Resident Record System</h2>
        </div>
    </div>

    <div class="main">

        <img src="baranggay.png" alt="Barangay Logo" width="155px" class="main-logo">

        <form action="" method="post">

            <div class="field">
                <img src="username.png" alt="Username Icon">
                <input type="text" name="uname" id="uname"
                    placeholder="Enter Username" required autofocus>
            </div>

            <div class="field">
                <img src="password.png" alt="Password Icon">
                <input type="password" name="password" id="password"
                    placeholder="Enter Password" required>
            </div>

            <div class="field">
                <img src="Refresh.png" alt="Password Icon">
                <input type="password" name="password2" id="password2"
                    placeholder="Re-enter Password" required>
            </div>

            <div class="notification-area">
                <?php foreach ($errors as $e): ?>
                    <p class="notification error"><?= h($e) ?></p>
                <?php endforeach; ?>

                <?php foreach ($mess as $m): ?>
                    <p class="notification success"><?= h($m) ?></p>
                <?php endforeach; ?>
            </div>

            <input type="submit" value="Register" class="signin">

            <button type="button" onclick="togglePassword(this)" class="btn">
                Show password
            </button>

        </form>

        <p class="account">
            Already have an account? <a href="index.php" class="link">Sign in</a>
        </p>

    </div>

    <footer></footer>

    <script>
        function togglePassword(button) {
            const password1 = document.getElementById('password');
            const password2 = document.getElementById('password2');

            const isHidden = password1.type === "password";

            password1.type = isHidden ? "text" : "password";
            password2.type = isHidden ? "text" : "password";

            button.innerHTML = isHidden ? "Hide password" : "Show password";
        }

        setTimeout(() => {
            document.querySelectorAll('.notification').forEach(notification => {
                notification.style.display = 'none';
            });
        }, 3000);
    </script>

</body>
</html>