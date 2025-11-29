<?php
/**
 * Wishlist Controller
 * Handles wishlist operations between view and model
 */

require_once(__DIR__ . '/../classes/wishlist_class.php');

/**
 * Add product to wishlist
 */
function add_to_wishlist_ctr($customer_id, $product_id) {
    $wishlist = new Wishlist();
    return $wishlist->add_to_wishlist($customer_id, $product_id);
}

/**
 * Remove from wishlist
 */
function remove_from_wishlist_ctr($customer_id, $product_id) {
    $wishlist = new Wishlist();
    return $wishlist->remove_from_wishlist($customer_id, $product_id);
}

/**
 * Get wishlist items
 */
function get_wishlist_items_ctr($customer_id) {
    $wishlist = new Wishlist();
    return $wishlist->get_wishlist_items($customer_id);
}

/**
 * Check if in wishlist
 */
function is_in_wishlist_ctr($customer_id, $product_id) {
    $wishlist = new Wishlist();
    return $wishlist->is_in_wishlist($customer_id, $product_id);
}

/**
 * Get wishlist count
 */
function get_wishlist_count_ctr($customer_id) {
    $wishlist = new Wishlist();
    return $wishlist->get_wishlist_count($customer_id);
}

/**
 * Clear wishlist
 */
function clear_wishlist_ctr($customer_id) {
    $wishlist = new Wishlist();
    return $wishlist->clear_wishlist($customer_id);
}

/**
 * Move all wishlist items to cart
 */
function move_wishlist_to_cart_ctr($customer_id) {
    $wishlist = new Wishlist();
    return $wishlist->move_wishlist_to_cart($customer_id);
}

/**
 * Get popular wishlist products
 */
function get_popular_wishlist_products_ctr($limit = 10) {
    $wishlist = new Wishlist();
    return $wishlist->get_popular_wishlist_products($limit);
}
?>
