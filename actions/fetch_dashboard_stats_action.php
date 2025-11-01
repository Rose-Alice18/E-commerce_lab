<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core and controller
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/stats_controller.php';

// Set header for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to perform this action',
        'data' => []
    ]);
    exit();
}

// Check if user is SUPER ADMIN (role 0 only)
if (!isSuperAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Only Super Admins can access the dashboard',
        'data' => []
    ]);
    exit();
}

// Call controller to get dashboard statistics
$result = get_dashboard_stats_ctr();

// Return response
echo json_encode($result);
