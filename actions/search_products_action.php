<?php
/**
 * Search Products Action
 * Handles product search requests
 */

require_once('../controllers/product_controller.php');

header('Content-Type: application/json');

// Get search term from query parameter
$search_term = trim($_GET['search'] ?? '');

if (empty($search_term)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a search term',
        'data' => []
    ]);
    exit();
}

// Search products using the existing controller function
$result = search_products_ctr($search_term);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'data' => $result['data'],
        'count' => count($result['data']),
        'message' => count($result['data']) > 0 ? 'Products found' : 'No products found'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $result['message'] ?? 'No products found',
        'data' => []
    ]);
}
?>
