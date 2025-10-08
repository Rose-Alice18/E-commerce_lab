<?php
// Include the database connection
require_once(dirname(__FILE__) . '/../settings/db_class.php');

/**
 * Category class for managing pharmacy service categories
 * Extends the database class for database operations
 */
class Category extends db_connection {
    
    /**
     * Add a new category
     * @param string $category_name - The name of the category
     * @param int $user_id - The ID of the user creating the category
     * @return bool - True if successful, false otherwise
     */
    public function add_category($category_name, $user_id) {
        // Check if category name already exists for this user
        $check_sql = "SELECT cat_id FROM categories WHERE cat_name = ? AND user_id = ?";
        $check_stmt = $this->db_conn()->prepare($check_sql);
        $check_stmt->bind_param("si", $category_name, $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            return false; // Category already exists
        }
        
        // Insert new category
        $sql = "INSERT INTO categories (cat_name, user_id, created_at) VALUES (?, ?, NOW())";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("si", $category_name, $user_id);
        
        return $stmt->execute();
    }
    
    /**
     * Get all categories for a specific user
     * @param int $user_id - The ID of the user
     * @return array - Array of categories or false if none found
     */
    public function get_categories_by_user($user_id) {
        $sql = "SELECT cat_id, cat_name, created_at FROM categories WHERE user_id = ? ORDER BY cat_name ASC";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }
    
    /**
     * Get a single category by ID and user
     * @param int $cat_id - The category ID
     * @param int $user_id - The user ID
     * @return array|false - Category data or false if not found
     */
    public function get_category($cat_id, $user_id) {
        $sql = "SELECT cat_id, cat_name, created_at FROM categories WHERE cat_id = ? AND user_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("ii", $cat_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return false;
    }
    
    /**
     * Update a category
     * @param int $cat_id - The category ID to update
     * @param string $category_name - The new category name
     * @param int $user_id - The user ID (for security)
     * @return bool - True if successful, false otherwise
     */
    public function update_category($cat_id, $category_name, $user_id) {
        // Check if new name already exists for this user (excluding current category)
        $check_sql = "SELECT cat_id FROM categories WHERE cat_name = ? AND user_id = ? AND cat_id != ?";
        $check_stmt = $this->db_conn()->prepare($check_sql);
        $check_stmt->bind_param("sii", $category_name, $user_id, $cat_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            return false; // Category name already exists
        }
        
        // Update the category
        $sql = "UPDATE categories SET cat_name = ?, updated_at = NOW() WHERE cat_id = ? AND user_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("sii", $category_name, $cat_id, $user_id);
        
        return $stmt->execute() && $stmt->affected_rows > 0;
    }
    
    /**
     * Delete a category
     * @param int $cat_id - The category ID to delete
     * @param int $user_id - The user ID (for security)
     * @return bool - True if successful, false otherwise
     */
    public function delete_category($cat_id, $user_id) {
        $sql = "DELETE FROM categories WHERE cat_id = ? AND user_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("ii", $cat_id, $user_id);
        
        return $stmt->execute() && $stmt->affected_rows > 0;
    }
    
    /**
     * Get total number of categories for a user
     * @param int $user_id - The user ID
     * @return int - Number of categories
     */
    public function count_user_categories($user_id) {
        $sql = "SELECT COUNT(*) as total FROM categories WHERE user_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
}
?>