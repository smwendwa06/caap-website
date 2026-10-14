<?php
require_once __DIR__ . '/../../../caap_includes/config.php';
require_once __DIR__ . '/../../../caap_includes/auth.php';
require_login();

$donors = [];
$result = mysqli_query($conn, "SELECT id, name, type, country, relationship_status FROM donors ORDER BY name ASC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $donors[] = $row;
    }
}

$pillClass = [
    'prospect' => 'pill',
    'engaged'  => 'pill--warn',
    'active'   => 'pill--good',
    'lapsed'   => 'pill--bad',
];

$pageTitle = 'Donors — CAAP Admin';
$activeNav = 'donors';
require __DIR__ . '/../../../caap_includes/admin-header.php';
?>

<div class="admin-topbar">
    <h1>Donors &amp; Partners</h1>
    <a href="/admin/donors/form.php" class="btn btn--primary">+ Add Donor</a>
</div>

<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert--success">Donor saved.</div>
<?php elseif (isset($_GET['deleted'])): ?>
    <div class="alert alert--success">Donor deleted.</div>
<?php endif; ?>

<?php if (empty($donors)): ?>
    <div class="empty-state">No donors or partners logged yet.</div>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Country</th>
                <th>Relationship</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($donors as $donor): ?>
                <tr>
                    <td><?= htmlspecialchars($donor['name']) ?></td>
                    <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $donor['type']))) ?></td>
                    <td><?= htmlspecialchars($donor['country'] ?? '—') ?></td>
                    <td><span class="pill <?= $pillClass[$donor['relationship_status']] ?? 'pill' ?>"><?= htmlspecialchars($donor['relationship_status']) ?></span></td>
                    <td class="actions">
                        <a href="/admin/donors/form.php?id=<?= (int) $donor['id'] ?>">Edit</a>
                        <a href="/admin/donors/delete.php?id=<?= (int) $donor['id'] ?>" onclick="return confirm('Delete this donor?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../../../caap_includes/admin-footer.php'; ?>
