<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

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
$rejected_properties = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM properties WHERE is_approved = -1"))['count'];
$total_inquiries = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM inquiries"))['count'];

// Get monthly statistics for the last 6 months
$monthly_stats = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $next_month = date('Y-m', strtotime("-" . ($i-1) . " months"));
    
    $users_count = mysqli_fetch_assoc(mysqli_query($connection, 
        "SELECT COUNT(*) as count FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'"))['count'];
    
    $properties_count = mysqli_fetch_assoc(mysqli_query($connection, 
        "SELECT COUNT(*) as count FROM properties WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'"))['count'];
    
    $inquiries_count = mysqli_fetch_assoc(mysqli_query($connection, 
        "SELECT COUNT(*) as count FROM inquiries WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'"))['count'];
    
    $monthly_stats[] = [
        'month' => date('M Y', strtotime("-$i months")),
        'users' => $users_count,
        'properties' => $properties_count,
        'inquiries' => $inquiries_count
    ];
}

// Get property type distribution
$property_types = mysqli_query($connection, 
    "SELECT property_type, COUNT(*) as count FROM properties WHERE is_approved = 1 GROUP BY property_type");

// Get recent activities
$recent_activities = mysqli_query($connection, 
    "SELECT 'user' as type, first_name, last_name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5
     UNION ALL
     SELECT 'property' as type, '' as first_name, '' as last_name, title as email, created_at FROM properties ORDER BY created_at DESC LIMIT 5
     ORDER BY created_at DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Reports - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <h1 class="text-3xl font-bold text-gray-800">System Reports</h1>
                    <p class="text-gray-600 mt-2">Analytics and system overview</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Generated</p>
                    <p class="font-semibold"><?php echo date('M j, Y g:i A'); ?></p>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
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
                        <p class="text-xs text-gray-500"><?php echo $total_students; ?> students, <?php echo $total_landlords; ?> landlords</p>
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
                        <p class="text-sm font-medium text-gray-500">Properties</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $total_properties; ?></p>
                        <p class="text-xs text-gray-500"><?php echo $approved_properties; ?> approved, <?php echo $pending_properties; ?> pending</p>
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
                        <p class="text-sm font-medium text-gray-500">Inquiries</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $total_inquiries; ?></p>
                        <p class="text-xs text-gray-500">Total inquiries made</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Approval Rate</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            <?php echo $total_properties > 0 ? round(($approved_properties / $total_properties) * 100, 1) : 0; ?>%
                        </p>
                        <p class="text-xs text-gray-500">Properties approved</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Monthly Trends Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Monthly Trends</h2>
                <canvas id="monthlyChart" width="400" height="200"></canvas>
            </div>

            <!-- Property Type Distribution -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Property Type Distribution</h2>
                <canvas id="propertyTypeChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Property Status Overview -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Property Status Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600"><?php echo $approved_properties; ?></div>
                    <div class="text-sm text-gray-500">Approved Properties</div>
                    <div class="text-xs text-gray-400 mt-1">
                        <?php echo $total_properties > 0 ? round(($approved_properties / $total_properties) * 100, 1) : 0; ?>% of total
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-600"><?php echo $pending_properties; ?></div>
                    <div class="text-sm text-gray-500">Pending Review</div>
                    <div class="text-xs text-gray-400 mt-1">
                        <?php echo $total_properties > 0 ? round(($pending_properties / $total_properties) * 100, 1) : 0; ?>% of total
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-red-600"><?php echo $rejected_properties; ?></div>
                    <div class="text-sm text-gray-500">Rejected Properties</div>
                    <div class="text-xs text-gray-400 mt-1">
                        <?php echo $total_properties > 0 ? round(($rejected_properties / $total_properties) * 100, 1) : 0; ?>% of total
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Activities</h2>
            <div class="space-y-4">
                <?php if (mysqli_num_rows($recent_activities) == 0): ?>
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No recent activities</h3>
                        <p class="mt-1 text-sm text-gray-500">Activities will appear here as they occur.</p>
                    </div>
                <?php else: ?>
                    <?php while ($activity = mysqli_fetch_assoc($recent_activities)): ?>
                        <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg">
                            <div class="flex-shrink-0">
                                <?php if ($activity['type'] === 'user'): ?>
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                <?php else: ?>
                                    <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">
                                    <?php if ($activity['type'] === 'user'): ?>
                                        New user registered: <?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?>
                                    <?php else: ?>
                                        New property added: <?php echo htmlspecialchars($activity['email']); ?>
                                    <?php endif; ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Monthly Trends Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_stats, 'month')); ?>,
                datasets: [{
                    label: 'Users',
                    data: <?php echo json_encode(array_column($monthly_stats, 'users')); ?>,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1
                }, {
                    label: 'Properties',
                    data: <?php echo json_encode(array_column($monthly_stats, 'properties')); ?>,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.1
                }, {
                    label: 'Inquiries',
                    data: <?php echo json_encode(array_column($monthly_stats, 'inquiries')); ?>,
                    borderColor: 'rgb(168, 85, 247)',
                    backgroundColor: 'rgba(168, 85, 247, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Property Type Distribution Chart
        const propertyTypeCtx = document.getElementById('propertyTypeChart').getContext('2d');
        const propertyTypeData = {
            labels: [],
            data: []
        };
        
        <?php while ($type = mysqli_fetch_assoc($property_types)): ?>
            propertyTypeData.labels.push('<?php echo getPropertyTypeLabel($type['property_type']); ?>');
            propertyTypeData.data.push(<?php echo $type['count']; ?>);
        <?php endwhile; ?>
        
        new Chart(propertyTypeCtx, {
            type: 'doughnut',
            data: {
                labels: propertyTypeData.labels,
                datasets: [{
                    data: propertyTypeData.data,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(168, 85, 247, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

    <script src="../../js/main.js"></script>
</body>
</html>
