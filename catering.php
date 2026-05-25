<?php
require_once 'includes/functions.php';
require_login();
$user = current_user();
$errors = [];
$success = '';
$inputs = [
    'event_type' => '',
    'event_date' => '',
    'guest_count' => '',
    'location' => '',
    'notes' => '',
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($inputs as $key => $value) {
        $inputs[$key] = trim($_POST[$key] ?? '');
    }
    if ($inputs['event_type'] === '') $errors[] = 'Select an event type.';
    if ($inputs['event_date'] === '' || !strtotime($inputs['event_date'])) $errors[] = 'Choose a valid event date.';
    if ($inputs['guest_count'] === '' || !is_numeric($inputs['guest_count']) || (int)$inputs['guest_count'] <= 0) $errors[] = 'Enter the number of guests.';
    if ($inputs['location'] === '') $errors[] = 'Enter the event location.';
    if ($inputs['notes'] === '') $errors[] = 'Provide a few details about your catering request.';
    if (empty($errors)) {
        // Save catering quote requests to the catering_requests table.
        create_catering_request([
            'customer_id' => $user['id'],
            'customer_name' => $user['name'],
            'customer_email' => $user['email'],
            'event_type' => $inputs['event_type'],
            'event_date' => $inputs['event_date'],
            'guest_count' => (int)$inputs['guest_count'],
            'location' => $inputs['location'],
            'notes' => $inputs['notes'],
            'status' => 'Pending',
        ]);
        // Log the confirmation message in the emails table.
        send_system_email($user['email'], 'Catering Quote Request Received', "Dear {$user['name']},\n\nThank you for requesting catering from Precious Nkomose. We have received your request for a {$inputs['event_type']} on {$inputs['event_date']} for {$inputs['guest_count']} guests. Our team will contact you soon with a formal quote.\n\nRegards,\nPrecious Nkomose");
        $success = 'Your catering quote request was submitted successfully. Please check your email for confirmation.';
        $inputs = array_fill_keys(array_keys($inputs), '');
    }
}
$pageTitle = 'Catering';
require_once 'includes/header.php';
?>
<section class="section-title">
    <h2>Event Catering Requests</h2>
    <p>Request catering for weddings, parties, business functions, or formal gatherings.</p>
</section>
<section class="card">
    <?php if ($errors): ?>
        <div class="alert"><strong>There were issues with your request</strong><ul><?php foreach ($errors as $error): ?><li><?= sanitize($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="confirmation-panel"><div class="success-badge"><i class="fa-solid fa-check"></i></div><h2>Request received</h2><p><?= sanitize($success) ?></p></div>
    <?php endif; ?>
    <form method="post" class="input-group">
        <label class="select-label" for="event_type">Event Type</label>
        <select class="input-select" id="event_type" name="event_type" required>
            <option value="">Choose an event</option>
            <option value="Wedding" <?= $inputs['event_type'] === 'Wedding' ? 'selected' : '' ?>>Wedding</option>
            <option value="Business Function" <?= $inputs['event_type'] === 'Business Function' ? 'selected' : '' ?>>Business Function</option>
            <option value="Formal Gathering" <?= $inputs['event_type'] === 'Formal Gathering' ? 'selected' : '' ?>>Formal Gathering</option>
            <option value="Party" <?= $inputs['event_type'] === 'Party' ? 'selected' : '' ?>>Party</option>
        </select>
        <input class="input-field" type="date" name="event_date" value="<?= sanitize($inputs['event_date']) ?>" required>
        <input class="input-field" type="number" name="guest_count" min="1" placeholder="Number of guests" value="<?= sanitize($inputs['guest_count']) ?>" required>
        <input class="input-field" type="text" name="location" placeholder="Event location" value="<?= sanitize($inputs['location']) ?>" required>
        <textarea class="input-textarea" id="notes" name="notes" placeholder="Tell us your catering vision" required><?= sanitize($inputs['notes']) ?></textarea>
        <button class="button button-primary primary-action" type="submit">Request Quote</button>
    </form>
</section>
<?php require_once 'includes/footer.php';
