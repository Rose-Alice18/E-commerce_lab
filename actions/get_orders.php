<?php
/**
 * Get Orders
 * Returns all orders for pharmacy admin
 */

session_start();
require_once('../settings/core.php');
require_once('../controllers/order_controller.php');

header('Content-Type: application/json');

// Check if user is logged in and is pharmacy admin
if (!isLoggedIn() || !isPharmacyAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Get all orders
    $orders = get_all_orders_ctr();

    // Add calculated fields for display
    foreach ($orders as &$order) {
        // Get order details to count items
        $details = get_order_details_ctr($order['order_id']);
        $total_items = 0;
        $total_amount = 0;

        foreach ($details as $item) {
            $total_items += $item['qty'];
            $total_amount += ($item['product_price'] * $item['qty']);
        }

        $order['total_items'] = $total_items;
        $order['total_amount'] = number_format($total_amount, 2);

        // Format order date
        $order['order_date'] = date('M d, Y g:i A', strtotime($order['order_date']));

        // Ensure order_status is set
        if (empty($order['order_status'])) {
            $order['order_status'] = 'Pending';
        }

        // Get customer contact (if available)
        $order['customer_contact'] = $order['customer_email'] ?? 'N/A';
    }

    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'count' => count($orders)
    ]);

} catch (Exception $e) {
    error_log("Get orders error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch orders'
    ]);
}
?>
