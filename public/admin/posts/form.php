<?php
require_once __DIR__ . '/../../../caap_includes/config.php';
require_once __DIR__ . '/../../../caap_includes/auth.php';
require_once __DIR__ . '/../../../caap_includes/helpers.php';
require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$post = [
    'title' => '', 'slug' => '', 'category' => 'blog', 'body' => '',
    'cover_image' => '', 'status' => 'draft', 'published_at' => '',
];
$errors = [];

if ($id) {
    $stmt = mysqli_prepare($conn, 'SELECT * FROM posts WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $found = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($found) {
        $post = $found;
    } else {
        $id = null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post['title']       = trim($_POST['title'] ?? '');
    $post['slug']        = trim($_POST['slug'] ?? '') ?: slugify($post['title']);
    $post['category']    = $_POST['category'] ?? 'blog';
    $post['body']        = trim($_POST['body'] ?? '');
    $post['cover_image'] = trim($_POST['cover_image'] ?? '');
    $post['status']      = $_POST['status'] ?? 'draft';

    if ($post['status'] === 'published' && empty($_POST['published_at'])) {
        $post['published_at'] = date('Y-m-d H:i:s');
    } else {
        $post['published_at'] = $_POST['published_at'] ?: null;
    }

    if ($post['title'] === '') { $errors[] = 'Title is required.'; }
    if ($post['body'] === '')  { $errors[] = 'Body is required.'; }

    if (empty($errors)) {
        $checkStmt = mysqli_prepare($conn, 'SELECT id FROM posts WHERE slug = ? AND id != ?');
        $checkId = $id ?? 0;
        mysqli_stmt_bind_param($checkStmt, 'si', $post['slug'], $checkId);
        mysqli_stmt_execute($checkStmt);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt))) {
            $errors[] = 'That slug is already in use — try a different title or edit the slug directly.';
        }
    }

    if (empty($errors)) {
        $authorId = $_SESSION['user_id'];
        if ($id) {
            $stmt = mysqli_prepare($conn, 'UPDATE posts SET title=?, slug=?, category=?, body=?, cover_image=?, status=?, published_at=? WHERE id=?');
            mysqli_stmt_bind_param(
                $stmt, 'sssssssi',
                $post['title'], $post['slug'], $post['category'], $post['body'],
                $post['cover_image'], $post['status'], $post['published_at'], $id
            );
        } else {
            $stmt = mysqli_prepare($conn, 'INSERT INTO posts (title, slug, category, body, cover_image, author_id, status, published_at) VALUES (?,?,?,?,?,?,?,?)');
            mysqli_stmt_bind_param(
                $stmt, 'sssssiss',
                $post['title'], $post['slug'], $post['category'], $post['body'],
                $post['cover_image'], $authorId, $post['status'], $post['published_at']
            );
        }
        mysqli_stmt_execute($stmt);
        header('Location: /admin/posts/index.php?saved=1');
        exit;
    }
}

$pageTitle = ($id ? 'Edit' : 'Add') . ' Post — CAAP Admin';
$activeNav = 'posts';
require __DIR__ . '/../../../caap_includes/admin-header.php';
?>

<div class="admin-topbar">
    <h1><?= $id ? 'Edit Post' : 'Add Post' ?></h1>
    <a href="/admin/posts/index.php">&larr; Back to Posts</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert--error">
        <?php foreach ($errors as $error): ?><p style="margin:0;"><?= htmlspecialchars($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="admin-form">
    <div class="field">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($post['title']) ?>">
    </div>
    <div class="field">
        <label for="slug">Slug</label>
        <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($post['slug']) ?>" placeholder="auto-generated from title if left blank">
    </div>
    <div class="field">
        <label for="category">Category</label>
        <select id="category" name="category">
            <option value="blog" <?= $post['category'] === 'blog' ? 'selected' : '' ?>>Blog</option>
            <option value="impact_story" <?= $post['category'] === 'impact_story' ? 'selected' : '' ?>>Impact Story</option>
        </select>
    </div>
    <div class="field">
        <label for="body">Body</label>
        <textarea id="body" name="body" rows="10"><?= htmlspecialchars($post['body']) ?></textarea>
    </div>
    <div class="field">
        <label for="cover_image">Cover Image URL</label>
        <input type="text" id="cover_image" name="cover_image" value="<?= htmlspecialchars($post['cover_image']) ?>">
    </div>
    <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
        </select>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn--primary" style="border:none; cursor:pointer;">Save Post</button>
        <a href="/admin/posts/index.php" class="btn">Cancel</a>
    </div>
</form>

<script>
(function () {
    var titleField = document.getElementById('title');
    var slugField = document.getElementById('slug');
    var slugTouched = slugField.value !== '';

    slugField.addEventListener('input', function () { slugTouched = true; });

    titleField.addEventListener('input', function () {
        if (slugTouched) return;
        slugField.value = titleField.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    });
})();
</script>

<?php require __DIR__ . '/../../../caap_includes/admin-footer.php'; ?>
