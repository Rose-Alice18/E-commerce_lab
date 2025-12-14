<?php
/**
 * Get Payment Details
 * Fetch detailed information about a specific payment
 */
session_start();
require_once('../settings/core.php');

header('Content-Type: application/json');

// Check if user is super admin
if (!isSuperAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$pay_id = isset($_GET['pay_id']) ? intval($_GET['pay_id']) : 0;

if ($pay_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment ID']);
    exit();
}

$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$query = "SELECT p.*, o.invoice_no, o.order_date, o.order_status, o.delivery_address,
                 c.customer_name, c.customer_email, c.customer_contact
          FROM payment p
          INNER JOIN orders o ON p.order_id = o.order_id
          INNER JOIN customer c ON p.customer_id = c.customer_id
          WHERE p.pay_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $pay_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $payment = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'payment' => $payment
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Payment not found'
    ]);
}

$stmt->close();
$conn->close();
?>
