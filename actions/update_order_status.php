<?php
/**
 * Update Order Status Action
 * Handles manual status updates by pharmacy admin
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../settings/db_class.php');

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to update order status'
    ]);
    exit();
}

// Get user info
$user_id = getUserId();
$user_role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? 2;

// Only pharmacy admins and super admins can update order status
if ($user_role != 1 && $user_role != 0) {
    echo json_encode([
        'success' => false,
        'message' => 'You do not have permission to update order status'
    ]);
    exit();
}

// Get POST data
$order_id = intval($_POST['order_id'] ?? 0);
$new_status = trim($_POST['status'] ?? '');
$notes = trim($_POST['notes'] ?? '');

// Validate inputs
if ($order_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid order ID'
    ]);
    exit();
}

// Valid status values
$valid_statuses = ['Pending', 'Processing', 'Out for Delivery', 'Delivered', 'Cancelled'];

if (!in_array($new_status, $valid_statuses)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status value'
    ]);
    exit();
}

try {
    $db = new db_connection();
    $conn = $db->db_conn();

    // Get current order status
    $check_sql = "SELECT order_status, customer_id FROM orders WHERE order_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $order_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found'
        ]);
        exit();
    }

    $order = $result->fetch_assoc();
    $old_status = $order['order_status'];
    $customer_id = $order['customer_id'];

    // Check if status is actually changing
    if ($old_status === $new_status) {
        echo json_encode([
            'success' => false,
            'message' => 'Order is already in ' . $new_status . ' status'
        ]);
        exit();
    }

    // Update order status
    $update_sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_status, $order_id);

    if ($update_stmt->execute()) {
        // Try to log status change in history table (if it exists)
        try {
            $history_sql = "INSERT INTO order_status_history (order_id, old_status, new_status, notes, updated_by) VALUES (?, ?, ?, ?, ?)";
            $history_stmt = $conn->prepare($history_sql);
            if ($history_stmt) {
                $history_stmt->bind_param("isssi", $order_id, $old_status, $new_status, $notes, $user_id);
                $history_stmt->execute();
            }
        } catch (Exception $e) {
            // History table doesn't exist or error logging - continue anyway
            error_log("Order status history logging failed: " . $e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message' => 'Order status updated from ' . $old_status . ' to ' . $new_status,
            'order_id' => $order_id,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update order status: ' . $conn->error
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating order status: ' . $e->getMessage()
    ]);
}
?>
