<?php
$pageTitle = 'Home';
require_once 'includes/header.php';
?>
<section class="hero">
    <div class="hero-card">
        <p class="eyebrow">Presenting</p>
        <h1>Precious Nkomose - A modern restaurant with a nostalgic soul.</h1>
        <p>Enjoy South African classics, seafood boil feasts, intimate corner seating, and a premium reservation experience designed for live audiences.</p>
        <div style="display:flex; flex-wrap:wrap; gap:12px; margin-top:28px;">
            <!-- app_url keeps these home buttons correct inside the project folder. -->
            <a class="button button-primary" href="<?= sanitize(app_url('reservation.php')) ?>">Reserve Now</a>
            <a class="button button-ghost" href="<?= sanitize(app_url('menu.php')) ?>">Explore Menu</a>
        </div>
    </div>
    <div class="hero-card">
        <h2>Dining designed for every occasion</h2>
        <p>From elegant seafood boils to South African comfort plates, our online system handles reservations, catering requests, and beautiful table planning.</p>
        <div class="feature-grid" style="margin-top:24px;">
            <div class="feature-card"><h3>Smart Reservations</h3><p>Choose the perfect seating section and complete your booking with a modern flow.</p></div>
            <div class="feature-card"><h3>Catering Quotes</h3><p>Request catering for weddings, business functions, parties, and formal events.</p></div>
            <div class="feature-card"><h3>Admin Control</h3><p>Restaurant staff manage table prep, bookings, and menu items</p></div>
        </div>
    </div>
</section>
<section class="card">
    <div class="section-title">
        <h2>South African flavours with seafood finesse</h2>
        <p>Browse our curated menu and book your table in one smooth experience.</p>
    </div>
    <div class="menu-grid">
        <div class="menu-item"><h4>Seafood Boil</h4><span>Signature</span><p>Large sharing boil with prawns, mussels and more.</p></div>
        <div class="menu-item"><h4>Bobotie Supreme</h4><span>Tradition</span><p>Cape Malay spiced mince with fragrant yellow rice.</p></div>
        <div class="menu-item"><h4>Lobster Tail</h4><span>Premium</span><p>Garlic butter lobster from the West Coast.</p></div>
        <div class="menu-item"><h4>Boerewors & Pap</h4><span>Comfort</span><p>Hearty sausage and creamy pap with chakalaka.</p></div>
    </div>
</section>
<section class="card">
    <div class="section-title">
        <h2>How it works</h2>
        <p>Book the section you want, select guest count, add seating instructions, and confirm your reservation.</p>
    </div>
    <div class="feature-grid">
        <div class="feature-card"><h3>Log in or register</h3><p>Create an account to save your booking history and receive email confirmations.</p></div>
        <div class="feature-card"><h3>Select your seat</h3><p>Pick your preferred section from our interactive restaurant layout.</p></div>
        <div class="feature-card"><h3>Confirm with confidence</h3><p>Receive a confirmation screen and match it with the email sent to your inbox.</p></div>
    </div>
</section>
<?php require_once 'includes/footer.php';
