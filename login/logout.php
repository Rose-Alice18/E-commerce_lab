<?php
/**
 * Logout Script
 * Destroys user session and redirects to index page
 */

// Start the session
session_start();

// Unset all session variables
$_SESSION = array();

// If you want to destroy the session cookie as well
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Destroy the session
session_destroy();

// Clear the session completely
session_write_close();

// Redirect to index page
header("Location: ../index.php");
exit();
?>