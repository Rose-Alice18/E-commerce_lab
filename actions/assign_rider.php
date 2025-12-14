<?php
/**
 * Assign Rider to Order
 * Create a new delivery assignment
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
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$rider_id = isset($_POST['rider_id']) ? intval($_POST['rider_id']) : 0;
$delivery_fee = isset($_POST['delivery_fee']) ? floatval($_POST['delivery_fee']) : 0;
$pickup_address = isset($_POST['pickup_address']) ? trim($_POST['pickup_address']) : '';
$delivery_address = isset($_POST['delivery_address']) ? trim($_POST['delivery_address']) : '';
$distance_km = isset($_POST['distance_km']) ? floatval($_POST['distance_km']) : null;

// Validate inputs
if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit();
}

if ($rider_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid rider ID']);
    exit();
}

if (empty($pickup_address) || empty($delivery_address)) {
    echo json_encode(['success' => false, 'message' => 'Pickup and delivery addresses are required']);
    exit();
}

$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Check if order already has a delivery assignment
$check_query = "SELECT delivery_id FROM delivery_assignments WHERE order_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("i", $order_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'This order already has a delivery assignment']);
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Get order details
$order_query = "SELECT customer_id, order_total FROM orders WHERE order_id = ?";
$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    $order_stmt->close();
    $conn->close();
    exit();
}

$order = $order_result->fetch_assoc();
$customer_id = $order['customer_id'];
$order_stmt->close();

// Check if rider is available
$rider_query = "SELECT status FROM riders WHERE rider_id = ?";
$rider_stmt = $conn->prepare($rider_query);
$rider_stmt->bind_param("i", $rider_id);
$rider_stmt->execute();
$rider_result = $rider_stmt->get_result();

if ($rider_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Rider not found']);
    $rider_stmt->close();
    $conn->close();
    exit();
}

$rider = $rider_result->fetch_assoc();
if ($rider['status'] !== 'available') {
    echo json_encode(['success' => false, 'message' => 'Rider is not available']);
    $rider_stmt->close();
    $conn->close();
    exit();
}
$rider_stmt->close();

// Create delivery assignment
$insert_query = "INSERT INTO delivery_assignments
                 (order_id, rider_id, customer_id, pickup_address, delivery_address,
                  delivery_fee, distance_km, status, assigned_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'assigned', NOW())";

$stmt = $conn->prepare($insert_query);
$stmt->bind_param("iiissdd", $order_id, $rider_id, $customer_id, $pickup_address,
                  $delivery_address, $delivery_fee, $distance_km);

if ($stmt->execute()) {
    $delivery_id = $stmt->insert_id;

    // Update rider status to busy
    $update_rider = "UPDATE riders SET status = 'busy' WHERE rider_id = ?";
    $update_stmt = $conn->prepare($update_rider);
    $update_stmt->bind_param("i", $rider_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Update order status
    $update_order = "UPDATE orders SET order_status = 'Processing' WHERE order_id = ?";
    $update_order_stmt = $conn->prepare($update_order);
    $update_order_stmt->bind_param("i", $order_id);
    $update_order_stmt->execute();
    $update_order_stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Rider assigned successfully',
        'delivery_id' => $delivery_id
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to assign rider: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
