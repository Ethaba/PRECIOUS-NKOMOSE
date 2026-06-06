<?php
require_once 'includes/functions.php';
if (is_logged_in()) {
    header('Location: ' . app_url('index.php'));
    exit;
}
$errors = [];
$flash = flash_get();
$showLoginSuccess = !empty($_SESSION['login_success']);
unset($_SESSION['login_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $result = login_user($email, $password);
    if (!empty($result['error'])) {
        $errors[] = $result['error'];
    } else {
        $_SESSION['login_success'] = true;
        $showLoginSuccess = true;
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
    <?php if ($flash): ?>
        <div class="alert"><strong><?= sanitize($flash['type'] === 'success' ? 'Success' : 'Notice') ?></strong> <?= sanitize($flash['message']) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert"><strong>Login error</strong><ul><?php foreach ($errors as $error): ?><li><?= sanitize($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" class="input-group">
        <input class="input-field" type="email" name="email" placeholder="Email address" autocomplete="email" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
        <input class="input-field" type="password" name="password" placeholder="Password" autocomplete="current-password" required>
        <button class="button button-primary primary-action" type="submit">Login</button>
    </form>
    <p style="margin-top:16px; color: var(--muted);">New here? <a href="register.php">Create an account</a>.</p>
</section>
<?php if ($showLoginSuccess): ?>
    <div class="modal-overlay" id="loginSuccessModal">
        <div class="modal-card">
            <div class="success-badge"><i class="fa-solid fa-check"></i></div>
            <h2>Thank you</h2>
            <p>You have successfully logged in.</p>
            <button type="button" class="button button-primary" id="dismissLoginSuccess">Continue to home</button>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var button = document.getElementById('dismissLoginSuccess');
            if (!button) return;
            button.addEventListener('click', function () {
                window.location.href = <?= json_encode(app_url('index.php')) ?>;
            });
        });
    </script>
<?php endif; ?>
<?php require_once 'includes/footer.php';
