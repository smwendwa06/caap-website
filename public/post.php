<?php
require_once __DIR__ . '/../caap_includes/config.php';
require_once __DIR__ . '/../caap_includes/helpers.php';

$slug = $_GET['slug'] ?? '';

$post = null;
if ($slug !== '') {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT title, body, published_at FROM posts WHERE slug = ? AND status = 'published' LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 's', $slug);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $post = mysqli_fetch_assoc($result);
}

$pageTitle = $post ? $post['title'] . ' — CAAP' : 'Post Not Found — CAAP';

require __DIR__ . '/../caap_includes/header.php';
?>

<?php if (!$post): ?>
    <section class="hero" style="padding-bottom: 40px;">
        <div class="container">
            <h1>We couldn't find that post</h1>
            <p>It may have been moved or unpublished. <a href="/blog.php">Back to the blog</a>.</p>
        </div>
    </section>
<?php else: ?>
    <section class="hero" style="padding-bottom: 24px;">
        <div class="container">
            <p style="margin-bottom: 8px;">
                <time datetime="<?= htmlspecialchars($post['published_at']) ?>">
                    <?= date('F j, Y', strtotime($post['published_at'])) ?>
                </time>
            </p>
            <h1><?= htmlspecialchars($post['title']) ?></h1>
        </div>
    </section>
    <section class="posts" style="padding-top: 0;">
        <div class="container" style="max-width: 760px;">
            <div style="font-size: 1.05rem; line-height: 1.75;">
                <?= nl2br(htmlspecialchars($post['body'])) ?>
            </div>
            <p style="margin-top: 40px;"><a href="/blog.php">&larr; Back to all posts</a></p>
        </div>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/../caap_includes/footer.php'; ?>
