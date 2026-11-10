<?php
require_once 'config.php';
$user = require_login();
$errors = [];
$message = $_GET['message'] ?? '';
$fields = ['first_name', 'last_name', 'birth_date', 'sex', 'civil_status', 'street'];
$sexes = ['male', 'female'];
$statuses = ['single', 'married', 'widowed', 'seperated'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            $errors[] = 'Invalid resident.';
        } else {
            $stmt = $connect->prepare("DELETE FROM resident WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect_with_message('residents.php', 'Resident deleted.');
        }
    } elseif (in_array($action, ['add', 'update'], true)) {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = trim($_POST[$field] ?? '');
        }

        if ($data['first_name'] === '' || $data['last_name'] === '' || $data['street'] === '') {
            $errors[] = 'Name and street are required.';
        }

        $birthDate = DateTime::createFromFormat('!Y-m-d', $data['birth_date']);
        $dateErrors = DateTime::getLastErrors();
        if (
            !$birthDate ||
            ($dateErrors && ($dateErrors['warning_count'] || $dateErrors['error_count'])) ||
            $birthDate->format('Y-m-d') !== $data['birth_date'] ||
            $data['birth_date'] > date('Y-m-d')
        ) {
            $errors[] = 'Enter a valid birth date.';
        }

        if (!in_array($data['sex'], $sexes, true)) {
            $errors[] = 'Choose a valid sex.';
        }

        if (!in_array($data['civil_status'], $statuses, true)) {
            $errors[] = 'Choose a valid civil status.';
        }

        if (
            strlen($data['first_name']) > 100 ||
            strlen($data['last_name']) > 100 ||
            strlen($data['street']) > 50
        ) {
            $errors[] = 'One or more fields exceed the database limits.';
        }
        if (!$errors) {
            if ($action === 'add') {
                $stmt = $connect->prepare("INSERT INTO resident (first_name, last_name, birth_date, sex, civil_status, street, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('ssssssi', $data['first_name'], $data['last_name'], $data['birth_date'], $data['sex'], $data['civil_status'], $data['street'], $user['id']);
                $stmt->execute();
                $stmt->close();
                redirect_with_message('residents.php', 'Resident added.');
            } elseif ($id) {
                $stmt = $connect->prepare("UPDATE resident SET first_name = ?, last_name = ?, birth_date = ?, sex = ?, civil_status = ?, street = ? WHERE id = ?");
                $stmt->bind_param('ssssssi', $data['first_name'], $data['last_name'], $data['birth_date'], $data['sex'], $data['civil_status'], $data['street'], $id);
                $stmt->execute();
                $stmt->close();
                redirect_with_message('residents.php', 'Resident updated.');
            } else {
                $errors[] = 'Invalid resident.';
            }
        }
    }
}

$edit = null;
if (isset($_GET['edit']) && ctype_digit($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $connect->prepare("SELECT id, first_name, last_name, birth_date, sex, civil_status, street FROM resident WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
$result = $connect->query("SELECT id, first_name, last_name, birth_date, sex, civil_status, street, created_at FROM resident ORDER BY last_name, first_name");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Residents</title>
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
            flex-direction: row;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px
        }

        .actions form {
            display: inline-flex;
            margin: 0
        }

        .actions .btn {
            width: auto;
            min-width: 68px
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
        <h1>Resident Records</h1>
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
            <h2>
                <?= $edit ? 'Edit Resident' : 'Add Resident' ?>
            </h2>
            <form method="post" class="resident-form">
                <input type="hidden" name="csrf_token"
                    value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action"
                    value="<?= $edit ? 'update' : 'add' ?>">
                <?php if ($edit): ?>
                <input type="hidden" name="id"
                    value="<?= (int)$edit['id'] ?>">
                <?php endif; ?>
                <label>First Name<input name="first_name" maxlength="100" required
                        value="<?= h($edit['first_name'] ?? $_POST['first_name'] ?? '') ?>">
                </label>
                <label>Last Name<input name="last_name" maxlength="100" required
                        value="<?= h($edit['last_name'] ?? $_POST['last_name'] ?? '') ?>">
                </label>
                <label>Birth Date<input type="date" name="birth_date"
                        max="<?= date('Y-m-d') ?>"
                        required
                        value="<?= h($edit['birth_date'] ?? $_POST['birth_date'] ?? '') ?>">
                </label>
                <label>Sex<select name="sex" required>
                        <option value="">Select</option>
                        <?php foreach ($sexes as $option): ?>
                        <option value="<?= h($option) ?>" <?= ($edit['sex'] ?? $_POST['sex'] ?? '') === $option ? 'selected' : '' ?>>
                            <?= ucfirst($option) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Civil Status<select name="civil_status" required>
                        <option value="">Select</option>
                        <?php foreach ($statuses as $option): ?>
                        <option value="<?= h($option) ?>" <?= ($edit['civil_status'] ?? $_POST['civil_status'] ?? '') === $option ? 'selected' : '' ?>>
                            <?= ucfirst($option) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Street<input name="street" maxlength="50" required
                        value="<?= h($edit['street'] ?? $_POST['street'] ?? '') ?>">
                </label>
                <div class="form-actions">
                    <button class="btn" type="submit">
                        <?= $edit ? 'Save Changes' : 'Add Resident' ?>
                    </button>
                    <?php if ($edit): ?>
                    <a class="btn secondary" href="residents.php">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>
        <section class="panel">
            <h2>Resident List</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Birth Date</th>
                            <th>Sex</th>
                            <th>Civil Status</th>
                            <th>Street</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?= h($row['first_name'] . ' ' . $row['last_name']) ?>
                            </td>
                            <td>
                                <?= h($row['birth_date']) ?>
                            </td>
                            <td>
                                <?= h(ucfirst($row['sex'])) ?>
                            </td>
                            <td>
                                <?= h(ucfirst($row['civil_status'])) ?>
                            </td>
                            <td>
                                <?= h($row['street']) ?>
                            </td>
                            <td>
                                <?= h($row['created_at']) ?>
                            </td>
                            <td class="actions">
                                <a class="btn small"
                                    href="residents.php?edit=<?= (int)$row['id'] ?>">Edit</a>
                                <form method="post" onsubmit="return confirm('Delete this resident?')">
                                    <input type="hidden" name="csrf_token"
                                        value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id"
                                        value="<?= (int)$row['id'] ?>">
                                    <button class="btn small danger" type="submit">Delete</button>
                                </form>
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