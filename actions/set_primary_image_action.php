<?php
/**
 * Set Primary Product Image Action
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
require_once('../classes/product_class.php');

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
        $response['message'] = 'Only pharmacy admins can set primary images';
        echo json_encode($response);
        exit();
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['image_id']) || !isset($input['product_id'])) {
        $response['message'] = 'Image ID and Product ID are required';
        echo json_encode($response);
        exit();
    }

    $image_id = intval($input['image_id']);
    $product_id = intval($input['product_id']);

    // Set primary image
    $product = new Product();
    $result = $product->set_primary_image($image_id, $product_id);

    if ($result) {
        $response['success'] = true;
        $response['message'] = 'Primary image updated successfully';
    } else {
        $response['message'] = 'Failed to set primary image';
    }

} catch (Exception $e) {
    error_log("Set primary image error: " . $e->getMessage());
    $response['message'] = 'An error occurred while setting primary image';
}

echo json_encode($response);
exit();
?>
