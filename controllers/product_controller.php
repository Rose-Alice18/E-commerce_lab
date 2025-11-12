<?php
// Include the product class
require_once __DIR__ . '/../classes/product_class.php';

/**
 * Add a new product
 * @param array $kwargs - Associative array with product data
 * @return array - ['success' => bool, 'message' => string, 'product_id' => int]
 */
function add_product_ctr($kwargs) {
    // Validate required fields
    $required_fields = ['pharmacy_id', 'product_cat', 'product_brand', 'product_title', 'product_price'];
    foreach ($required_fields as $field) {
        if (empty($kwargs[$field])) {
            return [
                'success' => false,
                'message' => 'Missing required field: ' . $field
            ];
        }
    }

    // Sanitize and prepare data
    $data = [
        'pharmacy_id' => (int)$kwargs['pharmacy_id'],
        'product_cat' => (int)$kwargs['product_cat'],
        'product_brand' => (int)$kwargs['product_brand'],
        'product_title' => trim($kwargs['product_title']),
        'product_price' => (float)$kwargs['product_price'],
        'product_desc' => isset($kwargs['product_desc']) ? trim($kwargs['product_desc']) : '',
        'product_image' => isset($kwargs['product_image']) ? trim($kwargs['product_image']) : null,
        'product_keywords' => isset($kwargs['product_keywords']) ? trim($kwargs['product_keywords']) : '',
        'product_stock' => isset($kwargs['product_stock']) ? (int)$kwargs['product_stock'] : 0
    ];

    // Validate product title length
    if (strlen($data['product_title']) < 3) {
        return [
            'success' => false,
            'message' => 'Product title must be at least 3 characters long'
        ];
    }

    if (strlen($data['product_title']) > 200) {
        return [
            'success' => false,
            'message' => 'Product title cannot exceed 200 characters'
        ];
    }

    // Validate price
    if ($data['product_price'] < 0) {
        return [
            'success' => false,
            'message' => 'Product price cannot be negative'
        ];
    }

    // Create product instance
    $product = new Product();
    $product_id = $product->add_product($data);

    if ($product_id) {
        return [
            'success' => true,
            'message' => 'Product added successfully',
            'product_id' => $product_id
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to add product. Please try again'
        ];
    }
}

/**
 * Get all products for a pharmacy
 * @param int $pharmacy_id - Pharmacy/User ID
 * @return array - ['success' => bool, 'data' => array, 'message' => string]
 */
function get_products_by_pharmacy_ctr($pharmacy_id) {
    if (empty($pharmacy_id)) {
        return [
            'success' => false,
            'message' => 'Pharmacy ID is required',
            'data' => []
        ];
    }

    $product = new Product();
    $products = $product->get_products_by_pharmacy((int)$pharmacy_id);

    return [
        'success' => true,
        'message' => 'Products retrieved successfully',
        'data' => $products
    ];
}

/**
 * Get all products (for customers)
 * @return array - ['success' => bool, 'data' => array, 'message' => string]
 */
function get_all_products_ctr($limit = null) {
    $product = new Product();
    $products = $product->get_all_products($limit);

    return [
        'success' => true,
        'message' => 'Products retrieved successfully',
        'data' => $products
    ];
}

/**
 * Get a single product
 * @param int $product_id - Product ID
 * @param int $pharmacy_id - Pharmacy/User ID (optional, for security)
 * @return array - ['success' => bool, 'data' => array, 'message' => string]
 */
function get_product_ctr($product_id, $pharmacy_id = null) {
    if (empty($product_id)) {
        return [
            'success' => false,
            'message' => 'Product ID is required',
            'data' => null
        ];
    }

    $product = new Product();
    $product_data = $product->get_product((int)$product_id, $pharmacy_id);

    if (!$product_data) {
        return [
            'success' => false,
            'message' => 'Product not found',
            'data' => null
        ];
    }

    return [
        'success' => true,
        'message' => 'Product retrieved successfully',
        'data' => $product_data
    ];
}

/**
 * Update a product
 * @param array $kwargs - Associative array with product data
 * @return array - ['success' => bool, 'message' => string]
 */
function update_product_ctr($kwargs) {
    // Validate required fields
    $required_fields = ['product_id', 'pharmacy_id', 'product_cat', 'product_brand', 'product_title', 'product_price'];
    foreach ($required_fields as $field) {
        if (empty($kwargs[$field])) {
            return [
                'success' => false,
                'message' => 'Missing required field: ' . $field
            ];
        }
    }

    $product_id = (int)$kwargs['product_id'];
    $pharmacy_id = (int)$kwargs['pharmacy_id'];

    // Sanitize and prepare data
    $data = [
        'product_cat' => (int)$kwargs['product_cat'],
        'product_brand' => (int)$kwargs['product_brand'],
        'product_title' => trim($kwargs['product_title']),
        'product_price' => (float)$kwargs['product_price'],
        'product_desc' => isset($kwargs['product_desc']) ? trim($kwargs['product_desc']) : '',
        'product_keywords' => isset($kwargs['product_keywords']) ? trim($kwargs['product_keywords']) : '',
        'product_stock' => isset($kwargs['product_stock']) ? (int)$kwargs['product_stock'] : 0
    ];

    // Add image if provided
    if (isset($kwargs['product_image']) && !empty($kwargs['product_image'])) {
        $data['product_image'] = trim($kwargs['product_image']);
    }

    // Validate product title length
    if (strlen($data['product_title']) < 3) {
        return [
            'success' => false,
            'message' => 'Product title must be at least 3 characters long'
        ];
    }

    if (strlen($data['product_title']) > 200) {
        return [
            'success' => false,
            'message' => 'Product title cannot exceed 200 characters'
        ];
    }

    // Validate price
    if ($data['product_price'] < 0) {
        return [
            'success' => false,
            'message' => 'Product price cannot be negative'
        ];
    }

    // Create product instance
    $product = new Product();
    $result = $product->update_product($product_id, $data, $pharmacy_id);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Product updated successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to update product. Please try again'
        ];
    }
}

/**
 * Delete a product
 * @param int $product_id - Product ID
 * @param int $pharmacy_id - Pharmacy/User ID
 * @return array - ['success' => bool, 'message' => string]
 */
function delete_product_ctr($product_id, $pharmacy_id) {
    if (empty($product_id) || empty($pharmacy_id)) {
        return [
            'success' => false,
            'message' => 'Product ID and Pharmacy ID are required'
        ];
    }

    $product = new Product();
    $result = $product->delete_product((int)$product_id, (int)$pharmacy_id);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Product deleted successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to delete product. It may not exist or may be in use'
        ];
    }
}

/**
 * Get products by category
 * @param int $cat_id - Category ID
 * @return array - ['success' => bool, 'data' => array, 'message' => string]
 */
function get_products_by_category_ctr($cat_id) {
    if (empty($cat_id)) {
        return [
            'success' => false,
            'message' => 'Category ID is required',
            'data' => []
        ];
    }

    $product = new Product();
    $products = $product->get_products_by_category((int)$cat_id);

    return [
        'success' => true,
        'message' => 'Products retrieved successfully',
        'data' => $products
    ];
}

/**
 * Get products by brand
 * @param int $brand_id - Brand ID
 * @return array - ['success' => bool, 'data' => array, 'message' => string]
 */
function get_products_by_brand_ctr($brand_id) {
    if (empty($brand_id)) {
        return [
            'success' => false,
            'message' => 'Brand ID is required',
            'data' => []
        ];
    }

    $product = new Product();
    $products = $product->get_products_by_brand((int)$brand_id);

    return [
        'success' => true,
        'message' => 'Products retrieved successfully',
        'data' => $products
    ];
}

/**
 * Search products
 * @param string $keyword - Search keyword
 * @return array - ['success' => bool, 'data' => array, 'message' => string]
 */
function search_products_ctr($keyword) {
    if (empty($keyword)) {
        return [
            'success' => false,
            'message' => 'Search keyword is required',
            'data' => []
        ];
    }

    $product = new Product();
    $products = $product->search_products(trim($keyword));

    return [
        'success' => true,
        'message' => 'Search completed successfully',
        'data' => $products
    ];
}

/**
 * Get customer stats (cart, orders, deliveries)
 * @param int $customer_id - Customer ID
 * @return array - ['success' => bool, 'data' => array]
 */
function get_customer_stats_ctr($customer_id) {
    if (empty($customer_id)) {
        return [
            'success' => false,
            'message' => 'Customer ID is required',
            'data' => []
        ];
    }

    $product = new Product();

    // Include wishlist controller for wishlist count
    require_once(__DIR__ . '/wishlist_controller.php');

    return [
        'success' => true,
        'message' => 'Stats retrieved successfully',
        'data' => [
            'cart_items' => $product->get_cart_count($customer_id),
            'total_orders' => $product->get_customer_orders_count($customer_id),
            'pending_deliveries' => $product->get_pending_deliveries($customer_id),
            'wishlist_items' => get_wishlist_count_ctr($customer_id)
        ]
    ];
}

/**
 * Save multiple product images to database
 * EXTRA CREDIT Feature: Bulk image upload
 *
 * @param int $product_id - Product ID
 * @param array $images - Array of image data
 * @return array - Success/failure status and message
 */
function save_product_images_ctr($product_id, $images) {
    if (empty($product_id)) {
        return [
            'success' => false,
            'message' => 'Product ID is required'
        ];
    }

    if (empty($images) || !is_array($images)) {
        return [
            'success' => false,
            'message' => 'Image data is required'
        ];
    }

    $product = new Product();
    $result = $product->save_product_images($product_id, $images);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Images saved successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to save images to database'
        ];
    }
}

/**
 * Get all images for a product
 *
 * @param int $product_id - Product ID
 * @return array - Success/failure status and image data
 */
function get_product_images_ctr($product_id) {
    if (empty($product_id)) {
        return [
            'success' => false,
            'message' => 'Product ID is required',
            'data' => []
        ];
    }

    $product = new Product();
    $images = $product->get_product_images($product_id);

    return [
        'success' => true,
        'message' => 'Images retrieved successfully',
        'data' => $images
    ];
}

/**
 * Delete a product image
 *
 * @param int $image_id - Image ID
 * @param string $image_path - Path to image file
 * @return array - Success/failure status and message
 */
function delete_product_image_ctr($image_id, $image_path) {
    if (empty($image_id)) {
        return [
            'success' => false,
            'message' => 'Image ID is required'
        ];
    }

    $product = new Product();
    $result = $product->delete_product_image($image_id);

    if ($result) {
        // Delete physical file
        $file_path = '../' . $image_path;
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        return [
            'success' => true,
            'message' => 'Image deleted successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to delete image'
        ];
    }
}
