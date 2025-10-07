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
    // Check for any of these session variables indicating a logged-in user
    return (isset($_SESSION['id']) && !empty($_SESSION['id'])) || 
           (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) ||
           (isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id']));
}

/**
 * Get the current user's ID
 * @return int|null User ID if logged in, null otherwise
 */
function getUserId() {
    if (isset($_SESSION['id']) && !empty($_SESSION['id'])) {
        return $_SESSION['id'];
    } elseif (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    } elseif (isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id'])) {
        return $_SESSION['customer_id'];
    }
    return null;
}

/**
 * Check if the current user has administrative privileges
 * @return bool True if user has admin privileges, false otherwise
 */
function hasAdminPrivileges() {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Check if role is set in session and equals 'admin' OR equals 1 (numeric admin role)
    if (isset($_SESSION['role'])) {
        return $_SESSION['role'] === 'admin' || $_SESSION['role'] == 1;
    }
    
    if (isset($_SESSION['user_role'])) {
        return $_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] == 1;
    }
    
    return false;
}

/**
 * Check if user has a specific role
 * @param string|int $role The role to check for
 * @return bool True if user has the specified role, false otherwise
 */
function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Check both possible session keys
    if (isset($_SESSION['role'])) {
        return $_SESSION['role'] === $role || $_SESSION['role'] == $role;
    }
    
    if (isset($_SESSION['user_role'])) {
        return $_SESSION['user_role'] === $role || $_SESSION['user_role'] == $role;
    }
    
    return false;
}

/**
 * Get the current user's role
 * @return string|int|null User role if logged in, null otherwise
 */
function getUserRole() {
    if (!isLoggedIn()) {
        return null;
    }
    
    if (isset($_SESSION['role'])) {
        return $_SESSION['role'];
    }
    
    if (isset($_SESSION['user_role'])) {
        return $_SESSION['user_role'];
    }
    
    return null;
}

/**
 * Get the current user's name
 * @return string|null User name if logged in, null otherwise
 */
function getUserName() {
    if (!isLoggedIn()) {
        return null;
    }
    
    if (isset($_SESSION['user_name'])) {
        return $_SESSION['user_name'];
    }
    
    if (isset($_SESSION['customer_name'])) {
        return $_SESSION['customer_name'];
    }
    
    return null;
}

/**
 * Redirect to login page if user is not logged in
 * @param string $loginPath Path to login page (default: ../login/login.php)
 */
function requireLogin($loginPath = "../login/login.php") {
    if (!isLoggedIn()) {
        header("Location: $loginPath");
        exit;
    }
}

/**
 * Redirect to access denied page if user doesn't have admin privileges
 * @param string $accessDeniedPath Path to access denied page
 */
function requireAdmin($accessDeniedPath = "../login/login.php") {
    requireLogin(); // First ensure user is logged in
    
    if (!hasAdminPrivileges()) {
        header("Location: $accessDeniedPath");
        exit;
    }
}

?>