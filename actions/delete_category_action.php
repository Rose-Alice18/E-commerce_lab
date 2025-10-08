<?php
/**
 * Delete Category Action
 * Handles the deletion of categories
 */

// Start session
session_start();

// Set JSON header
header('Content-Type: application/json');

// Include necessary files
require_once(dirname(__FILE__) . '/../settings/core.php');
require_once(dirname(__FILE__) . '/../controllers/category_controller.php');

try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method. POST required.'
        ]);
        exit();
    }
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo json_encode([
            'success' => false,
            'message' => 'User not authenticated'
        ]);
        exit();
    }
    
    // Check if user is admin
    if (!hasAdminPrivileges()) {
        echo json_encode([
            'success' => false,
            'message' => 'Access denied. Admin privileges required.'
        ]);
        exit();
    }
    
    // Get user ID from session using helper function
    $user_id = getUserId();
    
    // Validate user ID
    if (!$user_id || $user_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid user session'
        ]);
        exit();
    }
    
    // Get and validate form data
    if (!isset($_POST['cat_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Category ID is required'
        ]);
        exit();
    }
    
    $cat_id = intval($_POST['cat_id']);
    
    // Validate category ID
    if ($cat_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid category ID'
        ]);
        exit();
    }
    
    // Before deleting, check if category belongs to the user (security check)
    $category_check = get_category_ctr($cat_id, $user_id);
    if (!$category_check['success']) {
        echo json_encode([
            'success' => false,
            'message' => 'Category not found or access denied'
        ]);
        exit();
    }
    
    // Call controller function to delete category
    $result = delete_category_ctr($cat_id, $user_id);
    
    // Return the result
    echo json_encode($result);
    
} catch (Exception $e) {
    // Log error (in production, use proper logging)
    error_log("Delete category error: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while deleting the category'
    ]);
}
?>