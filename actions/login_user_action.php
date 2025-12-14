<?php
header('Content-Type: application/json');

session_start();

$response = array();

// Check if the user is already logged in
if (isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true) {
    $response['status'] = 'error';
    $response['success'] = false;
    $response['message'] = 'You are already logged in';
    echo json_encode($response);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['status'] = 'error';
    $response['success'] = false;
    $response['message'] = 'Method not allowed';
    echo json_encode($response);
    exit();
}

require_once '../controllers/user_controller.php';

try {
    // Get form data
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Sanitize input data
    $email = trim(strtolower($email));
    $password = $password; // Don't sanitize password
    
    // Call the controller function
    $result = login_user_ctr($email, $password);
    
    // Return the response from controller
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    
    $response['status'] = 'error';
    $response['success'] = false;
    $response['message'] = 'Server error: ' . $e->getMessage();
    echo json_encode($response);
}
?>