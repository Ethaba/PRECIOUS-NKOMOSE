<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$reservations = get_reservations();
$pageTitle = 'Tables Overview';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Tables & Sections</h2>
        <p>Quick overview of seating sections and current bookings.</p>
    </div>
    <?php if (empty($reservations)): ?>
        <div class="alert">No current reservations.</div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>Section</th><th>Bookings</th><th>Next Booking</th></tr></thead>
            <tbody>
            <?php
            $bySection = [];
            foreach ($reservations as $r) {
                $s = $r['preferred_section'] ?? $r['section'] ?? 'Unknown';
                $bySection[$s][] = $r;
            }
            foreach ($bySection as $section => $list):
                usort($list, function($a,$b){ return strtotime($a['date'].' '.$a['time']) <=> strtotime($b['date'].' '.$b['time']); });
                $next = $list[0];
            ?>
                <tr>
                    <td><?= sanitize($section) ?></td>
                    <td><?= count($list) ?></td>
                    <td><?= sanitize(($next['date'] ?? '') . ' ' . ($next['time'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
