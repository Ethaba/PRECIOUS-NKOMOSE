<?php
require __DIR__ . "/includes/functions.php";
echo "functions loaded\n";
$items = load_menu_items();
echo "after load\n";
echo "count=" . count($items) . "\n";
