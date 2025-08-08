<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

// Redirect based on user type
if (isStudent()) {
    header('Location: student_dashboard.php');
    exit();
} elseif (isLandlord()) {
    header('Location: landlord_dashboard.php');
    exit();
} else {
    // If user type is not recognized, redirect to login
    session_destroy();
    header('Location: ../login.php');
    exit();
}
?>
