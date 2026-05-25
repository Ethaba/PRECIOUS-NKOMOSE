<?php
require_once 'includes/functions.php';
logout_user();
header('Location: index.php');
exit;
