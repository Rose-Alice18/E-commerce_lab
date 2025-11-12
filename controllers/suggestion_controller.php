<?php
/**
 * Suggestion Controller
 * Handles suggestion operations between view and model
 */

require_once(__DIR__ . '/../classes/suggestion_class.php');

// Submit category suggestion
function suggest_category_ctr($suggested_name, $suggested_by, $pharmacy_name, $reason = '') {
    $suggestion = new Suggestion();
    return $suggestion->suggest_category($suggested_name, $suggested_by, $pharmacy_name, $reason);
}

// Submit brand suggestion
function suggest_brand_ctr($suggested_name, $suggested_by, $pharmacy_name, $reason = '') {
    $suggestion = new Suggestion();
    return $suggestion->suggest_brand($suggested_name, $suggested_by, $pharmacy_name, $reason);
}

// Get pending category suggestions
function get_pending_category_suggestions_ctr() {
    $suggestion = new Suggestion();
    return $suggestion->get_pending_category_suggestions();
}

// Get pending brand suggestions
function get_pending_brand_suggestions_ctr() {
    $suggestion = new Suggestion();
    return $suggestion->get_pending_brand_suggestions();
}

// Get all category suggestions
function get_all_category_suggestions_ctr($status = null) {
    $suggestion = new Suggestion();
    return $suggestion->get_all_category_suggestions($status);
}

// Get all brand suggestions
function get_all_brand_suggestions_ctr($status = null) {
    $suggestion = new Suggestion();
    return $suggestion->get_all_brand_suggestions($status);
}

// Get pharmacy's category suggestions
function get_pharmacy_category_suggestions_ctr($pharmacy_id) {
    $suggestion = new Suggestion();
    return $suggestion->get_pharmacy_category_suggestions($pharmacy_id);
}

// Get pharmacy's brand suggestions
function get_pharmacy_brand_suggestions_ctr($pharmacy_id) {
    $suggestion = new Suggestion();
    return $suggestion->get_pharmacy_brand_suggestions($pharmacy_id);
}

// Approve category suggestion
function approve_category_suggestion_ctr($suggestion_id, $reviewed_by, $review_comment = '') {
    $suggestion = new Suggestion();
    return $suggestion->approve_category_suggestion($suggestion_id, $reviewed_by, $review_comment);
}

// Approve brand suggestion
function approve_brand_suggestion_ctr($suggestion_id, $reviewed_by, $review_comment = '') {
    $suggestion = new Suggestion();
    return $suggestion->approve_brand_suggestion($suggestion_id, $reviewed_by, $review_comment);
}

// Reject category suggestion
function reject_category_suggestion_ctr($suggestion_id, $reviewed_by, $review_comment) {
    $suggestion = new Suggestion();
    return $suggestion->reject_category_suggestion($suggestion_id, $reviewed_by, $review_comment);
}

// Reject brand suggestion
function reject_brand_suggestion_ctr($suggestion_id, $reviewed_by, $review_comment) {
    $suggestion = new Suggestion();
    return $suggestion->reject_brand_suggestion($suggestion_id, $reviewed_by, $review_comment);
}

// Get pending suggestions count
function get_pending_suggestions_count_ctr() {
    $suggestion = new Suggestion();
    return $suggestion->get_pending_suggestions_count();
}
?>
