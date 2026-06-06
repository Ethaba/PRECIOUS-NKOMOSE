<?php
$pageTitle = 'Database Setup';
require_once __DIR__ . '/includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Database setup</h2>
        <p>This project now uses MySQL only. Import the SQL file below before using the system.</p>
    </div>
    <div class="alert">
        <strong>Import this file in phpMyAdmin:</strong>
        <p><code>database/preciousnkomose.sql</code></p>
    </div>
    <ol>
        <li>Start Apache and MySQL in XAMPP.</li>
        <li>Open <code>http://localhost/phpmyadmin</code>.</li>
        <li>Click <strong>Import</strong>.</li>
        <li>Select <code>C:\xampp\htdocs\PRECIOUSNKOMOSE\database\preciousnkomose.sql</code>.</li>
        <li>Click <strong>Go</strong>.</li>
        <li>Open <code>http://localhost/PRECIOUSNKOMOSE</code>.</li>
    </ol>
    <p>Default admin login: <code>admin@localhost</code> / <code>admin123</code></p>
</section>
<?php require_once __DIR__ . '/includes/footer.php';
