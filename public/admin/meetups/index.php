<?php
require_once __DIR__ . '/../../../caap_includes/config.php';
require_once __DIR__ . '/../../../caap_includes/auth.php';
require_login();

$meetups = [];
$result = mysqli_query($conn, "SELECT id, title, meetup_date, location, is_virtual, status FROM meetups ORDER BY meetup_date DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $meetups[] = $row;
    }
}

$pillClass = [
    'planned'   => 'pill',
    'confirmed' => 'pill--warn',
    'completed' => 'pill--good',
    'cancelled' => 'pill--bad',
];

$pageTitle = 'Meetups — CAAP Admin';
$activeNav = 'meetups';
require __DIR__ . '/../../../caap_includes/admin-header.php';
?>

<div class="admin-topbar">
    <h1>Meetups</h1>
    <a href="/admin/meetups/form.php" class="btn btn--primary">+ Add Meetup</a>
</div>

<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert--success">Meetup saved.</div>
<?php elseif (isset($_GET['deleted'])): ?>
    <div class="alert alert--success">Meetup deleted.</div>
<?php endif; ?>

<?php if (empty($meetups)): ?>
    <div class="empty-state">No meetups logged yet.</div>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Date</th>
                <th>Location</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($meetups as $meetup): ?>
                <tr>
                    <td><?= htmlspecialchars($meetup['title']) ?></td>
                    <td><?= date('M j, Y', strtotime($meetup['meetup_date'])) ?></td>
                    <td><?= $meetup['is_virtual'] ? 'Virtual' : htmlspecialchars($meetup['location'] ?? '—') ?></td>
                    <td><span class="pill <?= $pillClass[$meetup['status']] ?? 'pill' ?>"><?= htmlspecialchars($meetup['status']) ?></span></td>
                    <td class="actions">
                        <a href="/admin/meetups/form.php?id=<?= (int) $meetup['id'] ?>">Edit</a>
                        <a href="/admin/meetups/delete.php?id=<?= (int) $meetup['id'] ?>" onclick="return confirm('Delete this meetup?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../../../caap_includes/admin-footer.php'; ?>
