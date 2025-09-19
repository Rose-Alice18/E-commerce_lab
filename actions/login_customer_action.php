<?php
header('Content-Type: application/json');

session_start();

$response = array();

// Check if the user is already logged in
if (isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true) {
    $response['status'] = 'error';
    $response['message'] = 'You are already logged in';
    echo json_encode($response);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['status'] = 'error';
    $response['message'] = 'Method not allowed';
    echo json_encode($response);
    exit();
}

require_once '../controllers/customer_controller.php';

try {
    // Get form data
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate required fields
    if (empty($email) || empty($password)) {
        $response['status'] = 'error';
        $response['message'] = 'Email and password are required';
        echo json_encode($response);
        exit();
    }
    
    // Sanitize email
    $email = trim(strtolower($email));
    
    // Call the controller function
    $result = login_customer_ctr(['email' => $email, 'password' => $password]);
    
    if ($result['success']) {
        // Set session variables for user ID, role, name, and other attributes
        $_SESSION['user_id'] = $result['user_data']['customer_id'];
        $_SESSION['user_name'] = $result['user_data']['customer_name'];
        $_SESSION['user_email'] = $result['user_data']['customer_email'];
        $_SESSION['user_role'] = $result['user_data']['user_role'];
        $_SESSION['user_country'] = $result['user_data']['customer_country'];
        $_SESSION['user_city'] = $result['user_data']['customer_city'];
        $_SESSION['user_contact'] = $result['user_data']['customer_contact'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        $response['status'] = 'success';
        $response['message'] = $result['message'];
        $response['redirect'] = '../index.php'; // Direct user to landing page on successful login
    } else {
        $response['status'] = 'error';
        $response['message'] = $result['message'];
    }
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    
    $response['status'] = 'error';
    $response['message'] = 'Server error occurred. Please try again.';
}

echo json_encode($response);
?>