<?php
/**
 * Get Cart Count for Current User
 * Returns JSON response with cart item count
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once(__DIR__ . '/../controllers/cart_controller.php');
require_once(__DIR__ . '/../settings/core.php');

// Set JSON header
header('Content-Type: application/json');

try {
    // Get customer ID from session or use IP address for guest users
    $customer_id = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 0;
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Get cart count
    $count = get_cart_count_ctr($customer_id, $ip_address);

    echo json_encode([
        'success' => true,
        'count' => intval($count)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching cart count: ' . $e->getMessage(),
        'count' => 0
    ]);
}
?>
