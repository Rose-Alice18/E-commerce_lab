<?php
header('Content-Type: application/json');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../settings/core.php');
require_once('../classes/customer_class.php');

$response = ['success' => false, 'message' => ''];

try {
    // Check if user is logged in
    if (!isLoggedIn()) {
        $response['message'] = 'You must be logged in to change your password';
        echo json_encode($response);
        exit();
    }

    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Invalid request method';
        echo json_encode($response);
        exit();
    }

    $user_id = getUserId();

    // Validate required fields
    if (!isset($_POST['current_password']) || !isset($_POST['new_password']) || !isset($_POST['confirm_password'])) {
        $response['message'] = 'All fields are required';
        echo json_encode($response);
        exit();
    }

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate new password length
    if (strlen($new_password) < 8) {
        $response['message'] = 'New password must be at least 8 characters long';
        echo json_encode($response);
        exit();
    }

    // Check if new passwords match
    if ($new_password !== $confirm_password) {
        $response['message'] = 'New passwords do not match';
        echo json_encode($response);
        exit();
    }

    // Check if new password is same as current
    if ($current_password === $new_password) {
        $response['message'] = 'New password must be different from current password';
        echo json_encode($response);
        exit();
    }

    // Verify current password
    $customer = new Customer();
    if (!$customer->verify_password($user_id, $current_password)) {
        $response['message'] = 'Current password is incorrect';
        echo json_encode($response);
        exit();
    }

    // Update password
    if ($customer->update_password($user_id, $new_password)) {
        $response['success'] = true;
        $response['message'] = 'Password updated successfully!';
    } else {
        $response['message'] = 'Failed to update password. Please try again.';
    }

} catch (Exception $e) {
    error_log("Change password error: " . $e->getMessage());
    $response['message'] = 'An error occurred while changing your password';
}

echo json_encode($response);
?>
