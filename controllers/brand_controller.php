<?php
// Include the brand class
require_once __DIR__ . '/../classes/brand_class.php';

/**
 * Add a new brand
 * @param array $kwargs - Associative array with keys: brand_name, cat_id, user_id
 * @return array - ['success' => bool, 'message' => string]
 */
function add_brand_ctr($kwargs) {
    // Validate required fields
    if (empty($kwargs['brand_name']) || empty($kwargs['cat_id']) || empty($kwargs['user_id'])) {
        return [
            'success' => false,
            'message' => 'Brand name, category, and user ID are required'
        ];
    }

    // Sanitize inputs
    $brand_name = trim($kwargs['brand_name']);
    $cat_id = (int)$kwargs['cat_id'];
    $user_id = (int)$kwargs['user_id'];

    // Validate brand name length
    if (strlen($brand_name) < 2) {
        return [
            'success' => false,
            'message' => 'Brand name must be at least 2 characters long'
        ];
    }

    if (strlen($brand_name) > 100) {
        return [
            'success' => false,
            'message' => 'Brand name cannot exceed 100 characters'
        ];
    }

    // Create brand instance
    $brand = new Brand();

    // Check if brand already exists for this category and user
    if ($brand->brand_exists($brand_name, $cat_id, $user_id, null)) {
        return [
            'success' => false,
            'message' => 'This brand already exists in the selected category'
        ];
    }

    // Add brand
    $result = $brand->add_brand($brand_name, $cat_id, $user_id);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Brand added successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to add brand. Please try again'
        ];
    }
}

/**
 * Get all brands for a user or all platform brands
 * @param int|null $user_id - User ID (null for all platform brands)
 * @return array - ['success' => bool, 'data' => array, 'message' => string]
 */
function get_all_brands_ctr($user_id = null) {
    $brand = new Brand();

    // If user_id is null, fetch all platform brands
    // Otherwise, fetch brands for specific user
    $brands = $brand->get_all_brands($user_id);

    return [
        'success' => true,
        'message' => 'Brands retrieved successfully',
        'data' => $brands
    ];
}

/**
 * Get brands by category for a user
 * @param int $cat_id - Category ID
 * @param int $user_id - User ID
 * @return array - ['success' => bool, 'data' => array, 'message' => string]
 */
function get_brands_by_category_ctr($cat_id, $user_id) {
    if (empty($cat_id) || empty($user_id)) {
        return [
            'success' => false,
            'message' => 'Category ID and User ID are required',
            'data' => []
        ];
    }

    $brand = new Brand();
    $brands = $brand->get_brands_by_category((int)$cat_id, (int)$user_id);

    return [
        'success' => true,
        'message' => 'Brands retrieved successfully',
        'data' => $brands
    ];
}

/**
 * Get a single brand
 * @param int $brand_id - Brand ID
 * @param int $user_id - User ID
 * @return array - ['success' => bool, 'data' => array, 'message' => string]
 */
function get_brand_ctr($brand_id, $user_id) {
    if (empty($brand_id) || empty($user_id)) {
        return [
            'success' => false,
            'message' => 'Brand ID and User ID are required',
            'data' => null
        ];
    }

    $brand = new Brand();
    $brand_data = $brand->get_brand((int)$brand_id, (int)$user_id);

    if (!$brand_data) {
        return [
            'success' => false,
            'message' => 'Brand not found',
            'data' => null
        ];
    }

    return [
        'success' => true,
        'message' => 'Brand retrieved successfully',
        'data' => $brand_data
    ];
}

/**
 * Update a brand
 * @param array $kwargs - Associative array with keys: brand_id, brand_name, cat_id, user_id
 * @return array - ['success' => bool, 'message' => string]
 */
function update_brand_ctr($kwargs) {
    // Validate required fields
    if (empty($kwargs['brand_id']) || empty($kwargs['brand_name']) || empty($kwargs['cat_id']) || empty($kwargs['user_id'])) {
        return [
            'success' => false,
            'message' => 'Brand ID, brand name, category, and user ID are required'
        ];
    }

    // Sanitize inputs
    $brand_id = (int)$kwargs['brand_id'];
    $brand_name = trim($kwargs['brand_name']);
    $cat_id = (int)$kwargs['cat_id'];
    $user_id = (int)$kwargs['user_id'];

    // Validate brand name length
    if (strlen($brand_name) < 2) {
        return [
            'success' => false,
            'message' => 'Brand name must be at least 2 characters long'
        ];
    }

    if (strlen($brand_name) > 100) {
        return [
            'success' => false,
            'message' => 'Brand name cannot exceed 100 characters'
        ];
    }

    // Create brand instance
    $brand = new Brand();

    // Check if brand already exists (excluding current brand)
    if ($brand->brand_exists($brand_name, $cat_id, $user_id, $brand_id)) {
        return [
            'success' => false,
            'message' => 'This brand name already exists in the selected category'
        ];
    }

    // Update brand
    $result = $brand->update_brand($brand_id, $brand_name, $cat_id, $user_id);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Brand updated successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to update brand. Please try again'
        ];
    }
}

/**
 * Delete a brand
 * @param int $brand_id - Brand ID
 * @param int $user_id - User ID
 * @return array - ['success' => bool, 'message' => string]
 */
function delete_brand_ctr($brand_id, $user_id) {
    if (empty($brand_id) || empty($user_id)) {
        return [
            'success' => false,
            'message' => 'Brand ID and User ID are required'
        ];
    }

    $brand_id = (int)$brand_id;
    $user_id = (int)$user_id;

    $brand = new Brand();
    $result = $brand->delete_brand($brand_id, $user_id);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Brand deleted successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to delete brand. It may not exist or may be in use by products'
        ];
    }
}
