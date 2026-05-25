<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$requests = get_catering_requests();
$pageTitle = 'Catering Requests';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Catering Requests</h2>
        <p>Requests submitted by customers for event catering.</p>
    </div>
    <?php if (empty($requests)): ?>
        <div class="alert">No catering requests yet.</div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>ID</th><th>Customer</th><th>Event</th><th>Date</th><th>Guests</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td><?= sanitize($r['id']) ?></td>
                        <td><?= sanitize($r['customer_name'] ?? '') ?></td>
                        <td><?= sanitize($r['event_type'] ?? '') ?></td>
                        <td><?= sanitize($r['event_date'] ?? '') ?></td>
                        <td><?= sanitize((string)($r['guest_count'] ?? '')) ?></td>
                        <td><?= sanitize($r['status'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
