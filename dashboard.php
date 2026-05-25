<?php
require_once __DIR__ . '/includes/functions.php';

// Friendly shortcut that sends each user type to the right dashboard.
if (is_admin()) {
    header('Location: ' . app_url('admin/dashboard.php'));
} else {
    header('Location: ' . app_url('customer/dashboard.php'));
}
exit;
