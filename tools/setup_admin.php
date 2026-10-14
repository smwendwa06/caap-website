<?php
// ONE-TIME SETUP: creates the first admin account for the CAAP dashboard.
// It locks itself as soon as any user exists. DELETE THIS FILE after you use it.

require_once __DIR__ . '/../caap_includes/config.php';

// Self-lock: if an account already exists, do nothing.
try {
    $res = mysqli_query($conn, "SELECT COUNT(*) FROM users");
    $existing = (int) mysqli_fetch_row($res)[0];
} catch (Throwable $e) {
    http_response_code(500);
    die('Could not read the users table. Check that step 3 (importing caap_schema_clean.sql) worked.');
}
if ($existing > 0) {
    http_response_code(403);
    die('Setup is already done. Delete setup_admin.php from the server, then log in at /admin/login.php.');
}

$errors = [];
$created = false;
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($name === '')                              { $errors[] = 'Enter your full name.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Enter a valid email address.'; }
    if (strlen($password) < 10)                    { $errors[] = 'Password must be at least 10 characters.'; }
    if ($password !== $confirm)                    { $errors[] = 'The two passwords do not match.'; }

    if (empty($errors)) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
            mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $hash);
            mysqli_stmt_execute($stmt);
            $created = true;
        } catch (Throwable $e) {
            $errors[] = 'Could not save the account. Check the users table exists (step 3).';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>CAAP one-time admin setup</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #4A1216; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .box { background: #fff; width: 380px; max-width: 92vw; padding: 28px; }
        h1 { font-size: 1.3rem; margin: 0 0 6px; }
        p.note { color: #555; font-size: 0.9rem; margin: 0 0 18px; }
        label { display: block; margin: 14px 0 5px; font-weight: 600; font-size: 0.9rem; }
        input { width: 100%; padding: 10px; border: 1px solid #bbb; font: inherit; box-sizing: border-box; }
        button { margin-top: 20px; width: 100%; padding: 12px; background: #7A1E24; color: #fff; border: 0; font: inherit; font-weight: 600; cursor: pointer; }
        .err { border: 1px solid #7A1E24; color: #7A1E24; padding: 10px 12px; margin-bottom: 8px; font-size: 0.9rem; }
        .ok { border: 1px solid #2e6b34; color: #2e6b34; padding: 12px; font-size: 0.95rem; line-height: 1.5; }
    </style>
</head>
<body>
<div class="box">
    <?php if ($created): ?>
        <h1>Admin created</h1>
        <div class="ok">
            <strong>Now delete <code>setup_admin.php</code> from the server</strong> (File Manager &gt; select it &gt; Delete),
            then log in at <a href="/admin/login.php">/admin/login.php</a>.
        </div>
    <?php else: ?>
        <h1>Create the first admin</h1>
        <p class="note">One-time setup. This page locks itself once an account exists.</p>

        <?php foreach ($errors as $error): ?>
            <div class="err"><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>

        <form method="post" novalidate>
            <label for="full_name">Full name</label>
            <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($name) ?>">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>">

            <label for="password">Password (10+ characters)</label>
            <input type="password" id="password" name="password">

            <label for="confirm">Confirm password</label>
            <input type="password" id="confirm" name="confirm">

            <button type="submit">Create admin account</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
