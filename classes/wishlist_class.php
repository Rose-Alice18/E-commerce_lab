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
}
?>
