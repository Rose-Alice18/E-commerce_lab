<?php
/**
 * Cart Class
 * Handles all cart-related database operations
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Cart extends db_connection {

    /**
     * Add product to cart or set quantity
     */
    public function add_to_cart($product_id, $customer_id, $ip_address, $quantity = 1, $set_quantity = false) {
        // First, get the product stock
        $stock_sql = "SELECT product_stock FROM products WHERE product_id = ?";
        $stock_stmt = $this->db_conn()->prepare($stock_sql);
        $stock_stmt->bind_param("i", $product_id);
        $stock_stmt->execute();
        $stock_result = $stock_stmt->get_result();

        if ($stock_result->num_rows === 0) {
            return false; // Product doesn't exist
        }

        $product = $stock_result->fetch_assoc();
        $available_stock = intval($product['product_stock']);

        if ($available_stock <= 0) {
            return false; // Out of stock
        }

        // Check if product already exists in cart
        $check_sql = "SELECT qty FROM cart WHERE p_id = ? AND (c_id = ? OR ip_add = ?)";
        $stmt = $this->db_conn()->prepare($check_sql);
        $stmt->bind_param("iis", $product_id, $customer_id, $ip_address);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $current_qty = intval($row['qty']);

            // Calculate new quantity
            if ($set_quantity) {
                $new_qty = $quantity; // Set to exact quantity
            } else {
                $new_qty = $current_qty + $quantity; // Add to existing
            }

            // Validate against stock
            if ($new_qty > $available_stock) {
                $new_qty = $available_stock; // Cap at available stock
            }

            if ($new_qty <= 0) {
                return $this->remove_from_cart($product_id, $customer_id, $ip_address);
            }

            // Update quantity
            $sql = "UPDATE cart SET qty = ? WHERE p_id = ? AND (c_id = ? OR ip_add = ?)";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("iiis", $new_qty, $product_id, $customer_id, $ip_address);
        } else {
            // Validate quantity for new item
            if ($quantity > $available_stock) {
                $quantity = $available_stock; // Cap at available stock
            }

            if ($quantity <= 0) {
                return false;
            }

            // Insert new item
            $sql = "INSERT INTO cart (p_id, c_id, ip_add, qty) VALUES (?, ?, ?, ?)";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("iisi", $product_id, $customer_id, $ip_address, $quantity);
        }

        return $stmt->execute();
    }

    /**
     * Get cart items for a customer
     */
    public function get_cart_items($customer_id = null, $ip_address = null) {
        $sql = "SELECT c.*, p.product_title, p.product_price, p.product_image, p.product_stock,
                cat.cat_name, b.brand_name
                FROM cart c
                INNER JOIN products p ON c.p_id = p.product_id
                LEFT JOIN categories cat ON p.product_cat = cat.cat_id
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE ";

        if ($customer_id) {
            $sql .= "c.c_id = ?";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("i", $customer_id);
        } else {
            $sql .= "c.ip_add = ?";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("s", $ip_address);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Update cart item quantity (with stock validation)
     */
    public function update_cart_quantity($product_id, $customer_id, $ip_address, $quantity) {
        if ($quantity <= 0) {
            return $this->remove_from_cart($product_id, $customer_id, $ip_address);
        }

        // Get available stock
        $stock_sql = "SELECT product_stock FROM products WHERE product_id = ?";
        $stock_stmt = $this->db_conn()->prepare($stock_sql);
        $stock_stmt->bind_param("i", $product_id);
        $stock_stmt->execute();
        $stock_result = $stock_stmt->get_result();

        if ($stock_result->num_rows === 0) {
            return false;
        }

        $product = $stock_result->fetch_assoc();
        $available_stock = intval($product['product_stock']);

        // Cap quantity at available stock
        if ($quantity > $available_stock) {
            $quantity = $available_stock;
        }

        $sql = "UPDATE cart SET qty = ? WHERE p_id = ? AND (c_id = ? OR ip_add = ?)";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("iiis", $quantity, $product_id, $customer_id, $ip_address);
        return $stmt->execute();
    }

    /**
     * Remove item from cart
     */
    public function remove_from_cart($product_id, $customer_id = null, $ip_address = null) {
        if ($customer_id) {
            $sql = "DELETE FROM cart WHERE p_id = ? AND c_id = ?";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("ii", $product_id, $customer_id);
        } else {
            $sql = "DELETE FROM cart WHERE p_id = ? AND ip_add = ?";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("is", $product_id, $ip_address);
        }
        return $stmt->execute();
    }

    /**
     * Clear entire cart
     */
    public function clear_cart($customer_id = null, $ip_address = null) {
        if ($customer_id) {
            $sql = "DELETE FROM cart WHERE c_id = ?";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("i", $customer_id);
        } else {
            $sql = "DELETE FROM cart WHERE ip_add = ?";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("s", $ip_address);
        }
        return $stmt->execute();
    }

    /**
     * Get cart total
     */
    public function get_cart_total($customer_id = null, $ip_address = null) {
        $sql = "SELECT SUM(p.product_price * c.qty) as total
                FROM cart c
                INNER JOIN products p ON c.p_id = p.product_id
                WHERE ";

        if ($customer_id) {
            $sql .= "c.c_id = ?";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("i", $customer_id);
        } else {
            $sql .= "c.ip_add = ?";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("s", $ip_address);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    /**
     * Get cart count
     */
    public function get_cart_count($customer_id = null, $ip_address = null) {
        $sql = "SELECT SUM(qty) as count FROM cart WHERE ";

        if ($customer_id) {
            $sql .= "c_id = ?";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("i", $customer_id);
        } else {
            $sql .= "ip_add = ?";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("s", $ip_address);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }

    /**
     * Get product quantity in cart
     */
    public function get_product_quantity_in_cart($product_id, $customer_id = null, $ip_address = null) {
        $sql = "SELECT qty FROM cart WHERE p_id = ? AND ";

        if ($customer_id) {
            $sql .= "c_id = ?";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("ii", $product_id, $customer_id);
        } else {
            $sql .= "ip_add = ?";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("is", $product_id, $ip_address);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return intval($row['qty']);
        }

        return 0;
    }
}
?>
