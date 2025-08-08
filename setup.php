<?php
/**
 * BOUESTI Off-Campus Accommodation System - Setup Script
 * 
 * This script handles:
 * 1. Database connection test
 * 2. Database schema creation
 * 3. Initial data insertion
 * 4. Admin password setup
 * 5. File permissions check
 * 
 * Usage: Run this script once after uploading files to your web server
 */

// Prevent direct access if already configured
if (file_exists('includes/config.php')) {
    require_once 'includes/config.php';
    if (defined('DB_HOST') && defined('DB_NAME')) {
        die('Setup already completed. Remove this file for security.');
    }
}

// Error reporting for setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

$step = $_GET['step'] ?? 1;
$errors = [];
$success = [];

// Step 1: Database Configuration
if ($step == 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = trim($_POST['db_host'] ?? '');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';
    
    if (empty($db_host) || empty($db_name) || empty($db_user)) {
        $errors[] = "All database fields are required.";
    } else {
        // Test database connection
        try {
            $test_connection = new mysqli($db_host, $db_user, $db_pass, $db_name);
            
            if ($test_connection->connect_error) {
                $errors[] = "Database connection failed: " . $test_connection->connect_error;
            } else {
                // Create config file
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

                if (file_put_contents('includes/config.php', $config_content)) {
                    $success[] = "Database configuration saved successfully.";
                    $step = 2;
                } else {
                    $errors[] = "Failed to create config file. Check file permissions.";
                }
                
                $test_connection->close();
            }
        } catch (Exception $e) {
            $errors[] = "Database connection error: " . $e->getMessage();
        }
    }
}

// Step 2: Database Setup
if ($step == 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/config.php';
    
    // Read and execute SQL file
    $sql_file = 'database_setup.sql';
    if (file_exists($sql_file)) {
        $sql_content = file_get_contents($sql_file);
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql_content)));
        
        $success_count = 0;
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                if ($connection->query($statement)) {
                    $success_count++;
                } else {
                    $errors[] = "SQL Error: " . $connection->error . " in statement: " . substr($statement, 0, 50) . "...";
                }
            }
        }
        
        if (empty($errors)) {
            $success[] = "Database schema created successfully with $success_count statements.";
            $step = 3;
        }
    } else {
        $errors[] = "Database setup file not found: $sql_file";
    }
}

// Step 3: Admin Password Setup
if ($step == 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/config.php';
    
    $admin_email = trim($_POST['admin_email'] ?? '');
    $admin_password = $_POST['admin_password'] ?? '';
    $admin_confirm = $_POST['admin_confirm'] ?? '';
    
    if (empty($admin_email) || empty($admin_password)) {
        $errors[] = "Admin email and password are required.";
    } elseif ($admin_password !== $admin_confirm) {
        $errors[] = "Passwords do not match.";
    } elseif (strlen($admin_password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    } else {
        // Hash password and update admin
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $admin_email = $connection->real_escape_string($admin_email);
        
        $update_query = "UPDATE users SET email = ?, password = ? WHERE user_type = 'admin' LIMIT 1";
        $stmt = $connection->prepare($update_query);
        $stmt->bind_param("ss", $admin_email, $hashed_password);
        
        if ($stmt->execute()) {
            $success[] = "Admin credentials updated successfully.";
            $step = 4;
        } else {
            $errors[] = "Failed to update admin credentials: " . $connection->error;
        }
        
        $stmt->close();
    }
}

// Step 4: Final Setup
if ($step == 4) {
    require_once 'includes/config.php';
    
    // Check file permissions
    $upload_dir = 'uploads/properties/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    if (is_writable($upload_dir)) {
        $success[] = "Upload directory is writable.";
    } else {
        $errors[] = "Upload directory is not writable. Please set permissions to 755.";
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
        $success[] = "Security .htaccess file created.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOUESTI Housing System Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    BOUESTI Housing System Setup
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Step <?php echo $step; ?> of 4
                </p>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo ($step / 4) * 100; ?>%"></div>
            </div>

            <!-- Display Messages -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        <?php foreach ($success as $msg): ?>
                            <li><?php echo htmlspecialchars($msg); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Step 1: Database Configuration -->
            <?php if ($step == 1): ?>
                <form method="POST" class="mt-8 space-y-6">
                    <div class="rounded-md shadow-sm -space-y-px">
                        <div>
                            <label for="db_host" class="sr-only">Database Host</label>
                            <input id="db_host" name="db_host" type="text" required 
                                   class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                                   placeholder="Database Host (e.g., localhost)" value="<?php echo htmlspecialchars($_POST['db_host'] ?? 'localhost'); ?>">
                        </div>
                        <div>
                            <label for="db_name" class="sr-only">Database Name</label>
                            <input id="db_name" name="db_name" type="text" required 
                                   class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                                   placeholder="Database Name" value="<?php echo htmlspecialchars($_POST['db_name'] ?? 'bouesti_housing'); ?>">
                        </div>
                        <div>
                            <label for="db_user" class="sr-only">Database Username</label>
                            <input id="db_user" name="db_user" type="text" required 
                                   class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                                   placeholder="Database Username" value="<?php echo htmlspecialchars($_POST['db_user'] ?? 'root'); ?>">
                        </div>
                        <div>
                            <label for="db_pass" class="sr-only">Database Password</label>
                            <input id="db_pass" name="db_pass" type="password" 
                                   class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                                   placeholder="Database Password (leave empty if none)">
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Test Connection & Continue
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <!-- Step 2: Database Setup -->
            <?php if ($step == 2): ?>
                <form method="POST" class="mt-8 space-y-6">
                    <div class="text-center">
                        <p class="text-gray-600">Ready to create database tables and insert initial data.</p>
                        <p class="text-sm text-gray-500 mt-2">This will create all necessary tables and insert sample data.</p>
                    </div>

                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Create Database Schema
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <!-- Step 3: Admin Password Setup -->
            <?php if ($step == 3): ?>
                <form method="POST" class="mt-8 space-y-6">
                    <div class="rounded-md shadow-sm -space-y-px">
                        <div>
                            <label for="admin_email" class="sr-only">Admin Email</label>
                            <input id="admin_email" name="admin_email" type="email" required 
                                   class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                                   placeholder="Admin Email" value="<?php echo htmlspecialchars($_POST['admin_email'] ?? 'admin@bouesti.edu.ng'); ?>">
                        </div>
                        <div>
                            <label for="admin_password" class="sr-only">Admin Password</label>
                            <input id="admin_password" name="admin_password" type="password" required 
                                   class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                                   placeholder="Admin Password (min 8 characters)">
                        </div>
                        <div>
                            <label for="admin_confirm" class="sr-only">Confirm Password</label>
                            <input id="admin_confirm" name="admin_confirm" type="password" required 
                                   class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                                   placeholder="Confirm Password">
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Set Admin Password
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <!-- Step 4: Setup Complete -->
            <?php if ($step == 4): ?>
                <div class="mt-8 space-y-6">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Setup Complete!</h3>
                        <p class="mt-1 text-sm text-gray-500">Your BOUESTI Housing System is ready to use.</p>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                        <h4 class="text-sm font-medium text-blue-800">Default Login Credentials:</h4>
                        <div class="mt-2 text-sm text-blue-700">
                            <p><strong>Admin:</strong> admin@bouesti.edu.ng / [Your Password]</p>
                            <p><strong>Student:</strong> student@example.com / password123</p>
                            <p><strong>Landlord:</strong> landlord@example.com / password123</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <a href="index.php" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            Go to Homepage
                        </a>
                        <a href="admin/" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Go to Admin Panel
                        </a>
                    </div>

                    <div class="text-center">
                        <p class="text-xs text-gray-500">
                            <strong>Important:</strong> Delete this setup.php file for security.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
