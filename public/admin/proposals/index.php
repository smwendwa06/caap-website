<?php
require_once __DIR__ . '/../../../caap_includes/config.php';
require_once __DIR__ . '/../../../caap_includes/auth.php';
require_login();

$proposals = [];
$result = mysqli_query($conn, "
    SELECT p.id, p.title, p.amount_requested, p.status, g.title AS grant_title, d.name AS donor_name
    FROM proposals p
    LEFT JOIN grants g ON p.grant_id = g.id
    LEFT JOIN donors d ON p.donor_id = d.id
    ORDER BY p.created_at DESC
");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $proposals[] = $row;
    }
}

$pillClass = [
    'concept_note' => 'pill',
    'drafting'     => 'pill',
    'submitted'    => 'pill--warn',
    'awarded'      => 'pill--good',
    'rejected'     => 'pill--bad',
];

$pageTitle = 'Proposals — CAAP Admin';
$activeNav = 'proposals';
require __DIR__ . '/../../../caap_includes/admin-header.php';
?>

<div class="admin-topbar">
    <h1>Proposals</h1>
    <a href="/admin/proposals/form.php" class="btn btn--primary">+ Add Proposal</a>
</div>

<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert--success">Proposal saved.</div>
<?php elseif (isset($_GET['deleted'])): ?>
    <div class="alert alert--success">Proposal deleted.</div>
<?php endif; ?>

<?php if (empty($proposals)): ?>
    <div class="empty-state">No proposals logged yet.</div>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Grant</th>
                <th>Donor</th>
                <th>Amount Requested</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($proposals as $proposal): ?>
                <tr>
                    <td><?= htmlspecialchars($proposal['title']) ?></td>
                    <td><?= htmlspecialchars($proposal['grant_title'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($proposal['donor_name'] ?? '—') ?></td>
                    <td><?= $proposal['amount_requested'] !== null ? number_format((float) $proposal['amount_requested'], 2) : '—' ?></td>
                    <td><span class="pill <?= $pillClass[$proposal['status']] ?? 'pill' ?>"><?= htmlspecialchars(str_replace('_', ' ', $proposal['status'])) ?></span></td>
                    <td class="actions">
                        <a href="/admin/proposals/form.php?id=<?= (int) $proposal['id'] ?>">Edit</a>
                        <a href="/admin/proposals/delete.php?id=<?= (int) $proposal['id'] ?>" onclick="return confirm('Delete this proposal?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../../../caap_includes/admin-footer.php'; ?>
