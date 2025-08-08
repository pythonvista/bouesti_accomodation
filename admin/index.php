<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Ensure user is logged in and is an admin
requireAdmin();

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Get system statistics
$total_users = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM users"))['count'];
$total_students = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM users WHERE user_type = 'student'"))['count'];
$total_landlords = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM users WHERE user_type = 'landlord'"))['count'];
$total_properties = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM properties"))['count'];
$pending_properties = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM properties WHERE is_approved = 0"))['count'];
$approved_properties = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM properties WHERE is_approved = 1"))['count'];
$total_inquiries = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM inquiries"))['count'];

// Get recent activities
$recent_properties = mysqli_query($connection, "SELECT p.*, u.first_name, u.last_name FROM properties p JOIN users u ON p.landlord_id = u.id ORDER BY p.created_at DESC LIMIT 5");
$recent_users = mysqli_query($connection, "SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/style.css">
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
                    <span>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</span>
                    <a href="../pages/logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg transition duration-200">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Dashboard Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
                    <p class="text-gray-600 mt-2">System overview and management</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Last Login</p>
                    <p class="font-semibold"><?php echo date('M j, Y g:i A'); ?></p>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Users</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $total_users; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Properties</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $total_properties; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pending Properties</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $pending_properties; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Inquiries</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $total_inquiries; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="users/" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                    <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Manage Users</h3>
                        <p class="text-sm text-gray-500">View and manage all users</p>
                    </div>
                </a>

                <a href="properties/" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                    <div class="p-2 rounded-full bg-green-100 text-green-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Manage Properties</h3>
                        <p class="text-sm text-gray-500">Review and approve properties</p>
                    </div>
                </a>

                <a href="reports/" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                    <div class="p-2 rounded-full bg-purple-100 text-purple-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">View Reports</h3>
                        <p class="text-sm text-gray-500">System analytics and reports</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Properties -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Recent Properties</h2>
                    <p class="text-gray-600 mt-1">Latest property submissions</p>
                </div>
                <div class="p-6">
                    <?php if (mysqli_num_rows($recent_properties) == 0): ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No properties yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Properties will appear here when landlords submit them.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php while ($property = mysqli_fetch_assoc($recent_properties)): ?>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($property['title']); ?></h3>
                                            <p class="text-sm text-gray-600 mt-1">By: <?php echo htmlspecialchars($property['first_name'] . ' ' . $property['last_name']); ?></p>
                                            <p class="text-sm text-gray-500 mt-1">₦<?php echo number_format($property['rent_amount']); ?>/month</p>
                                        </div>
                                        <div class="ml-4">
                                            <?php if ($property['is_approved'] == 1): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Approved
                                                </span>
                                            <?php elseif ($property['is_approved'] == 0): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Rejected
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex space-x-2">
                                        <a href="../pages/properties/details.php?id=<?php echo $property['id']; ?>" class="text-sm text-blue-600 hover:text-blue-800">View</a>
                                        <a href="properties/edit.php?id=<?php echo $property['id']; ?>" class="text-sm text-indigo-600 hover:text-indigo-800">Review</a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <div class="mt-6 text-center">
                            <a href="properties/" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                View All Properties
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Recent Users</h2>
                    <p class="text-gray-600 mt-1">Latest user registrations</p>
                </div>
                <div class="p-6">
                    <?php if (mysqli_num_rows($recent_users) == 0): ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No users yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Users will appear here when they register.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php while ($user_data = mysqli_fetch_assoc($recent_users)): ?>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></h3>
                                            <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($user_data['email']); ?></p>
                                            <p class="text-sm text-gray-500 mt-1"><?php echo ucfirst($user_data['user_type']); ?></p>
                                        </div>
                                        <div class="ml-4">
                                            <?php if ($user_data['is_verified']): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Verified
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex space-x-2">
                                        <a href="users/edit.php?id=<?php echo $user_data['id']; ?>" class="text-sm text-blue-600 hover:text-blue-800">View</a>
                                        <a href="users/edit.php?id=<?php echo $user_data['id']; ?>" class="text-sm text-indigo-600 hover:text-indigo-800">Edit</a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <div class="mt-6 text-center">
                            <a href="users/" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                View All Users
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- System Statistics -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">System Statistics</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600"><?php echo $total_students; ?></div>
                    <div class="text-sm text-gray-500">Students</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600"><?php echo $total_landlords; ?></div>
                    <div class="text-sm text-gray-500">Landlords</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600"><?php echo $approved_properties; ?></div>
                    <div class="text-sm text-gray-500">Approved Properties</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600"><?php echo $pending_properties; ?></div>
                    <div class="text-sm text-gray-500">Pending Properties</div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/main.js"></script>
</body>
</html>
