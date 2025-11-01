<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core and controller
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/brand_controller.php');

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

// Check if user is admin (only admins can manage platform-wide brands)
if (!hasAdminPrivileges()) {
    echo json_encode([
        'success' => false,
        'message' => 'Only administrators can manage brands'
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
$brand_name = isset($_POST['brand_name']) ? trim($_POST['brand_name']) : '';
$cat_id = isset($_POST['cat_id']) ? trim($_POST['cat_id']) : '';

// Validate inputs
if (empty($brand_name)) {
    echo json_encode([
        'success' => false,
        'message' => 'Brand name is required'
    ]);
    exit();
}

if (empty($cat_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Category is required'
    ]);
    exit();
}

// Call controller with user_id
$result = add_brand_ctr([
    'brand_name' => $brand_name,
    'cat_id' => $cat_id,
    'user_id' => $user_id
]);

// Return response
echo json_encode($result);
?>