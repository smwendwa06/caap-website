<?php
require_once __DIR__ . '/../caap_includes/config.php';
require_once __DIR__ . '/../caap_includes/helpers.php';
require_once __DIR__ . '/../caap_includes/notify.php';

$pageTitle = 'Contact — CAAP';
$errors = [];
$old = ['name' => '', 'email' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']    = trim($_POST['name'] ?? '');
    $old['email']   = trim($_POST['email'] ?? '');
    $old['message'] = trim($_POST['message'] ?? '');
    $honeypot       = trim($_POST['website'] ?? ''); // hidden field — real visitors leave it blank

    if ($honeypot !== '') {
        // Looks like a bot filled the whole form. Pretend it worked, skip the insert.
        header('Location: /contact.php?sent=1');
        exit;
    }

    if ($old['name'] === '') {
        $errors[] = 'Please enter your name.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($old['message'] === '') {
        $errors[] = 'Please enter a message.';
    }
    if (mb_strlen($old['name']) > 120)     { $errors[] = 'Please keep your name under 120 characters.'; }
    if (mb_strlen($old['email']) > 150)    { $errors[] = 'That email address is too long.'; }
    if (mb_strlen($old['message']) > 5000) { $errors[] = 'Please keep your message under 5,000 characters.'; }

    if (empty($errors)) {
        $stmt = mysqli_prepare(
            $conn,
            'INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)'
        );
        mysqli_stmt_bind_param($stmt, 'sss', $old['name'], $old['email'], $old['message']);
        mysqli_stmt_execute($stmt);
        notify_new_message($conn, (int) mysqli_insert_id($conn), $old['name'], $old['email'], $old['message']);

        header('Location: /contact.php?sent=1');
        exit;
    }
}

require __DIR__ . '/../caap_includes/header.php';
?>

<section class="hero" style="padding-bottom: 40px;">
    <div class="container">
        <h1>Get in touch</h1>
        <p>Partnering with us, hosting a meetup, or asking how to support the work. Whatever it is, this reaches the team directly.</p>
    </div>
</section>

<section class="posts" style="padding-top: 0;">
    <div class="container" style="max-width: 640px;">

        <?php if (isset($_GET['sent']) && $_GET['sent'] == '1'): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 16px 20px; border-radius: 4px; margin-bottom: 24px; border: 1px solid #c3e6cb;">
                <strong>Success!</strong> Your message has been sent. We'll get back to you soon.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div style="border: 1px solid var(--stone); padding: 16px 20px; margin-bottom: 24px;">
                <?php foreach ($errors as $error): ?>
                    <p style="margin: 0; color: var(--stone);"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div style="margin-bottom: 20px;">
                <label for="name" style="display:block; margin-bottom:6px; font-weight:600;">Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name']) ?>"
                       style="width:100%; padding:10px; border:1px solid var(--cream-line); font: inherit;">
            </div>
            <div style="margin-bottom: 20px;">
                <label for="email" style="display:block; margin-bottom:6px; font-weight:600;">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email']) ?>"
                       style="width:100%; padding:10px; border:1px solid var(--cream-line); font: inherit;">
            </div>
            <div style="margin-bottom: 20px;">
                <label for="message" style="display:block; margin-bottom:6px; font-weight:600;">Message</label>
                <textarea id="message" name="message" rows="6"
                          style="width:100%; padding:10px; border:1px solid var(--cream-line); font: inherit;"><?= htmlspecialchars($old['message']) ?></textarea>
            </div>

            <!-- Honeypot: hidden from real visitors, bots tend to fill every field they find -->
            <div style="position:absolute; left:-9999px;" aria-hidden="true">
                <label for="website">Leave this field blank</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <button type="submit" class="btn btn--primary" style="border:none; cursor:pointer;">Send Message</button>
        </form>

    </div>
</section>

<?php require __DIR__ . '/../caap_includes/footer.php'; ?>
