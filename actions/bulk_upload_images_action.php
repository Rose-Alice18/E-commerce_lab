<?php
/**
 * Bulk Upload Product Images Action
 * Handles uploading multiple images for a single product
 *
 * EXTRA CREDIT Feature: Allows uploading images in bulk for products
 *
 * Directory Structure: uploads/u{user_id}/p{product_id}/image_1.png
 * As specified in PDF page 5
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
    'message' => '',
    'uploaded_images' => []
];

try {
    // Check if user is logged in
    if (!isLoggedIn()) {
        $response['message'] = 'You must be logged in to upload images';
        echo json_encode($response);
        exit();
    }

    // Check if user is pharmacy admin
    if (!isPharmacyAdmin()) {
        $response['message'] = 'Only pharmacy admins can upload product images';
        echo json_encode($response);
        exit();
    }

    // Get user ID
    $user_id = getUserId();

    // Validate required fields
    if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
        $response['message'] = 'Product ID is required';
        echo json_encode($response);
        exit();
    }

    $product_id = intval($_POST['product_id']);

    // Check if files were uploaded
    if (!isset($_FILES['product_images']) || empty($_FILES['product_images']['name'][0])) {
        $response['message'] = 'Please select at least one image to upload';
        echo json_encode($response);
        exit();
    }

    // Allowed image types
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    $max_file_size = 5 * 1024 * 1024; // 5MB per file
    $max_files = 10; // Maximum 10 images per product

    // Get uploaded files
    $files = $_FILES['product_images'];
    $file_count = count($files['name']);

    // Check maximum file limit
    if ($file_count > $max_files) {
        $response['message'] = "Maximum $max_files images allowed per upload";
        echo json_encode($response);
        exit();
    }

    // Create directory structure: uploads/u{user_id}/p{product_id}/
    $base_upload_dir = '../uploads/';
    $user_dir = $base_upload_dir . 'u' . $user_id . '/';
    $product_dir = $user_dir . 'p' . $product_id . '/';

    // Verify uploads directory exists
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

    // Process each uploaded file
    $uploaded_files = [];
    $errors = [];

    for ($i = 0; $i < $file_count; $i++) {
        // Skip if there's an upload error
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $errors[] = "File " . ($i + 1) . ": Upload error occurred";
            continue;
        }

        // Get file details
        $file_name = $files['name'][$i];
        $file_tmp = $files['tmp_name'][$i];
        $file_size = $files['size'][$i];
        $file_type = $files['type'][$i];

        // Validate file type
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "$file_name: Invalid file type. Only JPG, JPEG, and PNG allowed";
            continue;
        }

        // Validate file size
        if ($file_size > $max_file_size) {
            $errors[] = "$file_name: File too large. Maximum 5MB per file";
            continue;
        }

        // Additional security: Verify it's actually an image
        $image_info = getimagesize($file_tmp);
        if ($image_info === false) {
            $errors[] = "$file_name: File is not a valid image";
            continue;
        }

        // Generate unique filename with timestamp
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $unique_filename = 'image_' . time() . '_' . $i . '.' . $file_extension;
        $destination = $product_dir . $unique_filename;

        // Move uploaded file to destination
        if (move_uploaded_file($file_tmp, $destination)) {
            // Store relative path from project root
            $relative_path = 'uploads/u' . $user_id . '/p' . $product_id . '/' . $unique_filename;
            $uploaded_files[] = [
                'filename' => $unique_filename,
                'path' => $relative_path,
                'size' => $file_size,
                'original_name' => $file_name
            ];
        } else {
            $errors[] = "$file_name: Failed to move uploaded file";
        }
    }

    // Check if any files were successfully uploaded
    if (empty($uploaded_files)) {
        $response['message'] = 'No files were uploaded successfully';
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        echo json_encode($response);
        exit();
    }

    // Save image paths to database
    $result = save_product_images_ctr($product_id, $uploaded_files);

    if ($result['success']) {
        $response['success'] = true;
        $response['message'] = count($uploaded_files) . ' image(s) uploaded successfully!';
        $response['uploaded_images'] = $uploaded_files;

        if (!empty($errors)) {
            $response['warnings'] = $errors;
            $response['message'] .= ' Some files had errors.';
        }
    } else {
        // If database save failed, delete uploaded files
        foreach ($uploaded_files as $file) {
            $file_path = '../' . $file['path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $response['message'] = $result['message'] ?? 'Failed to save image information to database';
    }

} catch (Exception $e) {
    error_log("Bulk upload images error: " . $e->getMessage());
    $response['message'] = 'An error occurred during upload: ' . $e->getMessage();
}

// Return JSON response
echo json_encode($response);
exit();
?>
