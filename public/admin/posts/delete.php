<?php
require_once __DIR__ . '/../../../caap_includes/config.php';
require_once __DIR__ . '/../../../caap_includes/auth.php';
require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id) {
    $stmt = mysqli_prepare($conn, 'DELETE FROM posts WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
}

header('Location: /admin/posts/index.php?deleted=1');
exit;
