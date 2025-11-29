<?php
require_once(__DIR__ . '/../classes/review_class.php');

/**
 * Add a product review
 * @param int $customer_id
 * @param int $product_id
 * @param int $rating
 * @param string $review_title
 * @param string $review_text
 * @return array
 */
function add_review_ctr($customer_id, $product_id, $rating, $review_title = '', $review_text = '') {
    // Validate inputs
    if (empty($customer_id) || empty($product_id) || empty($rating)) {
        return [
            'success' => false,
            'message' => 'Missing required fields'
        ];
    }

    if ($rating < 1 || $rating > 5) {
        return [
            'success' => false,
            'message' => 'Rating must be between 1 and 5'
        ];
    }

    $review = new Review();

    // Check if customer can review
    if (!$review->can_review($customer_id, $product_id)) {
        return [
            'success' => false,
            'message' => 'You have already reviewed this product'
        ];
    }

    // Add review
    $result = $review->add_review($customer_id, $product_id, $rating, $review_title, $review_text);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Review submitted successfully! Thank you for your feedback.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to submit review. Please try again.'
        ];
    }
}

/**
 * Get reviews for a product
 * @param int $product_id
 * @return array
 */
function get_product_reviews_ctr($product_id) {
    if (empty($product_id)) {
        return [
            'success' => false,
            'message' => 'Product ID is required',
            'data' => []
        ];
    }

    $review = new Review();
    $reviews = $review->get_product_reviews($product_id);

    return [
        'success' => true,
        'data' => $reviews,
        'count' => count($reviews)
    ];
}

/**
 * Get product rating summary
 * @param int $product_id
 * @return array
 */
function get_product_rating_ctr($product_id) {
    if (empty($product_id)) {
        return [
            'success' => false,
            'message' => 'Product ID is required',
            'data' => []
        ];
    }

    $review = new Review();
    $rating = $review->get_product_rating($product_id);
    $distribution = $review->get_rating_distribution($product_id);

    return [
        'success' => true,
        'data' => [
            'average' => $rating['average'],
            'count' => $rating['count'],
            'distribution' => $distribution
        ]
    ];
}

/**
 * Check if customer can review a product
 * @param int $customer_id
 * @param int $product_id
 * @return bool
 */
function can_review_product_ctr($customer_id, $product_id) {
    $review = new Review();
    return $review->can_review($customer_id, $product_id);
}

/**
 * Mark review as helpful
 * @param int $review_id
 * @return array
 */
function mark_review_helpful_ctr($review_id) {
    if (empty($review_id)) {
        return [
            'success' => false,
            'message' => 'Review ID is required'
        ];
    }

    $review = new Review();
    $result = $review->mark_helpful($review_id);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Thank you for your feedback!'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to process request'
        ];
    }
}

/**
 * Delete a review
 * @param int $review_id
 * @param int $customer_id
 * @return array
 */
function delete_review_ctr($review_id, $customer_id) {
    if (empty($review_id) || empty($customer_id)) {
        return [
            'success' => false,
            'message' => 'Missing required fields'
        ];
    }

    $review = new Review();
    $result = $review->delete_review($review_id, $customer_id);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Review deleted successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to delete review or review not found'
        ];
    }
}
?>
