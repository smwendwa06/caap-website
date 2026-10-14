<?php
require_once __DIR__ . '/../../../caap_includes/config.php';
require_once __DIR__ . '/../../../caap_includes/auth.php';
require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$proposal = [
    'grant_id' => '', 'donor_id' => '', 'title' => '', 'amount_requested' => '',
    'status' => 'concept_note', 'submitted_at' => '', 'follow_up_date' => '', 'notes' => '',
];
$errors = [];

if ($id) {
    $stmt = mysqli_prepare($conn, 'SELECT * FROM proposals WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $found = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($found) {
        $proposal = $found;
    } else {
        $id = null;
    }
}

$grants = [];
$result = mysqli_query($conn, "SELECT id, title FROM grants ORDER BY title ASC");
if ($result) { while ($row = mysqli_fetch_assoc($result)) { $grants[] = $row; } }

$donors = [];
$result = mysqli_query($conn, "SELECT id, name FROM donors ORDER BY name ASC");
if ($result) { while ($row = mysqli_fetch_assoc($result)) { $donors[] = $row; } }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proposal['grant_id']         = $_POST['grant_id'] !== '' ? (int) $_POST['grant_id'] : null;
    $proposal['donor_id']         = $_POST['donor_id'] !== '' ? (int) $_POST['donor_id'] : null;
    $proposal['title']            = trim($_POST['title'] ?? '');
    $proposal['amount_requested'] = $_POST['amount_requested'] !== '' ? (float) $_POST['amount_requested'] : null;
    $proposal['status']           = $_POST['status'] ?? 'concept_note';
    $proposal['submitted_at']     = $_POST['submitted_at'] ?: null;
    $proposal['follow_up_date']   = $_POST['follow_up_date'] ?: null;
    $proposal['notes']            = trim($_POST['notes'] ?? '');

    if ($proposal['title'] === '') { $errors[] = 'Title is required.'; }

    if (empty($errors)) {
        if ($id) {
            $stmt = mysqli_prepare($conn, 'UPDATE proposals SET grant_id=?, donor_id=?, title=?, amount_requested=?, status=?, submitted_at=?, follow_up_date=?, notes=? WHERE id=?');
            mysqli_stmt_bind_param(
                $stmt, 'iisdssssi',
                $proposal['grant_id'], $proposal['donor_id'], $proposal['title'], $proposal['amount_requested'],
                $proposal['status'], $proposal['submitted_at'], $proposal['follow_up_date'], $proposal['notes'], $id
            );
        } else {
            $stmt = mysqli_prepare($conn, 'INSERT INTO proposals (grant_id, donor_id, title, amount_requested, status, submitted_at, follow_up_date, notes) VALUES (?,?,?,?,?,?,?,?)');
            mysqli_stmt_bind_param(
                $stmt, 'iisdssss',
                $proposal['grant_id'], $proposal['donor_id'], $proposal['title'], $proposal['amount_requested'],
                $proposal['status'], $proposal['submitted_at'], $proposal['follow_up_date'], $proposal['notes']
            );
        }
        mysqli_stmt_execute($stmt);
        header('Location: /admin/proposals/index.php?saved=1');
        exit;
    }
}

$pageTitle = ($id ? 'Edit' : 'Add') . ' Proposal — CAAP Admin';
$activeNav = 'proposals';
require __DIR__ . '/../../../caap_includes/admin-header.php';
?>

<div class="admin-topbar">
    <h1><?= $id ? 'Edit Proposal' : 'Add Proposal' ?></h1>
    <a href="/admin/proposals/index.php">&larr; Back to Proposals</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert--error">
        <?php foreach ($errors as $error): ?><p style="margin:0;"><?= htmlspecialchars($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="admin-form">
    <div class="field">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($proposal['title']) ?>">
    </div>
    <div class="field">
        <label for="grant_id">Linked Grant</label>
        <select id="grant_id" name="grant_id">
            <option value="">— None —</option>
            <?php foreach ($grants as $g): ?>
                <option value="<?= $g['id'] ?>" <?= (string) $proposal['grant_id'] === (string) $g['id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="donor_id">Linked Donor</label>
        <select id="donor_id" name="donor_id">
            <option value="">— None —</option>
            <?php foreach ($donors as $d): ?>
                <option value="<?= $d['id'] ?>" <?= (string) $proposal['donor_id'] === (string) $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="amount_requested">Amount Requested</label>
        <input type="number" step="0.01" id="amount_requested" name="amount_requested" value="<?= htmlspecialchars((string) ($proposal['amount_requested'] ?? '')) ?>">
    </div>
    <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
            <?php foreach (['concept_note','drafting','submitted','awarded','rejected'] as $s): ?>
                <option value="<?= $s ?>" <?= $proposal['status'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="submitted_at">Submitted On</label>
        <input type="date" id="submitted_at" name="submitted_at" value="<?= htmlspecialchars($proposal['submitted_at'] ?? '') ?>">
    </div>
    <div class="field">
        <label for="follow_up_date">Follow-up Date</label>
        <input type="date" id="follow_up_date" name="follow_up_date" value="<?= htmlspecialchars($proposal['follow_up_date'] ?? '') ?>">
    </div>
    <div class="field">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="4"><?= htmlspecialchars($proposal['notes']) ?></textarea>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn--primary" style="border:none; cursor:pointer;">Save Proposal</button>
        <a href="/admin/proposals/index.php" class="btn">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/../../../caap_includes/admin-footer.php'; ?>
