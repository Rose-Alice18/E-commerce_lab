<?php
session_start();
header('Content-Type: application/json');

require_once('../settings/core.php');
requireSuperAdmin();

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$role = isset($_POST['role']) ? intval($_POST['role']) : 2;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

if (!in_array($role, [0, 1, 2, 7])) {
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
    exit();
}

try {
    require_once('../classes/customer_class.php');
    $customer = new Customer();

    $sql = "UPDATE customer SET user_role = ? WHERE customer_id = ?";
    $stmt = $customer->db->prepare($sql);
    $stmt->bind_param("ii", $role, $user_id);

    if ($stmt->execute()) {
        $role_names = [0 => 'Super Admin', 1 => 'Pharmacy Admin', 2 => 'Customer', 7 => 'Super Admin'];
        echo json_encode([
            'success' => true,
            'message' => 'User role updated to ' . $role_names[$role]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update role']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
