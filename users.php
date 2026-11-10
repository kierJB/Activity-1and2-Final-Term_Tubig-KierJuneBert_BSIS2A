<?php
require_once 'config.php';
$admin = require_admin();
$errors = [];
$message = $_GET['message'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    if (!$id) {
        $errors[] = 'Invalid account.';
    } elseif ($action === 'delete') {
        if ($id === (int)$admin['id']) {
            $errors[] = 'You cannot delete your own account.';
        } else {
            $check = $connect->prepare("SELECT role, status FROM users WHERE id = ?");
            $check->bind_param('i', $id);
            $check->execute();
            $target = $check->get_result()->fetch_assoc();
            $check->close();
            $lastAdmin = false;
            if ($target && $target['role'] === 'admin' && $target['status'] === 'active') {
                $count = $connect->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin' AND status = 'active'")->fetch_assoc();
                $lastAdmin = (int)$count['total'] <= 1;
            }
            if ($lastAdmin) {
                $errors[] = 'The last active admin account cannot be deleted.';
            } else {
                $stmt = $connect->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
                redirect_with_message('users.php', 'User account deleted.');
            }
        }
    } elseif ($action === 'set_status' || $action === 'set_role') {
        $value = $_POST['value'] ?? '';
        $column = $action === 'set_status' ? 'status' : 'role';
        $allowed = $action === 'set_status' ? ['pending', 'active', 'disable'] : ['admin', 'staff'];
        if (!in_array($value, $allowed, true)) {
            $errors[] = 'Invalid account change.';
        } elseif ($id === (int)$admin['id'] && (($value !== 'active' && $column === 'status') || ($value !== 'admin' && $column === 'role'))) {
            $errors[] = 'You cannot disable or demote your own account.';
        } else {
            $check = $connect->prepare("SELECT role, status FROM users WHERE id = ?");
            $check->bind_param('i', $id);
            $check->execute();
            $target = $check->get_result()->fetch_assoc();
            $check->close();
            $removesActiveAdmin = $target && $target['role'] === 'admin' && $target['status'] === 'active' && (($column === 'status' && $value !== 'active') || ($column === 'role' && $value !== 'admin'));
            $lastAdmin = false;
            if ($removesActiveAdmin) {
                $count = $connect->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin' AND status = 'active'")->fetch_assoc();
                $lastAdmin = (int)$count['total'] <= 1;
            }
            if ($lastAdmin) {
                $errors[] = 'The last active admin account cannot be disabled or changed to staff.';
            } else {
                $sql = $column === 'status' ? "UPDATE users SET status = ? WHERE id = ?" : "UPDATE users SET role = ? WHERE id = ?";
                $stmt = $connect->prepare($sql);
                $stmt->bind_param('si', $value, $id);
                $stmt->execute();
                $stmt->close();
                redirect_with_message('users.php', 'User account updated.');
            }
        }
    } else {
        $errors[] = 'Invalid action.';
    }
}
$result = $connect->query("SELECT id, username, role, status, creared_at FROM users ORDER BY id");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Management</title>
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
            min-width: 470px;
            display: flex;
            flex-direction: row;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px
        }

        .actions form {
            display: inline-flex;
            align-items: center;
            flex-wrap: nowrap;
            gap: 5px;
            margin: 0
        }

        .actions select {
            padding: 6px;
            max-width: 120px
        }

        .actions .btn {
            white-space: nowrap
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
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="residents.php">Residents</a>
            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
            <a href="users.php">User Management</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main class="page">
        <h1>User Management</h1>
        <p>Admin accounts can activate, disable, change roles, or delete registered accounts.</p>
        <?php if ($message): ?>
        <p class="notification success">
            <?= h($message) ?>
        </p>
        <?php endif; ?>
        <?php foreach ($errors as $error): ?>
        <p class="notification error">
            <?= h($error) ?>
        </p>
        <?php endforeach; ?>
        <section class="panel">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?= h($row['username']) ?>
                                <?= (int)$row['id'] === (int)$admin['id'] ? ' (you)' : '' ?>
                            </td>
                            <td>
                                <?= h(ucfirst($row['role'])) ?>
                            </td>
                            <td>
                                <?= h(ucfirst($row['status'])) ?>
                            </td>
                            <td>
                                <?= h($row['creared_at']) ?>
                            </td>
                            <td class="actions">
                                <form method="post">
                                    <input type="hidden" name="csrf_token"
                                        value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="id"
                                        value="<?= (int)$row['id'] ?>">
                                    <input type="hidden" name="action" value="set_status">
                                    <select name="value" aria-label="Account status">
                                        <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending
                                        </option>
                                        <option value="active" <?= $row['status'] === 'active' ? 'selected' : '' ?>>Active
                                        </option>
                                        <option value="disable" <?= $row['status'] === 'disable' ? 'selected' : '' ?>>Disabled
                                        </option>
                                    </select>
                                    <button class="btn small" type="submit">Save Status</button>
                                </form>
                                <form method="post">
                                    <input type="hidden" name="csrf_token"
                                        value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="id"
                                        value="<?= (int)$row['id'] ?>">
                                    <input type="hidden" name="action" value="set_role">
                                    <select name="value" aria-label="Account role">
                                        <option value="staff" <?= $row['role'] === 'staff' ? 'selected' : '' ?>>Staff
                                        </option>
                                        <option value="admin" <?= $row['role'] === 'admin' ? 'selected' : '' ?>>Admin
                                        </option>
                                    </select>
                                    <button class="btn small" type="submit">Save Role</button>
                                </form>
                                <?php if ((int)$row['id'] !== (int)$admin['id']): ?>
                                <form method="post" onsubmit="return confirm('Delete this account?')">
                                    <input type="hidden" name="csrf_token"
                                        value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="id"
                                        value="<?= (int)$row['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button class="btn small danger" type="submit">Delete</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <footer>
    </footer>
</body>

</html>