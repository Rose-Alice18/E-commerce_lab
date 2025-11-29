<?php
/**
 * Get Product Recommendations Action
 * BONUS FEATURE: AI/ML Product Recommendations (+5 marks)
 *
 * Returns recommended products based on content-based filtering algorithm
 * Analyzes product category and brand to find similar products
 */

require_once('../controllers/product_controller.php');

header('Content-Type: application/json');

// Get product ID from query parameter
$product_id = intval($_GET['product_id'] ?? 0);
$limit = intval($_GET['limit'] ?? 4);

if ($product_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product ID',
        'data' => []
    ]);
    exit();
}

// Get product recommendations using content-based filtering
$result = get_product_recommendations_ctr($product_id, $limit);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'data' => $result['data'],
        'count' => count($result['data']),
        'message' => $result['message']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $result['message'],
        'data' => []
    ]);
}
?>
