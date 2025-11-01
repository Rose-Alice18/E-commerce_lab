<?php
/**
 * Delete Product Image Action
 * Part of EXTRA CREDIT: Bulk Image Upload feature
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON response header
header('Content-Type: application/json');

// Include necessary files
require_once('../settings/core.php');
require_once('../controllers/product_controller.php');

// Initialize response
$response = [
    'success' => false,
    'message' => ''
];

try {
    // Check if user is logged in
    if (!isLoggedIn()) {
        $response['message'] = 'You must be logged in';
        echo json_encode($response);
        exit();
    }

    // Check if user is pharmacy admin
    if (!isPharmacyAdmin()) {
        $response['message'] = 'Only pharmacy admins can delete images';
        echo json_encode($response);
        exit();
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['image_id']) || !isset($input['image_path'])) {
        $response['message'] = 'Image ID and path are required';
        echo json_encode($response);
        exit();
    }

    $image_id = intval($input['image_id']);
    $image_path = $input['image_path'];

    // Delete image
    $result = delete_product_image_ctr($image_id, $image_path);

    if ($result['success']) {
        $response['success'] = true;
        $response['message'] = 'Image deleted successfully';
    } else {
        $response['message'] = $result['message'] ?? 'Failed to delete image';
    }

} catch (Exception $e) {
    error_log("Delete product image error: " . $e->getMessage());
    $response['message'] = 'An error occurred while deleting the image';
}

echo json_encode($response);
exit();
?>
