<?php
$pageTitle = 'Contact';
require_once 'includes/header.php';
?>
<section class="section-title">
    <h2>Contact Precious Nkomose</h2>
    <p>Reach out to book a table, request a catering quote, or ask about our menu.</p>
</section>
<section class="card">
    <div class="grid" style="display:grid; gap:20px; grid-template-columns: repeat(2, minmax(0,1fr));">
        <div>
            <h3>Restaurant address</h3>
            <p>Durban, KwaZulu-Natal</p>
            <p>Email: info@preciousnkomose.co.za</p>
            <p>Phone: +27 31 563 7890</p>
        </div>
        <div>
            <h3>Operating hours</h3>
            <p>Mon-Thu: 11:00 - 23:00</p>
            <p>Fri-Sat: 11:00 - 00:00</p>
            <p>Sun: 12:00 - 22:00</p>
        </div>
    </div>
    <p style="margin-top:24px; color: var(--muted);">For reservations and special seating requests, use the <a href="reservation.php">reservation page</a>. For catering, visit the <a href="catering.php">catering page</a>.</p>
</section>
<?php require_once 'includes/footer.php';
