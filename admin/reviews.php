<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$reviews = get_reviews();
$pageTitle = 'Manage Reviews';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Reviews</h2>
        <p>User-submitted reviews and ratings.</p>
    </div>
    <?php if (empty($reviews)): ?>
        <div class="alert">No reviews yet.</div>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach (array_reverse($reviews) as $r): ?>
                <div class="review-card">
                    <strong><?= sanitize($r['name'] ?? '') ?> <span class="badge badge-success"><?= sanitize((string)($r['rating'] ?? '')) ?> ★</span></strong>
                    <p><?= sanitize($r['comment'] ?? '') ?></p>
                    <small style="color: var(--muted);"><?= sanitize($r['date'] ?? '') ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
