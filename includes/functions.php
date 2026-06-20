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

function bind_statement_params(PDOStatement $stmt, array $params): void
{
    foreach ($params as $key => $value) {
        if (is_int($key)) {
            $key = $key + 1; // positional placeholders are 1-based
        }

        $type = PDO::PARAM_STR;
        if (is_int($value)) {
            $type = PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            $type = PDO::PARAM_BOOL;
        } elseif (is_null($value)) {
            $type = PDO::PARAM_NULL;
        }

        $stmt->bindValue($key, $value, $type);
    }
}

// Fetch multiple rows from MySQL using prepared statements.
function db_all(string $sql, array $params = []): array
{
    $start = microtime(true);
    $stmt = db()->prepare($sql);
    bind_statement_params($stmt, $params);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    $duration = microtime(true) - $start;
    log_db_timing($sql, $params, $duration);
    return $rows;
}

// Fetch one row from MySQL using prepared statements.
function db_one(string $sql, array $params = [])
{
    $start = microtime(true);
    $stmt = db()->prepare($sql);
    bind_statement_params($stmt, $params);
    $stmt->execute();
    $row = $stmt->fetch() ?: null;
    $duration = microtime(true) - $start;
    log_db_timing($sql, $params, $duration);
    return $row;
}

// Run INSERT, UPDATE, or DELETE statements through MySQL.
function db_execute(string $sql, array $params = []): bool
{
    $start = microtime(true);
    $stmt = db()->prepare($sql);
    bind_statement_params($stmt, $params);
    $ok = $stmt->execute();
    $duration = microtime(true) - $start;
    log_db_timing($sql, $params, $duration);
    return $ok;
}

// Lightweight DB timing logger for diagnosing slow queries during registration/login.
function log_db_timing(string $sql, array $params, float $duration): void
{
    // Only log when DB is enabled and data directory exists.
    $logPath = __DIR__ . '/../data/slow_queries.log';
    $msg = sprintf("%s | %.6f sec | %s | params=%s\n", date('c'), $duration, str_replace("\n", ' ', trim($sql)), json_encode($params));
    // Append to log; ignore failures.
    try {
        error_log($msg, 3, $logPath);
    } catch (Exception $e) {
        // ignore
    }
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

function table_has_column(string $table, string $column): bool
{
    $rows = db_all('SHOW COLUMNS FROM ' . $table);
    foreach ($rows as $row) {
        if (($row['Field'] ?? '') === $column) {
            return true;
        }
    }
    return false;
}

function ensure_customer_verification_columns(): void
{
    if (!table_has_column('customers', 'email_verified')) {
        db_execute('ALTER TABLE customers ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0');
    }
    if (!table_has_column('customers', 'verification_token')) {
        db_execute('ALTER TABLE customers ADD COLUMN verification_token VARCHAR(255) DEFAULT NULL');
    }
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
    // Prefer the menu table, but fall back to data/menu.json when MySQL is unavailable.
    try {
        $pdo = db_conn();
        if ($pdo) {
            $stmt = $pdo->prepare('SELECT id,name,description,category,price,available,image,popular FROM menu ORDER BY category,name');
            $stmt->execute();
            $rows = $stmt->fetchAll();
            if (!empty($rows)) {
                return $rows;
            }
        }
    } catch (Exception $e) {
        // swallow and fallback to JSON file
    }

    $jsonPath = __DIR__ . '/../data/menu.json';
    if (file_exists($jsonPath)) {
        $raw = file_get_contents($jsonPath);
        $data = json_decode($raw, true);
        if (is_array($data)) {
            return $data;
        }
    }

    return [];
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

function menu_image_assets(): array
{
    static $assets = null;
    if ($assets === null) {
        $assets = [];
        $basePath = __DIR__ . '/../assets/images';
        if (is_dir($basePath)) {
            foreach (scandir($basePath) as $file) {
                if ($file === '.' || $file === '..' || !is_file($basePath . '/' . $file)) {
                    continue;
                }
                $assets[strtolower($file)] = $file;
            }
        }
    }

    return $assets;
}

function menu_image_file(string $image): string
{
    $image = trim((string)$image);
    if ($image === '') {
        return 'menu-placeholder.svg';
    }

    $assets = menu_image_assets();
    $normalized = strtolower($image);
    if (isset($assets[$normalized])) {
        return $assets[$normalized];
    }

    $aliases = [
        'kingklip.jpg' => 'Fillet.jpg',
        'boerewors.jpg' => 'wors&pap.jpg',
        'potjiekos.jpg' => 'stew.jpg',
        'malva.jpg' => 'malvaPudding.jpg',
        'wine.jpg' => 'homemadeWine.jpg',
        'seven_colours.jpg' => 'sevenColours.jpg',
        'seafood_share.jpg' => 'seafoodboil.jpg',
        'bread_beans.jpg' => 'steamBreadBeans.jpg',
        'craftbeer.jpg' => 'beer.jpg',
        'craftbeer.jpg' => 'beer.jpg',
        'chocoFlavouredSmoothie.jpg' => 'chocSmoothie.jpg',
        'chocolateflavouredsmoothie.jpg' => 'chocSmoothie.jpg',
        'chocosmoothie.jpg' => 'chocSmoothie.jpg',
        'biltong_salad.jpg' => 'menu-placeholder.svg',
        'calamari.jpg' => 'menu-placeholder.svg',
    ];

    if (isset($aliases[$normalized]) && isset($assets[strtolower($aliases[$normalized])])) {
        return $aliases[$normalized];
    }

    return 'menu-placeholder.svg';
}

function menu_image_url(string $image): string
{
    return app_url('assets/images/' . menu_image_file($image));
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

function flash_set(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function flash_get(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// User management
function register_user(string $name, string $email, string $phone, string $password): array
{
    // Log entry to help diagnose blocking or slow registration calls.
    $log = __DIR__ . '/../data/register_debug.log';
    $start = microtime(true);
    @error_log(sprintf("%s | ENTER register_user email=%s\n", date('c'), $email), 3, $log);

    try {
        ensure_customer_verification_columns();

        // Registration now checks and saves customer accounts in the customers table.
        $existing = db_one('SELECT id FROM customers WHERE email = ?', [$email]);
        if ($existing) {
            @error_log(sprintf("%s | EXIT register_user - exists\n", date('c')), 3, $log);
            return ['error' => 'An account with that email already exists.'];
        }

        $id = next_id('customers', 'U', 4);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $saved = db_execute(
            'INSERT INTO customers (id,name,email,phone,password,is_admin,email_verified,verification_token,created_at) VALUES (?,?,?,?,?,0,0,NULL,NOW())',
            [$id, $name, $email, $phone, $hash]
        );

        if (!$saved) {
            @error_log(sprintf("%s | EXIT register_user - save_failed\n", date('c')), 3, $log);
            return ['error' => 'Unable to save user data.'];
        }

        send_verification_email($id, $email, $name);

        $duration = microtime(true) - $start;
        @error_log(sprintf("%s | EXIT register_user - success duration=%.6f\n", date('c'), $duration), 3, $log);
        return ['id' => $id];
    } catch (Exception $e) {
        @error_log(sprintf("%s | EXCEPTION register_user: %s\n", date('c'), $e->getMessage()), 3, $log);
        return ['error' => 'An unexpected error occurred during registration.'];
    }
}

function generate_verification_token(): string
{
    return bin2hex(random_bytes(32));
}

function send_verification_email(string $customerId, string $email, string $name): bool
{
    ensure_customer_verification_columns();
    $token = generate_verification_token();
    $updated = db_execute(
        'UPDATE customers SET verification_token = ? WHERE id = ?',
        [$token, $customerId]
    );

    if (!$updated) {
        return false;
    }

    $verifyUrl = app_url('verify_email.php?token=' . urlencode($token));
    $body = "Hello {$name},\n\nPlease verify your email address by visiting:\n{$verifyUrl}\n\nIf you did not create this account, you can ignore this message.\n\nRegards,\nPrecious Nkomose";
    return send_system_email($email, 'Verify your email address', $body);
}

function verify_email_token(string $token): array
{
    ensure_customer_verification_columns();

    if (!table_has_column('customers', 'email_verified') || !table_has_column('customers', 'verification_token')) {
        return ['error' => 'Database verification columns are not available yet.'];
    }

    $customer = db_one(
        'SELECT id,name,email FROM customers WHERE verification_token = ? AND email_verified = 0',
        [$token]
    );

    if (!$customer) {
        return ['error' => 'This verification link is invalid or has already been used.'];
    }

    $ok = db_execute(
        'UPDATE customers SET email_verified = 1, verification_token = NULL WHERE id = ?',
        [$customer['id']]
    );

    if (!$ok) {
        return ['error' => 'Unable to verify your email address right now.'];
    }

    return ['success' => true, 'customer' => $customer];
}

function login_user(string $email, string $password): array
{
    // Login now reads the stored password hash from MySQL.
    // Keep the query compatible with older databases that do not yet have verification columns.
    $user = db_one(
        'SELECT id,name,email,phone,password,is_admin,created_at FROM customers WHERE email = ?',
        [$email]
    );
    if (!$user || !isset($user['password']) || !password_verify($password, $user['password'])) {
        return ['error' => 'Invalid email or password.'];
    }

    unset($user['password']);
    $_SESSION['user'] = $user;
    return [];
}

function get_customer_by_id(string $customerId)
{
    return db_one(
        'SELECT id,name,email,phone,is_admin,created_at FROM customers WHERE id = ?',
        [$customerId]
    );
}

function update_customer_profile(string $customerId, string $name, string $phone, string $email): bool
{
    ensure_customer_verification_columns();

    $existing = db_one(
        'SELECT id FROM customers WHERE email = ? AND id <> ?',
        [$email, $customerId]
    );
    if ($existing) {
        return false;
    }

    $updated = db_execute(
        'UPDATE customers SET name = ?, phone = ?, email = ?, email_verified = 0, verification_token = NULL WHERE id = ?',
        [$name, $phone, $email, $customerId]
    );

    if ($updated) {
        send_verification_email($customerId, $email, $name);
    }

    return $updated;
}

function update_customer_password(string $customerId, string $currentPassword, string $newPassword): array
{
    $user = db_one('SELECT password FROM customers WHERE id = ?', [$customerId]);
    if (!$user || !password_verify($currentPassword, $user['password'])) {
        return ['error' => 'Your current password is incorrect.'];
    }

    if (strlen($newPassword) < 6) {
        return ['error' => 'New password must be at least 6 characters long.'];
    }

    if (!db_execute('UPDATE customers SET password = ? WHERE id = ?', [password_hash($newPassword, PASSWORD_DEFAULT), $customerId])) {
        return ['error' => 'Unable to update your password right now.'];
    }

    return ['success' => true];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
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

function update_reservation(string $reservationId, array $data): bool
{
    $allowed = ['status', 'date', 'time', 'guests', 'notes', 'preferred_section', 'section_label', 'capacity'];
    foreach ($data as $key => $value) {
        if (!in_array($key, $allowed, true)) {
            unset($data[$key]);
        }
    }

    return db_execute(
        'UPDATE reservations SET status = ?, date = ?, time = ?, guests = ?, notes = ?, preferred_section = ?, section_label = ?, capacity = ? WHERE id = ?',
        [
            $data['status'] ?? 'Confirmed',
            $data['date'] ?? '',
            $data['time'] ?? '00:00:00',
            (int)($data['guests'] ?? 0),
            $data['notes'] ?? '',
            $data['preferred_section'] ?? '',
            $data['section_label'] ?? '',
            (int)($data['capacity'] ?? 0),
            $reservationId,
        ]
    );
}

function delete_reservation(string $reservationId): bool
{
    return db_execute('DELETE FROM reservations WHERE id = ?', [$reservationId]);
}

function reservation_slot_taken(string $sectionId, string $date, string $time): bool
{
    // Prevent double bookings at the same date/time.
    // "Whole venue" bookings block every section, and individual section bookings block a full-venue slot.
    if ($sectionId === 'whole-venue') {
        $row = db_one(
            "SELECT id FROM reservations WHERE date = ? AND time = ? AND status <> 'Cancelled' LIMIT 1",
            [$date, $time]
        );
    } else {
        $row = db_one(
            "SELECT id FROM reservations WHERE date = ? AND time = ? AND status <> 'Cancelled' AND (preferred_section = ? OR preferred_section = 'whole-venue') LIMIT 1",
            [$date, $time, $sectionId]
        );
    }
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

// --- Menu management (admin) ---
function create_menu_item(array $item): bool
{
    return db_execute(
        'INSERT INTO menu (id,name,description,category,price,available,image,popular) VALUES (?,?,?,?,?,?,?,?)',
        [next_id('menu', 'M', 3), $item['name'], $item['description'] ?? '', $item['category'] ?? 'Uncategorized', $item['price'] ?? 0, $item['available'] ? 1 : 0, $item['image'] ?? '', $item['popular'] ? 1 : 0]
    );
}

function update_menu_item(string $id, array $item): bool
{
    return db_execute(
        'UPDATE menu SET name = ?, description = ?, category = ?, price = ?, available = ?, image = ?, popular = ? WHERE id = ?',
        [$item['name'], $item['description'] ?? '', $item['category'] ?? 'Uncategorized', $item['price'] ?? 0, $item['available'] ? 1 : 0, $item['image'] ?? '', $item['popular'] ? 1 : 0, $id]
    );
}

function delete_menu_item(string $id): bool
{
    return db_execute('DELETE FROM menu WHERE id = ?', [$id]);
}

// --- Catering admin actions ---
function send_catering_quote(string $requestId, string $quoteText): bool
{
    // Store quote in dedicated `quote` column and set status to Quoted and record sent time.
    $ok = db_execute("UPDATE catering_requests SET status = 'Quoted', quote = ?, quote_sent_at = NOW() WHERE id = ?", [$quoteText, $requestId]);
    if ($ok) {
        $req = db_one('SELECT customer_email, customer_name FROM catering_requests WHERE id = ?', [$requestId]);
        if ($req && !empty($req['customer_email'])) {
            $to = $req['customer_email'];
            $name = $req['customer_name'] ?? 'Customer';
            $site = site_config();
            $restaurant = $site['restaurant']['name'] ?? 'Precious Nkomose';
            send_templated_email($to, 'Catering Quote from ' . $restaurant, 'catering_quote', [
                'name' => $name,
                'restaurant' => $restaurant,
                'quote' => $quoteText,
            ]);
        }
    }
    return (bool)$ok;
}

function compose_catering_quote_email(string $name, string $quoteText): string
{
    $site = site_config();
    $restaurant = $site['restaurant']['name'] ?? 'Precious Nkomose';
    $body = "Dear {$name},\n\nThanks for requesting catering from {$restaurant}.\n\nWe have prepared the following quote for your event:\n\n" . trim($quoteText) . "\n\nIf you are happy with this quote, please log into your account and accept or decline the quote in your Catering Requests area.\n\nRegards,\n{$restaurant}";
    return $body;
}

function mark_catering_completed(string $requestId): bool
{
    return db_execute("UPDATE catering_requests SET status = 'Completed' WHERE id = ?", [$requestId]);
}

function get_customer_catering_requests(string $customerId): array
{
    return db_all('SELECT * FROM catering_requests WHERE customer_id = ? ORDER BY created_at DESC', [$customerId]);
}

function respond_catering_quote(string $requestId, string $customerId, string $response): bool
{
    $valid = in_array($response, ['Accepted', 'Declined'], true);
    if (!$valid) return false;
    return db_execute('UPDATE catering_requests SET status = ? WHERE id = ? AND customer_id = ?', [$response, $requestId, $customerId]);
}

// --- Section availability status storage (simple JSON file) ---
function section_status_file(): string
{
    return __DIR__ . '/../data/section_status.json';
}

function load_section_statuses(): array
{
    $path = section_status_file();
    if (!file_exists($path)) return [];
    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function save_section_statuses(array $data): bool
{
    $path = section_status_file();
    $dir = dirname($path);
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    return (bool)file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
}

function get_section_status(string $sectionId): string
{
    $all = load_section_statuses();
    return $all[$sectionId] ?? 'available';
}

function set_section_status(string $sectionId, string $status): bool
{
    $all = load_section_statuses();
    $all[$sectionId] = $status;
    return save_section_statuses($all);
}

function cancel_customer_reservation(string $reservationId, string $customerId): bool
{
    // Customer cancellation updates the reservation status instead of deleting the record.
    return db_execute(
        "UPDATE reservations SET status = 'Cancelled' WHERE id = ? AND customer_id = ?",
        [$reservationId, $customerId]
    );
}

function create_table_entry(array $table): bool
{
    return db_execute(
        'INSERT INTO restaurant_tables (id,label,capacity,description,is_active) VALUES (?,?,?,?,?)',
        [
            $table['id'] ?? next_id('restaurant_tables', 'T', 3),
            $table['label'] ?? '',
            (int)($table['capacity'] ?? 0),
            $table['description'] ?? '',
            !empty($table['is_active']) ? 1 : 0,
        ]
    );
}

function update_table_entry(string $id, array $table): bool
{
    return db_execute(
        'UPDATE restaurant_tables SET label = ?, capacity = ?, description = ?, is_active = ? WHERE id = ?',
        [
            $table['label'] ?? '',
            (int)($table['capacity'] ?? 0),
            $table['description'] ?? '',
            !empty($table['is_active']) ? 1 : 0,
            $id,
        ]
    );
}

function delete_table_entry(string $id): bool
{
    return db_execute('DELETE FROM restaurant_tables WHERE id = ?', [$id]);
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
    // Log the message in the database for audit purposes.
    $inserted = db_execute(
        'INSERT INTO emails (id,recipient,subject,body,sent_at) VALUES (?,?,?,?,NOW())',
        [next_id('emails', 'E', 4), $to, $subject, $body]
    );

    // Try a real outbound send when PHP mail is available.
    $headers = "MIME-Version: 1.0\r\n" .
        "Content-type: text/plain; charset=UTF-8\r\n" .
        "From: no-reply@preciousnkomose.local\r\n" .
        "X-Mailer: PHP/" . phpversion();

    @mail($to, $subject, $body, $headers);

    return (bool)$inserted;
}

// Simple templated email sender wrapper (uses send_system_email for now)
function send_templated_email(string $to, string $subject, string $templateName, array $vars = []): bool
{
    // Basic templating: replace {{key}} with values
    $templatePath = __DIR__ . '/../data/email_templates/' . $templateName . '.txt';
    if (!file_exists($templatePath)) {
        // fallback to raw vars
        $body = $vars['body'] ?? '';
    } else {
        $body = file_get_contents($templatePath);
        foreach ($vars as $k => $v) {
            $body = str_replace('{{' . $k . '}}', $v, $body);
        }
    }
    return send_system_email($to, $subject, $body);
}

// ============ OPTIMIZED AGGREGATION FUNCTIONS (Performance Fix) ============
// These functions use SQL aggregation instead of loading all data into PHP

function get_reservation_stats(): array
{
    // Get reservation counts by status in ONE query instead of loading all reservations
    $result = db_all("
        SELECT 
            COALESCE(UPPER(status), 'CONFIRMED') as status,
            COUNT(*) as count
        FROM reservations
        GROUP BY status
    ");
    
    $stats = ['CONFIRMED' => 0, 'PENDING' => 0, 'CANCELLED' => 0];
    foreach ($result as $row) {
        $status = $row['status'] ?? 'CONFIRMED';
        $stats[$status] = (int)$row['count'];
    }
    return $stats;
}

function get_review_stats(): array
{
    // Get review counts and average rating in ONE query
    $result = db_one("
        SELECT 
            COUNT(*) as total,
            AVG(rating) as average_rating
        FROM reviews
    ");
    
    $stats = [
        'total' => (int)($result['total'] ?? 0),
        'average_rating' => (float)($result['average_rating'] ?? 0)
    ];
    
    // Get breakdown by rating
    if ($stats['total'] > 0) {
        $breakdown = db_all("
            SELECT rating, COUNT(*) as count
            FROM reviews
            WHERE rating >= 1 AND rating <= 5
            GROUP BY rating
        ");
        $stats['breakdown'] = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($breakdown as $row) {
            $stats['breakdown'][(int)$row['rating']] = (int)$row['count'];
        }
    }
    
    return $stats;
}

function get_recent_reservations(int $limit = 5): array
{
    // Get recent reservations efficiently using LIMIT instead of array_reverse
    return db_all("
        SELECT * FROM reservations
        ORDER BY date DESC, time DESC, created_at DESC
        LIMIT ?
    ", [$limit]);
}

function get_recent_reviews(int $limit = 5): array
{
    // Get recent reviews efficiently using LIMIT instead of array_reverse
    return db_all("
        SELECT * FROM reviews
        ORDER BY date DESC, id DESC
        LIMIT ?
    ", [$limit]);
}

function get_featured_menu_items(int $limit = 6): array
{
    // Get featured menu items with LIMIT instead of loading all items
    return db_all("
        SELECT id, name, description, category, price, available, image, popular 
        FROM menu 
        WHERE available = 1
        ORDER BY popular DESC, category, name
        LIMIT ?
    ", [$limit]);
}

function get_reservations_by_section(): array
{
    // Get reservations grouped by section with proper SQL ordering (no PHP loops/sorting)
    return db_all("
        SELECT 
            COALESCE(preferred_section, section, 'Unknown') as section_name,
            COUNT(*) as booking_count,
            MIN(CONCAT(date, ' ', time)) as next_booking
        FROM reservations
        WHERE status != 'Cancelled'
        GROUP BY COALESCE(preferred_section, section, 'Unknown')
        ORDER BY next_booking ASC
    ");
}
