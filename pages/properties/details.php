<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Get property ID from URL
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$property_id) {
    header('Location: list.php');
    exit();
}

// Get property details
$property = getPropertyById($property_id);

if (!$property || !$property['is_approved'] || !$property['landlord_verified']) {
    header('Location: list.php');
    exit();
}

// Handle inquiry submission
$inquiry_sent = false;
$inquiry_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_inquiry'])) {
    if (!isLoggedIn()) {
        $inquiry_error = 'Please login to send an inquiry.';
    } elseif (!isStudent()) {
        $inquiry_error = 'Only students can send inquiries.';
    } else {
        $message = sanitizeInput($_POST['message']);
        if (empty($message)) {
            $inquiry_error = 'Please enter a message.';
        } else {
            if (createInquiry($_SESSION['user_id'], $property_id, $message)) {
                $inquiry_sent = true;
            } else {
                $inquiry_error = 'Failed to send inquiry. Please try again.';
            }
        }
    }
}

// Get property images
$images = getPropertyImages($property_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['title']); ?> - BOUESTI Off-Campus Accommodation</title>
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
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li><a href="../../index.php" class="hover:text-blue-600">Home</a></li>
                <li><span class="mx-2">/</span></li>
                <li><a href="list.php" class="hover:text-blue-600">Properties</a></li>
                <li><span class="mx-2">/</span></li>
                <li class="text-gray-900"><?php echo htmlspecialchars($property['title']); ?></li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Property Images -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                    <div class="image-carousel relative h-96">
                        <?php if ($images && mysqli_num_rows($images) > 0): ?>
                            <?php $image_index = 0; ?>
                            <?php while ($image = mysqli_fetch_assoc($images)): ?>
                                <img src="../../<?php echo $image['image_path']; ?>" 
                                     alt="<?php echo htmlspecialchars($property['title']); ?>" 
                                     class="carousel-image w-full h-full object-cover <?php echo $image_index === 0 ? '' : 'hidden'; ?>">
                                <?php $image_index++; ?>
                            <?php endwhile; ?>
                            
                            <?php if (mysqli_num_rows($images) > 1): ?>
                                <button class="carousel-nav prev">‹</button>
                                <button class="carousel-nav next">›</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">
                                <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Property Details -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($property['title']); ?></h1>
                            <p class="text-gray-600"><?php echo htmlspecialchars($property['address']); ?></p>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-blue-600">₦<?php echo number_format($property['rent_amount']); ?></div>
                            <div class="text-gray-500">per month</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="text-center">
                            <div class="text-lg font-semibold text-gray-900"><?php echo getPropertyTypeLabel($property['property_type']); ?></div>
                            <div class="text-sm text-gray-500">Type</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-gray-900">Available</div>
                            <div class="text-sm text-gray-500">Status</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-gray-900">₦<?php echo number_format($property['rent_amount']); ?></div>
                            <div class="text-sm text-gray-500">Rent</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-gray-900">Verified</div>
                            <div class="text-sm text-gray-500">Landlord</div>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Description</h3>
                        <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                    </div>

                    <?php if (!empty($property['amenities'])): ?>
                        <div class="border-t pt-6 mt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Amenities</h3>
                            <p class="text-gray-700"><?php echo htmlspecialchars($property['amenities']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Contact Information -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <div class="text-sm text-gray-500">Landlord</div>
                            <div class="font-medium"><?php echo htmlspecialchars($property['first_name'] . ' ' . $property['last_name']); ?></div>
                        </div>
                        
                        <div>
                            <div class="text-sm text-gray-500">Phone</div>
                            <div class="font-medium">
                                <a href="tel:<?php echo htmlspecialchars($property['phone']); ?>" class="text-blue-600 hover:text-blue-800">
                                    <?php echo htmlspecialchars($property['phone']); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div>
                            <div class="text-sm text-gray-500">Email</div>
                            <div class="font-medium">
                                <a href="mailto:<?php echo htmlspecialchars($property['email']); ?>" class="text-blue-600 hover:text-blue-800">
                                    <?php echo htmlspecialchars($property['email']); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Send Inquiry -->
                <?php if (isLoggedIn() && isStudent()): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Send Inquiry</h3>
                        
                        <?php if ($inquiry_sent): ?>
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                                Your inquiry has been sent successfully!
                            </div>
                        <?php endif; ?>

                        <?php if ($inquiry_error): ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                <?php echo htmlspecialchars($inquiry_error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-4">
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                                <textarea id="message" name="message" rows="4" required 
                                          class="form-input form-textarea" 
                                          placeholder="Ask about availability, viewing, or any questions..."></textarea>
                            </div>
                            
                            <button type="submit" name="send_inquiry" 
                                    class="w-full btn btn-primary">
                                Send Inquiry
                            </button>
                        </form>
                    </div>
                <?php elseif (!isLoggedIn()): ?>
                    <div class="bg-blue-50 rounded-lg p-6 text-center">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Want to contact the landlord?</h3>
                        <p class="text-gray-600 mb-4">Login as a student to send inquiries and get more information.</p>
                        <a href="../login.php" class="btn btn-primary w-full">Login</a>
                    </div>
                <?php endif; ?>

                <!-- Property Actions -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    
                    <div class="space-y-3">
                        <a href="tel:<?php echo htmlspecialchars($property['phone']); ?>" 
                           class="w-full btn btn-outline flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            Call Landlord
                        </a>
                        
                        <a href="mailto:<?php echo htmlspecialchars($property['email']); ?>" 
                           class="w-full btn btn-outline flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Email Landlord
                        </a>
                        
                        <a href="list.php" class="w-full btn btn-secondary flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            View More Properties
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../js/main.js"></script>
</body>
</html>
