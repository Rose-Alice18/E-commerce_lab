<?php
header('Content-Type: application/json');
session_start();

require_once('../controllers/product_controller.php');

try {
    $category_name = $_GET['category'] ?? '';

    if (empty($category_name) || $category_name === 'all') {
        // Get all products
        $result = get_all_products_ctr();
    } else {
        // Get category ID by name first
        require_once('../classes/category_class.php');
        $category_obj = new Category();

        // Search for category by name (case-insensitive)
        $sql = "SELECT cat_id FROM categories WHERE LOWER(cat_name) LIKE ?";
        $search_term = "%" . strtolower($category_name) . "%";
        $stmt = $category_obj->db_conn()->prepare($sql);
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $cat_result = $stmt->get_result();

        if ($cat_result->num_rows > 0) {
            $cat = $cat_result->fetch_assoc();
            $result = get_products_by_category_ctr($cat['cat_id']);
        } else {
            $result = ['success' => true, 'data' => [], 'message' => 'No products in this category'];
        }
    }

    echo json_encode($result);

} catch (Exception $e) {
    error_log("Get products by category error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred',
        'data' => []
    ]);
}
?>
