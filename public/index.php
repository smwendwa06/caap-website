<?php
require_once __DIR__ . '/../caap_includes/config.php';

$pageTitle = 'CAAP — Centre for Awareness, Advocacy and Protection';

$posts = [];
$result = mysqli_query(
    $conn,
    "SELECT title, slug, body, published_at FROM posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 3"
);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
}

require_once __DIR__ . '/../caap_includes/helpers.php';

// Live count for the hero stat — meetups that have actually happened, not ones just planned.
$meetupCountResult = mysqli_query($conn, "SELECT COUNT(*) FROM meetups WHERE status = 'completed'");
$meetupCount = $meetupCountResult ? (int) mysqli_fetch_row($meetupCountResult)[0] : 0;

require __DIR__ . '/../caap_includes/header.php';
?>

<section class="hero">
    <div class="container hero__grid">
        <div>
            <h1>Ending gender-based violence starts with awareness and stays alive through action.</h1>
            <p>CAAP partners with communities across Kenya to raise awareness on gender-based violence, advocate for stronger protections, and support the grassroots groups doing this work every day.</p>
            <div class="hero__actions">
                <a href="/contact.php" class="btn btn--primary">Get Involved</a>
                <a href="/programmes.php" class="btn">What We Do</a>
            </div>
        </div>
        <div class="hero__stat">
            <span class="num" data-count-to="<?= $meetupCount ?>"><?= $meetupCount ?></span>
            <p>Community awareness meetups coordinated with local groups so far.</p>
        </div>
    </div>
</section>

<section class="band">
    <div class="container">
        <h2>Who we are</h2>
        <p>CAAP is a community-driven organization working to end gender-based violence in Kenya; One conversation, one partnership, one policy change at a time.</p>
        <a href="/about.php">Learn about our work</a>
    </div>
</section>

<section class="pillars">
    <div class="container">
        <h2>What we do</h2>
        <div class="pillars__grid">
            <div class="pillar pillar--awareness">
                <h3>Awareness</h3>
                <p>Monthly community meetups and campaigns that open honest conversations about gender-based violence and where to turn for help.</p>
            </div>
            <div class="pillar pillar--advocacy">
                <h3>Advocacy</h3>
                <p>Working with partners and policymakers to push for stronger protections and accountability for survivors.</p>
            </div>
            <div class="pillar pillar--protection">
                <h3>Protection</h3>
                <p>Connecting survivors and at-risk community members with organizations equipped to support and safeguard them.</p>
            </div>
        </div>
    </div>
</section>

<section class="posts">
    <div class="container">
        <h2>Latest from CAAP</h2>
        <?php if (empty($posts)): ?>
            <p class="posts__empty">Nothing published yet — blog posts and impact stories will appear here once the team starts posting.</p>
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

<section class="strip">
    <div class="container">
        <h2>Want to support this work?</h2>
        <p>Reach out to partner with us, volunteer at an upcoming meetup, or ask how to get involved.</p>
        <a href="/contact.php" class="btn btn--primary" style="border-color: var(--maroon-deep); background: var(--maroon-deep); color: var(--cream-on-maroon);">Contact CAAP</a>
    </div>
</section>

<script>
// Counts the hero stat up from 0 when it scrolls into view.
(function () {
    var counters = document.querySelectorAll('.num[data-count-to]');
    if (!counters.length) return;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function animate(el) {
        var target = parseInt(el.getAttribute('data-count-to'), 10) || 0;
        var suffix = el.getAttribute('data-suffix') || '';
        if (reduce || target === 0) { el.textContent = target + suffix; return; }
        var duration = 1400, start = null;
        function tick(now) {
            if (start === null) start = now;
            var p = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - p, 3); // ease-out
            el.textContent = Math.round(eased * target) + suffix;
            if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    }

    if (!('IntersectionObserver' in window)) {
        counters.forEach(function (el) { animate(el); });
        return;
    }
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) { animate(entry.target); observer.unobserve(entry.target); }
        });
    }, { threshold: 0.4 });
    counters.forEach(function (el) {
        el.textContent = '0' + (el.getAttribute('data-suffix') || '');
        observer.observe(el);
    });
})();
</script>

<?php require __DIR__ . '/../caap_includes/footer.php'; ?>
