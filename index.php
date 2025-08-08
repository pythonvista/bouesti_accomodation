<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOUESTI Off-Campus Accommodation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold">BOUESTI Housing</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="pages/dashboard/" class="hover:text-blue-200">Dashboard</a>
                        <a href="pages/logout.php" class="hover:text-blue-200">Logout</a>
                    <?php else: ?>
                        <a href="pages/login.php" class="hover:text-blue-200">Login</a>
                        <a href="pages/register.php" class="bg-white text-blue-600 px-4 py-2 rounded-md hover:bg-gray-100">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-bold mb-4">Find Your Perfect Off-Campus Home</h1>
            <p class="text-xl mb-8">Verified landlords, authentic properties, original prices</p>
            <div class="flex justify-center space-x-4">
                <a href="pages/properties/list.php" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100">
                    Browse Properties
                </a>
                <a href="pages/register.php" class="border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600">
                    Register Now
                </a>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-12">Why Choose BOUESTI Housing?</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Verified Landlords</h3>
                    <p class="text-gray-600">All landlords are verified by BOUESTI administration</p>
                </div>
                <div class="text-center">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Original Prices</h3>
                    <p class="text-gray-600">No hidden fees or markups - prices directly from landlords</p>
                </div>
                <div class="text-center">
                    <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Convenient Locations</h3>
                    <p class="text-gray-600">Properties located near BOUESTI campus</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Properties -->
    <div class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-12">Recent Properties</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <?php
                $recent_properties = getRecentProperties(6);
                if ($recent_properties && mysqli_num_rows($recent_properties) > 0):
                    while ($property = mysqli_fetch_assoc($recent_properties)):
                ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="h-48 bg-gray-200">
                        <?php
                        $images = getPropertyImages($property['id']);
                        if ($images && mysqli_num_rows($images) > 0):
                            $image = mysqli_fetch_assoc($images);
                        ?>
                        <img src="<?php echo $image['image_path']; ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($property['title']); ?></h3>
                        <p class="text-gray-600 text-sm mb-2"><?php echo htmlspecialchars($property['address']); ?></p>
                        <p class="text-blue-600 font-bold">₦<?php echo number_format($property['rent_amount']); ?>/month</p>
                        <a href="pages/properties/details.php?id=<?php echo $property['id']; ?>" class="mt-3 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">View Details</a>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <div class="col-span-3 text-center text-gray-500">
                    <p>No properties available at the moment.</p>
                </div>
                <?php endif; ?>
            </div>
            <div class="text-center mt-8">
                <a href="pages/properties/list.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700">
                    View All Properties
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p>&copy; 2024 BOUESTI Off-Campus Accommodation System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
