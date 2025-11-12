<?php
/**
 * Update Quantity Action
 * Updates the quantity of an existing cart item
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
    $quantity = intval($_POST['quantity'] ?? 0);

    if ($product_id > 0) {
        // Get product stock
        require_once('../classes/product_class.php');
        $product_obj = new Product();
        $product = $product_obj->get_product($product_id);

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit();
        }

        $available_stock = intval($product['product_stock']);

        // Check if quantity exceeds stock
        $capped = false;
        if ($quantity > $available_stock) {
            $quantity = $available_stock;
            $capped = true;
        }

        $result = update_cart_quantity_ctr($product_id, $customer_id, $ip_address, $quantity);

        if ($result) {
            $cart_count = get_cart_count_ctr($customer_id, $ip_address);
            $cart_total = get_cart_total_ctr($customer_id, $ip_address);

            $message = $capped
                ? "Quantity updated (max available: $available_stock)"
                : 'Quantity updated successfully';

            echo json_encode([
                'success' => true,
                'message' => $message,
                'cart_count' => $cart_count,
                'cart_total' => number_format($cart_total, 2),
                'product_quantity' => $quantity,
                'capped' => $capped
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
