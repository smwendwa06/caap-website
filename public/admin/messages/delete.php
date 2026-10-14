<?php
require_once __DIR__ . '/../../../caap_includes/config.php';
require_once __DIR__ . '/../../../caap_includes/auth.php';
require_login();

$listUrl = '/admin/messages/index.php';

// Deleting only works from the button on the message page (POST), never from a plain link.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id) {
        $stmt = mysqli_prepare($conn, 'DELETE FROM contact_messages WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
    }
    header('Location: ' . $listUrl . '?deleted=1');
    exit;
}

header('Location: ' . $listUrl);
exit;
