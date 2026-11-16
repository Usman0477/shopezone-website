<?php
// ============================================================
//  ShopZone — Database Configuration
//  Edit these values to match your XAMPP/MySQL setup
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP default: empty
define('DB_NAME', 'shopzone');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'ShopZone');
define('SITE_URL',  'http://localhost/shopzone');
define('UPLOAD_DIR', __DIR__ . '/uploads/products/');
define('UPLOAD_URL', SITE_URL . '/uploads/products/');

// Mail config (for OTP / password reset)
define('MAIL_FROM',    'noreply@shopzone.com');
define('MAIL_FROM_NAME', 'ShopZone');

// Session lifetime for "Remember Me" (30 days in seconds)
define('REMEMBER_LIFETIME', 30 * 24 * 60 * 60);

// ============================================================
//  Database Connection (PDO)
// ============================================================
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}
