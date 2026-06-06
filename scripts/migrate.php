<?php
// Simple migration runner for Precious Nkomose
// Usage: php scripts/migrate.php

require_once __DIR__ . '/../includes/config.php';
$pdo = db_conn();
if (!$pdo) {
    echo "DB connection not available. Check config.php\n";
    exit(1);
}

// Ensure migrations table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (filename)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$migrationDir = __DIR__ . '/../database/migrations';
$files = array_values(array_filter(scandir($migrationDir), function($f){ return preg_match('/^\d+_.*\.sql$/', $f); }));
sort($files);
// Security: prevent directory traversal and ensure files exist
$files = array_filter($files, function($f){ return is_file($migrationDir . '/' . $f) && strpos($f, '..') === false; });


foreach ($files as $file) {
    $stmt = $pdo->prepare('SELECT filename FROM migrations WHERE filename = ? LIMIT 1');
    $stmt->execute([$file]);
    $exists = $stmt->fetch();
    if ($exists) {
        echo "Skipping already applied migration: {$file}\n";
        continue;
    }

    echo "Applying migration: {$file}...\n";
    $sql = file_get_contents($migrationDir . '/' . $file);
    try {
        $pdo->beginTransaction();
        $pdo->exec($sql);
        $insert = $pdo->prepare('INSERT INTO migrations (filename) VALUES (?)');
        $insert->execute([$file]);
        $pdo->commit();
        echo "Applied: {$file}\n";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Failed to apply {$file}: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "Migrations complete.\n";
