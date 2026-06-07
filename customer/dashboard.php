<?php
// Customer pages sit one folder down, so includes must point back to /includes.
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();
// Load this customer's bookings from MySQL for the dashboard summary.
$bookings = get_customer_reservations($user['id']);
$pageTitle = 'Dashboard';
// Load the shared header from the parent includes folder.
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Welcome back, <?= sanitize($user['name']) ?></h2>
        <p>Your account is ready. Manage reservations, book catering, and review your history.</p>
    </div>
    <div class="dashboard-grid">
        <div class="dashboard-tile">
            <h3>Reservations</h3>
            <p>View your current bookings and upcoming seating plans.</p>
            <!-- app_url prevents customer subfolder links from resolving incorrectly. -->
            <a class="button button-ghost" href="<?= sanitize(app_url('customer/my-bookings.php')) ?>">My Bookings</a>
        </div>
        <div class="dashboard-tile">
            <h3>Reserve a table</h3>
            <p>Choose your preferred section and seating layout in our interactive map.</p>
            <a class="button button-ghost" href="<?= sanitize(app_url('reservation.php')) ?>">Reserve Now</a>
        </div>
        <div class="dashboard-tile">
            <h3>Catering quotes</h3>
            <p>Request a catering proposal for weddings, formal gatherings and events.</p>
            <a class="button button-ghost" href="<?= sanitize(app_url('catering.php')) ?>">Request Catering</a>
        </div>
        <div class="dashboard-tile">
            <h3>Profile & bookings</h3>
            <p>Edit your account details, view reservations, and cancel upcoming bookings.</p>
            <a class="button button-ghost" href="<?= sanitize(app_url('customer/profile.php')) ?>">Open Profile</a>
        </div>
    </div>
</section>
<section class="card" style="margin-top:24px;">
    <div class="section-title">
        <h2>Your latest bookings</h2>
        <p>Keep track of the reservations you have made through the platform.</p>
    </div>
    <?php if (empty($bookings)): ?>
        <div class="alert"><strong>No bookings yet.</strong> Reserve a table to see your details here.</div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>Section</th><th>Guests</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= sanitize($booking['section'] ?? $booking['preferred_section'] ?? 'Unknown') ?></td>
                        <td><?= sanitize($booking['guests']) ?></td>
                        <td><?= sanitize($booking['date']) ?></td>
                        <td><?= sanitize($booking['time']) ?></td>
                        <td><span class="badge <?= $booking['status'] === 'Confirmed' ? 'badge-success' : 'badge-warning' ?>"><?= sanitize($booking['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php

require_once __DIR__ . '/../includes/footer.php';

