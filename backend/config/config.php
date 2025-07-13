<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3308');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gaming_arena_v2');

// Application configuration
define('APP_URL', 'http://localhost/G-Arena');
define('ADMIN_SESSION_NAME', 'admin_logged_in');
define('USER_SESSION_NAME', 'user_logged_in');

// Timezone
date_default_timezone_set('Asia/Manila');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
