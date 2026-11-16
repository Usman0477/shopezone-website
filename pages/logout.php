<?php
require_once __DIR__ . '/../includes/functions.php';

// Clear session
session_destroy();

// Clear remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

header('Location: ' . SITE_URL . '/pages/login.php');
exit;
