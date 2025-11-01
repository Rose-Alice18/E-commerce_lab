<?php
// Include the category class
require_once(dirname(__FILE__) . '/../classes/category_class.php');

/**
 * Category Controller
 * Handles all category-related operations by creating instances of the Category class
 */

/**
 * Add a new category
 * @param array $kwargs - Associative array containing category data
 * @return array - Result array with success status and message
 */
function add_category_ctr($kwargs) {
    // Validate input parameters
    if (!isset($kwargs['category_name']) || !isset($kwargs['user_id'])) {
        return [
            'success' => false,
            'message' => 'Missing required parameters: category_name and user_id'
        ];
    }
    
    $category_name = trim($kwargs['category_name']);
    $user_id = (int)$kwargs['user_id'];
    
    // Validate category name
    if (empty($category_name)) {
        return [
            'success' => false,
            'message' => 'Category name cannot be empty'
        ];
    }
    
    if (strlen($category_name) > 100) {
        return [
            'success' => false,
            'message' => 'Category name must be less than 100 characters'
        ];
    }
    
    if ($user_id <= 0) {
        return [
            'success' => false,
            'message' => 'Invalid user ID'
        ];
    }
    
    // Create category instance and add category
    $category = new Category();
    $result = $category->add_category($category_name, $user_id);
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'Category added successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Category already exists or failed to add category'
        ];
    }
}

/**
 * Get all categories for a user
 * @param int $user_id - The user ID
 * @return array - Result array with categories data
 */
function fetch_categories_ctr($user_id) {
    if ($user_id <= 0) {
        return [
            'success' => false,
            'message' => 'Invalid user ID',
            'data' => []
        ];
    }
    
    $category = new Category();
    $categories = $category->get_categories_by_user($user_id);
    
    return [
        'success' => true,
        'message' => 'Categories retrieved successfully',
        'data' => $categories
    ];
}

/**
 * Get ALL categories platform-wide (for super admin)
 * @return array - Result array with all categories data
 */
function fetch_all_categories_ctr() {
    $category = new Category();
    $categories = $category->fetch_all_categories();

    return [
        'success' => true,
        'message' => 'All categories retrieved successfully',
        'data' => $categories
    ];
}

/**
 * Get a single category
 * @param int $cat_id - The category ID
 * @param int $user_id - The user ID
 * @return array - Result array with category data
 */
function get_category_ctr($cat_id, $user_id) {
    if ($cat_id <= 0 || $user_id <= 0) {
        return [
            'success' => false,
            'message' => 'Invalid category ID or user ID',
            'data' => null
        ];
    }
    
    $category = new Category();
    $result = $category->get_category($cat_id, $user_id);
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'Category retrieved successfully',
            'data' => $result
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Category not found',
            'data' => null
        ];
    }
}

/**
 * Update a category
 * @param array $kwargs - Associative array containing update data
 * @return array - Result array with success status and message
 */
function update_category_ctr($kwargs) {
    // Validate input parameters
    if (!isset($kwargs['cat_id']) || !isset($kwargs['category_name']) || !isset($kwargs['user_id'])) {
        return [
            'success' => false,
            'message' => 'Missing required parameters: cat_id, category_name, and user_id'
        ];
    }
    
    $cat_id = (int)$kwargs['cat_id'];
    $category_name = trim($kwargs['category_name']);
    $user_id = (int)$kwargs['user_id'];
    
    // Validate inputs
    if ($cat_id <= 0 || $user_id <= 0) {
        return [
            'success' => false,
            'message' => 'Invalid category ID or user ID'
        ];
    }
    
    if (empty($category_name)) {
        return [
            'success' => false,
            'message' => 'Category name cannot be empty'
        ];
    }
    
    if (strlen($category_name) > 100) {
        return [
            'success' => false,
            'message' => 'Category name must be less than 100 characters'
        ];
    }
    
    // Create category instance and update
    $category = new Category();
    $result = $category->update_category($cat_id, $category_name, $user_id);
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'Category updated successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Category name already exists or failed to update category'
        ];
    }
}

/**
 * Delete a category
 * @param int $cat_id - The category ID to delete
 * @param int $user_id - The user ID for security
 * @return array - Result array with success status and message
 */
function delete_category_ctr($cat_id, $user_id) {
    if ($cat_id <= 0 || $user_id <= 0) {
        return [
            'success' => false,
            'message' => 'Invalid category ID or user ID'
        ];
    }
    
    $category = new Category();
    $result = $category->delete_category($cat_id, $user_id);
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'Category deleted successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Category not found or failed to delete category'
        ];
    }
}

/**
 * Get category count for a user
 * @param int $user_id - The user ID
 * @return array - Result array with count data
 */
function count_categories_ctr($user_id) {
    if ($user_id <= 0) {
        return [
            'success' => false,
            'message' => 'Invalid user ID',
            'count' => 0
        ];
    }
    
    $category = new Category();
    $count = $category->count_user_categories($user_id);
    
    return [
        'success' => true,
        'message' => 'Category count retrieved successfully',
        'count' => $count
    ];
}
?>