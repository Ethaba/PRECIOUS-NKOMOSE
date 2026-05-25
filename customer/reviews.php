<?php
// Customer pages sit one folder down, so includes must point back to /includes.
require_once __DIR__ . '/../includes/functions.php';
$user = current_user();
// Reviews are read from MySQL so submitted feedback is shared across the site.
$reviews = get_reviews();
$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_logged_in()) {
        // Use the root login page even when this file runs from /customer.
        header('Location: ' . app_url('login.php'));
        exit;
    }
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if ($rating < 1 || $rating > 5) $errors[] = 'Choose a rating between 1 and 5 stars.';
    if ($comment === '') $errors[] = 'Write a short review of your experience.';
    if (empty($errors)) {
        // Store the customer's review in the reviews table.
        create_review($user['id'], $user['name'], $rating, $comment);
        // Refresh the list so the new review appears immediately.
        $reviews = get_reviews();
        $success = 'Thank you! Your review has been added.';
    }
}
$pageTitle = 'Reviews';
// Load the shared header from the parent includes folder.
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section-title">
    <h2>Reviews & Ratings</h2>
    <p>Share your Precious Nkomose experience and read what other guests enjoyed.</p>
</section>
<section class="card">
    <?php if ($errors): ?>
        <div class="alert"><strong>Review error</strong><ul><?php foreach ($errors as $error): ?><li><?= sanitize($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="confirmation-panel"><div class="success-badge"><i class="fa-solid fa-check"></i></div><h2>Review submitted</h2><p><?= sanitize($success) ?></p></div>
    <?php endif; ?>
    <?php if (is_logged_in()): ?>
        <form method="post" class="input-group">
            <label class="select-label" for="rating">Rating</label>
            <select class="input-select" id="rating" name="rating" required>
                <option value="">Select stars</option>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?= $i ?>"><?= str_repeat('★', $i) ?> <?= $i ?> star<?= $i === 1 ? '' : 's' ?></option>
                <?php endfor; ?>
            </select>
            <textarea class="input-textarea" name="comment" placeholder="Write your review here" required></textarea>
            <button class="button button-primary primary-action" type="submit">Submit Review</button>
        </form>
    <?php else: ?>
        <div class="alert"><strong>Login required</strong> Please <a href="<?= sanitize(app_url('login.php')) ?>">login</a> to leave a review.</div>
    <?php endif; ?>
</section>
<section class="review-grid" style="margin-top:24px;">
    <?php if (empty($reviews)): ?>
        <div class="alert">No reviews yet. Be the first to share your experience.</div>
    <?php else: ?>
        <?php foreach (array_reverse($reviews) as $review): ?>
            <div class="review-card">
                <strong><?= sanitize($review['name']) ?> <span class="badge badge-success"><?= sanitize($review['rating']) ?> ★</span></strong>
                <p><?= sanitize($review['comment']) ?></p>
                <small style="color: var(--muted);"><?= sanitize($review['date']) ?></small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
<?php
// Load the shared footer from the parent includes folder.
require_once __DIR__ . '/../includes/footer.php';
