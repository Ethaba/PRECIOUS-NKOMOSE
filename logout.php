<?php
require_once 'includes/functions.php';
logout_user();
header('Location: ' . app_url('login.php'));
exit;
