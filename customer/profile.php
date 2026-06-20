<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();
$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cancel_id'])) {
        $reservationId = trim($_POST['cancel_id']);
        if (cancel_customer_reservation($reservationId, $user['id'])) {
            $success = 'Your reservation has been cancelled.';
        } else {
            $errors[] = 'Unable to cancel the reservation. Please try again.';
        }
    } elseif (isset($_POST['update_profile'])) {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($name === '') {
            $errors[] = 'Enter your name.';
        }
        if ($phone === '') {
            $errors[] = 'Enter your phone number.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }
        if (empty($errors)) {
            if (update_customer_profile($user['id'], $name, $phone, $email)) {
                $user['name'] = $name;
                $user['phone'] = $phone;
                $user['email'] = $email;
                $_SESSION['user'] = $user;
                $success = 'Your profile was updated successfully.';
            } else {
                $errors[] = 'Unable to save your profile. The email may already be in use.';
            }
        }
    } elseif (isset($_POST['update_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        if ($currentPassword === '') {
            $errors[] = 'Enter your current password.';
        }
        if ($newPassword === '' || strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters long.';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New password and confirmation do not match.';
        }
        if (empty($errors)) {
            $result = update_customer_password($user['id'], $currentPassword, $newPassword);
            if (!empty($result['error'])) {
                $errors[] = $result['error'];
            } else {
                $success = 'Your password has been updated successfully.';
            }
        }
    }
    // Respond to catering quotes
    if (isset($_POST['respond_catering']) && !empty($_POST['request_id']) && in_array($_POST['response'] ?? '', ['Accepted','Declined'], true)) {
        $rid = trim($_POST['request_id']);
        $resp = $_POST['response'];
        if (respond_catering_quote($rid, $user['id'], $resp)) {
            $success = 'Your response was recorded.';
        } else {
            $errors[] = 'Unable to record your response.';
        }
    }
}
$bookings = get_customer_reservations($user['id']);
$cateringRequests = get_customer_catering_requests($user['id']);
$pageTitle = 'Profile';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section-title">
    <h2>My profile</h2>
    <p>Update your account information, track reservations, and cancel upcoming bookings.</p>
</section>
<section class="card" style="margin-top:24px;">
    <div class="section-title">
        <h3>Catering requests & quotes</h3>
        <p>View quotes sent by the restaurant and accept or decline them.</p>
    </div>
    <?php if (empty($cateringRequests)): ?>
        <div class="alert"><strong>No catering requests.</strong> Request catering from your dashboard.</div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>Request</th><th>Date</th><th>Guests</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($cateringRequests as $r): ?>
                    <tr>
                        <td>
                            <strong><?= sanitize($r['event_type'] ?? '') ?></strong>
                            <?php if (!empty($r['quote'])): ?>
                                <div style="margin-top:8px;padding:12px;border-radius:12px;background:rgba(255,249,240,0.9);border:1px solid rgba(255,206,148,0.6);white-space:pre-wrap;"><strong>Quote</strong><div style="margin-top:6px;"><?= sanitize($r['quote']) ?></div></div>
                            <?php endif; ?>
                            <div style="color:var(--muted);margin-top:6px;white-space:pre-wrap;"><?= sanitize($r['notes'] ?? '') ?></div>
                        </td>
                        <td><?= sanitize($r['event_date'] ?? '') ?></td>
                        <td><?= sanitize((string)($r['guest_count'] ?? '')) ?></td>
                        <td><span class="badge <?= $r['status'] === 'Accepted' ? 'badge-success' : ($r['status'] === 'Declined' ? 'badge-danger' : ($r['status'] === 'Quoted' ? 'badge-warning' : 'badge-warning')) ?>"><?= sanitize($r['status'] ?? '') ?></span></td>
                        <td>
                            <?php if ($r['status'] === 'Quoted'): ?>
                                <form method="post" style="display:inline-block;margin-right:8px;"><input type="hidden" name="request_id" value="<?= sanitize($r['id']) ?>"><input type="hidden" name="response" value="Accepted"><button class="button button-primary" type="submit" name="respond_catering">Accept</button></form>
                                <form method="post" style="display:inline-block;"><input type="hidden" name="request_id" value="<?= sanitize($r['id']) ?>"><input type="hidden" name="response" value="Declined"><button class="button button-ghost" type="submit" name="respond_catering">Decline</button></form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php if ($success): ?>
    <section class="confirmation-panel">
        <div class="success-badge"><i class="fa-solid fa-check"></i></div>
        <h2>Success</h2>
        <p><?= sanitize($success) ?></p>
    </section>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="alert"><strong>Please address the following</strong><ul><?php foreach ($errors as $error): ?><li><?= sanitize($error) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<section class="card">
    <div class="section-title">
        <h3>Account details</h3>
        <p>Edit your name, email, and phone number.</p>
    </div>
    <form method="post" class="input-group">
        <div class="field">
            <label for="name">Name</label>
            <input class="input-field" type="text" id="name" name="name" value="<?= sanitize($user['name'] ?? '') ?>" required>
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input class="input-field" type="email" id="email" name="email" value="<?= sanitize($user['email'] ?? '') ?>" required>
        </div>
        <div class="field">
            <label for="phone">Phone</label>
            <input class="input-field" type="text" id="phone" name="phone" value="<?= sanitize($user['phone'] ?? '') ?>" required>
        </div>
        <button class="button button-primary" type="submit" name="update_profile">Save profile</button>
    </form>
</section>
<section class="card" style="margin-top:24px;">
    <div class="section-title">
        <h3>Change password</h3>
        <p>Update your password regularly to keep your account secure.</p>
    </div>
    <form method="post" class="input-group">
        <div class="field">
            <label for="current_password">Current password</label>
            <input class="input-field" type="password" id="current_password" name="current_password" required>
        </div>
        <div class="field">
            <label for="new_password">New password</label>
            <input class="input-field" type="password" id="new_password" name="new_password" required>
        </div>
        <div class="field">
            <label for="confirm_password">Confirm new password</label>
            <input class="input-field" type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button class="button button-primary" type="submit" name="update_password">Update password</button>
    </form>
</section>
<section class="card" style="margin-top:24px;">
    <div class="section-title">
        <h3>Booking history</h3>
        <p>Track confirmed and cancelled reservations in one place.</p>
    </div>
    <?php if (empty($bookings)): ?>
        <div class="alert"><strong>No bookings found.</strong> Reserve a table to see your reservation details here.</div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>Location</th><th>Guests</th><th>Date</th><th>Time</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= sanitize($booking['section_label'] ?? $booking['preferred_section'] ?? 'Unknown') ?></td>
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
<?php require_once __DIR__ . '/../includes/footer.php';
?>
