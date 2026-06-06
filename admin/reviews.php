<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$reviews = get_reviews();
$stats = get_review_stats();
$pageTitle = 'Review Insights';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Review Insights</h2>
        <p>Overview of customer ratings and trends.</p>
    </div>
    <div class="analytics-layout">
        <div>
            <h3>Average rating</h3>
            <div style="font-size:2.4rem;font-weight:700;"><?= sanitize((string)round($stats['average_rating'] ?? 0,1)) ?> ★</div>
            <p><?= sanitize((string)($stats['total'] ?? 0)) ?> reviews recorded</p>
        </div>
        <div>
            <h3>Rating breakdown</h3>
            <div class="analytics-legend">
                <?php $break = $stats['breakdown'] ?? [1=>0,2=>0,3=>0,4=>0,5=>0];
                for ($i=5;$i>=1;$i--): ?>
                    <div class="legend-row">
                        <div class="legend-swatch" style="background:linear-gradient(90deg, rgba(255,166,61,.9), rgba(255,106,61,.9));"></div>
                        <strong><?= $i ?> ★</strong>
                        <div style="flex:1;"><small><?= sanitize((string)($break[$i] ?? 0)) ?> reviews</small></div>
                        <div><?= sanitize((string)round((($break[$i] ?? 0)/max(1,$stats['total']))*100)) ?>%</div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <div style="margin-top:18px;">
        <h3>Recent comments</h3>
        <?php if (empty($reviews)): ?>
            <div class="alert">No reviews yet.</div>
        <?php else: ?>
            <div class="review-grid">
                <?php foreach (array_reverse($reviews) as $r): ?>
                    <div class="review-card">
                        <strong><?= sanitize($r['name'] ?? '') ?> <span class="badge badge-success"><?= sanitize((string)($r['rating'] ?? '')) ?> ★</span></strong>
                        <p><?= sanitize($r['comment'] ?? '') ?></p>
                        <small style="color: var(--muted);"><?= sanitize($r['date'] ?? '') ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
