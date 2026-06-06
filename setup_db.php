<?php
/**
 * Database setup script to import preciousnkomose.sql
 * Run this once to initialize the database, then delete this file.
 */

try {
    $sqlFile = __DIR__ . '/database/preciousnkomose.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception('SQL file not found at: ' . $sqlFile);
    }

    $mysqli = new mysqli('127.0.0.1', 'root', '', '', 3306);
    if ($mysqli->connect_errno) {
        throw new Exception('MySQL connection failed: ' . $mysqli->connect_error);
    }

    $mysqli->set_charset('utf8mb4');
    $sql = file_get_contents($sqlFile);

    if (!$mysqli->multi_query($sql)) {
        throw new Exception('Import failed: ' . $mysqli->error);
    }

    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());

    echo "<div style='padding:20px; background:#e6f3e6; border:1px solid #4caf50; margin:20px; border-radius:5px;'>";
    echo "<h2 style='color:#2e7d32; margin-top:0;'>✅ Database Setup Complete!</h2>";
    echo "<p>The preciousnkomose database has been created and populated successfully.</p>";
    echo "<p><strong>Admin Login:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> admin@localhost</li>";
    echo "<li><strong>Password:</strong> admin123</li>";
    echo "</ul>";
    echo "<p><a href='http://localhost/PRECIOUS-NKOMOSE-master/admin.php' style='display:inline-block; padding:10px 20px; background:#ff6a3d; color:white; text-decoration:none; border-radius:4px;'>Go to Admin Dashboard</a></p>";
    echo "<p style='color:#666; font-size:12px; margin-bottom:0;'>You can now delete this file (setup_db.php)</p>";
    echo "</div>";
} catch (Throwable $e) {
    echo "<div style='padding:20px; background:#ffebee; border:1px solid #c62828; margin:20px; border-radius:5px;'>";
    echo "<h2 style='color:#b71c1c; margin-top:0;'>❌ Database Setup Failed</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please ensure MySQL is running and try again.</p>";
    echo "</div>";
    exit(1);
}
