<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Ensure user is logged in and is a student
requireStudent();

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Get student's inquiries
$inquiries = getInquiriesByStudent($user_id);

// Get recent properties for recommendations
$recent_properties = getRecentProperties(4);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <a href="../../index.php" class="text-xl font-bold">BOUESTI Housing</a>
                </div>
                <div class="flex items-center space-x-4">
                    <span>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</span>
                    <a href="../../pages/logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg transition duration-200">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Dashboard Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Student Dashboard</h1>
                    <p class="text-gray-600 mt-2">Manage your accommodation inquiries and preferences</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Student ID</p>
                    <p class="font-semibold"><?php echo htmlspecialchars($user['student_id']); ?></p>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Inquiries Sent</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo mysqli_num_rows($inquiries); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Properties Available</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo mysqli_num_rows($recent_properties); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Account Status</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $user['is_verified'] ? 'Verified' : 'Pending'; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- My Inquiries -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">My Inquiries</h2>
                    <p class="text-gray-600 mt-1">Properties you've inquired about</p>
                </div>
                <div class="p-6">
                    <?php if (mysqli_num_rows($inquiries) == 0): ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No inquiries yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Start browsing properties to send inquiries.</p>
                            <div class="mt-6">
                                <a href="../../pages/properties/list.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Browse Properties
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($inquiries as $inquiry): ?>
                                <?php $property = getPropertyById($inquiry['property_id']); ?>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($property['title']); ?></h3>
                                            <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($property['address']); ?></p>
                                            <p class="text-sm text-gray-500 mt-2"><?php echo htmlspecialchars($inquiry['message']); ?></p>
                                            <p class="text-xs text-gray-400 mt-2">Sent: <?php echo date('M j, Y', strtotime($inquiry['created_at'])); ?></p>
                                        </div>
                                        <div class="ml-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recommended Properties -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Recommended Properties</h2>
                    <p class="text-gray-600 mt-1">Recently added properties you might like</p>
                </div>
                <div class="p-6">
                    <?php if (mysqli_num_rows($recent_properties) == 0): ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No properties available</h3>
                            <p class="mt-1 text-sm text-gray-500">Check back later for new listings.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($recent_properties as $property): ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-200">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($property['title']); ?></h3>
                                            <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($property['address']); ?></p>
                                            <p class="text-lg font-bold text-blue-600 mt-2">₦<?php echo number_format($property['rent_amount']); ?>/month</p>
                                        </div>
                                        <div class="ml-4">
                                            <a href="../../pages/properties/details.php?id=<?php echo $property['id']; ?>" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-blue-600 bg-blue-100 hover:bg-blue-200">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-6 text-center">
                            <a href="../../pages/properties/list.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                View All Properties
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="bg-white rounded-lg shadow-md mt-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Account Information</h2>
                <p class="text-gray-600 mt-1">Your personal details and preferences</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($user['phone']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Student ID</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($user['student_id']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Status</label>
                        <p class="mt-1">
                            <?php if ($user['is_verified']): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Verified
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending Verification
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Member Since</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../js/main.js"></script>
</body>
</html>
