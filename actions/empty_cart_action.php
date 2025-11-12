<?php
/**
 * Empty Cart Action
 * Deletes all items in the current cart of a guest/logged-in user
 */

session_start();
require_once('../controllers/cart_controller.php');

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$customer_id = $_SESSION['user_id'];
$ip_address = $_SERVER['REMOTE_ADDR'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = clear_cart_ctr($customer_id, $ip_address);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Cart emptied successfully',
            'cart_count' => 0,
            'cart_total' => '0.00'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to empty cart']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
