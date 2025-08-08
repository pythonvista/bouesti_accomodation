<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Ensure user is logged in and is a landlord
requireLandlord();

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Get landlord's properties
$properties = getLandlordProperties($user_id);

// Handle property deletion
if (isset($_POST['delete_property']) && isset($_POST['property_id'])) {
    $property_id = (int)$_POST['property_id'];
    $property = getPropertyById($property_id);
    
    // Ensure the property belongs to this landlord
    if ($property && $property['landlord_id'] == $user_id) {
        if (deleteProperty($property_id)) {
            setMessage('success', 'Property deleted successfully.');
        } else {
            setMessage('error', 'Failed to delete property.');
        }
    } else {
        setMessage('error', 'You are not authorized to delete this property.');
    }
    
    header('Location: my_properties.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Properties - <?php echo SITE_NAME; ?></title>
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
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">My Properties</h1>
                    <p class="text-gray-600 mt-2">Manage your property listings</p>
                </div>
                <a href="add.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add New Property
                </a>
            </div>
        </div>

        <!-- Display Messages -->
        <?php displayMessage(); ?>

        <!-- Properties List -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Property Listings</h2>
                <p class="text-gray-600 mt-1">You have <?php echo count($properties); ?> property listing(s)</p>
            </div>
            
            <div class="p-6">
                <?php if (empty($properties)): ?>
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No properties yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by adding your first property.</p>
                        <div class="mt-6">
                            <a href="add.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Add Property
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rent</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($properties as $property): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-12 w-12">
                                                    <?php 
                                                    $images = getPropertyImages($property['id']);
                                                    if (!empty($images)): 
                                                    ?>
                                                        <img class="h-12 w-12 rounded-lg object-cover" src="../../<?php echo htmlspecialchars($images[0]['image_path']); ?>" alt="Property">
                                                    <?php else: ?>
                                                        <div class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                                                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                            </svg>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($property['title']); ?></div>
                                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($property['address']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($property['property_type']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">₦<?php echo number_format($property['rent_amount']); ?>/month</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
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
                                                <?php if (!empty($property['rejection_reason'])): ?>
                                                    <div class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($property['rejection_reason']); ?></div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M j, Y', strtotime($property['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="details.php?id=<?php echo $property['id']; ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                                <a href="edit.php?id=<?php echo $property['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                <button onclick="confirmDelete(<?php echo $property['id']; ?>, '<?php echo htmlspecialchars(addslashes($property['title'])); ?>')" class="text-red-600 hover:text-red-900">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics -->
        <?php if (!empty($properties)): ?>
            <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
                <?php
                $total_properties = count($properties);
                $approved_properties = 0;
                $pending_properties = 0;
                $rejected_properties = 0;
                $total_rent = 0;

                foreach ($properties as $property) {
                    if ($property['is_approved'] == 1) {
                        $approved_properties++;
                        $total_rent += $property['rent_amount'];
                    } elseif ($property['is_approved'] == 0) {
                        $pending_properties++;
                    } else {
                        $rejected_properties++;
                    }
                }
                ?>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
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
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Approved</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $approved_properties; ?></p>
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
                            <p class="text-sm font-medium text-gray-500">Pending</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $pending_properties; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Rent Value</p>
                            <p class="text-2xl font-semibold text-gray-900">₦<?php echo number_format($total_rent); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Delete Property</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">Are you sure you want to delete "<span id="propertyTitle"></span>"? This action cannot be undone.</p>
                </div>
                <div class="items-center px-4 py-3">
                    <form id="deleteForm" method="POST" class="inline">
                        <input type="hidden" name="delete_property" value="1">
                        <input type="hidden" name="property_id" id="propertyId">
                        <button type="submit" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-600">
                            Delete
                        </button>
                    </form>
                    <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../../js/main.js"></script>
    <script>
        function confirmDelete(propertyId, propertyTitle) {
            document.getElementById('propertyId').value = propertyId;
            document.getElementById('propertyTitle').textContent = propertyTitle;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>
