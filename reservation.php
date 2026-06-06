<?php
require_once 'includes/functions.php';
$user = current_user();
$errors = [];
$success = '';
$booking = [
    'preferred_section' => '',
    'date' => '',
    'time' => '',
    'guests' => '',
    'notes' => '',
];
$selectedSectionLabel = '';

$sections = [
    ['id' => 'corner-nw', 'label' => 'Corner Bench North West', 'capacity' => 6, 'description' => 'Cozy padded bench seating with a private finish.'],
    ['id' => 'corner-ne', 'label' => 'Corner Bench North East', 'capacity' => 6, 'description' => 'Bright corner seating with a warm restaurant view.'],
    ['id' => 'corner-sw', 'label' => 'Corner Bench South West', 'capacity' => 6, 'description' => 'Curtained semi-private seating for groups of six.'],
    ['id' => 'corner-se', 'label' => 'Corner Bench South East', 'capacity' => 6, 'description' => 'Soft bench seating with a luxurious feel.'],
    ['id' => 'center-left', 'label' => 'Center Round Table A', 'capacity' => 4, 'description' => 'Intimate round table in the heart of the room.'],
    ['id' => 'center-right', 'label' => 'Center Round Table B', 'capacity' => 4, 'description' => 'Shared center table ideal for seafood feasts.' ],
    ['id' => 'whole-venue', 'label' => 'Entire Venue', 'capacity' => 120, 'description' => 'Reserve the whole restaurant for private events, parties and special celebrations.'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Admin seating status updates
    if (is_admin() && isset($_POST['admin_action']) && $_POST['admin_action'] === 'set_status') {
        $sid = trim($_POST['section_id'] ?? '');
        $status = trim($_POST['status'] ?? 'available');
        if ($sid !== '') {
            set_section_status($sid, $status);
            header('Location: ' . app_url('reservation.php'));
            exit;
        }
    }

    foreach ($booking as $key => $value) {
        $booking[$key] = trim($_POST[$key] ?? '');
    }

    if ($booking['preferred_section'] === '') {
        $errors[] = 'Select your preferred seating section.';
    }
    if ($booking['date'] === '' || !strtotime($booking['date'])) {
        $errors[] = 'Choose a valid reservation date.';
    }
    if ($booking['time'] === '' || !preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $booking['time'])) {
        $errors[] = 'Choose a valid time.';
    }
    if ($booking['guests'] === '' || !is_numeric($booking['guests']) || (int)$booking['guests'] <= 0) {
        $errors[] = 'Enter how many guests will attend.';
    }
    if (!$user) {
        header('Location: ' . app_url('register.php'));
        exit;
    }
    if ($booking['notes'] === '') {
        $errors[] = 'Share any seating requests so we can prepare your table.';
    }

    $section = null;
    foreach ($sections as $sectionItem) {
        if ($sectionItem['id'] === $booking['preferred_section']) {
            $section = $sectionItem;
            break;
        }
    }

    if (!$section) {
        $errors[] = 'Please choose a valid section from the seating chart.';
    } elseif ((int)$booking['guests'] > $section['capacity']) {
        $errors[] = sprintf('This section seats up to %d guests. Please lower the guest count or choose another seating area.', $section['capacity']);
    }
    $selectedSectionLabel = $section ? $section['label'] : '';

    if (empty($errors)) {
        $reservationTs = strtotime($booking['date'] . ' ' . $booking['time']);
        if ($reservationTs < strtotime('today')) {
            $errors[] = 'Please choose a reservation date and time in the future.';
        }
    }

    if (empty($errors)) {
        // Check MySQL before inserting, so the same seating section cannot be double-booked.
        if (reservation_slot_taken($booking['preferred_section'], $booking['date'], $booking['time'])) {
            $errors[] = 'This seating area is already reserved at that time. Please choose another section or time.';
        }
    }

    if (empty($errors)) {
        // Save the confirmed reservation to the reservations table.
        create_reservation([
            'customer_id' => $user['id'] ?? '',
            'customer_name' => $user['name'] ?? 'Guest',
            'customer_email' => $user['email'] ?? '',
            'preferred_section' => $section['id'],
            'section_label' => $section['label'],
            'capacity' => $section['capacity'],
            'date' => $booking['date'],
            'time' => $booking['time'],
            'guests' => (int)$booking['guests'],
            'notes' => $booking['notes'],
            'status' => 'Confirmed',
        ]);
        // Log the confirmation message in the emails table.
        send_system_email($user['email'] ?? '', 'Precious Nkomose Reservation Confirmed', "Dear {$user['name']},\n\nYour reservation is confirmed.\n\nSection: {$section['label']}\nGuests: {$booking['guests']}\nDate: {$booking['date']}\nTime: {$booking['time']}\nNotes: {$booking['notes']}\n\nSee you soon at Precious Nkomose.");
        $success = 'Your reservation is confirmed. We have sent a confirmation email to you.';
        $booking = [
            'preferred_section' => '',
            'date' => '',
            'time' => '',
            'guests' => '',
            'notes' => '',
        ];
    }
}

$pageTitle = 'Reservation';
require_once 'includes/header.php';
?>
<section class="section-title">
    <h2>Discover your seating</h2>
    <p>Choose from six premium seating zones, then submit your booking with guest count and special notes.</p>
</section>
<?php if (is_admin()): ?>
    <section class="card">
        <div class="section-title">
            <h2>Manage seating</h2>
            <p>Update availability for each seating area. Admin-only controls.</p>
        </div>
        <div class="section-grid">
            <?php foreach ($sections as $section): $status = get_section_status($section['id']); ?>
                <article class="section-card" style="position:relative;">
                    <span class="status-pill"><?= sanitize(ucfirst($status)) ?></span>
                    <h4><?= sanitize($section['label']) ?></h4>
                    <p><?= sanitize($section['description']) ?></p>
                    <form method="post" style="margin-top:12px;">
                        <input type="hidden" name="admin_action" value="set_status">
                        <input type="hidden" name="section_id" value="<?= sanitize($section['id']) ?>">
                        <select name="status">
                            <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Available</option>
                            <option value="unavailable" <?= $status === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                        </select>
                        <button class="button button-ghost" type="submit">Update</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
<?php if ($success): ?>
    <section class="confirmation-panel">
        <div class="success-badge confirmation-badge"><i class="fa-solid fa-check"></i></div>
        <h2>Reservation confirmed</h2>
        <p><?= sanitize($success) ?></p>
        <p><a class="button button-ghost" href="<?= sanitize(app_url('customer/profile.php')) ?>">View your profile & bookings</a></p>
    </section>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="alert" id="reservation-feedback"><strong>Please address the following</strong><ul><?php foreach ($errors as $error): ?><li><?= sanitize($error) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<section class="card" style="margin-top:24px;">
    <div class="section-title">
        <h3>Pick your table</h3>
        <p>Choose from our interactive table layout to lock in your preferred spot.</p>
    </div>
    <div class="section-grid">
        <?php foreach ($sections as $section): ?>
            <article class="section-card<?= $section['id'] === $booking['preferred_section'] ? ' active' : '' ?>" data-section-id="<?= sanitize($section['id']) ?>" data-section-label="<?= sanitize($section['label']) ?>">
                <span class="status-pill">Seats <?= sanitize((string)$section['capacity']) ?></span>
                <h4><?= sanitize($section['label']) ?></h4>
                <p><?= sanitize($section['description']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<section class="card" style="margin-top:24px;">
    <div class="section-title">
        <h3>Reservation form</h3>
        <p>Complete your booking details below. Login is required to confirm reservations.</p>
    </div>
    <form method="post" class="input-group">
        <input type="hidden" id="preferredSection" name="preferred_section" value="<?= sanitize($booking['preferred_section']) ?>">
        <div class="field">
            <label for="preferredSectionText">Selected section</label>
            <input type="text" id="preferredSectionText" readonly value="<?= $selectedSectionLabel ? sanitize($selectedSectionLabel) : 'None selected' ?>">
        </div>
        <div class="field">
            <label for="date">Reservation date</label>
            <input class="input-field" type="date" id="date" name="date" value="<?= sanitize($booking['date']) ?>" required>
        </div>
        <div class="field">
            <label for="time">Reservation time</label>
            <input class="input-field" type="time" id="time" name="time" value="<?= sanitize($booking['time']) ?>" required>
        </div>
        <div class="field">
            <label for="guests">Guests</label>
            <input class="input-field" type="number" id="guests" name="guests" min="1" max="10" value="<?= sanitize($booking['guests']) ?>" required>
        </div>
        <div class="field">
            <label for="notes">Special seating notes</label>
            <textarea class="input-textarea" id="notes" name="notes" rows="4" required><?= sanitize($booking['notes']) ?></textarea>
        </div>
        <button class="button button-primary primary-action" type="submit">Confirm Booking</button>
    </form>
</section>
<?php if (!empty($errors)): ?>
<script>document.addEventListener('DOMContentLoaded', function(){var el=document.getElementById('reservation-feedback'); if(el){ el.scrollIntoView({behavior:'smooth', block:'start'}); } });</script>
<?php endif; ?>
<?php require_once 'includes/footer.php';

