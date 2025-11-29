<?php
require_once(__DIR__ . '/../settings/db_class.php');

/**
 * Review Class
 * Handles product reviews and ratings
 */
class Review extends db_connection {

    /**
     * Add a new review
     * @param int $customer_id
     * @param int $product_id
     * @param int $rating (1-5)
     * @param string $review_title
     * @param string $review_text
     * @return bool
     */
    public function add_review($customer_id, $product_id, $rating, $review_title = '', $review_text = '') {
        // Check if customer has already reviewed this product
        $check_sql = "SELECT review_id FROM product_reviews
                      WHERE customer_id = ? AND product_id = ?";
        $stmt = $this->db_conn()->prepare($check_sql);
        $stmt->bind_param("ii", $customer_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stmt->close();
            return false; // Already reviewed
        }
        $stmt->close();

        // Check if customer purchased this product (optional verification)
        $purchase_check = $this->check_verified_purchase($customer_id, $product_id);

        // Insert review
        $sql = "INSERT INTO product_reviews
                (customer_id, product_id, rating, review_title, review_text, is_verified_purchase)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("iiissi", $customer_id, $product_id, $rating, $review_title, $review_text, $purchase_check);

        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Get all reviews for a product
     * @param int $product_id
     * @return array
     */
    public function get_product_reviews($product_id) {
        $sql = "SELECT r.*, c.customer_name, c.customer_email
                FROM product_reviews r
                JOIN customer c ON r.customer_id = c.customer_id
                WHERE r.product_id = ?
                ORDER BY r.created_at DESC";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }

        $stmt->close();
        return $reviews;
    }

    /**
     * Get average rating for a product
     * @param int $product_id
     * @return array [average, count]
     */
    public function get_product_rating($product_id) {
        $sql = "SELECT AVG(rating) as average, COUNT(*) as count
                FROM product_reviews
                WHERE product_id = ?";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return [
            'average' => round($row['average'] ?? 0, 1),
            'count' => $row['count'] ?? 0
        ];
    }

    /**
     * Get rating distribution (how many 5-star, 4-star, etc.)
     * @param int $product_id
     * @return array
     */
    public function get_rating_distribution($product_id) {
        $sql = "SELECT rating, COUNT(*) as count
                FROM product_reviews
                WHERE product_id = ?
                GROUP BY rating
                ORDER BY rating DESC";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        while ($row = $result->fetch_assoc()) {
            $distribution[$row['rating']] = $row['count'];
        }

        $stmt->close();
        return $distribution;
    }

    /**
     * Check if customer has purchased this product
     * @param int $customer_id
     * @param int $product_id
     * @return bool
     */
    private function check_verified_purchase($customer_id, $product_id) {
        $sql = "SELECT od.order_detail_id
                FROM orderdetails od
                JOIN orders o ON od.order_id = o.order_id
                WHERE o.customer_id = ?
                AND od.product_id = ?
                AND o.order_status != 'cancelled'
                LIMIT 1";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("ii", $customer_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $has_purchased = $result->num_rows > 0;
        $stmt->close();

        return $has_purchased ? 1 : 0;
    }

    /**
     * Update review
     * @param int $review_id
     * @param int $rating
     * @param string $review_title
     * @param string $review_text
     * @return bool
     */
    public function update_review($review_id, $rating, $review_title, $review_text) {
        $sql = "UPDATE product_reviews
                SET rating = ?, review_title = ?, review_text = ?
                WHERE review_id = ?";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("issi", $rating, $review_title, $review_text, $review_id);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Delete review
     * @param int $review_id
     * @param int $customer_id - for security (ensure only owner can delete)
     * @return bool
     */
    public function delete_review($review_id, $customer_id) {
        $sql = "DELETE FROM product_reviews
                WHERE review_id = ? AND customer_id = ?";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("ii", $review_id, $customer_id);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Check if customer can review product
     * @param int $customer_id
     * @param int $product_id
     * @return bool
     */
    public function can_review($customer_id, $product_id) {
        // Check if already reviewed
        $sql = "SELECT review_id FROM product_reviews
                WHERE customer_id = ? AND product_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("ii", $customer_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $already_reviewed = $result->num_rows > 0;
        $stmt->close();

        return !$already_reviewed;
    }

    /**
     * Mark review as helpful
     * @param int $review_id
     * @return bool
     */
    public function mark_helpful($review_id) {
        $sql = "UPDATE product_reviews
                SET helpful_count = helpful_count + 1
                WHERE review_id = ?";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $review_id);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
}
?>
