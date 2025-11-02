<?php
// Include the database connection class
require_once __DIR__ . '/../settings/db_class.php';

/**
 * Product Class
 * Handles all database operations for products
 */
class Product extends db_connection {

    /**
     * Add a new product
     * @param array $data - Product data array
     * @return int|false - Insert ID on success, false on failure
     */
    public function add_product($data) {
        $sql = "INSERT INTO products (
                    pharmacy_id, product_cat, product_brand, product_title,
                    product_price, product_desc, product_image, product_keywords, product_stock
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "iiisdsssi",
            $data['pharmacy_id'],
            $data['product_cat'],
            $data['product_brand'],
            $data['product_title'],
            $data['product_price'],
            $data['product_desc'],
            $data['product_image'],
            $data['product_keywords'],
            $data['product_stock']
        );

        $result = $stmt->execute();
        $insert_id = $result ? $stmt->insert_id : false;
        $stmt->close();

        return $insert_id;
    }

    /**
     * Get all products for a pharmacy/user
     * @param int $pharmacy_id - Pharmacy/User ID
     * @return array - Array of products or empty array
     */
    public function get_products_by_pharmacy($pharmacy_id) {
        $sql = "SELECT p.*, c.cat_name, b.brand_name
                FROM products p
                INNER JOIN categories c ON p.product_cat = c.cat_id
                INNER JOIN brands b ON p.product_brand = b.brand_id
                WHERE p.pharmacy_id = ?
                ORDER BY p.created_at DESC";

        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $pharmacy_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $products;
    }

    /**
     * Get all products (for customers)
     * @param int $limit - Optional limit for number of products
     * @return array - Array of products or empty array
     */
    public function get_all_products($limit = null) {
        $sql = "SELECT p.*, c.cat_name, b.brand_name, cu.customer_name as pharmacy_name
                FROM products p
                INNER JOIN categories c ON p.product_cat = c.cat_id
                INNER JOIN brands b ON p.product_brand = b.brand_id
                INNER JOIN customer cu ON p.pharmacy_id = cu.customer_id
                ORDER BY p.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT " . intval($limit);
        }

        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $products;
    }

    /**
     * Get a single product by ID
     * @param int $product_id - Product ID
     * @param int $pharmacy_id - Pharmacy/User ID (for security)
     * @return array|false - Product data or false
     */
    public function get_product($product_id, $pharmacy_id = null) {
        if ($pharmacy_id) {
            $sql = "SELECT p.*, c.cat_name, b.brand_name
                    FROM products p
                    INNER JOIN categories c ON p.product_cat = c.cat_id
                    INNER JOIN brands b ON p.product_brand = b.brand_id
                    WHERE p.product_id = ? AND p.pharmacy_id = ?";
            $stmt = $this->db_conn()->prepare($sql);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param("ii", $product_id, $pharmacy_id);
        } else {
            $sql = "SELECT p.*, c.cat_name, b.brand_name, cu.customer_name as pharmacy_name
                    FROM products p
                    INNER JOIN categories c ON p.product_cat = c.cat_id
                    INNER JOIN brands b ON p.product_brand = b.brand_id
                    INNER JOIN customer cu ON p.pharmacy_id = cu.customer_id
                    WHERE p.product_id = ?";
            $stmt = $this->db_conn()->prepare($sql);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param("i", $product_id);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        return $product ?: false;
    }

    /**
     * Update a product
     * @param int $product_id - Product ID
     * @param array $data - Product data array
     * @param int $pharmacy_id - Pharmacy/User ID (for security)
     * @return bool - True on success, false on failure
     */
    public function update_product($product_id, $data, $pharmacy_id) {
        $sql = "UPDATE products SET
                    product_cat = ?,
                    product_brand = ?,
                    product_title = ?,
                    product_price = ?,
                    product_desc = ?,
                    product_keywords = ?,
                    product_stock = ?";

        // Add image update if provided
        if (isset($data['product_image']) && !empty($data['product_image'])) {
            $sql .= ", product_image = ?";
        }

        $sql .= " WHERE product_id = ? AND pharmacy_id = ?";

        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return false;
        }

        if (isset($data['product_image']) && !empty($data['product_image'])) {
            $stmt->bind_param(
                "iisdssisii",
                $data['product_cat'],
                $data['product_brand'],
                $data['product_title'],
                $data['product_price'],
                $data['product_desc'],
                $data['product_keywords'],
                $data['product_stock'],
                $data['product_image'],
                $product_id,
                $pharmacy_id
            );
        } else {
            $stmt->bind_param(
                "iisdssiiii",
                $data['product_cat'],
                $data['product_brand'],
                $data['product_title'],
                $data['product_price'],
                $data['product_desc'],
                $data['product_keywords'],
                $data['product_stock'],
                $product_id,
                $pharmacy_id
            );
        }

        $result = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        return $result && $affected > 0;
    }

    /**
     * Delete a product
     * @param int $product_id - Product ID
     * @param int $pharmacy_id - Pharmacy/User ID (for security)
     * @return bool - True on success, false on failure
     */
    public function delete_product($product_id, $pharmacy_id) {
        $sql = "DELETE FROM products WHERE product_id = ? AND pharmacy_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ii", $product_id, $pharmacy_id);
        $result = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        return $result && $affected > 0;
    }

    /**
     * Get products by category
     * @param int $cat_id - Category ID
     * @return array - Array of products or empty array
     */
    public function get_products_by_category($cat_id) {
        $sql = "SELECT p.*, c.cat_name, b.brand_name, cu.customer_name as pharmacy_name
                FROM products p
                INNER JOIN categories c ON p.product_cat = c.cat_id
                INNER JOIN brands b ON p.product_brand = b.brand_id
                INNER JOIN customer cu ON p.pharmacy_id = cu.customer_id
                WHERE p.product_cat = ?
                ORDER BY p.created_at DESC";

        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $cat_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $products;
    }

    /**
     * Get products by brand
     * @param int $brand_id - Brand ID
     * @return array - Array of products or empty array
     */
    public function get_products_by_brand($brand_id) {
        $sql = "SELECT p.*, c.cat_name, b.brand_name, cu.customer_name as pharmacy_name
                FROM products p
                INNER JOIN categories c ON p.product_cat = c.cat_id
                INNER JOIN brands b ON p.product_brand = b.brand_id
                INNER JOIN customer cu ON p.pharmacy_id = cu.customer_id
                WHERE p.product_brand = ?
                ORDER BY p.created_at DESC";

        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $brand_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $products;
    }

    /**
     * Search products by keyword
     * @param string $keyword - Search keyword
     * @return array - Array of products or empty array
     */
    public function search_products($keyword) {
        $search_term = "%{$keyword}%";
        $sql = "SELECT p.*, c.cat_name, b.brand_name, cu.customer_name as pharmacy_name
                FROM products p
                INNER JOIN categories c ON p.product_cat = c.cat_id
                INNER JOIN brands b ON p.product_brand = b.brand_id
                INNER JOIN customer cu ON p.pharmacy_id = cu.customer_id
                WHERE p.product_title LIKE ? OR p.product_desc LIKE ? OR p.product_keywords LIKE ?
                ORDER BY p.created_at DESC";

        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("sss", $search_term, $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $products;
    }

    /**
     * Count products for a pharmacy
     * @param int $pharmacy_id - Pharmacy/User ID
     * @return int - Number of products
     */
    public function count_products($pharmacy_id) {
        $sql = "SELECT COUNT(*) as count FROM products WHERE pharmacy_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param("i", $pharmacy_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ? (int)$row['count'] : 0;
    }

    /**
     * Get cart count for a customer
     * @param int $customer_id - Customer ID
     * @return int - Number of items in cart
     */
    public function get_cart_count($customer_id) {
        $sql = "SELECT SUM(qty) as total FROM cart WHERE c_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['total'] ?? 0;
    }

    /**
     * Get total orders for a customer
     * @param int $customer_id - Customer ID
     * @return int - Number of orders
     */
    public function get_customer_orders_count($customer_id) {
        $sql = "SELECT COUNT(*) as total FROM orders WHERE customer_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['total'] ?? 0;
    }

    /**
     * Get pending deliveries for a customer
     * @param int $customer_id - Customer ID
     * @return int - Number of pending orders
     */
    public function get_pending_deliveries($customer_id) {
        $sql = "SELECT COUNT(*) as total FROM orders
                WHERE customer_id = ?
                AND order_status IN ('pending', 'processing', 'shipped')";
        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['total'] ?? 0;
    }

    /**
     * Save multiple product images to database
     * EXTRA CREDIT Feature: Bulk image upload
     *
     * @param int $product_id - Product ID
     * @param array $images - Array of image data with paths
     * @return bool - Success or failure
     */
    public function save_product_images($product_id, $images) {
        try {
            // Start transaction
            $this->db_conn()->begin_transaction();

            $sql = "INSERT INTO product_images (product_id, image_path, is_primary)
                    VALUES (?, ?, ?)";
            $stmt = $this->db_conn()->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare statement");
            }

            // Check if this product has any images already
            $count_sql = "SELECT COUNT(*) as count FROM product_images WHERE product_id = ?";
            $count_stmt = $this->db_conn()->prepare($count_sql);
            $count_stmt->bind_param("i", $product_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count_row = $count_result->fetch_assoc();
            $existing_count = $count_row['count'] ?? 0;
            $count_stmt->close();

            // First image is primary if no images exist yet
            $is_first = ($existing_count == 0);

            foreach ($images as $index => $image) {
                $image_path = $image['path'];
                // First image is primary (1), rest are additional (0)
                $is_primary = ($is_first && $index === 0) ? 1 : 0;

                $stmt->bind_param("isi", $product_id, $image_path, $is_primary);

                if (!$stmt->execute()) {
                    throw new Exception("Failed to insert image: " . $stmt->error);
                }
            }

            $stmt->close();

            // Update main product image if this is the first image upload
            if ($is_first && !empty($images)) {
                $update_sql = "UPDATE products SET product_image = ? WHERE product_id = ?";
                $update_stmt = $this->db_conn()->prepare($update_sql);
                $main_image = $images[0]['path'];
                $update_stmt->bind_param("si", $main_image, $product_id);
                $update_stmt->execute();
                $update_stmt->close();
            }

            // Commit transaction
            $this->db_conn()->commit();
            return true;

        } catch (Exception $e) {
            // Rollback on error
            $this->db_conn()->rollback();
            error_log("Save product images error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all images for a specific product
     *
     * @param int $product_id - Product ID
     * @return array - Array of image data
     */
    public function get_product_images($product_id) {
        $sql = "SELECT image_id, product_id, image_path, is_primary, uploaded_at
                FROM product_images
                WHERE product_id = ?
                ORDER BY is_primary DESC, uploaded_at ASC";

        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $images = [];
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }

        $stmt->close();
        return $images;
    }

    /**
     * Delete a product image
     *
     * @param int $image_id - Image ID to delete
     * @return bool - Success or failure
     */
    public function delete_product_image($image_id) {
        try {
            // Get image info first
            $select_sql = "SELECT product_id, image_path, is_primary FROM product_images WHERE image_id = ?";
            $select_stmt = $this->db_conn()->prepare($select_sql);
            $select_stmt->bind_param("i", $image_id);
            $select_stmt->execute();
            $result = $select_stmt->get_result();
            $image_data = $result->fetch_assoc();
            $select_stmt->close();

            if (!$image_data) {
                return false;
            }

            $product_id = $image_data['product_id'];
            $is_primary = $image_data['is_primary'];

            // Delete the image record
            $delete_sql = "DELETE FROM product_images WHERE image_id = ?";
            $delete_stmt = $this->db_conn()->prepare($delete_sql);
            $delete_stmt->bind_param("i", $image_id);
            $success = $delete_stmt->execute();
            $delete_stmt->close();

            // If we deleted the primary image, set another image as primary
            if ($success && $is_primary == 1) {
                $update_sql = "UPDATE product_images
                              SET is_primary = 1
                              WHERE product_id = ?
                              ORDER BY uploaded_at ASC
                              LIMIT 1";
                $update_stmt = $this->db_conn()->prepare($update_sql);
                $update_stmt->bind_param("i", $product_id);
                $update_stmt->execute();
                $update_stmt->close();

                // Update main product image
                $new_primary_sql = "SELECT image_path FROM product_images
                                   WHERE product_id = ? AND is_primary = 1";
                $new_primary_stmt = $this->db_conn()->prepare($new_primary_sql);
                $new_primary_stmt->bind_param("i", $product_id);
                $new_primary_stmt->execute();
                $new_primary_result = $new_primary_stmt->get_result();
                $new_primary = $new_primary_result->fetch_assoc();
                $new_primary_stmt->close();

                if ($new_primary) {
                    $update_product_sql = "UPDATE products SET product_image = ? WHERE product_id = ?";
                    $update_product_stmt = $this->db_conn()->prepare($update_product_sql);
                    $update_product_stmt->bind_param("si", $new_primary['image_path'], $product_id);
                    $update_product_stmt->execute();
                    $update_product_stmt->close();
                } else {
                    // No images left, set to NULL
                    $update_product_sql = "UPDATE products SET product_image = NULL WHERE product_id = ?";
                    $update_product_stmt = $this->db_conn()->prepare($update_product_sql);
                    $update_product_stmt->bind_param("i", $product_id);
                    $update_product_stmt->execute();
                    $update_product_stmt->close();
                }
            }

            return $success;

        } catch (Exception $e) {
            error_log("Delete product image error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Set an image as primary for a product
     *
     * @param int $image_id - Image ID to set as primary
     * @param int $product_id - Product ID
     * @return bool - Success or failure
     */
    public function set_primary_image($image_id, $product_id) {
        try {
            // Start transaction
            $this->db_conn()->begin_transaction();

            // Remove primary status from all images of this product
            $remove_sql = "UPDATE product_images SET is_primary = 0 WHERE product_id = ?";
            $remove_stmt = $this->db_conn()->prepare($remove_sql);
            $remove_stmt->bind_param("i", $product_id);
            $remove_stmt->execute();
            $remove_stmt->close();

            // Set this image as primary
            $set_sql = "UPDATE product_images SET is_primary = 1 WHERE image_id = ?";
            $set_stmt = $this->db_conn()->prepare($set_sql);
            $set_stmt->bind_param("i", $image_id);
            $set_stmt->execute();
            $set_stmt->close();

            // Get the new primary image path
            $get_sql = "SELECT image_path FROM product_images WHERE image_id = ?";
            $get_stmt = $this->db_conn()->prepare($get_sql);
            $get_stmt->bind_param("i", $image_id);
            $get_stmt->execute();
            $result = $get_stmt->get_result();
            $image_data = $result->fetch_assoc();
            $get_stmt->close();

            // Update main product image
            if ($image_data) {
                $update_sql = "UPDATE products SET product_image = ? WHERE product_id = ?";
                $update_stmt = $this->db_conn()->prepare($update_sql);
                $update_stmt->bind_param("si", $image_data['image_path'], $product_id);
                $update_stmt->execute();
                $update_stmt->close();
            }

            // Commit transaction
            $this->db_conn()->commit();
            return true;

        } catch (Exception $e) {
            // Rollback on error
            $this->db_conn()->rollback();
            error_log("Set primary image error: " . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // WEEK 7 PDF REQUIREMENT: Method Aliases
    // ============================================================

    /**
     * View all products (PDF requirement - alias for get_all_products)
     * @param int $limit - Optional limit
     * @return array - Array of products
     */
    public function view_all_products($limit = null) {
        return $this->get_all_products($limit);
    }

    /**
     * View single product (PDF requirement - alias for get_product)
     * @param int $id - Product ID
     * @return array|false - Product data or false
     */
    public function view_single_product($id) {
        return $this->get_product($id);
    }

    /**
     * Filter products by category (PDF requirement - alias for get_products_by_category)
     * @param int $cat_id - Category ID
     * @return array - Filtered products
     */
    public function filter_products_by_category($cat_id) {
        return $this->get_products_by_category($cat_id);
    }

    /**
     * Filter products by brand (PDF requirement - alias for get_products_by_brand)
     * @param int $brand_id - Brand ID
     * @return array - Filtered products
     */
    public function filter_products_by_brand($brand_id) {
        return $this->get_products_by_brand($brand_id);
    }
}
