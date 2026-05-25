<?php
require_once __DIR__ . '/config.php';

// Central database guard. The app now requires MySQL instead of falling back to JSON files.
function db(): PDO
{
    $pdo = db_conn();
    if (!$pdo) {
        die('Database connection failed. Import database/preciousnkomose.sql in phpMyAdmin and confirm MySQL is running.');
    }
    return $pdo;
}

// Fetch multiple rows from MySQL using prepared statements.
function db_all(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Fetch one row from MySQL using prepared statements.
function db_one(string $sql, array $params = [])
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() ?: null;
}

// Run INSERT, UPDATE, or DELETE statements through MySQL.
function db_execute(string $sql, array $params = []): bool
{
    $stmt = db()->prepare($sql);
    return $stmt->execute($params);
}

// Generates readable IDs such as U0001, R001, RV001, and CR001 from existing table records.
function next_id(string $table, string $prefix, int $length = 4): string
{
    $row = db_one("SELECT id FROM {$table} WHERE id LIKE ? ORDER BY id DESC LIMIT 1", [$prefix . '%']);
    $lastNumber = 0;
    if ($row && preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $row['id'], $matches)) {
        $lastNumber = (int)$matches[1];
    }
    return $prefix . str_pad((string)($lastNumber + 1), $length, '0', STR_PAD_LEFT);
}

function sanitize($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Calculate the app root so links work from root, admin, and customer folders.
function app_base_url(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $dir = str_replace('\\', '/', dirname($scriptName));

    if (preg_match('#/(admin|customer)$#', $dir)) {
        $dir = dirname($dir);
    }

    if ($dir === '.' || $dir === '/' || $dir === '\\') {
        return '/';
    }

    return rtrim($dir, '/') . '/';
}

function app_url(string $path = ''): string
{
    return app_base_url() . ltrim($path, '/');
}

// Menu helpers
function load_menu_items(): array
{
    // Menu items are now loaded from the menu table instead of data/menu.json.
    return db_all('SELECT id,name,description,category,price,available,image FROM menu ORDER BY category,name');
}

function menu_categories(array $items): array
{
    $groups = [];
    foreach ($items as $i) {
        $cat = $i['category'] ?? 'Uncategorized';
        $groups[$cat][] = $i;
    }
    return $groups;
}

// Authentication helpers
function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function current_user()
{
    return $_SESSION['user'] ?? null;
}

function require_login()
{
    if (!is_logged_in()) {
        // Send users to the root login page from any subfolder.
        header('Location: ' . app_url('login.php'));
        exit;
    }
}

function is_admin(): bool
{
    $u = current_user();
    return !empty($u['is_admin']);
}

function require_admin()
{
    if (!is_logged_in() || !is_admin()) {
        // Avoid broken ../login.php redirects from admin pages.
        header('Location: ' . app_url('login.php'));
        exit;
    }
}

// User management
function register_user(string $name, string $email, string $phone, string $password): array
{
    // Registration now checks and saves customer accounts in the customers table.
    $existing = db_one('SELECT id FROM customers WHERE LOWER(email) = LOWER(?)', [$email]);
    if ($existing) {
        return ['error' => 'An account with that email already exists.'];
    }

    $id = next_id('customers', 'U', 4);
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $saved = db_execute(
        'INSERT INTO customers (id,name,email,phone,password,is_admin,created_at) VALUES (?,?,?,?,?,0,NOW())',
        [$id, $name, $email, $phone, $hash]
    );

    if (!$saved) {
        return ['error' => 'Unable to save user data.'];
    }

    return ['id' => $id];
}

function login_user(string $email, string $password): array
{
    // Login now reads the stored password hash from MySQL.
    $user = db_one('SELECT id,name,email,phone,password,is_admin,created_at FROM customers WHERE LOWER(email) = LOWER(?)', [$email]);
    if (!$user || !isset($user['password']) || !password_verify($password, $user['password'])) {
        return ['error' => 'Invalid email or password.'];
    }

    unset($user['password']);
    $_SESSION['user'] = $user;
    return [];
}

function logout_user()
{
    unset($_SESSION['user']);
}

function get_customers(): array
{
    // Admin customer records come directly from MySQL.
    return db_all('SELECT id,name,email,phone,is_admin,created_at FROM customers ORDER BY created_at DESC');
}

// Reservation helpers
function get_reservations(): array
{
    // Admin reservation lists come from the reservations table.
    return db_all('SELECT * FROM reservations ORDER BY date DESC,time DESC,created_at DESC');
}

function get_customer_reservations(string $customerId): array
{
    // Customer dashboards only show reservations that belong to the logged-in customer.
    return db_all('SELECT * FROM reservations WHERE customer_id = ? ORDER BY date DESC,time DESC,created_at DESC', [$customerId]);
}

function reservation_slot_taken(string $sectionId, string $date, string $time): bool
{
    // Prevent double bookings for the same section, date, and time unless the old booking was cancelled.
    $row = db_one(
        "SELECT id FROM reservations WHERE preferred_section = ? AND date = ? AND time = ? AND status <> 'Cancelled' LIMIT 1",
        [$sectionId, $date, $time]
    );
    return (bool)$row;
}

function create_reservation(array $reservation): bool
{
    // New reservations are inserted into MySQL instead of being appended to a JSON file.
    $id = next_id('reservations', 'R', 3);
    return db_execute(
        'INSERT INTO reservations (id,customer_id,customer_name,customer_email,preferred_section,section_label,capacity,date,time,guests,notes,status,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW())',
        [
            $id,
            $reservation['customer_id'],
            $reservation['customer_name'],
            $reservation['customer_email'],
            $reservation['preferred_section'],
            $reservation['section_label'],
            $reservation['capacity'],
            $reservation['date'],
            $reservation['time'],
            $reservation['guests'],
            $reservation['notes'],
            $reservation['status'],
        ]
    );
}

function cancel_customer_reservation(string $reservationId, string $customerId): bool
{
    // Customer cancellation updates the reservation status instead of deleting the record.
    return db_execute(
        "UPDATE reservations SET status = 'Cancelled' WHERE id = ? AND customer_id = ?",
        [$reservationId, $customerId]
    );
}

// Review helpers
function get_reviews(): array
{
    // Reviews are loaded from the reviews table.
    return db_all('SELECT * FROM reviews ORDER BY date DESC,id DESC');
}

function create_review(string $customerId, string $name, int $rating, string $comment): bool
{
    // Submitted reviews are stored in MySQL.
    return db_execute(
        'INSERT INTO reviews (id,customer_id,name,rating,comment,date) VALUES (?,?,?,?,?,CURDATE())',
        [next_id('reviews', 'RV', 3), $customerId, $name, $rating, $comment]
    );
}

// Catering helpers
function get_catering_requests(): array
{
    // Catering request lists are loaded from the catering_requests table.
    return db_all('SELECT * FROM catering_requests ORDER BY created_at DESC');
}

function create_catering_request(array $request): bool
{
    // Catering quote requests are stored in MySQL.
    return db_execute(
        'INSERT INTO catering_requests (id,customer_id,customer_name,customer_email,event_type,event_date,guest_count,location,notes,status,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())',
        [
            next_id('catering_requests', 'CR', 3),
            $request['customer_id'],
            $request['customer_name'],
            $request['customer_email'],
            $request['event_type'],
            $request['event_date'],
            $request['guest_count'],
            $request['location'],
            $request['notes'],
            $request['status'],
        ]
    );
}

// Email logging / system emails
function send_system_email(string $to, string $subject, string $body): bool
{
    // The project logs system emails in MySQL; it does not send real email yet.
    return db_execute(
        'INSERT INTO emails (id,recipient,subject,body,sent_at) VALUES (?,?,?,?,NOW())',
        [next_id('emails', 'E', 4), $to, $subject, $body]
    );
}
