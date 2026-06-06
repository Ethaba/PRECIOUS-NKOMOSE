<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
// PERFORMANCE FIX: Use SQL to group and sort instead of PHP loops
$reservationsBySection = get_reservations_by_section();
$pageTitle = 'Tables Overview';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Tables & Sections</h2>
        <p>Quick overview of seating sections and current bookings.</p>
    </div>
    <?php if (empty($reservationsBySection)): ?>
        <div class="alert">No current reservations.</div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>Section</th><th>Bookings</th><th>Next Booking</th></tr></thead>
            <tbody>
            <?php foreach ($reservationsBySection as $row): ?>
                <tr>
                    <td><?= sanitize($row['section_name']) ?></td>
                    <td><?= (int)$row['booking_count'] ?></td>
                    <td><?= sanitize($row['next_booking'] ?? 'N/A') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
