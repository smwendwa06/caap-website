<?php
require_once __DIR__ . '/../../../caap_includes/config.php';
require_once __DIR__ . '/../../../caap_includes/auth.php';
require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$meetup = [
    'title' => '', 'meetup_date' => '', 'location' => '', 'is_virtual' => 0,
    'partner_group' => '', 'coordinator_name' => '', 'coordinator_contact' => '',
    'description' => '', 'status' => 'planned',
];
$errors = [];

if ($id) {
    $stmt = mysqli_prepare($conn, 'SELECT * FROM meetups WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $found = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($found) {
        $meetup = $found;
    } else {
        $id = null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meetup['title']               = trim($_POST['title'] ?? '');
    $meetup['meetup_date']         = $_POST['meetup_date'] ?? '';
    $meetup['location']            = trim($_POST['location'] ?? '');
    $meetup['is_virtual']          = isset($_POST['is_virtual']) ? 1 : 0;
    $meetup['partner_group']       = trim($_POST['partner_group'] ?? '');
    $meetup['coordinator_name']    = trim($_POST['coordinator_name'] ?? '');
    $meetup['coordinator_contact'] = trim($_POST['coordinator_contact'] ?? '');
    $meetup['description']         = trim($_POST['description'] ?? '');
    $meetup['status']              = $_POST['status'] ?? 'planned';

    if ($meetup['title'] === '')       { $errors[] = 'Title is required.'; }
    if ($meetup['meetup_date'] === '') { $errors[] = 'Date is required.'; }

    if (empty($errors)) {
        if ($id) {
            $stmt = mysqli_prepare($conn, 'UPDATE meetups SET title=?, meetup_date=?, location=?, is_virtual=?, partner_group=?, coordinator_name=?, coordinator_contact=?, description=?, status=? WHERE id=?');
            mysqli_stmt_bind_param(
                $stmt, 'sssisssssi',
                $meetup['title'], $meetup['meetup_date'], $meetup['location'], $meetup['is_virtual'],
                $meetup['partner_group'], $meetup['coordinator_name'], $meetup['coordinator_contact'],
                $meetup['description'], $meetup['status'], $id
            );
        } else {
            $stmt = mysqli_prepare($conn, 'INSERT INTO meetups (title, meetup_date, location, is_virtual, partner_group, coordinator_name, coordinator_contact, description, status) VALUES (?,?,?,?,?,?,?,?,?)');
            mysqli_stmt_bind_param(
                $stmt, 'sssisssss',
                $meetup['title'], $meetup['meetup_date'], $meetup['location'], $meetup['is_virtual'],
                $meetup['partner_group'], $meetup['coordinator_name'], $meetup['coordinator_contact'],
                $meetup['description'], $meetup['status']
            );
        }
        mysqli_stmt_execute($stmt);
        header('Location: /admin/meetups/index.php?saved=1');
        exit;
    }
}

$pageTitle = ($id ? 'Edit' : 'Add') . ' Meetup — CAAP Admin';
$activeNav = 'meetups';
require __DIR__ . '/../../../caap_includes/admin-header.php';
?>

<div class="admin-topbar">
    <h1><?= $id ? 'Edit Meetup' : 'Add Meetup' ?></h1>
    <a href="/admin/meetups/index.php">&larr; Back to Meetups</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert--error">
        <?php foreach ($errors as $error): ?><p style="margin:0;"><?= htmlspecialchars($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="admin-form">
    <div class="field">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($meetup['title']) ?>">
    </div>
    <div class="field">
        <label for="meetup_date">Date</label>
        <input type="date" id="meetup_date" name="meetup_date" value="<?= htmlspecialchars($meetup['meetup_date']) ?>">
    </div>
    <div class="field">
        <label style="font-weight:400;"><input type="checkbox" name="is_virtual" value="1" <?= $meetup['is_virtual'] ? 'checked' : '' ?> style="width:auto; display:inline-block; margin-right:8px;"> This meetup is virtual</label>
    </div>
    <div class="field">
        <label for="location">Location</label>
        <input type="text" id="location" name="location" value="<?= htmlspecialchars($meetup['location']) ?>" placeholder="Leave blank if virtual">
    </div>
    <div class="field">
        <label for="partner_group">Partner Group</label>
        <input type="text" id="partner_group" name="partner_group" value="<?= htmlspecialchars($meetup['partner_group']) ?>">
    </div>
    <div class="field">
        <label for="coordinator_name">Coordinator Name</label>
        <input type="text" id="coordinator_name" name="coordinator_name" value="<?= htmlspecialchars($meetup['coordinator_name']) ?>">
    </div>
    <div class="field">
        <label for="coordinator_contact">Coordinator Contact</label>
        <input type="text" id="coordinator_contact" name="coordinator_contact" value="<?= htmlspecialchars($meetup['coordinator_contact']) ?>">
    </div>
    <div class="field">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4"><?= htmlspecialchars($meetup['description']) ?></textarea>
    </div>
    <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
            <?php foreach (['planned','confirmed','completed','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $meetup['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn--primary" style="border:none; cursor:pointer;">Save Meetup</button>
        <a href="/admin/meetups/index.php" class="btn">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/../../../caap_includes/admin-footer.php'; ?>
