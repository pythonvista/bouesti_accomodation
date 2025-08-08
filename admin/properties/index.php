<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Ensure user is logged in and is an admin
requireAdmin();

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Handle property actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['property_id'])) {
        $property_id = (int)$_POST['property_id'];
        $action = $_POST['action'];
        
        switch ($action) {
            case 'approve':
                if (updatePropertyStatus($property_id, 1)) {
                    setMessage('success', 'Property approved successfully.');
                } else {
                    setMessage('error', 'Failed to approve property.');
                }
                break;
            case 'reject':
                $rejection_reason = $_POST['rejection_reason'] ?? '';
                if (updatePropertyStatus($property_id, -1, $rejection_reason)) {
                    setMessage('success', 'Property rejected successfully.');
                } else {
                    setMessage('error', 'Failed to reject property.');
                }
                break;
            case 'delete':
                if (deleteProperty($property_id)) {
                    setMessage('success', 'Property deleted successfully.');
                } else {
                    setMessage('error', 'Failed to delete property.');
                }
                break;
        }
        
        header('Location: index.php');
        exit();
    }
}

// Get filter parameters
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$property_type = $_GET['property_type'] ?? '';

// Get properties with filters
$sql = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.is_verified as landlord_verified 
        FROM properties p 
        JOIN users u ON p.landlord_id = u.id 
        WHERE 1=1";

if ($status === 'approved') {
    $sql .= " AND p.is_approved = 1";
} elseif ($status === 'pending') {
    $sql .= " AND p.is_approved = 0";
} elseif ($status === 'rejected') {
    $sql .= " AND p.is_approved = -1";
}

if ($search) {
    $search = sanitizeInput($search);
    $sql .= " AND (p.title LIKE '%$search%' OR p.address LIKE '%$search%' OR u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%')";
}

if ($property_type) {
    $property_type = sanitizeInput($property_type);
    $sql .= " AND p.property_type = '$property_type'";
}

$sql .= " ORDER BY p.created_at DESC";

$properties = mysqli_query($connection, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Properties - Admin Dashboard</title>
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
                    <h1 class="text-3xl font-bold text-gray-800">Manage Properties</h1>
                    <p class="text-gray-600 mt-2">Review and approve property listings</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Total Properties</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo mysqli_num_rows($properties); ?></p>
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
                           placeholder="Property title, address, or landlord...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Property Type</label>
                    <select name="property_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="single_room" <?php echo $property_type === 'single_room' ? 'selected' : ''; ?>>Single Room</option>
                        <option value="shared_room" <?php echo $property_type === 'shared_room' ? 'selected' : ''; ?>>Shared Room</option>
                        <option value="apartment" <?php echo $property_type === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
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

        <!-- Properties Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (mysqli_num_rows($properties) == 0): ?>
                <div class="col-span-full text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No properties found</h3>
                    <p class="mt-1 text-sm text-gray-500">No properties match your current filters.</p>
                </div>
            <?php else: ?>
                <?php while ($property = mysqli_fetch_assoc($properties)): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($property['title']); ?></h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php echo $property['is_approved'] == 1 ? 'bg-green-100 text-green-800' : 
                                            ($property['is_approved'] == 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                    <?php echo $property['is_approved'] == 1 ? 'Approved' : 
                                            ($property['is_approved'] == 0 ? 'Pending' : 'Rejected'); ?>
                                </span>
                            </div>
                            
                            <div class="space-y-2 mb-4">
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">Address:</span> <?php echo htmlspecialchars($property['address']); ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">Type:</span> <?php echo getPropertyTypeLabel($property['property_type']); ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">Rent:</span> ₦<?php echo number_format($property['rent_amount']); ?>/month
                                </p>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">Landlord:</span> 
                                    <?php echo htmlspecialchars($property['first_name'] . ' ' . $property['last_name']); ?>
                                    <?php if ($property['landlord_verified']): ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-1">
                                            Verified
                                        </span>
                                    <?php endif; ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">Contact:</span> <?php echo htmlspecialchars($property['email']); ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <span class="font-medium">Submitted:</span> <?php echo date('M j, Y', strtotime($property['created_at'])); ?>
                                </p>
                            </div>
                            
                            <?php if ($property['description']): ?>
                                <div class="mb-4">
                                    <p class="text-sm text-gray-700"><?php echo htmlspecialchars(substr($property['description'], 0, 150)) . (strlen($property['description']) > 150 ? '...' : ''); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($property['is_approved'] == -1 && $property['rejection_reason']): ?>
                                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                                    <p class="text-sm text-red-800">
                                        <span class="font-medium">Rejection Reason:</span> <?php echo htmlspecialchars($property['rejection_reason']); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex flex-wrap gap-2">
                                <a href="../../pages/properties/details.php?id=<?php echo $property['id']; ?>" 
                                   class="text-sm text-blue-600 hover:text-blue-800">View Details</a>
                                
                                <?php if ($property['is_approved'] == 0): ?>
                                    <form method="POST" class="inline" onsubmit="return confirm('Approve this property?')">
                                        <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="text-sm text-green-600 hover:text-green-800">Approve</button>
                                    </form>
                                    
                                    <button onclick="showRejectModal(<?php echo $property['id']; ?>)" 
                                            class="text-sm text-red-600 hover:text-red-800">Reject</button>
                                <?php elseif ($property['is_approved'] == 1): ?>
                                    <form method="POST" class="inline" onsubmit="return confirm('Reject this property?')">
                                        <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="rejection_reason" value="Admin rejection">
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">Reject</button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this property? This action cannot be undone.')">
                                    <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Property</h3>
                <form method="POST" id="rejectForm">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="property_id" id="rejectPropertyId">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                        <textarea name="rejection_reason" rows="3" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideRejectModal()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                            Reject Property
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showRejectModal(propertyId) {
            document.getElementById('rejectPropertyId').value = propertyId;
            document.getElementById('rejectModal').classList.remove('hidden');
        }
        
        function hideRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>

    <script src="../../js/main.js"></script>
</body>
</html>
