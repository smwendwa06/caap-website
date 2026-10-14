<?php
require_once __DIR__ . '/../../../caap_includes/config.php';
require_once __DIR__ . '/../../../caap_includes/auth.php';
require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$grant = [
    'title' => '', 'funder' => '', 'scope' => 'local', 'amount_range' => '',
    'deadline' => '', 'requirements' => '', 'source_url' => '', 'status' => 'identified',
];
$errors = [];

if ($id) {
    $stmt = mysqli_prepare($conn, 'SELECT * FROM grants WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $found = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($found) {
        $grant = $found;
    } else {
        $id = null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grant['title']        = trim($_POST['title'] ?? '');
    $grant['funder']       = trim($_POST['funder'] ?? '');
    $grant['scope']        = $_POST['scope'] ?? 'local';
    $grant['amount_range'] = trim($_POST['amount_range'] ?? '');
    $grant['deadline']     = $_POST['deadline'] ?? '';
    $grant['requirements'] = trim($_POST['requirements'] ?? '');
    $grant['source_url']   = trim($_POST['source_url'] ?? '');
    $grant['status']       = $_POST['status'] ?? 'identified';

    if ($grant['title'] === '')    { $errors[] = 'Title is required.'; }
    if ($grant['funder'] === '')   { $errors[] = 'Funder is required.'; }
    if ($grant['deadline'] === '') { $errors[] = 'Deadline is required.'; }

    if (empty($errors)) {
        if ($id) {
            $stmt = mysqli_prepare($conn, 'UPDATE grants SET title=?, funder=?, scope=?, amount_range=?, deadline=?, requirements=?, source_url=?, status=? WHERE id=?');
            mysqli_stmt_bind_param(
                $stmt, 'ssssssssi',
                $grant['title'], $grant['funder'], $grant['scope'], $grant['amount_range'],
                $grant['deadline'], $grant['requirements'], $grant['source_url'], $grant['status'], $id
            );
        } else {
            $stmt = mysqli_prepare($conn, 'INSERT INTO grants (title, funder, scope, amount_range, deadline, requirements, source_url, status, added_by) VALUES (?,?,?,?,?,?,?,?,?)');
            $addedBy = $_SESSION['user_id'];
            mysqli_stmt_bind_param(
                $stmt, 'ssssssssi',
                $grant['title'], $grant['funder'], $grant['scope'], $grant['amount_range'],
                $grant['deadline'], $grant['requirements'], $grant['source_url'], $grant['status'], $addedBy
            );
        }
        mysqli_stmt_execute($stmt);
        header('Location: /admin/grants/index.php?saved=1');
        exit;
    }
}

$pageTitle = ($id ? 'Edit' : 'Add') . ' Grant — CAAP Admin';
$activeNav = 'grants';
require __DIR__ . '/../../../caap_includes/admin-header.php';
?>

<div class="admin-topbar">
    <h1><?= $id ? 'Edit Grant' : 'Add Grant' ?></h1>
    <a href="/admin/grants/index.php">&larr; Back to Grants</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert--error">
        <?php foreach ($errors as $error): ?><p style="margin:0;"><?= htmlspecialchars($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="admin-form">
    <div class="field">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($grant['title']) ?>">
    </div>
    <div class="field">
        <label for="funder">Funder</label>
        <input type="text" id="funder" name="funder" value="<?= htmlspecialchars($grant['funder']) ?>">
    </div>
    <div class="field">
        <label for="scope">Scope</label>
        <select id="scope" name="scope">
            <option value="local" <?= $grant['scope'] === 'local' ? 'selected' : '' ?>>Local</option>
            <option value="international" <?= $grant['scope'] === 'international' ? 'selected' : '' ?>>International</option>
        </select>
    </div>
    <div class="field">
        <label for="amount_range">Amount Range</label>
        <input type="text" id="amount_range" name="amount_range" value="<?= htmlspecialchars($grant['amount_range']) ?>" placeholder="e.g. KES 500,000 – 1,000,000">
    </div>
    <div class="field">
        <label for="deadline">Deadline</label>
        <input type="date" id="deadline" name="deadline" value="<?= htmlspecialchars($grant['deadline']) ?>">
    </div>
    <div class="field">
        <label for="requirements">Requirements</label>
        <textarea id="requirements" name="requirements" rows="4"><?= htmlspecialchars($grant['requirements']) ?></textarea>
    </div>
    <div class="field">
        <label for="source_url">Source URL</label>
        <input type="url" id="source_url" name="source_url" value="<?= htmlspecialchars($grant['source_url']) ?>">
    </div>
    <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
            <?php foreach (['identified','in_progress','submitted','awarded','rejected','closed'] as $s): ?>
                <option value="<?= $s ?>" <?= $grant['status'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn--primary" style="border:none; cursor:pointer;">Save Grant</button>
        <a href="/admin/grants/index.php" class="btn">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/../../../caap_includes/admin-footer.php'; ?>
