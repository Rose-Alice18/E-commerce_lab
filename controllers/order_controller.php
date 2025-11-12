<?php
/**
 * Order Controller
 * Handles order operations between view and model
 */

require_once(__DIR__ . '/../classes/order_class.php');

/**
 * Create a new order
 */
function create_order_ctr($customer_id, $invoice_no, $order_status = 'pending') {
    $order = new Order();
    return $order->create_order($customer_id, $invoice_no, $order_status);
}

/**
 * Add order details
 */
function add_order_details_ctr($order_id, $product_id, $qty) {
    $order = new Order();
    return $order->add_order_details($order_id, $product_id, $qty);
}

/**
 * Record payment
 */
function record_payment_ctr($order_id, $customer_id, $amount, $currency = 'GHS') {
    $order = new Order();
    return $order->record_payment($order_id, $customer_id, $amount, $currency);
}

/**
 * Get customer orders
 */
function get_customer_orders_ctr($customer_id) {
    $order = new Order();
    return $order->get_customer_orders($customer_id);
}

/**
 * Get order details
 */
function get_order_details_ctr($order_id) {
    $order = new Order();
    return $order->get_order_details($order_id);
}

/**
 * Get a single order
 */
function get_order_ctr($order_id) {
    $order = new Order();
    return $order->get_order($order_id);
}

/**
 * Update order status
 */
function update_order_status_ctr($order_id, $status) {
    $order = new Order();
    return $order->update_order_status($order_id, $status);
}

/**
 * Get all orders (admin)
 */
function get_all_orders_ctr() {
    $order = new Order();
    return $order->get_all_orders();
}

/**
 * Generate invoice number
 */
function generate_invoice_number_ctr() {
    $order = new Order();
    return $order->generate_invoice_number();
}

/**
 * Get order total
 */
function get_order_total_ctr($order_id) {
    $order = new Order();
    return $order->get_order_total($order_id);
}
?>
