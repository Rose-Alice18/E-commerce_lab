<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
 * Check if the current user has administrative privileges (Super Admin OR Pharmacy Admin)
 * @return bool True if user has admin privileges (role 0, 1, or 7), false otherwise
 */
function hasAdminPrivileges() {
    if (!isLoggedIn()) {
        return false;
    }

    // Check if role is set in session and equals 'admin' OR equals 0, 1, or 7 (numeric admin roles)
    if (isset($_SESSION['role'])) {
        return $_SESSION['role'] === 'admin' || $_SESSION['role'] == 0 || $_SESSION['role'] == 1 || $_SESSION['role'] == 7;
    }

    if (isset($_SESSION['user_role'])) {
        return $_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] == 0 || $_SESSION['user_role'] == 1 || $_SESSION['user_role'] == 7;
    }

    return false;
}

/**
 * Check if the current user is a Super Admin (role 0 or role 7)
 * Super admins can manage platform-wide settings
 * @return bool True if user is super admin, false otherwise
 */
function isSuperAdmin() {
    if (!isLoggedIn()) {
        return false;
    }

    if (isset($_SESSION['role'])) {
        return $_SESSION['role'] == 0 || $_SESSION['role'] == 7;
    }

    if (isset($_SESSION['user_role'])) {
        return $_SESSION['user_role'] == 0 || $_SESSION['user_role'] == 7;
    }

    return false;
}

/**
 * Check if the current user is a Pharmacy Owner/Admin (role 1)
 * Pharmacy admins can manage their own inventory
 * @return bool True if user is pharmacy admin, false otherwise
 */
function isPharmacyAdmin() {
    if (!isLoggedIn()) {
        return false;
    }

    if (isset($_SESSION['role'])) {
        return $_SESSION['role'] == 1;
    }

    if (isset($_SESSION['user_role'])) {
        return $_SESSION['user_role'] == 1;
    }

    return false;
}

/**
 * Check if the current user is a Regular Customer (role 2)
 * Regular customers can browse and purchase products
 * @return bool True if user is regular customer, false otherwise
 */
function isRegularCustomer() {
    if (!isLoggedIn()) {
        return false;
    }

    if (isset($_SESSION['role'])) {
        return $_SESSION['role'] == 2;
    }

    if (isset($_SESSION['user_role'])) {
        return $_SESSION['user_role'] == 2;
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

/**
 * Redirect to access denied page if user is not a Super Admin
 * @param string $accessDeniedPath Path to access denied page
 */
function requireSuperAdmin($accessDeniedPath = "../login/login.php") {
    requireLogin(); // First ensure user is logged in

    if (!isSuperAdmin()) {
        header("Location: $accessDeniedPath");
        exit;
    }
}

/**
 * Get user role name as string
 * @return string Role name (Super Admin, Pharmacy Admin, Customer, or Guest)
 */
function getUserRoleName() {
    if (!isLoggedIn()) {
        return 'Guest';
    }

    $role = getUserRole();

    switch ($role) {
        case 0:
        case 7:
            return 'Super Admin';
        case 1:
            return 'Pharmacy Admin';
        case 2:
            return 'Customer';
        default:
            return 'Unknown';
    }
}

?>