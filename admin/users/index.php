<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Ensure user is logged in and is an admin
requireAdmin();

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $target_user_id = (int)$_POST['user_id'];
        $action = $_POST['action'];
        
        switch ($action) {
            case 'verify':
                if (updateUserStatus($target_user_id, 1)) {
                    setMessage('success', 'User verified successfully.');
                } else {
                    setMessage('error', 'Failed to verify user.');
                }
                break;
            case 'unverify':
                if (updateUserStatus($target_user_id, 0)) {
                    setMessage('success', 'User verification removed.');
                } else {
                    setMessage('error', 'Failed to update user status.');
                }
                break;
            case 'delete':
                // Delete user and related data
                $sql = "DELETE FROM inquiries WHERE student_id = $target_user_id OR landlord_id = $target_user_id";
                mysqli_query($connection, $sql);
                
                $sql = "DELETE FROM properties WHERE landlord_id = $target_user_id";
                mysqli_query($connection, $sql);
                
                $sql = "DELETE FROM users WHERE id = $target_user_id";
                if (mysqli_query($connection, $sql)) {
                    setMessage('success', 'User deleted successfully.');
                } else {
                    setMessage('error', 'Failed to delete user.');
                }
                break;
        }
        
        header('Location: index.php');
        exit();
    }
}

// Get filter parameters
$user_type = $_GET['user_type'] ?? '';
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Get users with filters
$sql = "SELECT * FROM users WHERE 1=1";
if ($user_type) {
    $user_type = sanitizeInput($user_type);
    $sql .= " AND user_type = '$user_type'";
}
if ($search) {
    $search = sanitizeInput($search);
    $sql .= " AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%')";
}
if ($status === 'verified') {
    $sql .= " AND is_verified = 1";
} elseif ($status === 'pending') {
    $sql .= " AND is_verified = 0";
}
$sql .= " ORDER BY created_at DESC";

$users = mysqli_query($connection, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gray-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <a href="../index.php" class="text-xl font-bold">BOUESTI Housing</a>
                    <span class="text-gray-300">|</span>
                    <span class="text-sm">Admin Panel</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="../index.php" class="text-gray-300 hover:text-white">Dashboard</a>
                    <span>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</span>
                    <a href="../../pages/logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg transition duration-200">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Manage Users</h1>
                    <p class="text-gray-600 mt-2">View and manage all system users</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Total Users</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo mysqli_num_rows($users); ?></p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Name or email...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">User Type</label>
                    <select name="user_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="student" <?php echo $user_type === 'student' ? 'selected' : ''; ?>>Student</option>
                        <option value="landlord" <?php echo $user_type === 'landlord' ? 'selected' : ''; ?>>Landlord</option>
                        <option value="admin" <?php echo $user_type === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="verified" <?php echo $status === 'verified' ? 'selected' : ''; ?>>Verified</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Messages -->
        <?php displayMessage(); ?>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Users List</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (mysqli_num_rows($users) == 0): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No users found.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($user_data = mysqli_fetch_assoc($users)): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700">
                                                        <?php echo strtoupper(substr($user_data['first_name'], 0, 1) . substr($user_data['last_name'], 0, 1)); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($user_data['email']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($user_data['phone']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            <?php echo $user_data['user_type'] === 'admin' ? 'bg-red-100 text-red-800' : 
                                                    ($user_data['user_type'] === 'landlord' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'); ?>">
                                            <?php echo ucfirst($user_data['user_type']); ?>
                                        </span>
                                        <?php if ($user_data['user_type'] === 'student' && $user_data['student_id']): ?>
                                            <div class="text-xs text-gray-500 mt-1">ID: <?php echo htmlspecialchars($user_data['student_id']); ?></div>
                                        <?php endif; ?>
                                        <?php if ($user_data['user_type'] === 'landlord' && $user_data['business_name']): ?>
                                            <div class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($user_data['business_name']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($user_data['is_verified']): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Verified
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M j, Y', strtotime($user_data['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="edit.php?id=<?php echo $user_data['id']; ?>" 
                                               class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            
                                            <?php if ($user_data['id'] != $user_id): // Don't allow self-deletion ?>
                                                <?php if ($user_data['is_verified']): ?>
                                                    <form method="POST" class="inline" onsubmit="return confirm('Remove verification from this user?')">
                                                        <input type="hidden" name="user_id" value="<?php echo $user_data['id']; ?>">
                                                        <input type="hidden" name="action" value="unverify">
                                                        <button type="submit" class="text-yellow-600 hover:text-yellow-900">Unverify</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" class="inline" onsubmit="return confirm('Verify this user?')">
                                                        <input type="hidden" name="user_id" value="<?php echo $user_data['id']; ?>">
                                                        <input type="hidden" name="action" value="verify">
                                                        <button type="submit" class="text-green-600 hover:text-green-900">Verify</button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                    <input type="hidden" name="user_id" value="<?php echo $user_data['id']; ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../../js/main.js"></script>
</body>
</html>
