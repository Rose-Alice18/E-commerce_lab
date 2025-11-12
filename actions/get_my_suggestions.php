<?php
/**
 * Get My Suggestions
 * Returns pharmacy admin's own suggestions
 */

session_start();
require_once('../settings/core.php');
require_once('../controllers/suggestion_controller.php');

header('Content-Type: application/json');

if (!isLoggedIn() || !isPharmacyAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = getUserId();
$type = $_GET['type'] ?? 'category'; // category or brand

try {
    if ($type === 'category') {
        $suggestions = get_pharmacy_category_suggestions_ctr($user_id);
    } else {
        $suggestions = get_pharmacy_brand_suggestions_ctr($user_id);
    }

    echo json_encode([
        'success' => true,
        'suggestions' => $suggestions
    ]);

} catch (Exception $e) {
    error_log("Get my suggestions error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load suggestions'
    ]);
}
?>
