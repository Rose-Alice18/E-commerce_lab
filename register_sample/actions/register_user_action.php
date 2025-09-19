<?php
header('Content-Type: application/json');

session_start();

$response = array();

// Check if the user is already logged in and redirect to the dashboard
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

error_log("POST data: " . print_r($_POST, true));
error_log("Attempting to register user with data: name=$name, email=$email, country=$country, city=$city, phone_number=$phone_number, role=$role");

try {
    // Get form data
    $name = $_POST['name'] ?? $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $phone_number = $_POST['phone_number'] ?? $_POST['contact_number'] ?? '';
    $country = $_POST['country'] ?? '';
    $city = $_POST['city'] ?? '';
    $role = $_POST['role'] ?? $_POST['user_role'] ?? 2; // Default to customer
    
    // Sanitize input data
    $name = trim(htmlspecialchars($name));
    $email = trim(strtolower($email));

    // Note: Password is intentionally not sanitized here as it will be hashed
// $password remains unchanged for hashing process
    //$password = $password; // Don't sanitize password
    
    $phone_number = trim($phone_number);
    $country = trim(htmlspecialchars($country));
    $city = trim(htmlspecialchars($city));
    $role = intval($role);
    
    // Call the controller function
    $result = register_user_ctr($name, $email, $password, $phone_number, $country, $city, $role);
    
    if ($result['success']) {
        $response['status'] = 'success';
        $response['success'] = true;
        $response['message'] = $result['message'];
        $response['user_id'] = $result['user_id'];
        $response['redirect'] = $result['redirect'];
    } else {
        $response['status'] = 'error';
        $response['success'] = false;
        $response['message'] = $result['message'];
    }
    
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    
    $response['status'] = 'error';
    $response['success'] = false;
    $response['message'] = 'Server error: ' . $e->getMessage();
}

echo json_encode($response);
?>