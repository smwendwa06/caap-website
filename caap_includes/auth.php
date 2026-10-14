<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verify email/password against the users table and start a session on success.
 */
function attempt_login(mysqli $conn, string $email, string $password): bool
{
    $stmt = mysqli_prepare($conn, 'SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?');
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt); // needs mysqlnd — on by default in XAMPP
    $user = mysqli_fetch_assoc($result);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true); // prevents session fixation on login
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];

    return true;
}

/** Put at the top of any admin page — sends anyone not logged in to the login form. */
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: /admin/login.php');
        exit;
    }
}

/** Put at the top of pages restricted to one role, e.g. require_role('admin'). */
function require_role(string $role): void
{
    require_login();
    if ($_SESSION['role'] !== $role) {
        http_response_code(403);
        die('You do not have permission to view this page.');
    }
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
    header('Location: /admin/login.php');
    exit;
}
