<?php
/**
 * Suggestion Actions
 * Handle suggestion submissions and reviews
 */

session_start();
require_once('../settings/core.php');
require_once('../controllers/suggestion_controller.php');

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$user_id = getUserId();
$user_role = getUserRole();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'suggest_category':
            // Only pharmacy admins can suggest
            if ($user_role != 1) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }

            $suggested_name = trim($_POST['suggested_name'] ?? '');
            $pharmacy_name = trim($_POST['pharmacy_name'] ?? '');
            $reason = trim($_POST['reason'] ?? '');

            if (empty($suggested_name)) {
                echo json_encode(['success' => false, 'message' => 'Category name is required']);
                exit();
            }

            $result = suggest_category_ctr($suggested_name, $user_id, $pharmacy_name, $reason);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Category suggestion submitted successfully! Awaiting super admin approval.'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to submit suggestion']);
            }
            break;

        case 'suggest_brand':
            // Only pharmacy admins can suggest
            if ($user_role != 1) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }

            $suggested_name = trim($_POST['suggested_name'] ?? '');
            $pharmacy_name = trim($_POST['pharmacy_name'] ?? '');
            $reason = trim($_POST['reason'] ?? '');

            if (empty($suggested_name)) {
                echo json_encode(['success' => false, 'message' => 'Brand name is required']);
                exit();
            }

            $result = suggest_brand_ctr($suggested_name, $user_id, $pharmacy_name, $reason);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Brand suggestion submitted successfully! Awaiting super admin approval.'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to submit suggestion']);
            }
            break;

        case 'approve_category':
            // Only super admins can approve
            if (!isSuperAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }

            $suggestion_id = intval($_POST['suggestion_id'] ?? 0);
            $review_comment = trim($_POST['review_comment'] ?? '');

            if ($suggestion_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid suggestion']);
                exit();
            }

            $result = approve_category_suggestion_ctr($suggestion_id, $user_id, $review_comment);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Category suggestion approved and created successfully!'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to approve suggestion']);
            }
            break;

        case 'approve_brand':
            // Only super admins can approve
            if (!isSuperAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }

            $suggestion_id = intval($_POST['suggestion_id'] ?? 0);
            $review_comment = trim($_POST['review_comment'] ?? '');

            if ($suggestion_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid suggestion']);
                exit();
            }

            $result = approve_brand_suggestion_ctr($suggestion_id, $user_id, $review_comment);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Brand suggestion approved and created successfully!'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to approve suggestion']);
            }
            break;

        case 'reject_category':
            // Only super admins can reject
            if (!isSuperAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }

            $suggestion_id = intval($_POST['suggestion_id'] ?? 0);
            $review_comment = trim($_POST['review_comment'] ?? '');

            if ($suggestion_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid suggestion']);
                exit();
            }

            if (empty($review_comment)) {
                echo json_encode(['success' => false, 'message' => 'Please provide a reason for rejection']);
                exit();
            }

            $result = reject_category_suggestion_ctr($suggestion_id, $user_id, $review_comment);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Category suggestion rejected.'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to reject suggestion']);
            }
            break;

        case 'reject_brand':
            // Only super admins can reject
            if (!isSuperAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }

            $suggestion_id = intval($_POST['suggestion_id'] ?? 0);
            $review_comment = trim($_POST['review_comment'] ?? '');

            if ($suggestion_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid suggestion']);
                exit();
            }

            if (empty($review_comment)) {
                echo json_encode(['success' => false, 'message' => 'Please provide a reason for rejection']);
                exit();
            }

            $result = reject_brand_suggestion_ctr($suggestion_id, $user_id, $review_comment);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Brand suggestion rejected.'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to reject suggestion']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
