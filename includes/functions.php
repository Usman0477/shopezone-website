<?php
// ============================================================
//  ShopZone — Core Helper Functions
// ============================================================

require_once __DIR__ . '/../config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['samesite' => 'Lax', 'httponly' => true]);
    session_start();
}

// Auto-login via remember_token cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE remember_token = ? LIMIT 1");
    $stmt->execute([$token]);
    $u = $stmt->fetch();
    if ($u) {
        $_SESSION['user_id']   = $u['id'];
        $_SESSION['user_name'] = $u['name'];
        $_SESSION['user_role'] = $u['role'];
    }
}

// ── Auth helpers ──────────────────────────────────────────────
function isLoggedIn(): bool  { return isset($_SESSION['user_id']); }
function isAdmin(): bool     { return ($_SESSION['user_role'] ?? '') === 'admin'; }
function currentUserId(): int { return (int)($_SESSION['user_id'] ?? 0); }

function requireLogin(string $redirect = '/pages/login.php'): void {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . $redirect);
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) { header('Location: ' . SITE_URL . '/index.php'); exit; }
}

// ── Flash messages ────────────────────────────────────────────
function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function renderFlash(): void {
    $f = getFlash();
    if ($f) {
        $icon = $f['type'] === 'success' ? 'check-circle' : ($f['type'] === 'error' ? 'x-circle' : 'info');
        echo "<div class='flash flash-{$f['type']}' id='flash-msg'>
            <i data-feather='{$icon}'></i>
            <span>" . htmlspecialchars($f['msg']) . "</span>
            <button onclick=\"document.getElementById('flash-msg').remove()\">×</button>
        </div>";
    }
}

// ── Security ──────────────────────────────────────────────────
function sanitize(string $val): string { return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8'); }
function slugify(string $text): string {
    $text = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
    return $text;
}

function generateToken(int $len = 32): string { return bin2hex(random_bytes($len)); }
function generateOTP(): string { return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT); }

// ── Cart helpers ──────────────────────────────────────────────
function getCartCount(): int {
    if (!isLoggedIn()) return 0;
    $db = getDB();
    $stmt = $db->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id = ?");
    $stmt->execute([currentUserId()]);
    return (int)$stmt->fetchColumn();
}

function getWishlistCount(): int {
    if (!isLoggedIn()) return 0;
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $stmt->execute([currentUserId()]);
    return (int)$stmt->fetchColumn();
}

// ── Product helpers ───────────────────────────────────────────
function getProductImage(string $image = ''): string {
    if ($image && file_exists(UPLOAD_DIR . $image)) return UPLOAD_URL . $image;
    return SITE_URL . '/assets/images/placeholder.svg';
}
function formatPrice(float $price): string { return '$' . number_format($price, 2); }


function getStarRating(float $rating): string {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= $i <= round($rating)
            ? '<i data-feather="star" class="star filled"></i>'
            : '<i data-feather="star" class="star"></i>';
    }
    return $stars;
}

// ── Image upload helper ───────────────────────────────────────
function uploadProductImage(array $file): string|false {
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    if (!in_array($file['type'], $allowed)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false;
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('prod_', true) . '.' . $ext;
    $dest = UPLOAD_DIR . $filename;
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    return move_uploaded_file($file['tmp_name'], $dest) ? $filename : false;
}

// ── Email (simple mail wrapper) ───────────────────────────────
function sendMail(string $to, string $subject, string $htmlBody): bool {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\n";
    return mail($to, $subject, $htmlBody, $headers);
}
