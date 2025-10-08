<?php
/**
 * Add Category Action
 * Handles the creation of new categories
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
    if (!isset($_POST['category_name']) || empty(trim($_POST['category_name']))) {
        echo json_encode([
            'success' => false,
            'message' => 'Category name is required'
        ]);
        exit();
    }
    
    $category_name = trim($_POST['category_name']);
    
    // Additional server-side validation
    if (strlen($category_name) > 100) {
        echo json_encode([
            'success' => false,
            'message' => 'Category name must be less than 100 characters'
        ]);
        exit();
    }
    
    // Check for potentially harmful characters
    if (preg_match('/[<>"\']/', $category_name)) {
        echo json_encode([
            'success' => false,
            'message' => 'Category name contains invalid characters'
        ]);
        exit();
    }
    
    // Prepare data for controller
    $category_data = [
        'category_name' => $category_name,
        'user_id' => $user_id
    ];
    
    // Call controller function to add category
    $result = add_category_ctr($category_data);
    
    // Return the result
    echo json_encode($result);
    
} catch (Exception $e) {
    // Log error (in production, use proper logging)
    error_log("Add category error: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while adding the category'
    ]);
}
?>