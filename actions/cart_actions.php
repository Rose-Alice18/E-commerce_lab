<?php
/**
 * Cart AJAX Actions Handler
 * Handles all cart-related AJAX requests
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
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $product_id = intval($_POST['product_id']);
            $quantity = intval($_POST['quantity'] ?? 1);
            $set_quantity = isset($_POST['set_quantity']) ? boolval($_POST['set_quantity']) : false;

            if ($product_id > 0 && $quantity > 0) {
                // Get product stock first
                require_once('../classes/product_class.php');
                $product_obj = new Product();
                $product = $product_obj->get_product($product_id);

                if (!$product) {
                    echo json_encode(['success' => false, 'message' => 'Product not found']);
                    break;
                }

                $available_stock = intval($product['product_stock']);

                if ($available_stock <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Product is out of stock']);
                    break;
                }

                // Get current cart quantity
                $current_qty = get_product_quantity_in_cart_ctr($product_id, $customer_id, $ip_address);

                // Determine final quantity
                if ($set_quantity) {
                    $final_qty = $quantity;
                } else {
                    $final_qty = $current_qty + $quantity;
                }

                // Check if exceeds stock
                $capped = false;
                if ($final_qty > $available_stock) {
                    $final_qty = $available_stock;
                    $capped = true;
                }

                $result = add_to_cart_ctr($product_id, $customer_id, $ip_address, $final_qty, true);

                if ($result) {
                    $cart_count = get_cart_count_ctr($customer_id, $ip_address);
                    $product_qty = get_product_quantity_in_cart_ctr($product_id, $customer_id, $ip_address);

                    $message = $capped
                        ? "Added to cart (max available: $available_stock)"
                        : 'Product added to cart successfully!';

                    echo json_encode([
                        'success' => true,
                        'message' => $message,
                        'cart_count' => $cart_count,
                        'product_quantity' => $product_qty,
                        'in_cart' => true,
                        'capped' => $capped,
                        'max_stock' => $available_stock
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
            }
            break;

        case 'get_quantity':
            $product_id = intval($_POST['product_id']);
            if ($product_id > 0) {
                $quantity = get_product_quantity_in_cart_ctr($product_id, $customer_id, $ip_address);
                echo json_encode([
                    'success' => true,
                    'quantity' => $quantity,
                    'in_cart' => $quantity > 0
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid product']);
            }
            break;

        case 'remove':
            $product_id = intval($_POST['product_id']);

            if ($product_id > 0) {
                $result = remove_from_cart_ctr($product_id, $customer_id, $ip_address);
                if ($result) {
                    $cart_count = get_cart_count_ctr($customer_id, $ip_address);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Product removed from cart',
                        'cart_count' => $cart_count
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to remove product']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid product']);
            }
            break;

        case 'get_count':
            $cart_count = get_cart_count_ctr($customer_id, $ip_address);
            echo json_encode([
                'success' => true,
                'cart_count' => $cart_count
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
