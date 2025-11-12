<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core and controller
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/brand_controller.php';

// Set header for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to perform this action',
        'data' => []
    ]);
    exit();
}

// Check if user is admin
if (!hasAdminPrivileges()) {
    echo json_encode([
        'success' => false,
        'message' => 'Only administrators can view brands',
        'data' => []
    ]);
    exit();
}

// Get user ID from session
$user_id = getUserId();

// Both super admins and pharmacy admins see ALL platform brands
// The difference is in edit/delete permissions (handled in the frontend)
$result = get_all_brands_ctr(null); // Fetch all platform brands (null = platform-wide)

// Return response
echo json_encode($result);
