<?php
require_once __DIR__ . '/../caap_includes/config.php';
require_once __DIR__ . '/../caap_includes/helpers.php';

$pageTitle = 'Blog & Impact Stories — CAAP';

$posts = [];
$result = mysqli_query(
    $conn,
    "SELECT title, slug, body, published_at FROM posts WHERE status = 'published' ORDER BY published_at DESC"
);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
}

require __DIR__ . '/../caap_includes/header.php';
?>

<section class="hero" style="padding-bottom: 40px;">
    <div class="container">
        <h1>Blog &amp; Impact Stories</h1>
        <p>Updates from meetups, notes from the field, and the stories behind the numbers in our funding proposals.</p>
    </div>
</section>

<section class="posts" style="padding-top: 0;">
    <div class="container">
        <?php if (empty($posts)): ?>
            <p class="posts__empty">Nothing published yet — check back soon.</p>
        <?php else: ?>
            <div class="posts__list">
                <?php foreach ($posts as $post): ?>
                    <article class="post-row">
                        <time datetime="<?= htmlspecialchars($post['published_at']) ?>">
                            <?= date('M j, Y', strtotime($post['published_at'])) ?>
                        </time>
                        <div>
                            <h3><a href="/post.php?slug=<?= urlencode($post['slug']) ?>"><?= htmlspecialchars($post['title']) ?></a></h3>
                            <p><?= htmlspecialchars(excerpt($post['body'])) ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../caap_includes/footer.php'; ?>
