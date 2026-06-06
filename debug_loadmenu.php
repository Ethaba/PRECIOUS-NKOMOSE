<?php
require __DIR__ . "/includes/functions.php";
$items = load_menu_items();
echo "count=" . count($items) . "\n";
if (count($items) > 0) {
    print_r($items[0]);
}

