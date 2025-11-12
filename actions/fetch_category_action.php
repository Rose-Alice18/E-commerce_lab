<?php
/**
 * Fetch Category Action
 * Retrieves all categories for the logged-in user
 */

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    
    // Get user ID from session using helper function
    $user_id = getUserId();

    // Validate user ID
    if (!$user_id || $user_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid user session',
            'data' => []
        ]);
        exit();
    }

    // Both super admins and pharmacy admins see ALL platform categories
    // The difference is in edit/delete permissions (handled in the frontend)
    $result = fetch_all_categories_ctr(); // Fetch all platform categories

    // Return the result
    echo json_encode($result);
    
} catch (Exception $e) {
    // Return detailed error for debugging
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'data' => []
    ]);
}
?>