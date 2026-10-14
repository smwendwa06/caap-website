<?php
require_once __DIR__ . '/../../../caap_includes/config.php';
require_once __DIR__ . '/../../../caap_includes/auth.php';
require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$donor = [
    'name' => '', 'type' => 'individual', 'contact_person' => '', 'email' => '',
    'phone' => '', 'country' => '', 'relationship_status' => 'prospect', 'notes' => '',
];
$errors = [];

if ($id) {
    $stmt = mysqli_prepare($conn, 'SELECT * FROM donors WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $found = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($found) {
        $donor = $found;
    } else {
        $id = null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donor['name']                = trim($_POST['name'] ?? '');
    $donor['type']                = $_POST['type'] ?? 'individual';
    $donor['contact_person']      = trim($_POST['contact_person'] ?? '');
    $donor['email']               = trim($_POST['email'] ?? '');
    $donor['phone']               = trim($_POST['phone'] ?? '');
    $donor['country']             = trim($_POST['country'] ?? '');
    $donor['relationship_status'] = $_POST['relationship_status'] ?? 'prospect';
    $donor['notes']               = trim($_POST['notes'] ?? '');

    if ($donor['name'] === '') { $errors[] = 'Name is required.'; }
    if ($donor['email'] !== '' && !filter_var($donor['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address, or leave it blank.';
    }

    if (empty($errors)) {
        if ($id) {
            $stmt = mysqli_prepare($conn, 'UPDATE donors SET name=?, type=?, contact_person=?, email=?, phone=?, country=?, relationship_status=?, notes=? WHERE id=?');
            mysqli_stmt_bind_param(
                $stmt, 'ssssssssi',
                $donor['name'], $donor['type'], $donor['contact_person'], $donor['email'],
                $donor['phone'], $donor['country'], $donor['relationship_status'], $donor['notes'], $id
            );
        } else {
            $stmt = mysqli_prepare($conn, 'INSERT INTO donors (name, type, contact_person, email, phone, country, relationship_status, notes) VALUES (?,?,?,?,?,?,?,?)');
            mysqli_stmt_bind_param(
                $stmt, 'ssssssss',
                $donor['name'], $donor['type'], $donor['contact_person'], $donor['email'],
                $donor['phone'], $donor['country'], $donor['relationship_status'], $donor['notes']
            );
        }
        mysqli_stmt_execute($stmt);
        header('Location: /admin/donors/index.php?saved=1');
        exit;
    }
}

$pageTitle = ($id ? 'Edit' : 'Add') . ' Donor — CAAP Admin';
$activeNav = 'donors';
require __DIR__ . '/../../../caap_includes/admin-header.php';
?>

<div class="admin-topbar">
    <h1><?= $id ? 'Edit Donor' : 'Add Donor' ?></h1>
    <a href="/admin/donors/index.php">&larr; Back to Donors</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert--error">
        <?php foreach ($errors as $error): ?><p style="margin:0;"><?= htmlspecialchars($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="admin-form">
    <div class="field">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($donor['name']) ?>">
    </div>
    <div class="field">
        <label for="type">Type</label>
        <select id="type" name="type">
            <?php foreach (['individual','foundation','corporate','government','partner_org'] as $t): ?>
                <option value="<?= $t ?>" <?= $donor['type'] === $t ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$t)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="contact_person">Contact Person</label>
        <input type="text" id="contact_person" name="contact_person" value="<?= htmlspecialchars($donor['contact_person']) ?>">
    </div>
    <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($donor['email']) ?>">
    </div>
    <div class="field">
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($donor['phone']) ?>">
    </div>
    <div class="field">
        <label for="country">Country</label>
        <input type="text" id="country" name="country" value="<?= htmlspecialchars($donor['country']) ?>">
    </div>
    <div class="field">
        <label for="relationship_status">Relationship Status</label>
        <select id="relationship_status" name="relationship_status">
            <?php foreach (['prospect','engaged','active','lapsed'] as $s): ?>
                <option value="<?= $s ?>" <?= $donor['relationship_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="4"><?= htmlspecialchars($donor['notes']) ?></textarea>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn--primary" style="border:none; cursor:pointer;">Save Donor</button>
        <a href="/admin/donors/index.php" class="btn">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/../../../caap_includes/admin-footer.php'; ?>
