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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?= sanitize(app_url('assets/css/style.css')) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script>
        // Apply saved dark mode before the page paints, preventing a bright flash.
        (function () {
            var savedTheme = localStorage.getItem('pn-theme');
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <!-- Shared header links use app_url so subfolder pages do not break navigation. -->
        <a class="brand" href="<?= sanitize(app_url('index.php')) ?>">
            <span class="brand-mark">
                <img src="<?= sanitize(app_url('assets/images/logo/logo.png')) ?>" alt="Precious Nkomose logo">
            </span>
            <span class="brand-copy">
                <span class="brand-title">Precious Nkomose</span>
                <span class="brand-subtitle">Lounge / Lifestyle Experience</span>
            </span>
        </a>
        <nav class="site-nav">
            <a href="<?= sanitize(app_url('index.php')) ?>">Home</a>
            <a href="<?= sanitize(app_url('menu.php')) ?>">Menu</a>
            <a href="<?= sanitize(app_url('reservation.php')) ?>">Reserve</a>
            <a href="<?= sanitize(app_url('catering.php')) ?>">Catering</a>
            <a href="<?= sanitize(app_url('reviews.php')) ?>">Reviews</a>
            <a href="<?= sanitize(app_url('contact.php')) ?>">Contact</a>
            <?php if (is_logged_in()): ?>
                <a href="<?= sanitize(app_url('dashboard.php')) ?>">Dashboard</a>
                <?php if (!is_admin()): ?><a href="<?= sanitize(app_url('customer/profile.php')) ?>">Profile</a><?php endif; ?>
                <a href="<?= sanitize(app_url('logout.php')) ?>">Logout</a>
            <?php else: ?>
                <a href="<?= sanitize(app_url('login.php')) ?>">Login</a>
                <a href="<?= sanitize(app_url('register.php')) ?>">Register</a>
            <?php endif; ?>
            <button class="theme-toggle" type="button" aria-label="Switch to dark mode" aria-pressed="false" data-theme-toggle>
                <i class="fa-solid fa-moon"></i>
                <span>Dark</span>
            </button>
        </nav>
    </div>
</header>
<main class="site-main container">
    
