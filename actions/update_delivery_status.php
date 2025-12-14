<?php
/**
 * Update Delivery Status
 * Update the status of a delivery assignment
 */
session_start();
require_once('../settings/core.php');

header('Content-Type: application/json');

// Check if user is super admin
if (!isSuperAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get form data
$delivery_id = isset($_POST['delivery_id']) ? intval($_POST['delivery_id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

// Validate inputs
if ($delivery_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid delivery ID']);
    exit();
}

$valid_statuses = ['assigned', 'picked_up', 'in_transit', 'delivered', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Update delivery status
$update_query = "UPDATE delivery_assignments
                 SET status = ?,
                     updated_at = NOW()
                 WHERE delivery_id = ?";

$stmt = $conn->prepare($update_query);
$stmt->bind_param("si", $status, $delivery_id);

if ($stmt->execute()) {
    // Update specific timestamp fields based on status
    $timestamp_update = "";
    switch ($status) {
        case 'picked_up':
            $timestamp_update = "UPDATE delivery_assignments SET picked_up_at = NOW() WHERE delivery_id = ?";
            break;
        case 'delivered':
            $timestamp_update = "UPDATE delivery_assignments SET delivered_at = NOW() WHERE delivery_id = ?";
            break;
        case 'cancelled':
            $timestamp_update = "UPDATE delivery_assignments SET cancelled_at = NOW() WHERE delivery_id = ?";
            break;
    }

    if (!empty($timestamp_update)) {
        $ts_stmt = $conn->prepare($timestamp_update);
        $ts_stmt->bind_param("i", $delivery_id);
        $ts_stmt->execute();
        $ts_stmt->close();
    }

    // Update order status if delivery is completed
    if ($status === 'delivered') {
        $order_query = "UPDATE orders o
                       INNER JOIN delivery_assignments da ON o.order_id = da.order_id
                       SET o.order_status = 'Delivered'
                       WHERE da.delivery_id = ?";
        $order_stmt = $conn->prepare($order_query);
        $order_stmt->bind_param("i", $delivery_id);
        $order_stmt->execute();
        $order_stmt->close();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Delivery status updated successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update delivery status'
    ]);
}

$stmt->close();
$conn->close();
?>
