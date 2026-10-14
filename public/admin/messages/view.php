<?php
require_once __DIR__ . '/../../../caap_includes/config.php';
require_once __DIR__ . '/../../../caap_includes/auth.php';
require_login();

$listUrl = '/admin/messages/index.php';

// "Mark as unread" arrives as a POST from the button below
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_unread') {
    $postId = (int) ($_POST['id'] ?? 0);
    if ($postId) {
        $stmt = mysqli_prepare($conn, 'UPDATE contact_messages SET is_read = 0 WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $postId);
        mysqli_stmt_execute($stmt);
    }
    header('Location: ' . $listUrl . '?unread=1');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$message = null;
$gmailLink = $mailtoLink = '';

if ($id) {
    $stmt = mysqli_prepare($conn, 'SELECT id, name, email, message, is_read, submitted_at FROM contact_messages WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $message = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($message) {
        // Opening a message marks it as read
        if (!$message['is_read']) {
            $mark = mysqli_prepare($conn, 'UPDATE contact_messages SET is_read = 1 WHERE id = ?');
            mysqli_stmt_bind_param($mark, 'i', $id);
            mysqli_stmt_execute($mark);
        }
        $reSubject  = 'Re: your message to CAAP';
        $gmailLink  = 'https://mail.google.com/mail/?view=cm&fs=1&to=' . rawurlencode($message['email']) . '&su=' . rawurlencode($reSubject);
        $mailtoLink = 'mailto:' . $message['email'] . '?subject=' . rawurlencode($reSubject);
    }
}

$pageTitle = 'Message — CAAP Admin';
$activeNav = 'messages';
require __DIR__ . '/../../../caap_includes/admin-header.php';
?>

<div class="admin-topbar">
    <h1>Message</h1>
    <a href="<?= $listUrl ?>">&larr; Back to Messages</a>
</div>

<?php if (!$message): ?>
    <div class="empty-state">That message doesn't exist. It may have been deleted.</div>
<?php else: ?>
    <div class="message-card">
        <dl class="message-meta">
            <dt>From</dt>
            <dd><?= htmlspecialchars($message['name']) ?></dd>
            <dt>Email</dt>
            <dd><?= htmlspecialchars($message['email']) ?></dd>
            <dt>Received</dt>
            <dd><?= date('F j, Y \a\t g:i A', strtotime($message['submitted_at'])) ?></dd>
        </dl>

        <div class="message-body"><?= nl2br(htmlspecialchars($message['message'])) ?></div>

        <div class="message-actions">
            <a class="btn btn--primary" href="<?= htmlspecialchars($gmailLink) ?>" target="_blank" rel="noopener">Reply in Gmail</a>
            <a class="btn" href="<?= htmlspecialchars($mailtoLink) ?>">Open in mail app</a>

            <form method="post">
                <input type="hidden" name="action" value="mark_unread">
                <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
                <button type="submit" class="btn btn-plain">Mark as unread</button>
            </form>

            <form method="post" action="/admin/messages/delete.php"
                  onsubmit="return confirm('Delete this message permanently?');">
                <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
                <button type="submit" class="btn btn-plain btn--danger">Delete</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../../caap_includes/admin-footer.php'; ?>
