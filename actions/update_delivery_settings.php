<?php
/**
 * Update Pharmacy Delivery Settings
 * Allows pharmacy admins to configure their delivery options
 */

session_start();
header('Content-Type: application/json');

// Check if user is logged in as pharmacy admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. Only pharmacy administrators can update delivery settings.'
    ]);
    exit();
}

require_once('../controllers/customer_controller.php');
require_once('../classes/customer_class.php');

// Get form data
$offers_delivery = isset($_POST['offers_delivery']) ? 1 : 0;
$delivery_fee = isset($_POST['delivery_fee']) ? floatval($_POST['delivery_fee']) : 0;
$delivery_radius_km = isset($_POST['delivery_radius_km']) ? floatval($_POST['delivery_radius_km']) : 10;
$min_order_for_delivery = isset($_POST['min_order_for_delivery']) ? floatval($_POST['min_order_for_delivery']) : 0;

$user_id = $_SESSION['user_id'];

// Validate inputs
if ($delivery_fee < 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Delivery fee cannot be negative'
    ]);
    exit();
}

if ($delivery_radius_km < 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Delivery radius cannot be negative'
    ]);
    exit();
}

if ($min_order_for_delivery < 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Minimum order value cannot be negative'
    ]);
    exit();
}

try {
    $customer = new Customer();

    // Update delivery settings using prepared statement
    $sql = "UPDATE customer SET
            offers_delivery = ?,
            delivery_fee = ?,
            delivery_radius_km = ?,
            min_order_for_delivery = ?
            WHERE customer_id = ? AND user_role = 1";

    $stmt = $customer->db->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $customer->db->error);
    }

    $stmt->bind_param("idddi",
        $offers_delivery,
        $delivery_fee,
        $delivery_radius_km,
        $min_order_for_delivery,
        $user_id
    );

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Delivery settings updated successfully',
                'data' => [
                    'offers_delivery' => $offers_delivery,
                    'delivery_fee' => number_format($delivery_fee, 2),
                    'delivery_radius_km' => number_format($delivery_radius_km, 1),
                    'min_order_for_delivery' => number_format($min_order_for_delivery, 2)
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No changes made. Please verify you are a pharmacy administrator.'
            ]);
        }
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    error_log("Delivery settings update error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
