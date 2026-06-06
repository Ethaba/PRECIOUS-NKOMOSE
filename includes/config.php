<?php
// Basic site config path. System records are stored in MySQL.
define('DATA_DIR', __DIR__ . '/../data/');

// Ensure session is started early
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Site metadata loaded from data/config.json when needed
function site_config()
{
	static $cfg = null;
	if ($cfg === null) {
		$path = DATA_DIR . 'config.json';
		if (file_exists($path)) {
			$cfg = json_decode(file_get_contents($path), true) ?: [];
		} else {
			$cfg = [];
		}
	}
	return $cfg;
}

// Database configuration (MySQL / phpMyAdmin)
define('DB_ENABLED', true);
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'preciousnkomose');
define('DB_USER', 'root');
define('DB_PASS', '');

function db_conn()
{
	static $pdo = null;
	if ($pdo === null) {
		if (!DB_ENABLED) return null;
		$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
		try {
			$pdo = new PDO($dsn, DB_USER, DB_PASS, [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			]);
		} catch (Exception $e) {
			// Return null so the shared db() helper can show a clear setup message.
			$pdo = null;
		}
	}
	return $pdo;
}
