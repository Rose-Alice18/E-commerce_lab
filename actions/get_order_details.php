<?php
/**
 * Get Order Details
 * Fetch complete order information including items and delivery
 */
session_start();
require_once('../settings/core.php');
require_once('../settings/db_cred.php');

header('Content-Type: application/json');

// Check if user is super admin or pharmacy admin
if (!isSuperAdmin() && !isPharmacyAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit();
}

$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get order information
$order_query = "SELECT o.*,
                       c.customer_name, c.customer_email, c.customer_contact,
                       p.payment_status, p.payment_method, p.amount as payment_amount
                FROM orders o
                INNER JOIN customer c ON o.customer_id = c.customer_id
                LEFT JOIN payment p ON o.order_id = p.order_id
                WHERE o.order_id = ?";

$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    $stmt->close();
    $conn->close();
    exit();
}

$order = $order_result->fetch_assoc();
$stmt->close();

// Get order items with product and pharmacy details
$items_query = "SELECT oi.*,
                       p.product_title, p.product_image,
                       ph.customer_name as pharmacy_name
                FROM orderdetails oi
                INNER JOIN products p ON oi.product_id = p.product_id
                LEFT JOIN customer ph ON p.pharmacy_id = ph.customer_id
                WHERE oi.order_id = ?";

$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();

$items = [];
while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

// Get delivery information if exists
$delivery_query = "SELECT da.*,
                          r.rider_name, r.rider_phone, r.rider_email
                   FROM delivery_assignments da
                   INNER JOIN riders r ON da.rider_id = r.rider_id
                   WHERE da.order_id = ?";

$stmt = $conn->prepare($delivery_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$delivery_result = $stmt->get_result();

$delivery = null;
if ($delivery_result->num_rows > 0) {
    $delivery = $delivery_result->fetch_assoc();
}
$stmt->close();

$conn->close();

echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items,
    'delivery' => $delivery
]);
?>
