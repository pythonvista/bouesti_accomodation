<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_type = sanitizeInput($_POST['user_type']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $phone = sanitizeInput($_POST['phone']);
    
    // Validation
    if (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (!validatePassword($password)) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!validatePhone($phone)) {
        $error = 'Please enter a valid phone number.';
    } elseif (empty($first_name) || empty($last_name)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check if email already exists
        $existing_user = getUserByEmail($email);
        if ($existing_user) {
            $error = 'An account with this email already exists.';
        } else {
            // Prepare additional data based on user type
            $additional_data = [];
            if ($user_type === 'student') {
                $additional_data['student_id'] = sanitizeInput($_POST['student_id']);
            } elseif ($user_type === 'landlord') {
                $additional_data['business_name'] = sanitizeInput($_POST['business_name']);
            }
            
            // Create user
            $user_id = createUser($email, $password, $user_type, $first_name, $last_name, $phone, $additional_data);
            
            if ($user_id) {
                $success = 'Registration successful! Please wait for admin verification before you can login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BOUESTI Off-Campus Accommodation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="../index.php" class="text-xl font-bold">BOUESTI Housing</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="login.php" class="hover:text-blue-200">Login</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Create your account
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Join BOUESTI Housing to find or list accommodation
                </p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" method="POST" data-validate>
                <div class="rounded-md shadow-sm -space-y-px">
                    <!-- User Type Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">I am a:</label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="user_type" value="student" class="mr-2" required>
                                <span>Student</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="user_type" value="landlord" class="mr-2">
                                <span>Landlord</span>
                            </label>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="sr-only">First Name</label>
                            <input id="first_name" name="first_name" type="text" required 
                                   class="form-input rounded-t-md" placeholder="First Name">
                        </div>
                        <div>
                            <label for="last_name" class="sr-only">Last Name</label>
                            <input id="last_name" name="last_name" type="text" required 
                                   class="form-input rounded-t-md" placeholder="Last Name">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="sr-only">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required 
                               class="form-input" placeholder="Email address">
                    </div>

                    <div>
                        <label for="phone" class="sr-only">Phone Number</label>
                        <input id="phone" name="phone" type="tel" required 
                               class="form-input" placeholder="Phone Number">
                    </div>

                    <!-- Student-specific fields -->
                    <div id="student-fields" class="hidden">
                        <div>
                            <label for="student_id" class="sr-only">Student ID</label>
                            <input id="student_id" name="student_id" type="text" 
                                   class="form-input" placeholder="Student ID (Optional)">
                        </div>
                    </div>

                    <!-- Landlord-specific fields -->
                    <div id="landlord-fields" class="hidden">
                        <div>
                            <label for="business_name" class="sr-only">Business Name</label>
                            <input id="business_name" name="business_name" type="text" 
                                   class="form-input" placeholder="Business Name (Optional)">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" required 
                               class="form-input" placeholder="Password">
                    </div>

                    <div>
                        <label for="confirm_password" class="sr-only">Confirm Password</label>
                        <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required 
                               class="form-input rounded-b-md" placeholder="Confirm Password">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Register
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account? 
                        <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">
                            Sign in here
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show/hide user type specific fields
        document.querySelectorAll('input[name="user_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const studentFields = document.getElementById('student-fields');
                const landlordFields = document.getElementById('landlord-fields');
                
                if (this.value === 'student') {
                    studentFields.classList.remove('hidden');
                    landlordFields.classList.add('hidden');
                } else if (this.value === 'landlord') {
                    landlordFields.classList.remove('hidden');
                    studentFields.classList.add('hidden');
                } else {
                    studentFields.classList.add('hidden');
                    landlordFields.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>
