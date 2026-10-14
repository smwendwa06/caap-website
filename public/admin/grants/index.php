<?php
require_once __DIR__ . '/../../../caap_includes/config.php';
require_once __DIR__ . '/../../../caap_includes/auth.php';
require_login();

$grants = [];
$result = mysqli_query($conn, "SELECT id, title, funder, deadline, status FROM grants ORDER BY deadline ASC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $grants[] = $row;
    }
}

$pillClass = [
    'identified'  => 'pill',
    'in_progress' => 'pill--warn',
    'submitted'   => 'pill--warn',
    'awarded'     => 'pill--good',
    'rejected'    => 'pill--bad',
    'closed'      => 'pill',
];

$pageTitle = 'Grants — CAAP Admin';
$activeNav = 'grants';
require __DIR__ . '/../../../caap_includes/admin-header.php';
?>

<div class="admin-topbar">
    <h1>Grants</h1>
    <a href="/admin/grants/form.php" class="btn btn--primary">+ Add Grant</a>
</div>

<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert--success">Grant saved.</div>
<?php elseif (isset($_GET['deleted'])): ?>
    <div class="alert alert--success">Grant deleted.</div>
<?php endif; ?>

<?php if (empty($grants)): ?>
    <div class="empty-state">No grants logged yet. Add the first one to start replacing the spreadsheet.</div>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Funder</th>
                <th>Deadline</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($grants as $grant): ?>
                <tr>
                    <td><?= htmlspecialchars($grant['title']) ?></td>
                    <td><?= htmlspecialchars($grant['funder']) ?></td>
                    <td><?= date('M j, Y', strtotime($grant['deadline'])) ?></td>
                    <td><span class="pill <?= $pillClass[$grant['status']] ?? 'pill' ?>"><?= htmlspecialchars(str_replace('_', ' ', $grant['status'])) ?></span></td>
                    <td class="actions">
                        <a href="/admin/grants/form.php?id=<?= (int) $grant['id'] ?>">Edit</a>
                        <a href="/admin/grants/delete.php?id=<?= (int) $grant['id'] ?>"
                           onclick="return confirm('Delete this grant?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../../../caap_includes/admin-footer.php'; ?>
