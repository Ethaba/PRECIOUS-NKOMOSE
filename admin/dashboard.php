<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
// Dashboard counts now come from MySQL tables.
$reservations = get_reservations();
$catering = get_catering_requests();
$reviews = get_reviews();
$menu = load_menu_items();
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Admin Dashboard</h2>
        <p>Overview of site activity and quick management links.</p>
    </div>
    <div class="card-grid">
        <div class="feature-card">
            <h3>Reservations</h3>
            <p><?= count($reservations) ?> total reservations</p>
            <a class="button button-ghost" href="reservations.php">View</a>
        </div>
        <div class="feature-card">
            <h3>Catering</h3>
            <p><?= count($catering) ?> catering requests</p>
            <a class="button button-ghost" href="catering.php">View</a>
        </div>
        <div class="feature-card">
            <h3>Reviews</h3>
            <p><?= count($reviews) ?> reviews submitted</p>
            <a class="button button-ghost" href="reviews.php">View</a>
        </div>
        <div class="feature-card">
            <h3>Menu</h3>
            <p><?= count($menu) ?> menu items</p>
            <a class="button button-ghost" href="menu.php">View</a>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
