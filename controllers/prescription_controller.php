<?php
/**
 * Prescription Controller
 * Business logic layer for prescription operations
 */

require_once(__DIR__ . '/../classes/prescription_class.php');

/**
 * Generate unique prescription number
 */
function generate_prescription_number_ctr() {
    $prescription = new Prescription();
    return $prescription->generate_prescription_number();
}

/**
 * Upload new prescription
 */
function upload_prescription_ctr($data) {
    $prescription = new Prescription();
    return $prescription->upload_prescription($data);
}

/**
 * Add prescription item
 */
function add_prescription_item_ctr($prescription_id, $item_data) {
    $prescription = new Prescription();
    return $prescription->add_prescription_item($prescription_id, $item_data);
}

/**
 * Get prescription by ID
 */
function get_prescription_ctr($prescription_id) {
    $prescription = new Prescription();
    return $prescription->get_prescription($prescription_id);
}

/**
 * Get prescription items
 */
function get_prescription_items_ctr($prescription_id) {
    $prescription = new Prescription();
    return $prescription->get_prescription_items($prescription_id);
}

/**
 * Get customer's prescriptions
 */
function get_customer_prescriptions_ctr($customer_id, $status = null) {
    $prescription = new Prescription();
    return $prescription->get_customer_prescriptions($customer_id, $status);
}

/**
 * Get pending prescriptions
 */
function get_pending_prescriptions_ctr() {
    $prescription = new Prescription();
    return $prescription->get_pending_prescriptions();
}

/**
 * Get all prescriptions (admin)
 */
function get_all_prescriptions_ctr($status = null, $limit = null) {
    $prescription = new Prescription();
    return $prescription->get_all_prescriptions($status, $limit);
}

/**
 * Verify prescription
 */
function verify_prescription_ctr($prescription_id, $verified_by, $notes = null) {
    $prescription = new Prescription();
    return $prescription->verify_prescription($prescription_id, $verified_by, $notes);
}

/**
 * Reject prescription
 */
function reject_prescription_ctr($prescription_id, $rejected_by, $reason) {
    $prescription = new Prescription();
    return $prescription->reject_prescription($prescription_id, $rejected_by, $reason);
}

/**
 * Link prescription to order
 */
function link_prescription_to_order_ctr($order_id, $prescription_id) {
    $prescription = new Prescription();
    return $prescription->link_prescription_to_order($order_id, $prescription_id);
}

/**
 * Get order prescription
 */
function get_order_prescription_ctr($order_id) {
    $prescription = new Prescription();
    return $prescription->get_order_prescription($order_id);
}

/**
 * Mark prescription item as fulfilled
 */
function mark_prescription_item_fulfilled_ctr($prescription_item_id) {
    $prescription = new Prescription();
    return $prescription->mark_item_fulfilled($prescription_item_id);
}

/**
 * Check if product requires prescription
 */
function product_requires_prescription_ctr($product_id) {
    $prescription = new Prescription();
    return $prescription->product_requires_prescription($product_id);
}

/**
 * Get prescription-required products
 */
function get_prescription_required_products_ctr() {
    $prescription = new Prescription();
    return $prescription->get_prescription_required_products();
}

/**
 * Set product prescription requirement
 */
function set_product_prescription_requirement_ctr($product_id, $required = true) {
    $prescription = new Prescription();
    return $prescription->set_product_prescription_requirement($product_id, $required);
}

/**
 * Log prescription action
 */
function log_prescription_action_ctr($prescription_id, $action, $performed_by, $notes = null) {
    $prescription = new Prescription();
    return $prescription->log_prescription_action($prescription_id, $action, $performed_by, $notes);
}

/**
 * Get prescription history
 */
function get_prescription_history_ctr($prescription_id) {
    $prescription = new Prescription();
    return $prescription->get_prescription_history($prescription_id);
}

/**
 * Expire old prescriptions
 */
function expire_old_prescriptions_ctr() {
    $prescription = new Prescription();
    return $prescription->expire_old_prescriptions();
}

/**
 * Get prescription statistics
 */
function get_prescription_stats_ctr() {
    $prescription = new Prescription();
    return $prescription->get_prescription_stats();
}

/**
 * Search prescriptions
 */
function search_prescriptions_ctr($search_term) {
    $prescription = new Prescription();
    return $prescription->search_prescriptions($search_term);
}

/**
 * Delete prescription (admin only)
 */
function delete_prescription_ctr($prescription_id, $deleted_by) {
    $prescription = new Prescription();
    return $prescription->delete_prescription($prescription_id, $deleted_by);
}

/**
 * Upload prescription image file
 * @return array ['success' => bool, 'file_path' => string, 'message' => string]
 */
function upload_prescription_image($file, $customer_id) {
    $upload_dir = __DIR__ . '/../uploads/prescriptions/';
    $customer_dir = $upload_dir . 'u' . $customer_id . '/';

    // Create directories if they don't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    if (!is_dir($customer_dir)) {
        mkdir($customer_dir, 0755, true);
    }

    // Validate file
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        return [
            'success' => false,
            'message' => 'Invalid file type. Only JPG, PNG, GIF, and PDF are allowed.'
        ];
    }

    if ($file['size'] > $max_size) {
        return [
            'success' => false,
            'message' => 'File too large. Maximum size is 5MB.'
        ];
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'rx_' . time() . '_' . uniqid() . '.' . $extension;
    $file_path = $customer_dir . $filename;
    $relative_path = 'uploads/prescriptions/u' . $customer_id . '/' . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        return [
            'success' => true,
            'file_path' => $relative_path,
            'message' => 'File uploaded successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to upload file'
        ];
    }
}
?>
