<?php
// Include the database connection class
require_once __DIR__ . '/../settings/db_class.php';

/**
 * Brand Class
 * Handles all database operations for brands (product brands like Panadol, Centrum, etc.)
 */
class Brand extends db_connection {

    /**
     * Add a new brand
     * @param string $brand_name - Name of the brand
     * @param int $cat_id - Category ID
     * @param int $user_id - ID of the user creating the brand
     * @return bool - True on success, false on failure
     */
    public function add_brand($brand_name, $cat_id, $user_id) {
        $sql = "INSERT INTO brands (brand_name, cat_id, user_id)
                VALUES (?, ?, ?)";
        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("sii", $brand_name, $cat_id, $user_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Get all brands by user (or all brands if user_id is null)
     * @param int|null $user_id - ID of the user (optional, if null returns all brands)
     * @return array - Array of brands or empty array
     */
    public function get_all_brands($user_id = null) {
        if ($user_id === null) {
            // Get all brands platform-wide (for product dropdowns)
            $sql = "SELECT DISTINCT b.brand_id, b.brand_name, b.cat_id, b.brand_description,
                           b.created_at, b.updated_at, c.cat_name
                    FROM brands b
                    INNER JOIN categories c ON b.cat_id = c.cat_id
                    ORDER BY c.cat_name, b.brand_name";
            $stmt = $this->db_conn()->prepare($sql);
            if (!$stmt) {
                return [];
            }
            $stmt->execute();
        } else {
            // Get brands for specific user
            $sql = "SELECT b.brand_id, b.brand_name, b.cat_id, b.brand_description,
                           b.created_at, b.updated_at, c.cat_name
                    FROM brands b
                    INNER JOIN categories c ON b.cat_id = c.cat_id
                    WHERE b.user_id = ?
                    ORDER BY c.cat_name, b.brand_name";
            $stmt = $this->db_conn()->prepare($sql);
            if (!$stmt) {
                return [];
            }
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }
        $result = $stmt->get_result();
        $brands = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $brands;
    }

    /**
     * Get brands by category
     * @param int $cat_id - Category ID
     * @param int $user_id - ID of the user
     * @return array - Array of brands or empty array
     */
    public function get_brands_by_category($cat_id, $user_id) {
        $sql = "SELECT brand_id, brand_name, cat_id, brand_description, created_at, updated_at
                FROM brands
                WHERE cat_id = ? AND user_id = ?
                ORDER BY brand_name";
        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("ii", $cat_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $brands = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $brands;
    }

    /**
     * Get a single brand by ID
     * @param int $brand_id - Brand ID
     * @param int $user_id - ID of the user (for security)
     * @return array|false - Brand data or false
     */
    public function get_brand($brand_id, $user_id) {
        $sql = "SELECT b.brand_id, b.brand_name, b.cat_id, b.brand_description,
                       b.created_at, b.updated_at, c.cat_name
                FROM brands b
                INNER JOIN categories c ON b.cat_id = c.cat_id
                WHERE b.brand_id = ? AND b.user_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ii", $brand_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $brand = $result->fetch_assoc();
        $stmt->close();
        return $brand ? $brand : false;
    }

    /**
     * Update a brand
     * @param int $brand_id - Brand ID
     * @param string $brand_name - New brand name
     * @param int $cat_id - Category ID
     * @param int $user_id - ID of the user (for security)
     * @return bool - True on success, false on failure
     */
    public function update_brand($brand_id, $brand_name, $cat_id, $user_id) {
        $sql = "UPDATE brands
                SET brand_name = ?, cat_id = ?
                WHERE brand_id = ? AND user_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("siii", $brand_name, $cat_id, $brand_id, $user_id);
        $result = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $result && $affected > 0;
    }

    /**
     * Delete a brand
     * @param int $brand_id - Brand ID
     * @param int $user_id - ID of the user (for security)
     * @return bool - True on success, false on failure
     */
    public function delete_brand($brand_id, $user_id) {
        $sql = "DELETE FROM brands WHERE brand_id = ? AND user_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ii", $brand_id, $user_id);
        $result = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $result && $affected > 0;
    }

    /**
     * Check if brand name already exists for this category and user
     * @param string $brand_name - Brand name
     * @param int $cat_id - Category ID
     * @param int $user_id - ID of the user
     * @param int $brand_id - Brand ID to exclude (for updates)
     * @return bool - True if exists, false otherwise
     */
    public function brand_exists($brand_name, $cat_id, $user_id, $brand_id = null) {
        if ($brand_id) {
            $sql = "SELECT COUNT(*) as count FROM brands
                    WHERE brand_name = ? AND cat_id = ? AND user_id = ? AND brand_id != ?";
            $stmt = $this->db_conn()->prepare($sql);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param("siii", $brand_name, $cat_id, $user_id, $brand_id);
        } else {
            $sql = "SELECT COUNT(*) as count FROM brands
                    WHERE brand_name = ? AND cat_id = ? AND user_id = ?";
            $stmt = $this->db_conn()->prepare($sql);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param("sii", $brand_name, $cat_id, $user_id);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row && $row['count'] > 0;
    }

    /**
     * Count total brands for a user
     * @param int $user_id - ID of the user
     * @return int - Number of brands
     */
    public function count_brands($user_id) {
        $sql = "SELECT COUNT(*) as count FROM brands WHERE user_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return 0;
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? (int)$row['count'] : 0;
    }

    /**
     * Count brands by category for a user
     * @param int $cat_id - Category ID
     * @param int $user_id - ID of the user
     * @return int - Number of brands in category
     */
    public function count_brands_by_category($cat_id, $user_id) {
        $sql = "SELECT COUNT(*) as count FROM brands WHERE cat_id = ? AND user_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            return 0;
        }
        $stmt->bind_param("ii", $cat_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? (int)$row['count'] : 0;
    }
}
