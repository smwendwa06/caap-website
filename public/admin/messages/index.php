<?php
require_once __DIR__ . '/../../../caap_includes/config.php';
require_once __DIR__ . '/../../../caap_includes/auth.php';
require_once __DIR__ . '/../../../caap_includes/helpers.php';
require_login();

function count_messages(mysqli $conn, string $where = ''): int
{
    $result = mysqli_query($conn, "SELECT COUNT(*) FROM contact_messages $where");
    return $result ? (int) mysqli_fetch_row($result)[0] : 0;
}

// "Mark all as read" button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_all_read') {
    mysqli_query($conn, "UPDATE contact_messages SET is_read = 1 WHERE is_read = 0");
    header('Location: /admin/messages/index.php?allread=1');
    exit;
}

$filter  = (($_GET['filter'] ?? '') === 'unread') ? 'unread' : 'all';
$perPage = 25;
$page    = max(1, (int) ($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$totalAll    = count_messages($conn);
$totalUnread = count_messages($conn, 'WHERE is_read = 0');
$total       = ($filter === 'unread') ? $totalUnread : $totalAll;
$totalPages  = max(1, (int) ceil($total / $perPage));
$where       = ($filter === 'unread') ? 'WHERE is_read = 0' : '';

$messages = [];
$result = mysqli_query(
    $conn,
    "SELECT id, name, email, message, is_read, submitted_at
     FROM contact_messages $where
     ORDER BY submitted_at DESC, id DESC
     LIMIT $perPage OFFSET $offset"
);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }
}

$pageTitle = 'Messages — CAAP Admin';
$activeNav = 'messages';
require __DIR__ . '/../../../caap_includes/admin-header.php';

$base = '/admin/messages/index.php';
$qs   = ($filter === 'unread') ? 'filter=unread&amp;' : '';
?>

<div class="admin-topbar">
    <h1>Messages</h1>
    <?php if ($totalUnread > 0): ?>
        <form method="post">
            <input type="hidden" name="action" value="mark_all_read">
            <button type="submit" class="btn btn--primary" style="border:none; cursor:pointer;">Mark all as read</button>
        </form>
    <?php endif; ?>
</div>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert--success">Message deleted.</div>
<?php elseif (isset($_GET['allread'])): ?>
    <div class="alert alert--success">All messages marked as read.</div>
<?php elseif (isset($_GET['unread'])): ?>
    <div class="alert alert--success">Marked as unread.</div>
<?php endif; ?>

<div class="filter-tabs">
    <a href="<?= $base ?>" class="<?= $filter === 'all' ? 'is-active' : '' ?>">All (<?= $totalAll ?>)</a>
    <a href="<?= $base ?>?filter=unread" class="<?= $filter === 'unread' ? 'is-active' : '' ?>">Unread (<?= $totalUnread ?>)</a>
</div>

<?php if (empty($messages)): ?>
    <div class="empty-state">
        <?= $filter === 'unread'
            ? 'No unread messages. You are all caught up.'
            : 'No messages yet. When someone uses the contact form, it will show up here.' ?>
    </div>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>From</th>
                <th>Message</th>
                <th>Received</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $m): ?>
                <tr class="<?= $m['is_read'] ? '' : 'is-unread' ?>">
                    <td>
                        <a href="/admin/messages/view.php?id=<?= (int) $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></a>
                        <?php if (!$m['is_read']): ?><span class="pill pill--warn">New</span><?php endif; ?>
                        <div class="cell-sub"><?= htmlspecialchars($m['email']) ?></div>
                    </td>
                    <td><?= htmlspecialchars(excerpt($m['message'], 110)) ?></td>
                    <td><?= date('M j, Y g:i A', strtotime($m['submitted_at'])) ?></td>
                    <td class="actions">
                        <a href="/admin/messages/view.php?id=<?= (int) $m['id'] ?>">Open</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
        <div class="pager">
            <?php if ($page > 1): ?>
                <a href="<?= $base ?>?<?= $qs ?>page=<?= $page - 1 ?>">&larr; Newer</a>
            <?php endif; ?>
            <span>Page <?= $page ?> of <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="<?= $base ?>?<?= $qs ?>page=<?= $page + 1 ?>">Older &rarr;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../../../caap_includes/admin-footer.php'; ?>
