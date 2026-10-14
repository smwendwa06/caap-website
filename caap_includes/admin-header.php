<?php
require_once __DIR__ . '/auth.php';
require_login();

$pageTitle = $pageTitle ?? 'Admin — CAAP';
$activeNav = $activeNav ?? '';

// Unread contact-form messages, shown as a badge in the sidebar
$unreadMessages = 0;
try {
    $unreadResult = mysqli_query($conn, "SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
    if ($unreadResult) {
        $unreadMessages = (int) mysqli_fetch_row($unreadResult)[0];
    }
} catch (Throwable $e) {
    // A badge should never take a page down.
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div>
            <div class="admin-sidebar__brand">CAAP Admin</div>
            <nav class="admin-nav">
                <a href="/admin/index.php" class="<?= $activeNav === 'dashboard' ? 'is-active' : '' ?>">Dashboard</a>
                <a href="/admin/grants/index.php" class="<?= $activeNav === 'grants' ? 'is-active' : '' ?>">Grants</a>
                <a href="/admin/donors/index.php" class="<?= $activeNav === 'donors' ? 'is-active' : '' ?>">Donors</a>
                <a href="/admin/proposals/index.php" class="<?= $activeNav === 'proposals' ? 'is-active' : '' ?>">Proposals</a>
                <a href="/admin/posts/index.php" class="<?= $activeNav === 'posts' ? 'is-active' : '' ?>">Posts</a>
                <a href="/admin/meetups/index.php" class="<?= $activeNav === 'meetups' ? 'is-active' : '' ?>">Meetups</a>
                <a href="/admin/messages/index.php" class="<?= $activeNav === 'messages' ? 'is-active' : '' ?>">Messages<?php if ($unreadMessages > 0): ?><span class="nav-badge"><?= $unreadMessages ?></span><?php endif; ?></a>
            </nav>
        </div>
        <div class="admin-sidebar__user">
            <p><?= htmlspecialchars($_SESSION['full_name']) ?><br><span class="admin-role"><?= htmlspecialchars($_SESSION['role']) ?></span></p>
            <a href="/admin/logout.php">Log out</a>
        </div>
    </aside>
    <main class="admin-main">
