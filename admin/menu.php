<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$items = load_menu_items();
$pageTitle = 'Menu Management';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Menu Items</h2>
        <p>List of dishes currently available on the menu.</p>
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
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
