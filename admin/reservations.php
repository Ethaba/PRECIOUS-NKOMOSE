<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$reservations = get_reservations();
$pageTitle = 'Manage Reservations';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Reservations</h2>
        <p>All reservations recorded through the site.</p>
    </div>
    <?php if (empty($reservations)): ?>
        <div class="alert">No reservations yet.</div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>ID</th><th>Customer</th><th>Section</th><th>Guests</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($reservations as $r): ?>
                    <tr>
                        <td><?= sanitize($r['id'] ?? '') ?></td>
                        <td><?= sanitize($r['customer_name'] ?? $r['name'] ?? '') ?></td>
                        <td><?= sanitize($r['section_label'] ?? $r['preferred_section'] ?? '') ?></td>
                        <td><?= sanitize((string)($r['guests'] ?? '')) ?></td>
                        <td><?= sanitize($r['date'] ?? '') ?></td>
                        <td><?= sanitize($r['time'] ?? '') ?></td>
                        <td><?= sanitize($r['status'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
