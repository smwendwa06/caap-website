<?php
require_once __DIR__ . '/../caap_includes/config.php';
require_once __DIR__ . '/../caap_includes/helpers.php';

$pageTitle = 'What We Do — CAAP';

$meetups = [];
$result = mysqli_query(
    $conn,
    "SELECT title, meetup_date, location, is_virtual, description
     FROM meetups
     WHERE status IN ('planned', 'confirmed') AND meetup_date >= CURDATE()
     ORDER BY meetup_date ASC
     LIMIT 3"
);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $meetups[] = $row;
    }
}

require __DIR__ . '/../caap_includes/header.php';
?>

<section class="hero" style="padding-bottom: 40px;">
    <div class="container">
        <h1>Three pillars, one goal: communities where gender-based violence has nowhere to hide.</h1>
        <p>Everything CAAP runs from a monthly meetup to a funding proposal sits under one of three pillars.</p>
    </div>
</section>

<section class="pillars" style="padding-top: 0;">
    <div class="container">
        <div class="pillars__grid">
            <div class="pillar pillar--awareness">
                <h3>Awareness</h3>
                <p>Monthly community meetups, coordinated with local groups, that open honest conversations about gender-based violence, its warning signs, and where to turn for help.</p>
            </div>
            <div class="pillar pillar--advocacy">
                <h3>Advocacy</h3>
                <p>Concept notes and proposals that make the case — to funders and policymakers — for stronger protections and sustained resourcing.</p>
            </div>
            <div class="pillar pillar--protection">
                <h3>Protection</h3>
                <p>A growing network of partner organizations we connect survivors and at-risk community members to for direct support.</p>
            </div>
        </div>
    </div>
</section>

<section class="posts">
    <div class="container">
        <h2>Upcoming meetups</h2>
        <?php if (empty($meetups)): ?>
            <p class="posts__empty">No meetups scheduled right now — check back soon, or get in touch if your group would like to host one.</p>
        <?php else: ?>
            <div class="posts__list">
                <?php foreach ($meetups as $meetup): ?>
                    <article class="post-row">
                        <time><?= date('M j, Y', strtotime($meetup['meetup_date'])) ?></time>
                        <div>
                            <h3><?= htmlspecialchars($meetup['title']) ?></h3>
                            <p>
                                <?= $meetup['is_virtual'] ? 'Virtual' : htmlspecialchars($meetup['location'] ?? 'Location to be confirmed') ?>
                                <?php if (!empty($meetup['description'])): ?>
                                    — <?= htmlspecialchars(excerpt($meetup['description'], 120)) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="strip">
    <div class="container">
        <h2>Want your group to host a meetup?</h2>
        <p>We're always looking for community groups and partners to co-host the next awareness session.</p>
        <a href="/contact.php" class="btn btn--primary" style="border-color: var(--maroon-deep); background: var(--maroon-deep); color: var(--cream-on-maroon);">Get in Touch</a>
    </div>
</section>

<?php require __DIR__ . '/../caap_includes/footer.php'; ?>
