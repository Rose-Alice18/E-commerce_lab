<?php
/**
 * Wishlist AJAX Actions Handler
 * Handles all wishlist-related AJAX requests
 */

session_start();
require_once('../controllers/wishlist_controller.php');

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$customer_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $product_id = intval($_POST['product_id']);

            if ($product_id > 0) {
                $result = add_to_wishlist_ctr($customer_id, $product_id);
                if ($result) {
                    $wishlist_count = get_wishlist_count_ctr($customer_id);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Product added to wishlist!',
                        'wishlist_count' => $wishlist_count
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid product']);
            }
            break;

        case 'remove':
            $product_id = intval($_POST['product_id']);

            if ($product_id > 0) {
                $result = remove_from_wishlist_ctr($customer_id, $product_id);
                if ($result) {
                    $wishlist_count = get_wishlist_count_ctr($customer_id);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Product removed from wishlist',
                        'wishlist_count' => $wishlist_count
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid product']);
            }
            break;

        case 'toggle':
            $product_id = intval($_POST['product_id']);

            if ($product_id > 0) {
                $is_in_wishlist = is_in_wishlist_ctr($customer_id, $product_id);

                if ($is_in_wishlist) {
                    $result = remove_from_wishlist_ctr($customer_id, $product_id);
                    $message = 'Removed from wishlist';
                    $in_wishlist = false;
                } else {
                    $result = add_to_wishlist_ctr($customer_id, $product_id);
                    $message = 'Added to wishlist!';
                    $in_wishlist = true;
                }

                if ($result) {
                    $wishlist_count = get_wishlist_count_ctr($customer_id);
                    echo json_encode([
                        'success' => true,
                        'message' => $message,
                        'in_wishlist' => $in_wishlist,
                        'wishlist_count' => $wishlist_count
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update wishlist']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid product']);
            }
            break;

        case 'get_count':
            $wishlist_count = get_wishlist_count_ctr($customer_id);
            echo json_encode([
                'success' => true,
                'wishlist_count' => $wishlist_count
            ]);
            break;

        case 'move_to_cart':
            $result = move_wishlist_to_cart_ctr($customer_id);
            echo json_encode([
                'success' => $result['status'] === 'success',
                'message' => $result['message'],
                'moved_count' => $result['moved_count'],
                'errors' => $result['errors'] ?? []
            ]);
            break;

        case 'clear':
            $result = clear_wishlist_ctr($customer_id);
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Wishlist cleared successfully'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to clear wishlist']);
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
