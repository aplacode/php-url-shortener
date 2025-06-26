<?php
// includes/config.php

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'url_shortener');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site Configuration
define('SITE_URL', 'http://localhost/php-url-shortener');
define('SHORT_URL_LENGTH', 6);
define('MAX_URL_LENGTH', 2048);

// Security
define('RATE_LIMIT', 10); // Per IP per hour
define('ENABLE_ANALYTICS', true);
define('ADMIN_PASSWORD', 'admin123'); // Change this!

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session settings
session_start();

// Helper functions
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?> 