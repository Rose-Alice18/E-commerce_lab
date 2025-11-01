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
        $response['message'] = 'You must be logged in to add products';
        echo json_encode($response);
        exit();
    }

    if (!isPharmacyAdmin()) {
        $response['message'] = 'Only Pharmacy Admins can add products';
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
    $required_fields = ['product_title', 'product_cat', 'product_brand', 'product_price', 'product_stock', 'pharmacy_id'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $response['message'] = 'All required fields must be filled';
            echo json_encode($response);
            exit();
        }
    }

    // Security check: ensure pharmacy_id matches logged-in user
    $logged_in_user_id = getUserId();
    if ($_POST['pharmacy_id'] != $logged_in_user_id) {
        $response['message'] = 'Unauthorized: You can only add products for your own pharmacy';
        echo json_encode($response);
        exit();
    }

    // Get pharmacy_id for later use
    $pharmacy_id = intval($_POST['pharmacy_id']);

    // Sanitize and prepare data
    $product_data = [
        'pharmacy_id' => $pharmacy_id,
        'product_cat' => intval($_POST['product_cat']),
        'product_brand' => intval($_POST['product_brand']),
        'product_title' => trim($_POST['product_title']),
        'product_price' => floatval($_POST['product_price']),
        'product_desc' => isset($_POST['product_desc']) ? trim($_POST['product_desc']) : '',
        'product_keywords' => isset($_POST['product_keywords']) ? trim($_POST['product_keywords']) : '',
        'product_stock' => intval($_POST['product_stock']),
        'product_image' => '' // Default empty
    ];

    // Debug logging
    error_log("Attempting to add product with category ID: " . $product_data['product_cat'] . " and brand ID: " . $product_data['product_brand']);

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

    // Store uploaded file temporarily if provided
    $temp_file_data = null;
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
        $image_info = @getimagesize($file['tmp_name']);
        if ($image_info === false) {
            error_log("Image validation failed for file: " . $file['name']);
            $response['message'] = 'File is not a valid image';
            echo json_encode($response);
            exit();
        }

        // Store file info for later processing (after we get product_id)
        $temp_file_data = [
            'tmp_name' => $file['tmp_name'],
            'name' => $file['name'],
            'size' => $file['size'],
            'type' => $file['type']
        ];
    } elseif (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Log the upload error
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        ];
        $error_code = $_FILES['product_image']['error'];
        $error_msg = isset($upload_errors[$error_code]) ? $upload_errors[$error_code] : 'Unknown error';
        error_log("File upload error: " . $error_msg . " (Code: $error_code)");
        $response['message'] = 'File upload error: ' . $error_msg;
        echo json_encode($response);
        exit();
    }

    // Add product via controller (without image first)
    $result = add_product_ctr($product_data);

    if ($result['success']) {
        $product_id = $result['product_id'];

        // Now handle image upload if provided
        if ($temp_file_data !== null) {
            // Create directory structure: uploads/u{user_id}/p{product_id}/
            $base_upload_dir = '../uploads/';
            $user_dir = $base_upload_dir . 'u' . $pharmacy_id . '/';
            $product_dir = $user_dir . 'p' . $product_id . '/';

            // Verify base uploads directory exists
            if (!file_exists($base_upload_dir)) {
                $response['message'] = 'Upload directory does not exist. Product added but image upload failed.';
                $response['success'] = true;
                $response['product_id'] = $product_id;
                echo json_encode($response);
                exit();
            }

            // Create directories
            if (!file_exists($user_dir)) {
                mkdir($user_dir, 0777, true);
            }
            if (!file_exists($product_dir)) {
                mkdir($product_dir, 0777, true);
            }

            // Verify directory is inside uploads/ (security check)
            $real_product_dir = realpath($product_dir);
            $real_base_dir = realpath($base_upload_dir);

            if (strpos($real_product_dir, $real_base_dir) === 0) {
                // Generate unique filename
                $file_extension = pathinfo($temp_file_data['name'], PATHINFO_EXTENSION);
                $unique_filename = 'image_' . time() . '_' . uniqid() . '.' . $file_extension;
                $upload_path = $product_dir . $unique_filename;

                // Move uploaded file
                if (move_uploaded_file($temp_file_data['tmp_name'], $upload_path)) {
                    // Store relative path
                    $relative_path = 'uploads/u' . $pharmacy_id . '/p' . $product_id . '/' . $unique_filename;

                    // Try to add to product_images table (if table exists)
                    try {
                        $image_data = [[
                            'filename' => $unique_filename,
                            'path' => $relative_path,
                            'size' => $temp_file_data['size'],
                            'original_name' => $temp_file_data['name']
                        ]];

                        // Save to product_images table
                        save_product_images_ctr($product_id, $image_data);
                    } catch (Exception $e) {
                        // Log error but continue
                        error_log("Warning: Could not save to product_images table: " . $e->getMessage());
                    }

                    // Update main product image (this is the important part)
                    require_once('../classes/product_class.php');
                    $product_obj = new Product();
                    // Must pass ALL fields when updating, not just image
                    $product_data['product_image'] = $relative_path;
                    $product_obj->update_product($product_id, $product_data, $pharmacy_id);
                }
            }
        }

        $response['success'] = true;
        $response['message'] = 'Product added successfully!';
        $response['product_id'] = $product_id;
    } else {
        $response['message'] = $result['message'] ?? 'Failed to add product';
    }

} catch (Exception $e) {
    error_log("Add product error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // Show detailed error for debugging
    $response['message'] = 'ERROR DETAILS: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
}

echo json_encode($response);
?>
