<?php
/**
 * Fetch Category Action
 * Retrieves all categories for the logged-in user
 */

// Start session
session_start();

// Set JSON header
header('Content-Type: application/json');

// Include necessary files
require_once(dirname(__FILE__) . '/../settings/core.php');
require_once(dirname(__FILE__) . '/../controllers/category_controller.php');

try {
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo json_encode([
            'success' => false,
            'message' => 'User not authenticated',
            'data' => []
        ]);
        exit();
    }
    
    // Check if user is admin
    if (!hasAdminPrivileges()) {
        echo json_encode([
            'success' => false,
            'message' => 'Access denied. Admin privileges required.',
            'data' => []
        ]);
        exit();
    }
    
    // Get user ID from session
    $user_id = $_SESSION['id'];
    
    // Validate user ID
    if (!$user_id || $user_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid user session',
            'data' => []
        ]);
        exit();
    }
    
    // Call controller function to fetch categories
    $result = fetch_categories_ctr($user_id);
    
    // Return the result
    echo json_encode($result);
    
} catch (Exception $e) {
    // Log error (in production, use proper logging)
    error_log("Fetch categories error: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching categories',
        'data' => []
    ]);
}
?>