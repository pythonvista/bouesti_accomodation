<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$property_type = isset($_GET['type']) ? $_GET['type'] : '';
$max_rent = isset($_GET['max_rent']) ? $_GET['max_rent'] : '';

// Get approved properties
$properties = getApprovedProperties($search, $property_type, $max_rent);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Properties - BOUESTI Off-Campus Accommodation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="../../index.php" class="text-xl font-bold">BOUESTI Housing</a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <a href="../dashboard/" class="hover:text-blue-200">Dashboard</a>
                        <a href="../logout.php" class="hover:text-blue-200">Logout</a>
                    <?php else: ?>
                        <a href="../login.php" class="hover:text-blue-200">Login</a>
                        <a href="../register.php" class="bg-white text-blue-600 px-4 py-2 rounded-md hover:bg-gray-100">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Browse Properties</h1>
            <p class="text-gray-600">Find your perfect off-campus accommodation near BOUESTI</p>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="property-search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" id="property-search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           class="form-input" placeholder="Search properties...">
                </div>
                
                <div>
                    <label for="property-type" class="block text-sm font-medium text-gray-700 mb-1">Property Type</label>
                    <select id="property-type" name="type" class="form-input">
                        <option value="">All Types</option>
                        <option value="single_room" <?php echo $property_type === 'single_room' ? 'selected' : ''; ?>>Single Room</option>
                        <option value="shared_room" <?php echo $property_type === 'shared_room' ? 'selected' : ''; ?>>Shared Room</option>
                        <option value="apartment" <?php echo $property_type === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                    </select>
                </div>
                
                <div>
                    <label for="max-rent" class="block text-sm font-medium text-gray-700 mb-1">Max Rent (₦)</label>
                    <input type="number" id="max-rent" name="max_rent" value="<?php echo htmlspecialchars($max_rent); ?>" 
                           class="form-input" placeholder="Maximum rent">
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="btn btn-primary w-full">Search</button>
                </div>
            </form>
        </div>

        <!-- Results Count -->
        <div class="flex justify-between items-center mb-6">
            <p class="text-gray-600">
                Showing <span id="property-count"><?php echo mysqli_num_rows($properties); ?></span> properties
            </p>
            <?php if ($search || $property_type || $max_rent): ?>
                <a href="list.php" class="text-blue-600 hover:text-blue-800">Clear filters</a>
            <?php endif; ?>
        </div>

        <!-- Properties Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($properties && mysqli_num_rows($properties) > 0): ?>
                <?php while ($property = mysqli_fetch_assoc($properties)): ?>
                    <div class="property-card bg-white rounded-lg shadow-md overflow-hidden" 
                         data-type="<?php echo $property['property_type']; ?>" 
                         data-rent="<?php echo $property['rent_amount']; ?>">
                        
                        <!-- Property Image -->
                        <div class="h-48 bg-gray-200">
                            <?php
                            $images = getPropertyImages($property['id']);
                            if ($images && mysqli_num_rows($images) > 0):
                                $image = mysqli_fetch_assoc($images);
                            ?>
                                <img src="../../<?php echo $image['image_path']; ?>" 
                                     alt="<?php echo htmlspecialchars($property['title']); ?>" 
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Property Details -->
                        <div class="p-4">
                            <h3 class="property-title text-lg font-semibold mb-2"><?php echo htmlspecialchars($property['title']); ?></h3>
                            <p class="property-address text-gray-600 text-sm mb-2"><?php echo htmlspecialchars($property['address']); ?></p>
                            
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-blue-600 font-bold rent-amount">₦<?php echo number_format($property['rent_amount']); ?>/month</span>
                                <span class="status-badge status-verified"><?php echo getPropertyTypeLabel($property['property_type']); ?></span>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars(substr($property['description'], 0, 100)); ?>...</p>
                            
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500">
                                    <span class="status-badge status-verified">Verified Landlord</span>
                                </div>
                                <a href="details.php?id=<?php echo $property['id']; ?>" 
                                   class="btn btn-primary text-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No properties found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        <?php if ($search || $property_type || $max_rent): ?>
                            Try adjusting your search criteria.
                        <?php else: ?>
                            No properties are currently available.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../../js/main.js"></script>
</body>
</html>
