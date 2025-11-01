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
        $response['message'] = 'You must be logged in to update products';
        echo json_encode($response);
        exit();
    }

    if (!isPharmacyAdmin()) {
        $response['message'] = 'Only Pharmacy Admins can update products';
        echo json_encode($response);
        exit();
    }

    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Invalid request method';
        echo json_encode($response);
        exit();
    }

    // Validate required fields
    $required_fields = ['product_id', 'product_title', 'product_cat', 'product_brand', 'product_price', 'product_stock', 'pharmacy_id'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
            $response['message'] = 'All required fields must be filled';
            echo json_encode($response);
            exit();
        }
    }

    // Security check: ensure pharmacy_id matches logged-in user
    $logged_in_user_id = getUserId();
    if ($_POST['pharmacy_id'] != $logged_in_user_id) {
        $response['message'] = 'Unauthorized: You can only update your own products';
        echo json_encode($response);
        exit();
    }

    $product_id = intval($_POST['product_id']);
    $pharmacy_id = intval($_POST['pharmacy_id']);

    // Verify product belongs to this pharmacy
    $existing_product = get_product_ctr($product_id, $pharmacy_id);
    if (!$existing_product['success']) {
        $response['message'] = 'Product not found or you do not have permission to update it';
        echo json_encode($response);
        exit();
    }

    // Sanitize and prepare data
    $product_data = [
        'product_id' => $product_id,
        'pharmacy_id' => $pharmacy_id,
        'product_cat' => intval($_POST['product_cat']),
        'product_brand' => intval($_POST['product_brand']),
        'product_title' => trim($_POST['product_title']),
        'product_price' => floatval($_POST['product_price']),
        'product_desc' => isset($_POST['product_desc']) ? trim($_POST['product_desc']) : '',
        'product_keywords' => isset($_POST['product_keywords']) ? trim($_POST['product_keywords']) : '',
        'product_stock' => intval($_POST['product_stock'])
    ];

    // Validate numeric values
    if ($product_data['product_price'] < 0) {
        $response['message'] = 'Price must be a positive number';
        echo json_encode($response);
        exit();
    }

    if ($product_data['product_stock'] < 0) {
        $response['message'] = 'Stock quantity must be a positive number';
        echo json_encode($response);
        exit();
    }

    // Handle image upload if provided
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['product_image'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB

        // Validate file type
        if (!in_array($file['type'], $allowed_types)) {
            $response['message'] = 'Invalid file type. Only JPG, JPEG, and PNG are allowed';
            echo json_encode($response);
            exit();
        }

        // Validate file size
        if ($file['size'] > $max_size) {
            $response['message'] = 'File size too large. Maximum 5MB allowed';
            echo json_encode($response);
            exit();
        }

        // Additional security: Verify it's actually an image
        $image_info = getimagesize($file['tmp_name']);
        if ($image_info === false) {
            $response['message'] = 'File is not a valid image';
            echo json_encode($response);
            exit();
        }

        // Create directory structure: uploads/u{user_id}/p{product_id}/
        // As per PDF page 5 specification
        $base_upload_dir = '../uploads/';
        $user_dir = $base_upload_dir . 'u' . $pharmacy_id . '/';
        $product_dir = $user_dir . 'p' . $product_id . '/';

        // Verify base uploads directory exists
        if (!file_exists($base_upload_dir)) {
            $response['message'] = 'Upload directory does not exist. Please contact administrator.';
            echo json_encode($response);
            exit();
        }

        // Create user directory if it doesn't exist
        if (!file_exists($user_dir)) {
            if (!mkdir($user_dir, 0777, true)) {
                $response['message'] = 'Failed to create user directory';
                echo json_encode($response);
                exit();
            }
        }

        // Create product directory if it doesn't exist
        if (!file_exists($product_dir)) {
            if (!mkdir($product_dir, 0777, true)) {
                $response['message'] = 'Failed to create product directory';
                echo json_encode($response);
                exit();
            }
        }

        // Verify directory is inside uploads/ (security check)
        $real_product_dir = realpath($product_dir);
        $real_base_dir = realpath($base_upload_dir);

        if (strpos($real_product_dir, $real_base_dir) !== 0) {
            $response['message'] = 'Invalid upload directory. Security violation detected.';
            echo json_encode($response);
            exit();
        }

        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $unique_filename = 'image_' . time() . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $product_dir . $unique_filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Store relative path
            $relative_path = 'uploads/u' . $pharmacy_id . '/p' . $product_id . '/' . $unique_filename;

            // Try to add to product_images table (if table exists)
            try {
                $image_data = [[
                    'filename' => $unique_filename,
                    'path' => $relative_path,
                    'size' => $file['size'],
                    'original_name' => $file['name']
                ]];

                // Save to product_images table
                save_product_images_ctr($product_id, $image_data);
            } catch (Exception $e) {
                // Log error but don't fail the update
                error_log("Warning: Could not save to product_images table: " . $e->getMessage());
            }

            // Update main product image path (this is the important part)
            $product_data['product_image'] = $relative_path;
        } else {
            $response['message'] = 'Failed to upload image';
            echo json_encode($response);
            exit();
        }
    }

    // Update product via controller
    $result = update_product_ctr($product_data);

    if ($result['success']) {
        $response['success'] = true;
        $response['message'] = 'Product updated successfully!';
    } else {
        $response['message'] = $result['message'] ?? 'Failed to update product';
    }

} catch (Exception $e) {
    error_log("Update product error: " . $e->getMessage());
    $response['message'] = 'An error occurred while updating the product';
}

echo json_encode($response);
?>
