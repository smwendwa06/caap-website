<?php
require_once __DIR__ . '/../../../caap_includes/config.php';
require_once __DIR__ . '/../../../caap_includes/auth.php';
require_login();

$posts = [];
$result = mysqli_query($conn, "SELECT id, title, category, status, published_at FROM posts ORDER BY created_at DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
}

$pillClass = [
    'draft'     => 'pill',
    'published' => 'pill--good',
];

$pageTitle = 'Posts — CAAP Admin';
$activeNav = 'posts';
require __DIR__ . '/../../../caap_includes/admin-header.php';
?>

<div class="admin-topbar">
    <h1>Blog &amp; Impact Stories</h1>
    <a href="/admin/posts/form.php" class="btn btn--primary">+ Add Post</a>
</div>

<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert--success">Post saved.</div>
<?php elseif (isset($_GET['deleted'])): ?>
    <div class="alert alert--success">Post deleted.</div>
<?php endif; ?>

<?php if (empty($posts)): ?>
    <div class="empty-state">No posts yet. Add the first one — it'll show up on the public blog once published.</div>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Published</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $post): ?>
                <tr>
                    <td><?= htmlspecialchars($post['title']) ?></td>
                    <td><?= $post['category'] === 'impact_story' ? 'Impact Story' : 'Blog' ?></td>
                    <td><span class="pill <?= $pillClass[$post['status']] ?? 'pill' ?>"><?= htmlspecialchars($post['status']) ?></span></td>
                    <td><?= $post['published_at'] ? date('M j, Y', strtotime($post['published_at'])) : '—' ?></td>
                    <td class="actions">
                        <a href="/admin/posts/form.php?id=<?= (int) $post['id'] ?>">Edit</a>
                        <a href="/admin/posts/delete.php?id=<?= (int) $post['id'] ?>" onclick="return confirm('Delete this post?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../../../caap_includes/admin-footer.php'; ?>
