<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$requests = get_catering_requests();
$success = '';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'send_quote' && !empty($_POST['id'])) {
        $id = trim($_POST['id']);
        $quote = trim($_POST['quote'] ?? '');
        if ($quote === '') $errors[] = 'Quote text is required.';
        if (empty($errors) && send_catering_quote($id, $quote)) {
            $success = 'Quote sent to customer.';
            $requests = get_catering_requests();
        }
    }
    if (isset($_POST['action']) && $_POST['action'] === 'mark_completed' && !empty($_POST['id'])) {
        $id = trim($_POST['id']);
        if (mark_catering_completed($id)) {
            $success = 'Request marked completed.';
            $requests = get_catering_requests();
        }
    }
}
$pageTitle = 'Catering Requests';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Catering Requests</h2>
        <p>Requests submitted by customers for event catering.</p>
    </div>
    <?php if ($success): ?><div class="confirmation-panel"><div class="success-badge"><i class="fa-solid fa-check"></i></div><h2>Saved</h2><p><?= sanitize($success) ?></p></div><?php endif; ?>
    <?php if ($errors): ?><div class="alert"><strong>Please address the following</strong><ul><?php foreach ($errors as $e) echo '<li>' . sanitize($e) . '</li>'; ?></ul></div><?php endif; ?>
    <?php if (empty($requests)): ?>
        <div class="alert">No catering requests yet.</div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>ID</th><th>Customer</th><th>Event</th><th>Date</th><th>Guests</th><th>Status</th><th>Quote</th><th>Sent</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td><?= sanitize($r['id']) ?></td>
                        <td><?= sanitize($r['customer_name'] ?? '') ?></td>
                        <td><?= sanitize($r['event_type'] ?? '') ?></td>
                        <td><?= sanitize($r['event_date'] ?? '') ?></td>
                        <td><?= sanitize((string)($r['guest_count'] ?? '')) ?></td>
                        <td><?= sanitize($r['status'] ?? '') ?></td>
                        <td style="max-width:260px;white-space:pre-wrap;"><?= sanitize($r['quote'] ?? '') ?></td>
                        <td><?= sanitize($r['quote_sent_at'] ?? '') ?></td>
                        <td style="text-align:right;">
                            <details style="display:inline-block;text-align:left;">
                                <summary class="button button-ghost">Actions</summary>
                                <form method="post" style="margin-top:8px;"><input type="hidden" name="action" value="send_quote"><input type="hidden" name="id" value="<?= sanitize($r['id']) ?>"><div class="field"><label>Quote</label><textarea class="input-textarea" name="quote" rows="4"><?= sanitize($r['quote'] ?? '') ?></textarea></div><button class="button button-primary" type="submit">Send quote</button></form>
                                <form method="post" style="margin-top:8px;"><input type="hidden" name="action" value="mark_completed"><input type="hidden" name="id" value="<?= sanitize($r['id']) ?>"><button class="button button-ghost" type="submit">Mark completed</button></form>
                            </details>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
