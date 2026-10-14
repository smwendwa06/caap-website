<?php
require_once __DIR__ . '/../../caap_includes/config.php';
require_once __DIR__ . '/../../caap_includes/auth.php';
require_login();

function count_rows(mysqli $conn, string $sql): int
{
    $result = mysqli_query($conn, $sql);
    return $result ? (int) mysqli_fetch_row($result)[0] : 0;
}

// label, value, where the card links to
$stats = [
    ['Open Grants',       count_rows($conn, "SELECT COUNT(*) FROM grants WHERE status IN ('identified','in_progress')"),                          '/admin/grants/index.php'],
    ['Active Proposals',  count_rows($conn, "SELECT COUNT(*) FROM proposals WHERE status IN ('concept_note','drafting','submitted')"),             '/admin/proposals/index.php'],
    ['Donors & Partners', count_rows($conn, "SELECT COUNT(*) FROM donors"),                                                                        '/admin/donors/index.php'],
    ['Upcoming Meetups',  count_rows($conn, "SELECT COUNT(*) FROM meetups WHERE meetup_date >= CURDATE() AND status IN ('planned','confirmed')"), '/admin/meetups/index.php'],
    ['Unread Messages',   count_rows($conn, "SELECT COUNT(*) FROM contact_messages WHERE is_read = 0"),                                           '/admin/messages/index.php?filter=unread'],
];

$pageTitle = 'Dashboard — CAAP Admin';
$activeNav = 'dashboard';
require __DIR__ . '/../../caap_includes/admin-header.php';
?>

<div class="admin-topbar">
    <h1>Dashboard</h1>
</div>

<div class="stat-grid">
    <?php foreach ($stats as [$label, $value, $href]): ?>
        <a class="stat-card" href="<?= $href ?>">
            <span class="num"><?= $value ?></span>
            <span class="label"><?= htmlspecialchars($label) ?></span>
        </a>
    <?php endforeach; ?>
</div>

<p>Use the sidebar to manage grants, donors, proposals, blog posts, meetups, and messages.</p>

<?php require __DIR__ . '/../../caap_includes/admin-footer.php'; ?>
