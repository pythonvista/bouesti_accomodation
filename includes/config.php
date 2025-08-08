<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'bouesti_housing');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Configuration
define('SITE_NAME', 'BOUESTI Off-Campus Accommodation System');
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']));
define('UPLOAD_PATH', 'uploads/properties/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration - only set if session hasn't started yet
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
}

// Database connection
$connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($connection->connect_error) {
    die('Connection failed: ' . $connection->connect_error);
}

$connection->set_charset('utf8mb4');
?>