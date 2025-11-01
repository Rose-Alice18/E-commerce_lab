<?php
header('Content-Type: application/json');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../settings/core.php');
require_once('../controllers/product_controller.php');

$response = ['success' => false, 'message' => ''];

try {
    // Check if user is logged in and is a Pharmacy Admin
    if (!isLoggedIn()) {
        $response['message'] = 'You must be logged in to delete products';
        echo json_encode($response);
        exit();
    }

    if (!isPharmacyAdmin()) {
        $response['message'] = 'Only Pharmacy Admins can delete products';
        echo json_encode($response);
        exit();
    }

    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Invalid request method';
        echo json_encode($response);
        exit();
    }

    // Validate required field
    if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
        $response['message'] = 'Product ID is required';
        echo json_encode($response);
        exit();
    }

    $product_id = intval($_POST['product_id']);
    $pharmacy_id = getUserId();

    // Get product details to delete image file
    $product_result = get_product_ctr($product_id, $pharmacy_id);

    if (!$product_result['success']) {
        $response['message'] = 'Product not found or you do not have permission to delete it';
        echo json_encode($response);
        exit();
    }

    // Delete product via controller
    $result = delete_product_ctr($product_id, $pharmacy_id);

    if ($result['success']) {
        // Delete image file if exists
        $product_data = $product_result['data'];
        if (!empty($product_data['product_image'])) {
            $image_path = '../' . $product_data['product_image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        $response['success'] = true;
        $response['message'] = 'Product deleted successfully!';
    } else {
        $response['message'] = $result['message'] ?? 'Failed to delete product';
    }

} catch (Exception $e) {
    error_log("Delete product error: " . $e->getMessage());
    $response['message'] = 'An error occurred while deleting the product';
}

echo json_encode($response);
?>
