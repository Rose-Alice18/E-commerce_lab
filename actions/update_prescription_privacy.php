<?php
/**
 * Update Prescription Privacy Settings
 * Allows customers to toggle privacy settings for their prescriptions
 */

session_start();
header('Content-Type: application/json');

// Check if user is logged in as customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

require_once('../controllers/prescription_controller.php');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['prescription_id']) || !isset($input['allow_pharmacy_access'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit();
}

$prescription_id = intval($input['prescription_id']);
$allow_pharmacy_access = intval($input['allow_pharmacy_access']);
$customer_id = $_SESSION['user_id'];

// Validate privacy value (must be 0 or 1)
if ($allow_pharmacy_access !== 0 && $allow_pharmacy_access !== 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid privacy setting value'
    ]);
    exit();
}

// Verify prescription belongs to this customer
$prescription = get_prescription_ctr($prescription_id);
if (!$prescription || $prescription['customer_id'] != $customer_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Prescription not found or access denied'
    ]);
    exit();
}

// Update privacy setting
require_once('../classes/prescription_class.php');
$prescriptionClass = new Prescription();

try {
    $sql = "UPDATE prescriptions SET allow_pharmacy_access = ? WHERE prescription_id = ? AND customer_id = ?";
    $result = $prescriptionClass->db_query($sql, $allow_pharmacy_access, $prescription_id, $customer_id);

    if ($result) {
        // Log the privacy change
        $action_note = $allow_pharmacy_access == 1
            ? 'Privacy changed to Public - Pharmacies can view'
            : 'Privacy changed to Private - Customer only';

        log_prescription_action_ctr($prescription_id, 'privacy_updated', $customer_id, $action_note);

        echo json_encode([
            'success' => true,
            'message' => 'Privacy settings updated successfully',
            'allow_pharmacy_access' => $allow_pharmacy_access
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update privacy settings'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
