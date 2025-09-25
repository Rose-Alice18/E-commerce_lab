<?php
// Settings/core.php
session_start();

// For header redirection
ob_start();

/**
 * Check if a user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['id']) && !empty($_SESSION['id']);
}

/**
 * Get the current user's ID
 * @return int|null User ID if logged in, null otherwise
 */
function getUserId() {
    return isLoggedIn() ? $_SESSION['id'] : null;
}

/**
 * Check if the current user has administrative privileges
 * @return bool True if user has admin privileges, false otherwise
 */
function hasAdminPrivileges() {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Check if role is set in session and equals 'admin'
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user has a specific role
 * @param string $role The role to check for
 * @return bool True if user has the specified role, false otherwise
 */
function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Get the current user's role
 * @return string|null User role if logged in, null otherwise
 */
function getUserRole() {
    return isLoggedIn() && isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

/**
 * Redirect to login page if user is not logged in
 * @param string $loginPath Path to login page (default: ../Login/login_register.php)
 */
function requireLogin($loginPath = "../Login/login_register.php") {
    if (!isLoggedIn()) {
        header("Location: $loginPath");
        exit;
    }
}

/**
 * Redirect to access denied page if user doesn't have admin privileges
 * @param string $accessDeniedPath Path to access denied page
 */
function requireAdmin($accessDeniedPath = "../Error/access_denied.php") {
    requireLogin(); // First ensure user is logged in
    
    if (!hasAdminPrivileges()) {
        header("Location: $accessDeniedPath");
        exit;
    }
}

// Automatically redirect if not logged in (you can remove this if you want manual control)
requireLogin();

?>