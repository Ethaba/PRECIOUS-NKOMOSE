<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// PERFORMANCE FIX: Use optimized SQL functions instead of loading all data into PHP
$reservationStats = get_reservation_stats();
$confirmedReservations = $reservationStats['CONFIRMED'];
$pendingReservations = $reservationStats['PENDING'];
$cancelledReservations = $reservationStats['CANCELLED'];

$reviewStats = get_review_stats();
$reviewCounts = $reviewStats['breakdown'] ?? [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$averageRating = round($reviewStats['average_rating'], 1);

// Get only recent items (not all items)
$catering = db_all('SELECT * FROM catering_requests ORDER BY created_at DESC LIMIT 10');
$customers = db_all('SELECT id,name,email,phone,is_admin,created_at FROM customers ORDER BY created_at DESC LIMIT 10');

$reservationSegments = [
    ['label' => 'Confirmed', 'value' => $confirmedReservations, 'color' => '#22c55e'],
    ['label' => 'Pending', 'value' => $pendingReservations, 'color' => '#f59e0b'],
    ['label' => 'Cancelled', 'value' => $cancelledReservations, 'color' => '#f43f5e'],
];
$reviewSegments = [
    ['label' => '5★', 'value' => $reviewCounts[5], 'color' => '#22c55e'],
    ['label' => '4★', 'value' => $reviewCounts[4], 'color' => '#84cc16'],
    ['label' => '3★', 'value' => $reviewCounts[3], 'color' => '#f59e0b'],
    ['label' => '2★', 'value' => $reviewCounts[2], 'color' => '#f97316'],
    ['label' => '1★', 'value' => $reviewCounts[1], 'color' => '#f43f5e'],
];

$buildPieGradient = function (array $segments): string {
    $total = array_sum(array_column($segments, 'value'));
    if ($total === 0) {
        return 'conic-gradient(#e5e7eb 0 100%)';
    }

    $start = 0;
    $parts = [];
    foreach ($segments as $segment) {
        if ($segment['value'] <= 0) {
            continue;
        }
        $end = $start + ($segment['value'] / $total) * 100;
        $parts[] = sprintf('%s %0.2f%% %0.2f%%', $segment['color'], $start, $end);
        $start = $end;
    }

    return 'conic-gradient(' . implode(', ', $parts) . ')';
};

$formatPercent = function (int $count, int $total): string {
    if ($total === 0) {
        return '0%';
    }

    return number_format(($count / $total) * 100, 0) . '%';
};

$recentReservations = get_recent_reservations(5);
$recentReviews = get_recent_reviews(5);

// Get total counts for dashboard metrics
$totalReservations = (int)db_one("SELECT COUNT(*) as count FROM reservations")['count'];
$totalReviews = (int)db_one("SELECT COUNT(*) as count FROM reviews")['count'];
$totalCustomers = count($customers);
$totalCatering = count($catering);
$totalMenuItems = (int)db_one("SELECT COUNT(*) as count FROM menu WHERE available = 1")['count'];

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Admin Dashboard</h2>
        <p>Live operational insights for reservations, reviews, and high-value guest activity.</p>
    </div>

    <div class="dashboard-metric-grid">
        <div class="dashboard-metric-card">
            <span class="dashboard-metric-label">Reservations</span>
            <strong class="dashboard-metric-value"><?= $totalReservations ?></strong>
            <p><?= $confirmedReservations ?> confirmed · <?= $pendingReservations ?> pending</p>
        </div>
        <div class="dashboard-metric-card">
            <span class="dashboard-metric-label">Reviews</span>
            <strong class="dashboard-metric-value"><?= $totalReviews ?></strong>
            <p>Average rating <?= $averageRating ?>/5</p>
        </div>
        <div class="dashboard-metric-card">
            <span class="dashboard-metric-label">Customers</span>
            <strong class="dashboard-metric-value"><?= $totalCustomers ?></strong>
            <p>Registered customer accounts</p>
        </div>
        <div class="dashboard-metric-card">
            <span class="dashboard-metric-label">Catering</span>
            <strong class="dashboard-metric-value"><?= count($catering) ?></strong>
            <p>Open and closed quote requests</p>
        </div>
        <div class="dashboard-metric-card">
            <span class="dashboard-metric-label">Menu</span>
            <strong class="dashboard-metric-value"><?= $totalMenuItems ?></strong>
            <p>Menu items currently available</p>
        </div>
    </div>
</section>

<section class="analytics-grid">
    <div class="card analytics-card">
        <div class="section-title">
            <div>
                <h2>Reservation analytics</h2>
                <p>Distribution of booking statuses from the live reservation table.</p>
            </div>
            <a class="button button-ghost" href="reservations.php">Open reservations</a>
        </div>
        <div class="analytics-layout">
            <div class="chart-donut" style="background: <?= $buildPieGradient($reservationSegments) ?>;"></div>
            <div class="analytics-legend">
                <?php foreach ($reservationSegments as $segment): ?>
                    <div class="legend-row">
                        <span class="legend-swatch" style="background: <?= sanitize($segment['color']) ?>;"></span>
                        <span><?= sanitize($segment['label']) ?></span>
                        <strong><?= $segment['value'] ?></strong>
                        <small><?= $formatPercent($segment['value'], $totalReservations) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="card analytics-card">
        <div class="section-title">
            <div>
                <h2>Review sentiment</h2>
                <p>Star rating distribution for customer reviews submitted on the site.</p>
            </div>
            <a class="button button-ghost" href="reviews.php">Open reviews</a>
        </div>
        <div class="analytics-layout">
            <div class="chart-donut" style="background: <?= $buildPieGradient($reviewSegments) ?>;"></div>
            <div class="analytics-legend">
                <?php foreach ($reviewSegments as $segment): ?>
                    <div class="legend-row">
                        <span class="legend-swatch" style="background: <?= sanitize($segment['color']) ?>;"></span>
                        <span><?= sanitize($segment['label']) ?></span>
                        <strong><?= $segment['value'] ?></strong>
                        <small><?= $formatPercent($segment['value'], $totalReviews) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="analytics-grid">
    <div class="card analytics-card">
        <div class="section-title">
            <div>
                <h2>Recent reservations</h2>
                <p>Latest booking activity captured from customer submissions.</p>
            </div>
        </div>
        <?php if (empty($recentReservations)): ?>
            <div class="alert">No reservations have been recorded yet.</div>
        <?php else: ?>
            <div class="activity-list">
                <?php foreach ($recentReservations as $reservation): ?>
                    <div class="activity-item">
                        <div>
                            <strong><?= sanitize($reservation['customer_name'] ?? $reservation['name'] ?? 'Guest') ?></strong>
                            <p><?= sanitize($reservation['section_label'] ?? $reservation['preferred_section'] ?? 'Unknown section') ?> · <?= sanitize($reservation['date'] ?? '') ?> at <?= sanitize($reservation['time'] ?? '') ?></p>
                        </div>
                        <span class="badge <?= strtoupper((string)($reservation['status'] ?? '')) === 'CANCELLED' ? 'badge-danger' : (strtoupper((string)($reservation['status'] ?? '')) === 'PENDING' ? 'badge-warning' : 'badge-success') ?>">
                            <?= sanitize($reservation['status'] ?? 'Confirmed') ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="card analytics-card">
        <div class="section-title">
            <div>
                <h2>Recent reviews</h2>
                <p>Newest customer feedback with rating and date details.</p>
            </div>
        </div>
        <?php if (empty($recentReviews)): ?>
            <div class="alert">No reviews have been submitted yet.</div>
        <?php else: ?>
            <div class="activity-list">
                <?php foreach ($recentReviews as $review): ?>
                    <div class="activity-item">
                        <div>
                            <strong><?= sanitize($review['name'] ?? 'Guest') ?></strong>
                            <p><?= sanitize($review['comment'] ?? '') ?></p>
                            <small><?= sanitize($review['date'] ?? '') ?></small>
                        </div>
                        <span class="badge badge-success"><?= sanitize((string)($review['rating'] ?? 0)) ?>★</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php';
