<?php
/**
 * Stats Controller
 * Handles business logic for platform statistics
 */

// Include the stats class
require_once(dirname(__FILE__) . '/../classes/stats_class.php');

/**
 * Get total number of pharmacies
 * @return int - Total pharmacies count
 */
function get_total_pharmacies_ctr() {
    $stats = new Stats();
    return $stats->get_total_pharmacies();
}

/**
 * Get total number of customers
 * @return int - Total customers count
 */
function get_total_customers_ctr() {
    $stats = new Stats();
    return $stats->get_total_customers();
}

/**
 * Get total number of categories
 * @return int - Total categories count
 */
function get_total_categories_ctr() {
    $stats = new Stats();
    return $stats->get_total_categories();
}

/**
 * Get total number of users
 * @return int - Total users count
 */
function get_total_users_ctr() {
    $stats = new Stats();
    return $stats->get_total_users();
}

/**
 * Get all platform statistics
 * @return array - Array with all statistics
 */
function get_all_platform_stats_ctr() {
    $stats = new Stats();
    return $stats->get_all_stats();
}

/**
 * Format number with K/M suffix for display
 * @param int $number - Number to format
 * @return string - Formatted number (e.g., "1.5K", "2.3M")
 */
function format_stat_number($number) {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return (string)$number;
}

/**
 * Get formatted statistics for display
 * @return array - Array with formatted statistics
 */
function get_formatted_stats_ctr() {
    $stats = get_all_platform_stats_ctr();
    
    return [
        'pharmacies' => [
            'value' => $stats['total_pharmacies'],
            'formatted' => format_stat_number($stats['total_pharmacies']),
            'label' => 'Verified Pharmacies'
        ],
        'customers' => [
            'value' => $stats['total_customers'],
            'formatted' => format_stat_number($stats['total_customers']),
            'label' => 'Happy Customers'
        ],
        'categories' => [
            'value' => $stats['total_categories'],
            'formatted' => format_stat_number($stats['total_categories']),
            'label' => 'Product Categories'
        ],
        'users' => [
            'value' => $stats['total_users'],
            'formatted' => format_stat_number($stats['total_users']),
            'label' => 'Total Users'
        ]
    ];
}
?>