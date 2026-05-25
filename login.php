<?php
require_once 'includes/functions.php';
if (is_logged_in()) {
    // Logged-in users should land on the correct dashboard for their role.
    header('Location: ' . (is_admin() ? app_url('admin/dashboard.php') : app_url('customer/dashboard.php')));
    exit;
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $result = login_user($email, $password);
    if (!empty($result['error'])) {
        $errors[] = $result['error'];
    } else {
        // Use full app URLs so redirects work under /PRECIOUSNKOMOSE/.
        if (is_admin()) {
            header('Location: ' . app_url('admin/dashboard.php'));
        } else {
            header('Location: ' . app_url('customer/dashboard.php'));
        }
        exit;
    }
}
$pageTitle = 'Login';
require_once 'includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Login to your account</h2>
        <p>Access reservations, catering requests, and your booking history.</p>
    </div>
    <?php if ($errors): ?>
        <div class="alert"><strong>Login error</strong><ul><?php foreach ($errors as $error): ?><li><?= sanitize($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" class="input-group">
        <input class="input-field" type="email" name="email" placeholder="Email address" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
        <input class="input-field" type="password" name="password" placeholder="Password" required>
        <button class="button button-primary primary-action" type="submit">Login</button>
    </form>
    <p style="margin-top:16px; color: var(--muted);">New here? <a href="register.php">Create an account</a>.</p>
</section>
<?php require_once 'includes/footer.php';
