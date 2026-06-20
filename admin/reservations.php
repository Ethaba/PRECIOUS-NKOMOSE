<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$errors = [];
$success = '';
$search = trim($_GET['search'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_reservation' && !empty($_POST['id'])) {
        $id = trim($_POST['id']);
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');
        $guests = (int)($_POST['guests'] ?? 0);
        $status = trim($_POST['status'] ?? 'Confirmed');
        $notes = trim($_POST['notes'] ?? '');
        if ($date === '' || !strtotime($date)) {
            $errors[] = 'Choose a valid date.';
        }
        if ($time === '' || !preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time)) {
            $errors[] = 'Choose a valid time.';
        }
        if ($guests <= 0) {
            $errors[] = 'Guests must be greater than zero.';
        }
        if (empty($errors)) {
            if (update_reservation($id, [
                'status' => $status,
                'date' => $date,
                'time' => $time,
                'guests' => $guests,
                'notes' => $notes,
            ])) {
                $success = 'Reservation updated.';
            } else {
                $errors[] = 'Unable to update reservation.';
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_reservation' && !empty($_POST['id'])) {
        if (delete_reservation(trim($_POST['id']))) {
            $success = 'Reservation deleted.';
        } else {
            $errors[] = 'Unable to delete reservation.';
        }
    }
}

if ($search !== '') {
    $reservations = db_all(
        'SELECT * FROM reservations WHERE customer_name LIKE ? OR customer_email LIKE ? OR id LIKE ? ORDER BY date DESC,time DESC,created_at DESC',
        ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%']
    );
} else {
    $reservations = get_reservations();
}
$pageTitle = 'Manage Reservations';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Reservations</h2>
        <p>All reservations recorded through the site.</p>
    </div>
    <?php if ($success): ?><div class="confirmation-panel"><div class="success-badge"><i class="fa-solid fa-check"></i></div><h2>Saved</h2><p><?= sanitize($success) ?></p></div><?php endif; ?>
    <?php if ($errors): ?><div class="alert"><strong>Please address the following</strong><ul><?php foreach ($errors as $e) echo '<li>' . sanitize($e) . '</li>'; ?></ul></div><?php endif; ?>
    <form method="get" class="input-group" style="margin-bottom:16px;">
        <input class="input-field" type="search" name="search" value="<?= sanitize($search) ?>" placeholder="Search by customer, email, or reservation ID">
        <button class="button button-primary" type="submit">Search</button>
    </form>
    <?php if (empty($reservations)): ?>
        <div class="alert">No reservations yet.</div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>ID</th><th>Customer</th><th>Section</th><th>Guests</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead>
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
                        <td>
                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="action" value="delete_reservation">
                                <input type="hidden" name="id" value="<?= sanitize($r['id'] ?? '') ?>">
                                <button class="button button-ghost" type="submit">Delete</button>
                            </form>
                            <button class="button button-primary" type="button" onclick="document.getElementById('edit-<?= sanitize($r['id'] ?? '') ?>').style.display = document.getElementById('edit-<?= sanitize($r['id'] ?? '') ?>').style.display === 'none' ? 'table-row' : 'none';">Edit</button>
                        </td>
                    </tr>
                    <tr id="edit-<?= sanitize($r['id'] ?? '') ?>" style="display:none;">
                        <td colspan="8">
                            <form method="post" class="input-group">
                                <input type="hidden" name="action" value="update_reservation">
                                <input type="hidden" name="id" value="<?= sanitize($r['id'] ?? '') ?>">
                                <div class="field"><label>Date</label><input class="input-field" type="date" name="date" value="<?= sanitize($r['date'] ?? '') ?>" required></div>
                                <div class="field"><label>Time</label><input class="input-field" type="time" name="time" value="<?= sanitize($r['time'] ?? '') ?>" required></div>
                                <div class="field"><label>Guests</label><input class="input-field" type="number" name="guests" min="1" value="<?= sanitize((string)($r['guests'] ?? '')) ?>" required></div>
                                <div class="field"><label>Status</label><select class="input-select" name="status"><option value="Confirmed" <?= (($r['status'] ?? '') === 'Confirmed') ? 'selected' : '' ?>>Confirmed</option><option value="Pending" <?= (($r['status'] ?? '') === 'Pending') ? 'selected' : '' ?>>Pending</option><option value="Cancelled" <?= (($r['status'] ?? '') === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option></select></div>
                                <div class="field"><label>Notes</label><textarea class="input-textarea" name="notes"><?= sanitize($r['notes'] ?? '') ?></textarea></div>
                                <button class="button button-primary" type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
