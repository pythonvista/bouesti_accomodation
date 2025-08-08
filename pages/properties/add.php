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

    // If no errors, create the property
    if (empty($errors)) {
        $property_id = createProperty($user_id, $title, $description, $address, $rent_amount, $property_type, $amenities);
        
        if ($property_id) {
            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $upload_result = uploadPropertyImages($_FILES['images'], $property_id);
                if (!$upload_result) {
                    $errors[] = "Some images failed to upload";
                }
            }
            
            if (empty($errors)) {
                setMessage('success', 'Property added successfully! It will be reviewed by admin before being published.');
                header('Location: my_properties.php');
                exit;
            }
        } else {
            $errors[] = "Failed to create property. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - <?php echo SITE_NAME; ?></title>
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
                    <h1 class="text-3xl font-bold text-gray-800">Add New Property</h1>
                    <p class="text-gray-600 mt-2">Create a new property listing for students</p>
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

        <!-- Add Property Form -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Property Details</h2>
                <p class="text-gray-600 mt-1">Fill in the details below to create your property listing</p>
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
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">A catchy title for your property</p>
                    </div>

                    <div>
                        <label for="property_type" class="block text-sm font-medium text-gray-700">Property Type *</label>
                        <select id="property_type" name="property_type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select property type</option>
                            <option value="Apartment" <?php echo ($_POST['property_type'] ?? '') === 'Apartment' ? 'selected' : ''; ?>>Apartment</option>
                            <option value="Studio" <?php echo ($_POST['property_type'] ?? '') === 'Studio' ? 'selected' : ''; ?>>Studio</option>
                            <option value="Shared Room" <?php echo ($_POST['property_type'] ?? '') === 'Shared Room' ? 'selected' : ''; ?>>Shared Room</option>
                            <option value="Private Room" <?php echo ($_POST['property_type'] ?? '') === 'Private Room' ? 'selected' : ''; ?>>Private Room</option>
                            <option value="House" <?php echo ($_POST['property_type'] ?? '') === 'House' ? 'selected' : ''; ?>>House</option>
                            <option value="Condo" <?php echo ($_POST['property_type'] ?? '') === 'Condo' ? 'selected' : ''; ?>>Condo</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Address *</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-sm text-gray-500">Full address of the property</p>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                    <textarea id="description" name="description" rows="4" required
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    <p class="mt-1 text-sm text-gray-500">Detailed description of the property, amenities, and any special features</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="rent_amount" class="block text-sm font-medium text-gray-700">Monthly Rent (₦) *</label>
                        <input type="number" id="rent_amount" name="rent_amount" value="<?php echo htmlspecialchars($_POST['rent_amount'] ?? ''); ?>" min="0" step="1000" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">Monthly rent amount in Nigerian Naira</p>
                    </div>

                    <div>
                        <label for="amenities" class="block text-sm font-medium text-gray-700">Amenities</label>
                        <input type="text" id="amenities" name="amenities" value="<?php echo htmlspecialchars($_POST['amenities'] ?? ''); ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">e.g., WiFi, Kitchen, Parking, Security (comma separated)</p>
                    </div>
                </div>

                <!-- Image Upload -->
                <div>
                    <label for="images" class="block text-sm font-medium text-gray-700">Property Images</label>
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

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <a href="my_properties.php" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Property
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
