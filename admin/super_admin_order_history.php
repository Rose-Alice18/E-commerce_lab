<?php
/**
 * Order History - Super Admin
 * View complete order history with detailed information
 */
session_start();
require_once('../settings/core.php');
require_once('../settings/db_cred.php');

// Check if user is super admin
if (!isSuperAdmin()) {
    header("Location: ../login/login.php");
    exit();
}

$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

// Redirecting...
header("Location: all_orders.php");
exit();
?>
