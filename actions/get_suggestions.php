<?php
session_start();
header('Content-Type: application/json');

require_once('../settings/core.php');
require_once('../controllers/suggestion_controller.php');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$type = isset($_GET['type']) ? $_GET['type'] : '';
$pharmacy_id = isset($_GET['pharmacy_id']) ? intval($_GET['pharmacy_id']) : 0;

if (!$type || !$pharmacy_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    if ($type === 'category') {
        $suggestions = get_pharmacy_category_suggestions_ctr($pharmacy_id);
    } elseif ($type === 'brand') {
        $suggestions = get_pharmacy_brand_suggestions_ctr($pharmacy_id);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid suggestion type']);
        exit();
    }

    echo json_encode([
        'success' => true,
        'suggestions' => $suggestions
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
