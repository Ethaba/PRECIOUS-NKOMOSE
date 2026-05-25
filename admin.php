<?php
require_once __DIR__ . '/includes/functions.php';

// Friendly shortcut so /admin.php opens the real admin dashboard.
header('Location: ' . app_url('admin/dashboard.php'));
exit;
