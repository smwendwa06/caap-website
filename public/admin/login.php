<?php
require_once __DIR__ . '/../../caap_includes/config.php';
require_once __DIR__ . '/../../caap_includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: /admin/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Enter both your email and password.';
    } elseif (attempt_login($conn, $email, $password)) {
        header('Location: /admin/index.php');
        exit;
    } else {
        $error = 'Incorrect email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — CAAP</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body style="background: var(--maroon-deep); min-height:100vh; margin:0; display:flex; align-items:center; justify-content:center;">
    <div class="admin-form" style="width: 340px;">
        <h1 style="font-size:1.4rem; margin-bottom: 24px;">CAAP Admin</h1>

        <?php if ($error): ?>
            <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autofocus>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
            </div>
            <button type="submit" class="btn btn--primary" style="width:100%; border:none; cursor:pointer;">Log In</button>
        </form>
    </div>
</body>
</html>
