<?php
/**
 * BOUESTI Off-Campus Accommodation System - CLI Setup Script
 * 
 * This script handles automated setup via command line
 * Usage: php setup_cli.php [options]
 * 
 * Options:
 * --db-host=localhost     Database host
 * --db-name=bouesti_housing  Database name
 * --db-user=root         Database username
 * --db-pass=             Database password
 * --admin-email=admin@bouesti.edu.ng  Admin email
 * --admin-pass=          Admin password
 * --skip-db-check        Skip database connection test
 * --force                Force setup even if already configured
 */

// Prevent web access
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line.');
}

// Parse command line arguments
$options = getopt('', [
    'db-host:',
    'db-name:',
    'db-user:',
    'db-pass:',
    'admin-email:',
    'admin-pass:',
    'skip-db-check',
    'force',
    'help'
]);

// Show help
if (isset($options['help'])) {
    echo "BOUESTI Housing System - CLI Setup Script\n\n";
    echo "Usage: php setup_cli.php [options]\n\n";
    echo "Options:\n";
    echo "  --db-host=HOST        Database host (default: localhost)\n";
    echo "  --db-name=NAME        Database name (default: bouesti_housing)\n";
    echo "  --db-user=USER        Database username (default: root)\n";
    echo "  --db-pass=PASS        Database password\n";
    echo "  --admin-email=EMAIL   Admin email (default: admin@bouesti.edu.ng)\n";
    echo "  --admin-pass=PASS     Admin password (required)\n";
    echo "  --skip-db-check       Skip database connection test\n";
    echo "  --force               Force setup even if already configured\n";
    echo "  --help                Show this help message\n\n";
    echo "Example:\n";
    echo "  php setup_cli.php --db-host=localhost --db-name=bouesti_housing --db-user=root --admin-pass=mypassword\n\n";
    exit(0);
}

// Set default values
$db_host = $options['db-host'] ?? 'localhost';
$db_name = $options['db-name'] ?? 'bouesti_housing';
$db_user = $options['db-user'] ?? 'root';
$db_pass = $options['db-pass'] ?? '';
$admin_email = $options['admin-email'] ?? 'admin@bouesti.edu.ng';
$admin_password = $options['admin-pass'] ?? '';
$skip_db_check = isset($options['skip-db-check']);
$force = isset($options['force']);

// Check if already configured
if (file_exists('includes/config.php') && !$force) {
    echo "Error: System already configured. Use --force to override.\n";
    exit(1);
}

// Validate required parameters
if (empty($admin_password)) {
    echo "Error: Admin password is required. Use --admin-pass=PASSWORD\n";
    exit(1);
}

if (strlen($admin_password) < 8) {
    echo "Error: Admin password must be at least 8 characters long.\n";
    exit(1);
}

echo "BOUESTI Housing System - CLI Setup\n";
echo "==================================\n\n";

// Step 1: Test database connection
if (!$skip_db_check) {
    echo "Step 1: Testing database connection...\n";
    
    try {
        $test_connection = new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        if ($test_connection->connect_error) {
            echo "Error: Database connection failed: " . $test_connection->connect_error . "\n";
            echo "Please check your database credentials and ensure the database exists.\n";
            exit(1);
        }
        
        echo "✓ Database connection successful\n";
        $test_connection->close();
    } catch (Exception $e) {
        echo "Error: Database connection error: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "Step 1: Skipping database connection test...\n";
}

// Step 2: Create config file
echo "Step 2: Creating configuration file...\n";

$config_content = "<?php
// Database Configuration
define('DB_HOST', '" . addslashes($db_host) . "');
define('DB_NAME', '" . addslashes($db_name) . "');
define('DB_USER', '" . addslashes($db_user) . "');
define('DB_PASS', '" . addslashes($db_pass) . "');

// Application Configuration
define('SITE_NAME', 'BOUESTI Off-Campus Accommodation System');
define('SITE_URL', 'http://' . \$_SERVER['HTTP_HOST'] . dirname(\$_SERVER['PHP_SELF']));
define('UPLOAD_PATH', 'uploads/properties/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset(\$_SERVER['HTTPS']));

// Database connection
\$connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (\$connection->connect_error) {
    die('Connection failed: ' . \$connection->connect_error);
}

\$connection->set_charset('utf8mb4');
?>";

if (!is_dir('includes')) {
    mkdir('includes', 0755, true);
}

if (file_put_contents('includes/config.php', $config_content)) {
    echo "✓ Configuration file created\n";
} else {
    echo "Error: Failed to create configuration file. Check file permissions.\n";
    exit(1);
}

// Step 3: Setup database schema
echo "Step 3: Setting up database schema...\n";

require_once 'includes/config.php';

$sql_file = 'database_setup.sql';
if (file_exists($sql_file)) {
    $sql_content = file_get_contents($sql_file);
    $statements = array_filter(array_map('trim', explode(';', $sql_content)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            if ($connection->query($statement)) {
                $success_count++;
            } else {
                echo "Warning: SQL Error: " . $connection->error . "\n";
                $error_count++;
            }
        }
    }
    
    if ($error_count == 0) {
        echo "✓ Database schema created successfully ($success_count statements)\n";
    } else {
        echo "Warning: Database setup completed with $error_count errors\n";
    }
} else {
    echo "Error: Database setup file not found: $sql_file\n";
    exit(1);
}

// Step 4: Set admin password
echo "Step 4: Setting admin password...\n";

$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
$admin_email = $connection->real_escape_string($admin_email);

$update_query = "UPDATE users SET email = ?, password = ? WHERE user_type = 'admin' LIMIT 1";
$stmt = $connection->prepare($update_query);
$stmt->bind_param("ss", $admin_email, $hashed_password);

if ($stmt->execute()) {
    echo "✓ Admin credentials updated successfully\n";
} else {
    echo "Error: Failed to update admin credentials: " . $connection->error . "\n";
    exit(1);
}

$stmt->close();

// Step 5: Create directories and set permissions
echo "Step 5: Setting up directories and permissions...\n";

// Create upload directory
$upload_dir = 'uploads/properties/';
if (!is_dir($upload_dir)) {
    if (mkdir($upload_dir, 0755, true)) {
        echo "✓ Upload directory created\n";
    } else {
        echo "Warning: Failed to create upload directory\n";
    }
} else {
    echo "✓ Upload directory exists\n";
}

// Check upload directory permissions
if (is_writable($upload_dir)) {
    echo "✓ Upload directory is writable\n";
} else {
    echo "Warning: Upload directory is not writable. Please set permissions to 755.\n";
}

// Create .htaccess for security
$htaccess_content = "Options -Indexes
<Files \"*.php\">
    Order Deny,Allow
    Deny from all
</Files>
<Files \"setup.php\">
    Order Allow,Deny
    Allow from all
</Files>";

if (file_put_contents('.htaccess', $htaccess_content)) {
    echo "✓ Security .htaccess file created\n";
} else {
    echo "Warning: Failed to create .htaccess file\n";
}

// Step 6: Setup complete
echo "\nSetup Complete!\n";
echo "===============\n\n";

echo "Default Login Credentials:\n";
echo "-------------------------\n";
echo "Admin: $admin_email / [Your Password]\n";
echo "Student: student@example.com / password123\n";
echo "Landlord: landlord@example.com / password123\n\n";

echo "Next Steps:\n";
echo "-----------\n";
echo "1. Delete setup files: rm setup.php setup_cli.php\n";
echo "2. Test the system: http://your-domain.com/\n";
echo "3. Access admin panel: http://your-domain.com/admin/\n";
echo "4. Review SETUP.md for additional configuration\n\n";

echo "Security Notes:\n";
echo "---------------\n";
echo "- Always delete setup files after installation\n";
echo "- Change default passwords for test accounts\n";
echo "- Configure SSL/TLS for production use\n";
echo "- Set up regular backups\n\n";

$connection->close();
echo "Setup completed successfully!\n";
exit(0);
?>
