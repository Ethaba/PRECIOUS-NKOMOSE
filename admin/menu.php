<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$items = load_menu_items();
$success = '';
$errors = [];

// Handle add/edit/delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_item') {
        $item = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'price' => floatval($_POST['price'] ?? 0),
            'available' => isset($_POST['available']) ? 1 : 0,
            'image' => trim($_POST['image'] ?? ''),
            'popular' => isset($_POST['popular']) ? 1 : 0,
        ];
        if ($item['name'] === '') $errors[] = 'Name is required.';
        if (empty($errors) && create_menu_item($item)) {
            $success = 'Menu item created.';
            $items = load_menu_items();
        }
    }
    if (isset($_POST['action']) && $_POST['action'] === 'update_item' && !empty($_POST['id'])) {
        $id = trim($_POST['id']);
        $item = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'price' => floatval($_POST['price'] ?? 0),
            'available' => isset($_POST['available']) ? 1 : 0,
            'image' => trim($_POST['image'] ?? ''),
            'popular' => isset($_POST['popular']) ? 1 : 0,
        ];
        if ($item['name'] === '') $errors[] = 'Name is required.';
        if (empty($errors) && update_menu_item($id, $item)) {
            $success = 'Menu item updated.';
            $items = load_menu_items();
        }
    }
    if (isset($_POST['action']) && $_POST['action'] === 'delete_item' && !empty($_POST['id'])) {
        $id = trim($_POST['id']);
        if (delete_menu_item($id)) {
            $success = 'Menu item removed.';
            $items = load_menu_items();
        }
    }
}

$editItem = null;
if (isset($_GET['edit_id'])) {
    $editItem = db_one('SELECT * FROM menu WHERE id = ?', [trim($_GET['edit_id'])]);
}
$pageTitle = 'Menu Management';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Menu Items</h2>
        <p>List of dishes currently available on the menu.</p>
    </div>
    <?php if ($success): ?><div class="confirmation-panel"><div class="success-badge"><i class="fa-solid fa-check"></i></div><h2>Saved</h2><p><?= sanitize($success) ?></p></div><?php endif; ?>
    <?php if ($errors): ?><div class="alert"><strong>Please address the following</strong><ul><?php foreach ($errors as $e) echo '<li>' . sanitize($e) . '</li>'; ?></ul></div><?php endif; ?>

    <div style="display:flex;gap:18px;flex-wrap:wrap;margin-bottom:18px;align-items:flex-start;">
        <form method="post" style="max-width:420px;flex:1;" class="card input-group">
            <h3><?= $editItem ? 'Edit item' : 'Add menu item' ?></h3>
            <input type="hidden" name="action" value="<?= $editItem ? 'update_item' : 'add_item' ?>">
            <?php if ($editItem): ?><input type="hidden" name="id" value="<?= sanitize($editItem['id']) ?>"><?php endif; ?>
            <div class="field"><label>Name</label><input class="input-field" name="name" value="<?= sanitize($editItem['name'] ?? '') ?>" required></div>
            <div class="field"><label>Category</label><input class="input-field" name="category" value="<?= sanitize($editItem['category'] ?? '') ?>"></div>
            <div class="field"><label>Price</label><input class="input-field" name="price" type="number" step="0.01" value="<?= sanitize($editItem['price'] ?? '0.00') ?>"></div>
            <div class="field"><label>Description</label><textarea class="input-textarea" name="description"><?= sanitize($editItem['description'] ?? '') ?></textarea></div>
            <div style="display:flex;gap:8px;align-items:center;"><label><input type="checkbox" name="available" <?= !empty($editItem['available']) ? 'checked' : '' ?>> Available</label><label><input type="checkbox" name="popular" <?= !empty($editItem['popular']) ? 'checked' : '' ?>> Popular</label></div>
            <div class="field"><label>Image filename</label><input class="input-field" name="image" value="<?= sanitize($editItem['image'] ?? '') ?>"></div>
            <button class="button button-primary" type="submit"><?= $editItem ? 'Save changes' : 'Create item' ?></button>
            <?php if ($editItem): ?><a class="button button-ghost" href="<?= sanitize(app_url('admin/menu.php')) ?>">Cancel edit</a><?php endif; ?>
        </form>
    </div>
    <?php if (empty($items)): ?>
        <div class="alert">No menu items found.</div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Available</th></tr></thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= sanitize($it['id'] ?? '') ?></td>
                        <td><?= sanitize($it['name'] ?? '') ?></td>
                        <td><?= sanitize($it['category'] ?? '') ?></td>
                        <td>R<?= number_format($it['price'] ?? 0, 2) ?></td>
                        <td><?= !empty($it['available']) ? 'Yes' : 'No' ?></td>
                        <td style="text-align:right;">
                            <a class="button button-ghost" href="<?= sanitize(app_url('admin/menu.php')) . '?edit_id=' . sanitize($it['id']) ?>">Edit</a>
                            <form method="post" style="display:inline;margin-left:8px;">
                                <input type="hidden" name="action" value="delete_item">
                                <input type="hidden" name="id" value="<?= sanitize($it['id']) ?>">
                                <button class="button button-ghost" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
