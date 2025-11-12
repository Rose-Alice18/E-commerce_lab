<?php
/**
 * Cart Controller
 * Handles cart operations between view and model
 */

require_once(__DIR__ . '/../classes/cart_class.php');

/**
 * Add product to cart
 */
function add_to_cart_ctr($product_id, $customer_id, $ip_address, $quantity = 1, $set_quantity = false) {
    $cart = new Cart();
    return $cart->add_to_cart($product_id, $customer_id, $ip_address, $quantity, $set_quantity);
}

/**
 * Get cart items
 */
function get_cart_items_ctr($customer_id = null, $ip_address = null) {
    $cart = new Cart();
    return $cart->get_cart_items($customer_id, $ip_address);
}

/**
 * Update cart quantity
 */
function update_cart_quantity_ctr($product_id, $customer_id, $ip_address, $quantity) {
    $cart = new Cart();
    return $cart->update_cart_quantity($product_id, $customer_id, $ip_address, $quantity);
}

/**
 * Remove from cart
 */
function remove_from_cart_ctr($product_id, $customer_id = null, $ip_address = null) {
    $cart = new Cart();
    return $cart->remove_from_cart($product_id, $customer_id, $ip_address);
}

/**
 * Clear cart
 */
function clear_cart_ctr($customer_id = null, $ip_address = null) {
    $cart = new Cart();
    return $cart->clear_cart($customer_id, $ip_address);
}

/**
 * Get cart total
 */
function get_cart_total_ctr($customer_id = null, $ip_address = null) {
    $cart = new Cart();
    return $cart->get_cart_total($customer_id, $ip_address);
}

/**
 * Get cart count
 */
function get_cart_count_ctr($customer_id = null, $ip_address = null) {
    $cart = new Cart();
    return $cart->get_cart_count($customer_id, $ip_address);
}

/**
 * Get product quantity in cart
 */
function get_product_quantity_in_cart_ctr($product_id, $customer_id = null, $ip_address = null) {
    $cart = new Cart();
    return $cart->get_product_quantity_in_cart($product_id, $customer_id, $ip_address);
}
?>
