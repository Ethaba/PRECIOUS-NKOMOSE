<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$errors = [];
$success = '';
// PERFORMANCE FIX: Use SQL to group and sort instead of PHP loops
$reservationsBySection = get_reservations_by_section();
$tables = db_all('SELECT * FROM restaurant_tables ORDER BY label');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_table') {
        $table = [
            'id' => trim($_POST['id'] ?? ''),
            'label' => trim($_POST['label'] ?? ''),
            'capacity' => (int)($_POST['capacity'] ?? 0),
            'description' => trim($_POST['description'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
        if ($table['label'] === '' || $table['capacity'] <= 0) {
            $errors[] = 'Table label and capacity are required.';
        }
        if (empty($errors) && create_table_entry($table)) {
            $success = 'Table added.';
            $tables = db_all('SELECT * FROM restaurant_tables ORDER BY label');
        } else if (empty($errors)) {
            $errors[] = 'Unable to add table.';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update_table' && !empty($_POST['id'])) {
        $tableId = trim($_POST['id']);
        if (update_table_entry($tableId, [
            'label' => trim($_POST['label'] ?? ''),
            'capacity' => (int)($_POST['capacity'] ?? 0),
            'description' => trim($_POST['description'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ])) {
            $success = 'Table updated.';
            $tables = db_all('SELECT * FROM restaurant_tables ORDER BY label');
        } else {
            $errors[] = 'Unable to update table.';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_table' && !empty($_POST['id'])) {
        if (delete_table_entry(trim($_POST['id']))) {
            $success = 'Table deleted.';
            $tables = db_all('SELECT * FROM restaurant_tables ORDER BY label');
        } else {
            $errors[] = 'Unable to delete table.';
        }
    }
}
$pageTitle = 'Tables Overview';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Tables & Sections</h2>
        <p>Quick overview of seating sections and current bookings.</p>
    </div>
    <?php if ($success): ?><div class="confirmation-panel"><div class="success-badge"><i class="fa-solid fa-check"></i></div><h2>Saved</h2><p><?= sanitize($success) ?></p></div><?php endif; ?>
    <?php if ($errors): ?><div class="alert"><strong>Please address the following</strong><ul><?php foreach ($errors as $e) echo '<li>' . sanitize($e) . '</li>'; ?></ul></div><?php endif; ?>
    <form method="post" class="input-group" style="margin-bottom:18px;">
        <input type="hidden" name="action" value="add_table">
        <div class="field"><label>Table ID</label><input class="input-field" name="id" placeholder="e.g. table-10"></div>
        <div class="field"><label>Label</label><input class="input-field" name="label" required></div>
        <div class="field"><label>Capacity</label><input class="input-field" type="number" name="capacity" min="1" required></div>
        <div class="field"><label>Description</label><textarea class="input-textarea" name="description"></textarea></div>
        <label><input type="checkbox" name="is_active" checked> Active</label>
        <button class="button button-primary" type="submit">Add table</button>
    </form>
    <?php if (empty($reservationsBySection)): ?>
        <div class="alert">No current reservations.</div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>Section</th><th>Bookings</th><th>Next Booking</th></tr></thead>
            <tbody>
            <?php foreach ($reservationsBySection as $row): ?>
                <tr>
                    <td><?= sanitize($row['section_name']) ?></td>
                    <td><?= (int)$row['booking_count'] ?></td>
                    <td><?= sanitize($row['next_booking'] ?? 'N/A') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<section class="card" style="margin-top:24px;">
    <div class="section-title">
        <h2>Restaurant tables</h2>
        <p>Manage table seating capacity and availability.</p>
    </div>
    <?php if (empty($tables)): ?>
        <div class="alert">No tables found.</div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>ID</th><th>Label</th><th>Capacity</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($tables as $table): ?>
                    <tr>
                        <td><?= sanitize($table['id']) ?></td>
                        <td><?= sanitize($table['label']) ?></td>
                        <td><?= (int)$table['capacity'] ?></td>
                        <td><?= !empty($table['is_active']) ? 'Active' : 'Inactive' ?></td>
                        <td>
                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="action" value="delete_table">
                                <input type="hidden" name="id" value="<?= sanitize($table['id']) ?>">
                                <button class="button button-ghost" type="submit">Delete</button>
                            </form>
                            <button class="button button-primary" type="button" onclick="document.getElementById('table-edit-<?= sanitize($table['id']) ?>').style.display = document.getElementById('table-edit-<?= sanitize($table['id']) ?>').style.display === 'none' ? 'table-row' : 'none';">Edit</button>
                        </td>
                    </tr>
                    <tr id="table-edit-<?= sanitize($table['id']) ?>" style="display:none;">
                        <td colspan="5">
                            <form method="post" class="input-group">
                                <input type="hidden" name="action" value="update_table">
                                <input type="hidden" name="id" value="<?= sanitize($table['id']) ?>">
                                <div class="field"><label>Label</label><input class="input-field" name="label" value="<?= sanitize($table['label']) ?>" required></div>
                                <div class="field"><label>Capacity</label><input class="input-field" type="number" min="1" name="capacity" value="<?= (int)$table['capacity'] ?>" required></div>
                                <div class="field"><label>Description</label><textarea class="input-textarea" name="description"><?= sanitize($table['description'] ?? '') ?></textarea></div>
                                <label><input type="checkbox" name="is_active" <?= !empty($table['is_active']) ? 'checked' : '' ?>> Active</label>
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
