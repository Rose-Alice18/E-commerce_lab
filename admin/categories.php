<?php
/**
 * Categories Management (Super Admin View)
 * Role: Super Admin (0)
 */
session_start();
require_once('../settings/core.php');

// Check if user is logged in as super admin
if (!isLoggedIn() || !isSuperAdmin()) {
    header("Location: ../login/login_view.php");
    exit();
}

// For now, redirect to category.php which already exists
header("Location: category.php");
exit();
?>
