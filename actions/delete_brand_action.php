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
        'message' => 'You must be logged in to perform this action'
    ]);
    exit();
}

// Check if user is admin
if (!hasAdminPrivileges()) {
    echo json_encode([
        'success' => false,
        'message' => 'Only administrators can delete brands'
    ]);
    exit();
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

// Get user ID from session
$user_id = getUserId();

// Get POST data
$brand_id = isset($_POST['brand_id']) ? trim($_POST['brand_id']) : '';

// Validate inputs
if (empty($brand_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Brand ID is required'
    ]);
    exit();
}

// Call controller with user_id
$result = delete_brand_ctr((int)$brand_id, $user_id);

// Return response
echo json_encode($result);
