<?php
/**
 * Get Pending Suggestions Count
 * For displaying notification badge in super admin sidebar
 */

session_start();
require_once('../settings/core.php');
require_once('../controllers/suggestion_controller.php');

header('Content-Type: application/json');

if (!isLoggedIn() || !isSuperAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$counts = get_pending_suggestions_count_ctr();

echo json_encode([
    'success' => true,
    'category_count' => (int)($counts['category_count'] ?? 0),
    'brand_count' => (int)($counts['brand_count'] ?? 0),
    'total_count' => (int)(($counts['category_count'] ?? 0) + ($counts['brand_count'] ?? 0))
]);
?>
