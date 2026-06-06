<?php
require_once 'includes/functions.php';
if (is_logged_in()) {
    header('Location: ' . app_url('index.php'));
    exit;
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($name === '') {
        $errors[] = 'Please enter your full name.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($phone === '') {
        $errors[] = 'Please enter your phone number.';
    }
    if ($password === '' || strlen($password) < 6) {
        $errors[] = 'Create a password with at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (empty($errors)) {
        $result = register_user($name, $email, $phone, $password);
        if (!empty($result['error'])) {
            $errors[] = $result['error'];
        } else {
            flash_set('Your account was created successfully. Please log in to continue.', 'success');
            header('Location: ' . app_url('login.php'));
            exit;
        }
    }
}
$pageTitle = 'Register';
require_once 'includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Create your account</h2>
        <p>Register to save reservations, request catering, and receive confirmation emails.</p>
    </div>
    <?php if ($errors): ?>
        <div class="alert"><strong>Registration error</strong><ul><?php foreach ($errors as $error): ?><li><?= sanitize($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" class="input-group">
        <input class="input-field" name="name" type="text" placeholder="Full name" autocomplete="name" value="<?= sanitize($_POST['name'] ?? '') ?>" required>
        <input class="input-field" name="email" type="email" placeholder="Email address" autocomplete="email" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
        <input class="input-field" name="phone" type="tel" placeholder="Phone number" autocomplete="tel" value="<?= sanitize($_POST['phone'] ?? '') ?>" required>
        <input class="input-field" name="password" type="password" placeholder="Password" autocomplete="new-password" required>
        <input class="input-field" name="confirm_password" type="password" placeholder="Confirm password" autocomplete="new-password" required>
        <button class="button button-primary primary-action" type="submit">Register</button>
    </form>
    <p style="margin-top:16px; color: var(--muted);">Already have an account? <a href="login.php">Login here</a>.</p>
</section>
<?php require_once 'includes/footer.php';
