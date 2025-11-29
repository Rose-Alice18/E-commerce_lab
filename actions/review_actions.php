<?php
/**
 * Review Actions Handler
 * Handles all review-related AJAX requests
 */

session_start();
require_once('../controllers/review_controller.php');

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to leave a review']);
    exit();
}

$customer_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_review':
            $product_id = intval($_POST['product_id'] ?? 0);
            $rating = intval($_POST['rating'] ?? 0);
            $review_title = trim($_POST['review_title'] ?? '');
            $review_text = trim($_POST['review_text'] ?? '');

            if ($product_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product']);
                exit();
            }

            if ($rating < 1 || $rating > 5) {
                echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
                exit();
            }

            $result = add_review_ctr($customer_id, $product_id, $rating, $review_title, $review_text);
            echo json_encode($result);
            break;

        case 'mark_helpful':
            $review_id = intval($_POST['review_id'] ?? 0);

            if ($review_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid review']);
                exit();
            }

            $result = mark_review_helpful_ctr($review_id);
            echo json_encode($result);
            break;

        case 'delete_review':
            $review_id = intval($_POST['review_id'] ?? 0);

            if ($review_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid review']);
                exit();
            }

            $result = delete_review_ctr($review_id, $customer_id);
            echo json_encode($result);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'get_reviews':
            $product_id = intval($_GET['product_id'] ?? 0);

            if ($product_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product', 'data' => []]);
                exit();
            }

            $result = get_product_reviews_ctr($product_id);
            echo json_encode($result);
            break;

        case 'get_rating':
            $product_id = intval($_GET['product_id'] ?? 0);

            if ($product_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product', 'data' => []]);
                exit();
            }

            $result = get_product_rating_ctr($product_id);
            echo json_encode($result);
            break;

        case 'can_review':
            $product_id = intval($_GET['product_id'] ?? 0);

            if ($product_id <= 0) {
                echo json_encode(['success' => false, 'can_review' => false]);
                exit();
            }

            $can_review = can_review_product_ctr($customer_id, $product_id);
            echo json_encode(['success' => true, 'can_review' => $can_review]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
