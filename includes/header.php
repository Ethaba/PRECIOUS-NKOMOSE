<?php
require_once __DIR__ . '/functions.php';
$site = site_config();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= sanitize($pageTitle ?? ($site['restaurant']['name'] ?? 'Precious Nkomose')) ?></title>
    <!-- app_url keeps CSS loading from root, admin, and customer pages. -->
    <link rel="stylesheet" href="<?= sanitize(app_url('assets/css/style.css')) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <!-- Shared header links use app_url so subfolder pages do not break navigation. -->
        <a class="brand" href="<?= sanitize(app_url('index.php')) ?>"><?= sanitize($site['restaurant']['name'] ?? 'Precious Nkomose') ?></a>
        <nav class="site-nav">
            <a href="<?= sanitize(app_url('index.php')) ?>">Home</a>
            <a href="<?= sanitize(app_url('menu.php')) ?>">Menu</a>
            <a href="<?= sanitize(app_url('reservation.php')) ?>">Reserve</a>
            <a href="<?= sanitize(app_url('catering.php')) ?>">Catering</a>
            <a href="<?= sanitize(app_url('reviews.php')) ?>">Reviews</a>
            <a href="<?= sanitize(app_url('contact.php')) ?>">Contact</a>
            <?php if (is_logged_in()): ?>
                <a href="<?= sanitize(app_url('dashboard.php')) ?>">Dashboard</a>
                <?php if (is_admin()): ?><a href="<?= sanitize(app_url('admin.php')) ?>">Admin</a><?php endif; ?>
                <a href="<?= sanitize(app_url('logout.php')) ?>">Logout</a>
            <?php else: ?>
                <a href="<?= sanitize(app_url('login.php')) ?>">Login</a>
                <a href="<?= sanitize(app_url('register.php')) ?>">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="site-main container">
    
