<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Ensure user is logged in and is a landlord
requireLandlord();

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

$errors = [];
$success = false;

// Get property ID from URL
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$property_id) {
    header('Location: my_properties.php');
    exit;
}

// Get property details
$property = getPropertyById($property_id);

// Ensure the property belongs to this landlord
if (!$property || $property['landlord_id'] != $user_id) {
    setMessage('error', 'You are not authorized to edit this property.');
    header('Location: my_properties.php');
    exit;
}

// Get existing images
$existing_images = getPropertyImages($property_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $rent_amount = sanitizeInput($_POST['rent_amount'] ?? '');
    $property_type = sanitizeInput($_POST['property_type'] ?? '');
    $amenities = sanitizeInput($_POST['amenities'] ?? '');

    // Validation
    if (empty($title)) {
        $errors[] = "Property title is required";
    }
    if (empty($description)) {
        $errors[] = "Property description is required";
    }
    if (empty($address)) {
        $errors[] = "Property address is required";
    }
    if (empty($rent_amount) || !is_numeric($rent_amount) || $rent_amount <= 0) {
        $errors[] = "Valid rent amount is required";
    }
    if (empty($property_type)) {
        $errors[] = "Property type is required";
    }

    // If no errors, update the property
    if (empty($errors)) {
        // Update property details
        $update_query = "UPDATE properties SET 
                        title = ?, 
                        description = ?, 
                        address = ?, 
                        rent_amount = ?, 
                        property_type = ?, 
                        amenities = ?,
                        updated_at = NOW()
                        WHERE id = ? AND landlord_id = ?";
        
        $stmt = mysqli_prepare($connection, $update_query);
        mysqli_stmt_bind_param($stmt, "sssdssii", $title, $description, $address, $rent_amount, $property_type, $amenities, $property_id, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Handle new image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $upload_result = uploadPropertyImages($_FILES['images'], $property_id);
                if (!$upload_result) {
                    $errors[] = "Some images failed to upload";
                }
            }
            
            if (empty($errors)) {
                setMessage('success', 'Property updated successfully!');
                header('Location: my_properties.php');
                exit;
            }
        } else {
            $errors[] = "Failed to update property. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property - <?php echo SITE_NAME; ?></title>
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

    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Edit Property</h1>
                    <p class="text-gray-600 mt-2">Update your property listing details</p>
                </div>
                <a href="my_properties.php" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to My Properties
                </a>
            </div>
        </div>

        <!-- Display Messages -->
        <?php displayMessage(); ?>

        <!-- Edit Property Form -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Property Details</h2>
                <p class="text-gray-600 mt-1">Update the details below to modify your property listing</p>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Property Title *</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($property['title']); ?>" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">A catchy title for your property</p>
                    </div>

                    <div>
                        <label for="property_type" class="block text-sm font-medium text-gray-700">Property Type *</label>
                        <select id="property_type" name="property_type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select property type</option>
                            <option value="Apartment" <?php echo $property['property_type'] === 'Apartment' ? 'selected' : ''; ?>>Apartment</option>
                            <option value="Studio" <?php echo $property['property_type'] === 'Studio' ? 'selected' : ''; ?>>Studio</option>
                            <option value="Shared Room" <?php echo $property['property_type'] === 'Shared Room' ? 'selected' : ''; ?>>Shared Room</option>
                            <option value="Private Room" <?php echo $property['property_type'] === 'Private Room' ? 'selected' : ''; ?>>Private Room</option>
                            <option value="House" <?php echo $property['property_type'] === 'House' ? 'selected' : ''; ?>>House</option>
                            <option value="Condo" <?php echo $property['property_type'] === 'Condo' ? 'selected' : ''; ?>>Condo</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Address *</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($property['address']); ?>" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-sm text-gray-500">Full address of the property</p>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                    <textarea id="description" name="description" rows="4" required
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($property['description']); ?></textarea>
                    <p class="mt-1 text-sm text-gray-500">Detailed description of the property, amenities, and any special features</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="rent_amount" class="block text-sm font-medium text-gray-700">Monthly Rent (₦) *</label>
                        <input type="number" id="rent_amount" name="rent_amount" value="<?php echo htmlspecialchars($property['rent_amount']); ?>" min="0" step="1000" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">Monthly rent amount in Nigerian Naira</p>
                    </div>

                    <div>
                        <label for="amenities" class="block text-sm font-medium text-gray-700">Amenities</label>
                        <input type="text" id="amenities" name="amenities" value="<?php echo htmlspecialchars($property['amenities']); ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">e.g., WiFi, Kitchen, Parking, Security (comma separated)</p>
                    </div>
                </div>

                <!-- Existing Images -->
                <?php if (!empty($existing_images)): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Images</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <?php foreach ($existing_images as $image): ?>
                                <div class="relative">
                                    <img src="../../<?php echo htmlspecialchars($image['image_path']); ?>" 
                                         alt="Property Image" 
                                         class="w-full h-32 object-cover rounded-lg">
                                    <button type="button" 
                                            onclick="deleteImage(<?php echo $image['id']; ?>)" 
                                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                        ×
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- New Image Upload -->
                <div>
                    <label for="images" class="block text-sm font-medium text-gray-700">Add New Images</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="images" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Upload images</span>
                                    <input id="images" name="images[]" type="file" class="sr-only" multiple accept="image/*">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB each (max 5 images)</p>
                        </div>
                    </div>
                    <div id="image-preview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                </div>

                <!-- Property Status -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Property Status</h3>
                    <div class="flex items-center space-x-4">
                        <?php if ($property['is_approved'] == 1): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Approved
                            </span>
                        <?php elseif ($property['is_approved'] == 0): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Pending Review
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Rejected
                            </span>
                            <?php if (!empty($property['rejection_reason'])): ?>
                                <p class="text-sm text-gray-600">Reason: <?php echo htmlspecialchars($property['rejection_reason']); ?></p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Note: After editing, your property will need to be reviewed again by admin.</p>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <a href="my_properties.php" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Update Property
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../js/main.js"></script>
    <script>
        // Image preview functionality
        document.getElementById('images').addEventListener('change', function(e) {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';
            
            for (let i = 0; i < e.target.files.length; i++) {
                const file = e.target.files[i];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'relative';
                        div.innerHTML = `
                            <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg">
                            <button type="button" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600" onclick="this.parentElement.remove()">
                                ×
                            </button>
                        `;
                        preview.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        // Delete image functionality
        function deleteImage(imageId) {
            if (confirm('Are you sure you want to delete this image?')) {
                // You would typically make an AJAX call here to delete the image
                // For now, we'll just remove it from the DOM
                const imageElement = document.querySelector(`[onclick="deleteImage(${imageId})"]`).parentElement;
                imageElement.remove();
            }
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const address = document.getElementById('address').value.trim();
            const rentAmount = document.getElementById('rent_amount').value;
            const propertyType = document.getElementById('property_type').value;

            if (!title || !description || !address || !rentAmount || !propertyType) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            if (rentAmount <= 0) {
                e.preventDefault();
                alert('Rent amount must be greater than 0.');
                return false;
            }
        });
    </script>
</body>
</html>
