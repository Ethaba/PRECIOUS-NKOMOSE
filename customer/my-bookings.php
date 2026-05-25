<?php
// Customer pages sit one folder down, so includes must point back to /includes.
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();
$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $reservationId = trim($_POST['cancel_id']);
    // Cancel only reservations that belong to the logged-in customer.
    if (cancel_customer_reservation($reservationId, $user['id'])) {
        $success = 'Your reservation has been cancelled.';
    }
}
// Reload bookings from MySQL after a possible cancellation.
$bookings = get_customer_reservations($user['id']);
$pageTitle = 'My Bookings';
// Load the shared header from the parent includes folder.
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section-title">
    <h2>Your bookings</h2>
    <p>Review your reservation history and cancel upcoming bookings if needed.</p>
</section>
<section class="card">
    <?php if ($success): ?>
        <div class="confirmation-panel"><div class="success-badge"><i class="fa-solid fa-check"></i></div><h2>Cancelled</h2><p><?= sanitize($success) ?></p></div>
    <?php endif; ?>
    <?php if (empty($bookings)): ?>
        <div class="alert"><strong>No bookings found.</strong> Reserve a table now to see your booking details.</div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>Table</th><th>Guests</th><th>Date</th><th>Time</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= sanitize($booking['section'] ?? $booking['preferred_section'] ?? 'Section') ?></td>
                        <td><?= sanitize($booking['guests']) ?></td>
                        <td><?= sanitize($booking['date']) ?></td>
                        <td><?= sanitize($booking['time']) ?></td>
                        <td><span class="badge <?= $booking['status'] === 'Confirmed' ? 'badge-success' : ($booking['status'] === 'Cancelled' ? 'badge-danger' : 'badge-warning') ?>"><?= sanitize($booking['status']) ?></span></td>
                        <td>
                            <?php if ($booking['status'] !== 'Cancelled'): ?>
                                <form method="post" style="margin:0;"><input type="hidden" name="cancel_id" value="<?= sanitize($booking['id']) ?>"><button class="button button-ghost" type="submit">Cancel</button></form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php
// Load the shared footer from the parent includes folder.
require_once __DIR__ . '/../includes/footer.php';
