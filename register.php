<?php
require_once 'config.php';
$errors = [];
$messages = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $uname = trim($_POST['uname'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    if ($uname === '' || strlen($uname) > 50) {
        $errors[] = 'Username must be 1 to 50 characters.';
    } elseif ($password !== $password2) {
        $errors[] = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } else {
        $check = $connect->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param('s', $uname);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();
        if ($exists) {
            $errors[] = 'Username is already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $connect->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param('ss', $uname, $hash);
            if ($stmt->execute()) {
                $messages[] = 'Registered successfully. Please wait for an admin to activate your account.';
            } else {
                $errors[] = 'Something went wrong. Please try again.';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif
        }

        html,
        body {
            min-height: 100%
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #D3E6C8;
            color: #183019
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
            padding: 12px 20px
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, .67)
        }

        .logo img,
        .main-logo {
            width: 55px;
            height: 55px;
            object-fit: cover;
            border-radius: 50%;
            box-shadow: 0 4px 5px -2px rgba(0, 0, 0, .3)
        }

        nav {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap
        }

        nav a {
            color: white;
            font-weight: 600;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, .5)
        }

        a {
            color: darkgreen
        }

        .main {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px;
            margin: 50px auto 30px;
            flex: 1
        }

        .auth-main {
            width: min(100%, 520px);
            text-align: center
        }

        .auth-main h1 {
            margin: 6px 0 20px
        }

        .main-logo {
            width: 155px;
            height: 155px;
            margin-bottom: 12px
        }

        .form-card {
            width: min(100%, 360px);
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin: 15px auto
        }

        .form-card label,
        .resident-form label {
            display: flex;
            flex-direction: column;
            gap: 6px;
            text-align: left;
            font-weight: 600
        }

        input,
        select {
            padding: 12px;
            border: 1px solid #657a5b;
            border-radius: 4px;
            background: #fff;
            font: inherit
        }

        .btn {
            display: inline-block;
            padding: 9px 14px;
            border: 0;
            border-radius: 4px;
            background: #538c3b;
            color: white;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 4px 5px -2px rgba(0, 0, 0, .3)
        }

        .btn:hover {
            background: #3f702c
        }

        .secondary {
            background: #71816b
        }

        .small {
            padding: 6px 9px;
            font-size: 13px
        }

        .danger {
            background: #a63d36
        }

        .link {
            color: darkgreen
        }

        .notification {
            margin: 8px auto;
            padding: 9px 12px;
            border-radius: 4px;
            text-align: center
        }

        .error {
            color: #8b1717;
            background: #ffe5e2
        }

        .success {
            color: #1b5e20;
            background: #e4f4df
        }

        .page {
            width: min(1200px, 96%);
            margin: 35px auto;
            flex: 1
        }

        .page h1 {
            margin-bottom: 20px
        }

        .page>p {
            margin: 12px 0
        }

        .cards {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin: 22px 0
        }

        .stat {
            min-width: 200px;
            flex: 1;
            padding: 22px;
            text-align: center;
            color: white;
            background: #538c3b;
            box-shadow: 0 4px 5px -2px rgba(0, 0, 0, .3)
        }

        .stat:nth-child(2) {
            background: #d49312
        }

        .stat:nth-child(3) {
            background: #4a9344
        }

        .stat h2 {
            font-size: 18px;
            margin-bottom: 14px
        }

        .stat p {
            font-size: 26px;
            font-weight: 700
        }

        .muted {
            color: #52644d
        }

        .panel {
            background: rgba(255, 255, 255, .65);
            padding: 20px;
            margin: 18px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, .12)
        }

        .panel h2 {
            margin-bottom: 16px
        }

        .resident-form {
            display: grid;
            grid-template-columns: repeat(3, minmax(160px, 1fr));
            gap: 14px
        }

        .form-actions {
            display: flex;
            align-items: end;
            gap: 8px
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white
        }

        th,
        td {
            border: 1px solid #bdcbb6;
            padding: 9px;
            text-align: left;
            vertical-align: top
        }

        th {
            background: #8FC96F;
            color: #17300f
        }

        tbody tr:nth-child(even) {
            background: #f3f8f0
        }

        .actions {
            min-width: 190px;
            display: flex;
            flex-direction: column;
            gap: 6px
        }

        .actions form {
            display: flex;
            gap: 5px;
            align-items: center;
            flex-wrap: wrap
        }

        .actions select {
            padding: 6px;
            max-width: 120px
        }

        footer {
            padding: 30px;
            background: #8FC96F;
            margin-top: auto
        }

        @media(max-width: 760px) {
            .nav {
                align-items: flex-start;
                flex-direction: column
            }

            .logo {
                padding-left: 0
            }

            .logo h2 {
                font-size: 18px
            }

            nav {
                gap: 12px
            }

            .resident-form {
                grid-template-columns: 1fr 1fr
            }
        }

        @media(max-width: 480px) {
            .resident-form {
                grid-template-columns: 1fr
            }

            .main {
                padding: 24px 14px
            }

            .page {
                width: 94%
            }

            .panel {
                padding: 12px
            }
        }
    </style>
</head>

<body>
    <header class="nav">
        <div class="logo">
            <img src="baranggay.png" alt="Barangay Logo">
            <h2>Baranggay Resident Record System</h2>
        </div>
    </header>
    <main class="main auth-main">
        <img src="baranggay.png" alt="Barangay Logo" class="main-logo">
        <h1>Create Account</h1>
        <?php foreach ($errors as $error): ?>
        <p class="notification error">
            <?= h($error) ?>
        </p>
        <?php endforeach; ?>
        <?php foreach ($messages as $message): ?>
        <p class="notification success">
            <?= h($message) ?>
        </p>
        <?php endforeach; ?>
        <form method="post" class="form-card">
            <input type="hidden" name="csrf_token"
                value="<?= h(csrf_token()) ?>">
            <label>Username<input type="text" name="uname" maxlength="50" placeholder="Enter Username" required
                    autofocus>
            </label>
            <label>Password<input type="password" name="password" id="password" placeholder="Enter Password" required>
            </label>
            <label>Re-enter Password<input type="password" name="password2" id="password2"
                    placeholder="Re-enter Password" required>
            </label>
            <button type="button" class="btn secondary" onclick="togglePasswords(this)">Show password</button>
            <button type="submit" class="btn">Register</button>
        </form>
        <p>Already have an account? <a class="link" href="index.php">Sign in</a>
        </p>
    </main>
    <footer>
    </footer>
    <script>
        function togglePasswords(button) {
            const a = document.getElementById('password'),
                b = document.getElementById('password2');
            const show = a.type === 'password';
            a.type = b.type = show ? 'text' : 'password';
            button.textContent = show ? 'Hide password' : 'Show password';
        }
    </script>
</body>

</html>