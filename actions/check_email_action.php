<?php
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit();
}

require_once '../controllers/user_controller.php';

try {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Email is required']);
        exit();
    }
    
    // Sanitize email
    $email = trim(strtolower($email));
    
    // Check if email is available
    $available = check_email_availability_ctr($email);
    
    if ($available) {
        echo json_encode(['status' => 'available']);
    } else {
        echo json_encode(['status' => 'taken']);
    }
    
} catch (Exception $e) {
    error_log("Check email error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
}
?>