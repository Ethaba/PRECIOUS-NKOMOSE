<?php
require_once 'includes/functions.php';
$menuItems = load_menu_items();
$categories = menu_categories($menuItems);
$pageTitle = 'Menu';
require_once 'includes/header.php';
?>
<section class="section-title">
    <h2>Our menu</h2>
    <p>Feast on the finest South African cuisine and seafood boil experiences. Each dish is styled with a modern edge and nostalgic flavour.</p>
</section>
<?php foreach ($categories as $category => $items): ?>
    <section class="card" style="margin-top:20px;">
        <div class="section-title"><h3><?= sanitize($category) ?></h3></div>
        <div class="menu-grid">
            <?php foreach ($items as $item): ?>
                <article class="menu-item<?= !empty($item['popular']) ? ' popular' : '' ?>">
                    <?php if (!empty($item['popular'])): ?>
                        <span class="popular-ribbon">Popular</span>
                    <?php endif; ?>
                    <div class="menu-image">
                        <img src="<?= sanitize(menu_image_url($item['image'] ?? '')) ?>" alt="<?= sanitize($item['name']) ?>">
                    </div>
                    <div class="menu-content">
                        <h4><?= sanitize($item['name']) ?></h4>
                        <span class="menu-price">R <?= number_format($item['price'], 2) ?></span>
                        <p><?= sanitize($item['description']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>
<?php require_once 'includes/footer.php';
