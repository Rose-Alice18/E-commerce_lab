<?php
/**
 * Wishlist Class
 * Handles all wishlist-related database operations
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Wishlist extends db_connection {

    /**
     * Add product to wishlist
     */
    public function add_to_wishlist($customer_id, $product_id) {
        $sql = "INSERT INTO wishlist (customer_id, product_id) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE added_at = CURRENT_TIMESTAMP";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("ii", $customer_id, $product_id);
        return $stmt->execute();
    }

    /**
     * Remove product from wishlist
     */
    public function remove_from_wishlist($customer_id, $product_id) {
        $sql = "DELETE FROM wishlist WHERE customer_id = ? AND product_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("ii", $customer_id, $product_id);
        return $stmt->execute();
    }

    /**
     * Get wishlist items for a customer
     */
    public function get_wishlist_items($customer_id) {
        $sql = "SELECT w.*, p.product_title, p.product_price, p.product_image, p.product_stock,
                cat.cat_name, b.brand_name, p.product_desc
                FROM wishlist w
                INNER JOIN products p ON w.product_id = p.product_id
                LEFT JOIN categories cat ON p.product_cat = cat.cat_id
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE w.customer_id = ?
                ORDER BY w.added_at DESC";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Check if product is in wishlist
     */
    public function is_in_wishlist($customer_id, $product_id) {
        $sql = "SELECT COUNT(*) as count FROM wishlist WHERE customer_id = ? AND product_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("ii", $customer_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }

    /**
     * Get wishlist count
     */
    public function get_wishlist_count($customer_id) {
        $sql = "SELECT COUNT(*) as count FROM wishlist WHERE customer_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }

    /**
     * Clear entire wishlist
     */
    public function clear_wishlist($customer_id) {
        $sql = "DELETE FROM wishlist WHERE customer_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $customer_id);
        return $stmt->execute();
    }

    /**
     * Move all wishlist items to cart
     *
     * @param int $customer_id Customer ID
     * @return array Result with status and message
     */
    public function move_wishlist_to_cart($customer_id) {
        // Get wishlist items
        $wishlist_items = $this->get_wishlist_items($customer_id);

        if (empty($wishlist_items)) {
            return [
                'status' => 'info',
                'message' => 'Wishlist is empty',
                'moved_count' => 0
            ];
        }

        $moved_count = 0;
        $errors = [];

        foreach ($wishlist_items as $item) {
            // Check if product is in stock
            if ($item['product_stock'] <= 0) {
                $errors[] = $item['product_title'] . ' is out of stock';
                continue;
            }

            // Add to cart (use INSERT IGNORE to avoid duplicates)
            $cart_sql = "INSERT INTO cart (p_id, c_id, qty)
                        VALUES (?, ?, 1)
                        ON DUPLICATE KEY UPDATE qty = qty + 1";

            $stmt = $this->db_conn()->prepare($cart_sql);

            if ($stmt) {
                $stmt->bind_param("ii", $item['product_id'], $customer_id);
                if ($stmt->execute()) {
                    $moved_count++;
                }
                $stmt->close();
            }
        }

        // Clear wishlist after moving
        if ($moved_count > 0) {
            $this->clear_wishlist($customer_id);
        }

        return [
            'status' => 'success',
            'message' => "$moved_count items moved to cart",
            'moved_count' => $moved_count,
            'errors' => $errors
        ];
    }

    /**
     * Get popular wishlist products (for recommendations)
     *
     * @param int $limit Number of products to return
     * @return array Array of popular products
     */
    public function get_popular_wishlist_products($limit = 10) {
        $sql = "SELECT
                    p.product_id,
                    p.product_title,
                    p.product_price,
                    p.product_image,
                    COUNT(w.wishlist_id) as wishlist_count
                FROM products p
                INNER JOIN wishlist w ON p.product_id = w.product_id
                GROUP BY p.product_id
                ORDER BY wishlist_count DESC
                LIMIT ?";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
