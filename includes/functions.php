<?php
// Security Functions
function sanitizeInput($data) {
    global $connection;
    return mysqli_real_escape_string($connection, htmlspecialchars(strip_tags(trim($data))));
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Authentication Functions
function authenticateUser($email, $password) {
    global $connection;
    
    $email = sanitizeInput($email);
    $sql = "SELECT * FROM users WHERE email = '$email' AND is_active = 1";
    $result = mysqli_query($connection, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['is_verified'] = $user['is_verified'];
            return true;
        }
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function isLandlord() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'landlord';
}

function isStudent() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}

function requireLandlord() {
    requireLogin();
    if (!isLandlord()) {
        header('Location: ../index.php');
        exit();
    }
}

function requireStudent() {
    requireLogin();
    if (!isStudent()) {
        header('Location: ../index.php');
        exit();
    }
}

// User Management Functions
function createUser($email, $password, $user_type, $first_name, $last_name, $phone, $additional_data = []) {
    global $connection;
    
    $email = sanitizeInput($email);
    $first_name = sanitizeInput($first_name);
    $last_name = sanitizeInput($last_name);
    $phone = sanitizeInput($phone);
    $hashed_password = hashPassword($password);
    
    $student_id = isset($additional_data['student_id']) ? "'" . sanitizeInput($additional_data['student_id']) . "'" : "NULL";
    $business_name = isset($additional_data['business_name']) ? "'" . sanitizeInput($additional_data['business_name']) . "'" : "NULL";
    
    $sql = "INSERT INTO users (email, password, user_type, first_name, last_name, phone, student_id, business_name, is_verified) 
            VALUES ('$email', '$hashed_password', '$user_type', '$first_name', '$last_name', '$phone', $student_id, $business_name, 0)";
    
    if (mysqli_query($connection, $sql)) {
        return mysqli_insert_id($connection);
    }
    return false;
}

function getUserById($user_id) {
    global $connection;
    $user_id = (int)$user_id;
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = mysqli_query($connection, $sql);
    return $result ? mysqli_fetch_assoc($result) : false;
}

function getUserByEmail($email) {
    global $connection;
    $email = sanitizeInput($email);
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($connection, $sql);
    return $result ? mysqli_fetch_assoc($result) : false;
}

function updateUserStatus($user_id, $is_verified) {
    global $connection;
    $user_id = (int)$user_id;
    $is_verified = (int)$is_verified;
    $sql = "UPDATE users SET is_verified = $is_verified WHERE id = $user_id";
    return mysqli_query($connection, $sql);
}

// Property Management Functions
function createProperty($landlord_id, $title, $description, $address, $rent_amount, $property_type, $amenities = '') {
    global $connection;
    
    $landlord_id = (int)$landlord_id;
    $title = sanitizeInput($title);
    $description = sanitizeInput($description);
    $address = sanitizeInput($address);
    $rent_amount = (float)$rent_amount;
    $property_type = sanitizeInput($property_type);
    $amenities = sanitizeInput($amenities);
    
    $sql = "INSERT INTO properties (landlord_id, title, description, address, rent_amount, property_type, amenities, is_approved) 
            VALUES ($landlord_id, '$title', '$description', '$address', $rent_amount, '$property_type', '$amenities', 0)";
    
    if (mysqli_query($connection, $sql)) {
        return mysqli_insert_id($connection);
    }
    return false;
}

function getPropertyById($property_id) {
    global $connection;
    $property_id = (int)$property_id;
    $sql = "SELECT p.*, u.first_name, u.last_name, u.phone, u.email, u.is_verified as landlord_verified 
            FROM properties p 
            JOIN users u ON p.landlord_id = u.id 
            WHERE p.id = $property_id";
    $result = mysqli_query($connection, $sql);
    return $result ? mysqli_fetch_assoc($result) : false;
}

function getApprovedProperties($search = '', $property_type = '', $max_rent = '') {
    global $connection;
    
    $sql = "SELECT p.*, u.first_name, u.last_name, u.phone, u.is_verified as landlord_verified 
            FROM properties p 
            JOIN users u ON p.landlord_id = u.id 
            WHERE p.is_approved = 1 AND p.is_available = 1 AND u.is_verified = 1";
    
    if ($search) {
        $search = sanitizeInput($search);
        $sql .= " AND (p.title LIKE '%$search%' OR p.address LIKE '%$search%' OR p.description LIKE '%$search%')";
    }
    
    if ($property_type) {
        $property_type = sanitizeInput($property_type);
        $sql .= " AND p.property_type = '$property_type'";
    }
    
    if ($max_rent) {
        $max_rent = (float)$max_rent;
        $sql .= " AND p.rent_amount <= $max_rent";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    return mysqli_query($connection, $sql);
}

function getRecentProperties($limit = 6) {
    global $connection;
    $limit = (int)$limit;
    $sql = "SELECT p.*, u.first_name, u.last_name, u.phone, u.is_verified as landlord_verified 
            FROM properties p 
            JOIN users u ON p.landlord_id = u.id 
            WHERE p.is_approved = 1 AND p.is_available = 1 AND u.is_verified = 1 
            ORDER BY p.created_at DESC 
            LIMIT $limit";
    return mysqli_query($connection, $sql);
}

function getLandlordProperties($landlord_id) {
    global $connection;
    $landlord_id = (int)$landlord_id;
    $sql = "SELECT * FROM properties WHERE landlord_id = $landlord_id ORDER BY created_at DESC";
    return mysqli_query($connection, $sql);
}

function updatePropertyStatus($property_id, $is_approved, $rejection_reason = '') {
    global $connection;
    $property_id = (int)$property_id;
    $is_approved = (int)$is_approved;
    $rejection_reason = sanitizeInput($rejection_reason);
    
    $sql = "UPDATE properties SET is_approved = $is_approved";
    if (!$is_approved && $rejection_reason) {
        $sql .= ", rejection_reason = '$rejection_reason'";
    }
    $sql .= " WHERE id = $property_id";
    
    return mysqli_query($connection, $sql);
}

function deleteProperty($property_id) {
    global $connection;
    $property_id = (int)$property_id;
    
    // Delete property images first
    $images = mysqli_query($connection, "SELECT image_path FROM property_images WHERE property_id = $property_id");
    while ($image = mysqli_fetch_assoc($images)) {
        if (file_exists($image['image_path'])) {
            unlink($image['image_path']);
        }
    }
    
    mysqli_query($connection, "DELETE FROM property_images WHERE property_id = $property_id");
    mysqli_query($connection, "DELETE FROM inquiries WHERE property_id = $property_id");
    
    $sql = "DELETE FROM properties WHERE id = $property_id";
    return mysqli_query($connection, $sql);
}

// Image Management Functions
function uploadPropertyImages($files, $property_id) {
    global $connection;
    
    $upload_dir = PROPERTY_IMAGES_PATH;
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $uploaded_images = [];
    
    foreach ($files['tmp_name'] as $key => $tmp_name) {
        if ($files['error'][$key] === UPLOAD_ERR_OK) {
            $filename = time() . '_' . $files['name'][$key];
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($tmp_name, $filepath)) {
                $is_primary = ($key === 0) ? 1 : 0; // First image is primary
                $sql = "INSERT INTO property_images (property_id, image_path, is_primary) VALUES ($property_id, '$filepath', $is_primary)";
                if (mysqli_query($connection, $sql)) {
                    $uploaded_images[] = $filepath;
                }
            }
        }
    }
    
    return $uploaded_images;
}

function getPropertyImages($property_id) {
    global $connection;
    $property_id = (int)$property_id;
    $sql = "SELECT * FROM property_images WHERE property_id = $property_id ORDER BY is_primary DESC, id ASC";
    return mysqli_query($connection, $sql);
}

// Inquiry Management Functions
function createInquiry($student_id, $property_id, $message) {
    global $connection;
    
    $student_id = (int)$student_id;
    $property_id = (int)$property_id;
    $message = sanitizeInput($message);
    
    $property = getPropertyById($property_id);
    $landlord_id = $property['landlord_id'];
    
    $sql = "INSERT INTO inquiries (student_id, property_id, landlord_id, message) 
            VALUES ($student_id, $property_id, $landlord_id, '$message')";
    
    return mysqli_query($connection, $sql);
}

function getInquiriesByLandlord($landlord_id) {
    global $connection;
    $landlord_id = (int)$landlord_id;
    $sql = "SELECT i.*, p.title as property_title, u.first_name, u.last_name, u.email, u.phone 
            FROM inquiries i 
            JOIN properties p ON i.property_id = p.id 
            JOIN users u ON i.student_id = u.id 
            WHERE i.landlord_id = $landlord_id 
            ORDER BY i.created_at DESC";
    return mysqli_query($connection, $sql);
}

function getInquiriesByStudent($student_id) {
    global $connection;
    $student_id = (int)$student_id;
    $sql = "SELECT i.*, p.title as property_title, u.first_name, u.last_name, u.email, u.phone 
            FROM inquiries i 
            JOIN properties p ON i.property_id = p.id 
            JOIN users u ON i.landlord_id = u.id 
            WHERE i.student_id = $student_id 
            ORDER BY i.created_at DESC";
    return mysqli_query($connection, $sql);
}

// Admin Functions
function getAllUsers($user_type = '', $search = '') {
    global $connection;
    
    $sql = "SELECT * FROM users WHERE 1=1";
    if ($user_type) {
        $user_type = sanitizeInput($user_type);
        $sql .= " AND user_type = '$user_type'";
    }
    if ($search) {
        $search = sanitizeInput($search);
        $sql .= " AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%')";
    }
    $sql .= " ORDER BY created_at DESC";
    
    return mysqli_query($connection, $sql);
}

function getAllProperties($status = '', $search = '') {
    global $connection;
    
    $sql = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.is_verified as landlord_verified 
            FROM properties p 
            JOIN users u ON p.landlord_id = u.id 
            WHERE 1=1";
    
    if ($status === 'approved') {
        $sql .= " AND p.is_approved = 1";
    } elseif ($status === 'pending') {
        $sql .= " AND p.is_approved = 0";
    }
    
    if ($search) {
        $search = sanitizeInput($search);
        $sql .= " AND (p.title LIKE '%$search%' OR p.address LIKE '%$search%' OR u.first_name LIKE '%$search%')";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    return mysqli_query($connection, $sql);
}

function logAdminAction($admin_id, $action, $target_type, $target_id, $details) {
    global $connection;
    
    $admin_id = (int)$admin_id;
    $action = sanitizeInput($action);
    $target_type = sanitizeInput($target_type);
    $target_id = (int)$target_id;
    $details = sanitizeInput($details);
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    
    $sql = "INSERT INTO admin_logs (admin_id, action, target_type, target_id, details, ip_address) 
            VALUES ($admin_id, '$action', '$target_type', $target_id, '$details', '$ip_address')";
    
    return mysqli_query($connection, $sql);
}

// Utility Functions
function formatCurrency($amount) {
    return '₦' . number_format($amount);
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M j, Y g:i A', strtotime($datetime));
}

function getPropertyTypeLabel($type) {
    $types = [
        'single_room' => 'Single Room',
        'shared_room' => 'Shared Room',
        'apartment' => 'Apartment'
    ];
    return $types[$type] ?? $type;
}

function getUserTypeLabel($type) {
    $types = [
        'student' => 'Student',
        'landlord' => 'Landlord',
        'admin' => 'Administrator'
    ];
    return $types[$type] ?? $type;
}

// Validation Functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    // Basic Nigerian phone validation
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 11;
}

function validatePassword($password) {
    return strlen($password) >= 6;
}

// Error and Success Messages
function setMessage($type, $message) {
    $_SESSION['message'] = [
        'type' => $type,
        'text' => $message
    ];
}

function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}

function displayMessage() {
    $message = getMessage();
    if ($message) {
        $type = $message['type'];
        $text = $message['text'];
        $bg_color = $type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
        echo "<div class='$bg_color border px-4 py-3 rounded mb-4'>$text</div>";
    }
}
?>
