<?php
/**
 * Shared header: opens <html>, sets up <head>, renders the top nav.
 * Set $pageTitle before requiring this file; falls back to a default.
 */
$pageTitle = $pageTitle ?? 'CAAP- Centre for Awareness, Advocacy and Protection';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <nav class="nav">
        <a href="/index.php" class="nav__logo">CAAP</a>
        <ul class="nav__links">
            <li><a href="/index.php">Home</a></li>
            <li><a href="/about.php">About</a></li>
            <li><a href="/programmes.php">What We Do</a></li>
            <li><a href="/blog.php">Blog</a></li>
            <li><a href="/contact.php">Contact</a></li>
        </ul>
        <a href="/contact.php" class="nav__cta">Get Involved</a>
    </nav>
</header>
<main>
