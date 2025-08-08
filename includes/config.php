<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bouesti_accomd');

// Application Configuration
define('SITE_NAME', 'BOUESTI Off-Campus Accommodation');
define('SITE_URL', 'http://localhost/accomodation');
define('UPLOAD_PATH', 'uploads/');
define('PROPERTY_IMAGES_PATH', 'uploads/properties/');

// Database Connection
$connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($connection, "utf8mb4");

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS

// Timezone
date_default_timezone_set('Africa/Lagos');
?>
