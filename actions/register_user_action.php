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
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $country = $_POST['country'] ?? '';
    $city = $_POST['city'] ?? '';
    $role = intval($_POST['role'] ?? 2); // Default to customer
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($password) || empty($phone_number) || empty($country) || empty($city)) {
        $response['status'] = 'error';
        $response['message'] = 'All fields are required';
        echo json_encode($response);
        exit();
    }
    
    // Additional validations based on database schema
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid email format';
        echo json_encode($response);
        exit();
    }
    
    if (strlen($password) < 6) {
        $response['status'] = 'error';
        $response['message'] = 'Password must be at least 6 characters long';
        echo json_encode($response);
        exit();
    }
    
    // Check field lengths according to database schema
    if (strlen($name) > 100) {
        $response['status'] = 'error';
        $response['message'] = 'Name is too long (max 100 characters)';
        echo json_encode($response);
        exit();
    }
    
    if (strlen($email) > 50) {
        $response['status'] = 'error';
        $response['message'] = 'Email is too long (max 50 characters)';
        echo json_encode($response);
        exit();
    }
    
    if (strlen($phone_number) > 15) {
        $response['status'] = 'error';
        $response['message'] = 'Contact number is too long (max 15 characters)';
        echo json_encode($response);
        exit();
    }
    
    if (strlen($country) > 30) {
        $response['status'] = 'error';
        $response['message'] = 'Country name is too long (max 30 characters)';
        echo json_encode($response);
        exit();
    }
    
    if (strlen($city) > 30) {
        $response['status'] = 'error';
        $response['message'] = 'City name is too long (max 30 characters)';
        echo json_encode($response);
        exit();
    }
    
    // Prepare data for controller
    $kwargs = [
        'name' => trim(htmlspecialchars($name)),
        'email' => trim(strtolower($email)),
        'password' => $password, // Don't sanitize password
        'phone_number' => trim($phone_number),
        'country' => trim(htmlspecialchars($country)),
        'city' => trim(htmlspecialchars($city)),
        'role' => $role
    ];
    
    // Invoke the relevant function from the customer controller
    $result = register_customer_ctr($kwargs);
    
    if ($result['success']) {
        $response['status'] = 'success';
        $response['message'] = $result['message'];
        $response['customer_id'] = $result['customer_id'];
        $response['redirect'] = $result['redirect'];
    } else {
        $response['status'] = 'error';
        $response['message'] = $result['message'];
    }
    
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'Server error occurred. Please try again.';
}

// Return message to the caller
echo json_encode($response);
?>