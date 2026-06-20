<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$reservationStats = get_reservation_stats();
$reviewStats = get_review_stats();
$customers = get_customers();
$reservations = get_reservations();
$reviews = get_reviews();
$tables = db_all('SELECT * FROM restaurant_tables ORDER BY label');

$pageTitle = 'Reports';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Reports</h2>
        <p>Generate and review key business reports for reservations, customers, reviews, and table usage.</p>
    </div>

    <div class="analytics-grid">
        <div class="card analytics-card">
            <h3>Reservation report</h3>
            <table class="table">
                <thead><tr><th>Status</th><th>Count</th></tr></thead>
                <tbody>
                    <tr><td>Confirmed</td><td><?= (int)($reservationStats['CONFIRMED'] ?? 0) ?></td></tr>
                    <tr><td>Pending</td><td><?= (int)($reservationStats['PENDING'] ?? 0) ?></td></tr>
                    <tr><td>Cancelled</td><td><?= (int)($reservationStats['CANCELLED'] ?? 0) ?></td></tr>
                </tbody>
            </table>
        </div>
        <div class="card analytics-card">
            <h3>Customer report</h3>
            <table class="table">
                <thead><tr><th>Customer</th><th>Email</th><th>Phone</th></tr></thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?= sanitize($customer['name'] ?? '') ?></td>
                            <td><?= sanitize($customer['email'] ?? '') ?></td>
                            <td><?= sanitize($customer['phone'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="analytics-grid" style="margin-top:24px;">
        <div class="card analytics-card">
            <h3>Review report</h3>
            <table class="table">
                <thead><tr><th>Rating</th><th>Count</th></tr></thead>
                <tbody>
                    <?php foreach (($reviewStats['breakdown'] ?? [1=>0,2=>0,3=>0,4=>0,5=>0]) as $rating => $count): ?>
                        <tr><td><?= (int)$rating ?>★</td><td><?= (int)$count ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card analytics-card">
            <h3>Table utilization report</h3>
            <table class="table">
                <thead><tr><th>Table</th><th>Capacity</th><th>Reservations</th></tr></thead>
                <tbody>
                    <?php foreach ($tables as $table): ?>
                        <tr>
                            <td><?= sanitize($table['label'] ?? '') ?></td>
                            <td><?= (int)$table['capacity'] ?></td>
                            <td><?= (int)db_one('SELECT COUNT(*) AS total FROM reservations WHERE preferred_section = ? AND status <> "Cancelled"', [$table['id']])['total'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
