<?php
header('Content-Type: application/json');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../settings/core.php');
require_once('../controllers/product_controller.php');
require_once('../classes/category_class.php');
require_once('../classes/brand_class.php');

$response = [
    'success' => false,
    'message' => '',
    'successful' => 0,
    'failed' => 0,
    'results' => []
];

try {
    // Check if user is logged in and is a Pharmacy Admin
    if (!isLoggedIn()) {
        $response['message'] = 'You must be logged in to upload products';
        echo json_encode($response);
        exit();
    }

    if (!isPharmacyAdmin()) {
        $response['message'] = 'Only Pharmacy Admins can upload products';
        echo json_encode($response);
        exit();
    }

    // Check if file was uploaded
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'No file uploaded or upload error occurred';
        echo json_encode($response);
        exit();
    }

    $pharmacy_id = getUserId();
    $file = $_FILES['csv_file'];

    // Validate file type
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file_extension !== 'csv') {
        $response['message'] = 'Invalid file type. Only CSV files are allowed';
        echo json_encode($response);
        exit();
    }

    // Read CSV file
    $csv_data = array_map('str_getcsv', file($file['tmp_name']));

    if (empty($csv_data)) {
        $response['message'] = 'CSV file is empty';
        echo json_encode($response);
        exit();
    }

    // Get header row
    $headers = array_map('trim', $csv_data[0]);
    unset($csv_data[0]); // Remove header row

    // Validate headers
    $required_headers = ['product_title', 'category_name', 'brand_name', 'product_price', 'product_stock'];
    $missing_headers = array_diff($required_headers, $headers);

    if (!empty($missing_headers)) {
        $response['message'] = 'CSV is missing required columns: ' . implode(', ', $missing_headers);
        echo json_encode($response);
        exit();
    }

    // Get all categories and brands for lookup
    $category_obj = new Category();
    $brand_obj = new Brand();

    $all_categories = $category_obj->fetch_all_categories();
    $all_brands = $brand_obj->get_all_brands(null);

    // Create lookup arrays
    $category_lookup = [];
    foreach ($all_categories as $cat) {
        $category_lookup[strtolower(trim($cat['cat_name']))] = $cat['cat_id'];
    }

    $brand_lookup = [];
    foreach ($all_brands as $brand) {
        $brand_lookup[strtolower(trim($brand['brand_name']))] = $brand['brand_id'];
    }

    // Process each row
    $successful = 0;
    $failed = 0;

    foreach ($csv_data as $row_num => $row) {
        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }

        // Create associative array from row data
        $data = array_combine($headers, $row);

        // Trim all values
        $data = array_map('trim', $data);

        $product_title = $data['product_title'] ?? '';
        $category_name = $data['category_name'] ?? '';
        $brand_name = $data['brand_name'] ?? '';
        $product_price = $data['product_price'] ?? '';
        $product_stock = $data['product_stock'] ?? '0';
        $product_desc = $data['product_desc'] ?? '';
        $product_keywords = $data['product_keywords'] ?? '';

        // Validate required fields
        if (empty($product_title) || empty($category_name) || empty($brand_name) || empty($product_price)) {
            $response['results'][] = [
                'success' => false,
                'product_title' => $product_title ?: "Row " . ($row_num + 1),
                'message' => 'Missing required fields'
            ];
            $failed++;
            continue;
        }

        // Lookup category ID
        $cat_id = $category_lookup[strtolower($category_name)] ?? null;
        if (!$cat_id) {
            $response['results'][] = [
                'success' => false,
                'product_title' => $product_title,
                'message' => "Category '$category_name' not found"
            ];
            $failed++;
            continue;
        }

        // Lookup brand ID
        $brand_id = $brand_lookup[strtolower($brand_name)] ?? null;
        if (!$brand_id) {
            $response['results'][] = [
                'success' => false,
                'product_title' => $product_title,
                'message' => "Brand '$brand_name' not found"
            ];
            $failed++;
            continue;
        }

        // Validate price
        if (!is_numeric($product_price) || $product_price < 0) {
            $response['results'][] = [
                'success' => false,
                'product_title' => $product_title,
                'message' => 'Invalid price'
            ];
            $failed++;
            continue;
        }

        // Validate stock
        if (!is_numeric($product_stock) || $product_stock < 0) {
            $response['results'][] = [
                'success' => false,
                'product_title' => $product_title,
                'message' => 'Invalid stock quantity'
            ];
            $failed++;
            continue;
        }

        // Prepare product data
        $product_data = [
            'pharmacy_id' => $pharmacy_id,
            'product_cat' => intval($cat_id),
            'product_brand' => intval($brand_id),
            'product_title' => $product_title,
            'product_price' => floatval($product_price),
            'product_desc' => $product_desc,
            'product_keywords' => $product_keywords,
            'product_stock' => intval($product_stock),
            'product_image' => '' // No image in bulk upload
        ];

        // Add product
        $result = add_product_ctr($product_data);

        if ($result['success']) {
            $response['results'][] = [
                'success' => true,
                'product_title' => $product_title,
                'message' => 'Product added successfully'
            ];
            $successful++;
        } else {
            $response['results'][] = [
                'success' => false,
                'product_title' => $product_title,
                'message' => $result['message'] ?? 'Failed to add product'
            ];
            $failed++;
        }
    }

    $response['success'] = true;
    $response['successful'] = $successful;
    $response['failed'] = $failed;
    $response['message'] = "Upload complete: $successful successful, $failed failed";

} catch (Exception $e) {
    error_log("Bulk upload error: " . $e->getMessage());
    $response['message'] = 'An error occurred during bulk upload';
}

echo json_encode($response);
?>
