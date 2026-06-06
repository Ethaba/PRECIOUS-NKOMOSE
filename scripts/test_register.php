<?php
require_once __DIR__ . '/../includes/functions.php';

// Use a unique email to avoid duplicates
$email = 'cli_test_' . time() . '@example.local';
$name = 'CLI Test User';
$phone = '0000000000';
$password = 'cli_test_pass';

$start = microtime(true);
$result = register_user($name, $email, $phone, $password);
$duration = microtime(true) - $start;

echo "DURATION: " . number_format($duration, 6) . " seconds\n";
echo "RESULT: ";
var_export($result);
echo "\n";

// Print the last lines of the slow_queries.log if it exists
$log = __DIR__ . '/../data/slow_queries.log';
if (file_exists($log)) {
    echo "\n-- slow_queries.log (last 20 lines) --\n";
    $lines = explode("\n", trim(file_get_contents($log)));
    $tail = array_slice($lines, -20);
    echo implode("\n", $tail) . "\n";
} else {
    echo "\nNo slow_queries.log found.\n";
}
