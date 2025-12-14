<?php
/**
 * Get Delivery Details
 * Fetch detailed information about a specific delivery assignment
 */
session_start();
require_once('../settings/core.php');

header('Content-Type: application/json');

// Check if user is super admin
if (!isSuperAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$delivery_id = isset($_GET['delivery_id']) ? intval($_GET['delivery_id']) : 0;

if ($delivery_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid delivery ID']);
    exit();
}

$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$query = "SELECT da.*,
                 r.rider_name, r.rider_phone, r.rider_email, r.vehicle_type, r.vehicle_number,
                 c.customer_name, c.customer_email, c.customer_contact, c.customer_address,
                 p.customer_name as pharmacy_name, p.customer_contact as pharmacy_contact, p.customer_address as pharmacy_address,
                 o.invoice_no, o.order_date, o.order_status, o.order_total, o.delivery_address
          FROM delivery_assignments da
          INNER JOIN riders r ON da.rider_id = r.rider_id
          INNER JOIN customer c ON da.customer_id = c.customer_id
          LEFT JOIN customer p ON da.pharmacy_id = p.customer_id
          INNER JOIN orders o ON da.order_id = o.order_id
          WHERE da.delivery_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $delivery_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $delivery = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'delivery' => $delivery
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Delivery not found'
    ]);
}

$stmt->close();
$conn->close();
?>
