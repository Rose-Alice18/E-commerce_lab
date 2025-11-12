<?php
/**
 * Remove from Cart Action
 * Removes an item from cart completely
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
    $product_id = intval($_POST['product_id'] ?? 0);

    if ($product_id > 0) {
        $result = remove_from_cart_ctr($product_id, $customer_id, $ip_address);

        if ($result) {
            $cart_count = get_cart_count_ctr($customer_id, $ip_address);
            echo json_encode([
                'success' => true,
                'message' => 'Product removed from cart successfully',
                'cart_count' => $cart_count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove product from cart']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
