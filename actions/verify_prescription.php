<?php
/**
 * Verify or Reject Prescription
 * Pharmacy admins can verify prescriptions they have access to
 */

session_start();
header('Content-Type: application/json');

require_once('../settings/core.php');
require_once('../classes/prescription_class.php');

// Check if user is admin
if (!hasAdminPrivileges()) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. Only administrators can verify prescriptions.'
    ]);
    exit();
}

// Get form data
$prescription_id = isset($_POST['prescription_id']) ? intval($_POST['prescription_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : ''; // 'verify' or 'reject'
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Validate inputs
if (!$prescription_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid prescription ID'
    ]);
    exit();
}

if (!in_array($action, ['verify', 'reject'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
    exit();
}

if ($action == 'reject' && empty($notes)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please provide a reason for rejection'
    ]);
    exit();
}

try {
    $prescription = new Prescription();

    // Check if pharmacy admin can access this prescription
    if (!$prescription->can_pharmacy_access($prescription_id, $user_role)) {
        echo json_encode([
            'success' => false,
            'message' => 'You do not have permission to access this prescription. The customer has not made it public.'
        ]);
        exit();
    }

    // Perform action
    if ($action == 'verify') {
        $result = $prescription->verify_prescription($prescription_id, $user_id, $notes);
        $message = 'Prescription verified successfully';
    } else {
        $result = $prescription->reject_prescription($prescription_id, $user_id, $notes);
        $message = 'Prescription rejected successfully';
    }

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update prescription. Please try again.'
        ]);
    }

} catch (Exception $e) {
    error_log("Verify prescription error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
