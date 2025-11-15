<?php
/**
 * Process Checkout Action
 * Handles the backend processing of checkout after simulated payment confirmation
 */

session_start();
require_once('../controllers/cart_controller.php');
require_once('../controllers/order_controller.php');
require_once('../controllers/product_controller.php');

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$customer_id = $_SESSION['user_id'];
$ip_address = $_SERVER['REMOTE_ADDR'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start database transaction (for data integrity)
        $conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

        if ($conn->connect_error) {
            throw new Exception('Database connection failed');
        }

        $conn->begin_transaction();

        // 1. Get selected items from session (set by checkout.php)
        $selected_items_ids = isset($_SESSION['checkout_items']) ? $_SESSION['checkout_items'] : [];

        if (empty($selected_items_ids)) {
            echo json_encode(['success' => false, 'message' => 'No items selected for checkout']);
            exit();
        }

        // Get all cart items
        $all_cart_items = get_cart_items_ctr($customer_id, $ip_address);

        // Filter only selected items for checkout
        $cart_items = [];
        foreach ($all_cart_items as $item) {
            if (in_array($item['p_id'], $selected_items_ids)) {
                $cart_items[] = $item;
            }
        }

        if (empty($cart_items)) {
            echo json_encode(['success' => false, 'message' => 'Selected items not found in cart']);
            exit();
        }

        // 2. Validate stock availability for selected items
        foreach ($cart_items as $item) {
            if ($item['qty'] > $item['product_stock']) {
                $conn->rollback();
                echo json_encode([
                    'success' => false,
                    'message' => "Insufficient stock for {$item['product_title']}. Available: {$item['product_stock']}"
                ]);
                exit();
            }
        }

        // 3. Calculate total amount for selected items only
        $total_amount = 0;
        foreach ($cart_items as $item) {
            $total_amount += $item['product_price'] * $item['qty'];
        }

        if ($total_amount <= 0) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Invalid cart total']);
            exit();
        }

        // 4. Generate unique invoice number
        $invoice_no = generate_invoice_number_ctr();

        // 5. Create order
        $order_id = create_order_ctr($customer_id, $invoice_no, 'pending');

        if (!$order_id) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to create order']);
            exit();
        }

        // 6. Add order details (each cart item)
        foreach ($cart_items as $item) {
            $result = add_order_details_ctr($order_id, $item['p_id'], $item['qty']);
            if (!$result) {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Failed to add order details']);
                exit();
            }
        }

        // 7. Record payment
        $payment_result = record_payment_ctr($order_id, $customer_id, $total_amount, 'GHS');

        if (!$payment_result) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to record payment']);
            exit();
        }

        // 8. Update product stock (reduce quantities)
        foreach ($cart_items as $item) {
            $new_stock = $item['product_stock'] - $item['qty'];
            $update_stock_sql = "UPDATE products SET product_stock = ? WHERE product_id = ?";
            $stmt = $conn->prepare($update_stock_sql);
            $stmt->bind_param("ii", $new_stock, $item['p_id']);
            if (!$stmt->execute()) {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Failed to update product stock']);
                exit();
            }
        }

        // 9. Remove only checked-out items from cart (not all items)
        foreach ($cart_items as $item) {
            $remove_result = remove_from_cart_ctr($item['p_id'], $customer_id, $ip_address);
            if (!$remove_result) {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Failed to remove items from cart']);
                exit();
            }
        }

        // Clear checkout items from session
        unset($_SESSION['checkout_items']);

        // 10. Commit transaction
        $conn->commit();
        $conn->close();

        // 11. Return success response with order details
        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully!',
            'order_id' => $order_id,
            'invoice_no' => $invoice_no,
            'total_amount' => number_format($total_amount, 2),
            'order_date' => date('Y-m-d H:i:s')
        ]);

    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
            $conn->close();
        }
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred during checkout: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
