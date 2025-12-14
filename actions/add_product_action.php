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

    // Store uploaded files temporarily if provided (multiple images)
    $temp_files_data = [];
    if (isset($_FILES['product_images']) && is_array($_FILES['product_images']['name'])) {
        $file_count = count($_FILES['product_images']['name']);
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB

        for ($i = 0; $i < $file_count; $i++) {
            // Check if file was uploaded successfully
            if ($_FILES['product_images']['error'][$i] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['product_images']['tmp_name'][$i];
                $file_name = $_FILES['product_images']['name'][$i];
                $file_size = $_FILES['product_images']['size'][$i];
                $file_type = $_FILES['product_images']['type'][$i];

                // Validate file type
                if (!in_array($file_type, $allowed_types)) {
                    $response['message'] = "Invalid file type for $file_name. Only JPG, JPEG, and PNG are allowed";
                    echo json_encode($response);
                    exit();
                }

                // Validate file size
                if ($file_size > $max_size) {
                    $response['message'] = "File size too large for $file_name. Maximum 5MB allowed";
                    echo json_encode($response);
                    exit();
                }

                // Additional security: Verify it's actually an image
                $image_info = @getimagesize($file_tmp);
                if ($image_info === false) {
                    error_log("Image validation failed for file: $file_name");
                    $response['message'] = "File $file_name is not a valid image";
                    echo json_encode($response);
                    exit();
                }

                // Store file info for later processing (after we get product_id)
                $temp_files_data[] = [
                    'tmp_name' => $file_tmp,
                    'name' => $file_name,
                    'size' => $file_size,
                    'type' => $file_type,
                    'is_primary' => ($i === 0) // First image is primary
                ];
            } elseif ($_FILES['product_images']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                // Log the upload error
                $upload_errors = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
                ];
                $error_code = $_FILES['product_images']['error'][$i];
                $error_msg = isset($upload_errors[$error_code]) ? $upload_errors[$error_code] : 'Unknown error';
                error_log("File upload error for $file_name: $error_msg (Code: $error_code)");
                $response['message'] = "File upload error for $file_name: $error_msg";
                echo json_encode($response);
                exit();
            }
        }

        // Require at least one image
        if (empty($temp_files_data)) {
            $response['message'] = 'At least one product image is required';
            echo json_encode($response);
            exit();
        }
    } else {
        $response['message'] = 'At least one product image is required';
        echo json_encode($response);
        exit();
    }

    // Add product via controller (without image first)
    $result = add_product_ctr($product_data);

    if ($result['success']) {
        $product_id = $result['product_id'];

        // Now handle multiple image uploads if provided
        if (!empty($temp_files_data)) {
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
                $uploaded_images = [];
                $primary_image_path = '';

                // Process each uploaded image
                foreach ($temp_files_data as $index => $file_data) {
                    // Generate unique filename
                    $file_extension = pathinfo($file_data['name'], PATHINFO_EXTENSION);
                    $unique_filename = 'image_' . time() . '_' . uniqid() . '_' . $index . '.' . $file_extension;
                    $upload_path = $product_dir . $unique_filename;

                    // Move uploaded file
                    if (move_uploaded_file($file_data['tmp_name'], $upload_path)) {
                        // Store relative path
                        $relative_path = 'uploads/u' . $pharmacy_id . '/p' . $product_id . '/' . $unique_filename;

                        // Save the first image as primary
                        if ($file_data['is_primary']) {
                            $primary_image_path = $relative_path;
                        }

                        // Store image data for product_images table
                        $uploaded_images[] = [
                            'filename' => $unique_filename,
                            'path' => $relative_path,
                            'size' => $file_data['size'],
                            'original_name' => $file_data['name'],
                            'is_primary' => $file_data['is_primary']
                        ];
                    }
                }

                // Save all images to product_images table
                if (!empty($uploaded_images)) {
                    try {
                        require_once('../controllers/product_controller.php');
                        save_product_images_ctr($product_id, $uploaded_images);
                    } catch (Exception $e) {
                        // Log error but continue
                        error_log("Warning: Could not save to product_images table: " . $e->getMessage());
                    }

                    // Update main product image with the primary image
                    if (!empty($primary_image_path)) {
                        require_once('../classes/product_class.php');
                        $product_obj = new Product();
                        $product_data['product_image'] = $primary_image_path;
                        $product_obj->update_product($product_id, $product_data, $pharmacy_id);
                    }
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
