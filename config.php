<?php

$connect = mysqli_connect("localhost", "root", "", "baranggaydb");
if (!$connect) {
    exit("Database connection failed.");
}
$connect->set_charset("utf8mb4");

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax'
    ]);
    session_start();
}

function h($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf()
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(400);
        exit('Invalid request. Please go back and try again.');
    }
}

function require_login()
{
    global $connect;
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }

    $stmt = $connect->prepare("SELECT id, username, role, status FROM users WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user || $user['status'] !== 'active' || !in_array($user['role'], ['admin', 'staff'], true)) {
        $_SESSION = [];
        session_destroy();
        header('Location: index.php?blocked=1');
        exit;
    }
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    return $user;
}

function require_admin()
{
    $user = require_login();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        exit('Access denied. Admin only.');
    }
    return $user;
}

function redirect_with_message($page, $message)
{
    header('Location: ' . $page . '?message=' . rawurlencode($message));
    exit;
}
