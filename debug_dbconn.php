<?php
require __DIR__ . "/includes/functions.php";
echo "before db_conn\n";
$pdo = db_conn();
echo "after db_conn\n";
var_dump($pdo);
