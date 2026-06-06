<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $result = $pdo->query("SHOW DATABASES");
    $dbs = $result->fetchAll(PDO::FETCH_COLUMN);
    echo implode("\n", $dbs);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
