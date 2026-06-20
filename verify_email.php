<?php
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        ensure_customer_verification_columns();
        $customer = db_one('SELECT id,name,email FROM customers WHERE email = ? AND email_verified = 0', [$email]);
        if ($customer) {
            if (send_verification_email($customer['id'], $customer['email'], $customer['name'])) {
                $success = 'A new verification email has been sent.';
            } else {
                $errors[] = 'Unable to send the verification email right now.';
            }
        } else {
            $errors[] = 'No unverified account was found for that email.';
        }
    }
} elseif (!empty($_GET['token'])) {
    $result = verify_email_token(trim($_GET['token']));
    if (!empty($result['success'])) {
        $success = 'Your email address has been verified successfully.';
    } else {
        $errors[] = $result['error'] ?? 'Unable to verify your email address.';
    }
}

$pageTitle = 'Verify Email';
require_once __DIR__ . '/includes/header.php';
?>
<section class="card">
    <div class="section-title">
        <h2>Verify your email</h2>
        <p>Use the link from your confirmation email, or request a new one here.</p>
    </div>
    <?php if ($success): ?>
        <div class="confirmation-panel"><div class="success-badge"><i class="fa-solid fa-check"></i></div><h2>Verified</h2><p><?= sanitize($success) ?></p></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert"><strong>Please address the following</strong><ul><?php foreach ($errors as $error): ?><li><?= sanitize($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" class="input-group">
        <input class="input-field" type="email" name="email" placeholder="Email address" required>
        <button class="button button-primary" type="submit">Resend verification email</button>
    </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php';
