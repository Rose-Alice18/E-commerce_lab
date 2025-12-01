<?php
/**
 * Rider Controller
 * Handles all rider-related business logic
 */

require_once(__DIR__ . '/../classes/rider_class.php');

/**
 * Add a new rider
 */
function add_rider_ctr($name, $phone, $email, $license, $vehicle_number, $address, $latitude = null, $longitude = null) {
    $rider = new Rider();
    return $rider->add_rider($name, $phone, $email, $license, $vehicle_number, $address, $latitude, $longitude);
}

/**
 * Get all riders
 */
function get_all_riders_ctr() {
    $rider = new Rider();
    return $rider->get_all_riders();
}

/**
 * Get active riders
 */
function get_active_riders_ctr() {
    $rider = new Rider();
    return $rider->get_active_riders();
}

/**
 * Get rider by ID
 */
function get_rider_by_id_ctr($rider_id) {
    $rider = new Rider();
    return $rider->get_rider_by_id($rider_id);
}

/**
 * Update rider
 */
function update_rider_ctr($rider_id, $name, $phone, $email, $license, $vehicle_number, $address, $latitude = null, $longitude = null) {
    $rider = new Rider();
    return $rider->update_rider($rider_id, $name, $phone, $email, $license, $vehicle_number, $address, $latitude, $longitude);
}

/**
 * Update rider status
 */
function update_rider_status_ctr($rider_id, $status) {
    $rider = new Rider();
    return $rider->update_rider_status($rider_id, $status);
}

/**
 * Delete rider
 */
function delete_rider_ctr($rider_id) {
    $rider = new Rider();
    return $rider->delete_rider($rider_id);
}

/**
 * Get rider statistics
 */
function get_rider_stats_ctr($rider_id) {
    $rider = new Rider();
    return $rider->get_rider_stats($rider_id);
}

/**
 * Assign delivery to rider
 */
function assign_delivery_ctr($order_id, $rider_id, $customer_id, $pharmacy_id, $pickup_address, $delivery_address, $delivery_lat, $delivery_lng, $distance_km, $delivery_fee) {
    $rider = new Rider();
    return $rider->assign_delivery($order_id, $rider_id, $customer_id, $pharmacy_id, $pickup_address, $delivery_address, $delivery_lat, $delivery_lng, $distance_km, $delivery_fee);
}

/**
 * Get all delivery assignments
 */
function get_all_deliveries_ctr() {
    $rider = new Rider();
    return $rider->get_all_deliveries();
}

/**
 * Get deliveries by rider
 */
function get_deliveries_by_rider_ctr($rider_id) {
    $rider = new Rider();
    return $rider->get_deliveries_by_rider($rider_id);
}

/**
 * Update delivery status
 */
function update_delivery_status_ctr($assignment_id, $status) {
    $rider = new Rider();
    return $rider->update_delivery_status($assignment_id, $status);
}

/**
 * Get delivery by ID
 */
function get_delivery_by_id_ctr($assignment_id) {
    $rider = new Rider();
    return $rider->get_delivery_by_id($assignment_id);
}

/**
 * Get delivery statistics
 */
function get_delivery_statistics_ctr() {
    $rider = new Rider();
    return $rider->get_delivery_statistics();
}
?>
