<?php
header('Content-Type: application/json');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../settings/core.php');
require_once('../controllers/customer_controller.php');

$response = ['success' => false, 'message' => ''];

try {
    // Check if user is logged in
    if (!isLoggedIn()) {
        $response['message'] = 'You must be logged in to update your profile';
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
    $required_fields = ['customer_name', 'customer_email', 'customer_contact', 'customer_country', 'customer_city'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $response['message'] = 'All fields are required';
            echo json_encode($response);
            exit();
        }
    }

    // Sanitize data
    $data = [
        'customer_id' => $user_id,
        'customer_name' => trim($_POST['customer_name']),
        'customer_email' => trim($_POST['customer_email']),
        'customer_contact' => trim($_POST['customer_contact']),
        'customer_country' => trim($_POST['customer_country']),
        'customer_city' => trim($_POST['customer_city'])
    ];

    // Validate email format
    if (!filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format';
        echo json_encode($response);
        exit();
    }

    // Handle profile image upload if provided
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB

        // Validate file type
        if (!in_array($file['type'], $allowed_types)) {
            $response['message'] = 'Invalid file type. Only JPG, JPEG, and PNG are allowed';
            echo json_encode($response);
            exit();
        }

        // Validate file size
        if ($file['size'] > $max_size) {
            $response['message'] = 'File size too large. Maximum 5MB allowed';
            echo json_encode($response);
            exit();
        }

        // Create uploads directory if it doesn't exist
        $upload_dir = '../uploads/profiles/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $unique_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $unique_filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Get old image to delete it
            $user_result = get_customer_by_id_ctr($user_id);
            if ($user_result['success'] && !empty($user_result['data']['customer_image'])) {
                $old_image = '../' . $user_result['data']['customer_image'];
                if (file_exists($old_image)) {
                    unlink($old_image);
                }
            }

            $data['customer_image'] = 'uploads/profiles/' . $unique_filename;
        } else {
            $response['message'] = 'Failed to upload profile image';
            echo json_encode($response);
            exit();
        }
    }

    // Update profile via controller
    $result = update_customer_profile_ctr($data);

    if ($result['success']) {
        // Update session data
        $_SESSION['user_name'] = $data['customer_name'];
        $_SESSION['user_email'] = $data['customer_email'];

        $response['success'] = true;
        $response['message'] = 'Profile updated successfully!';
    } else {
        $response['message'] = $result['message'] ?? 'Failed to update profile';
    }

} catch (Exception $e) {
    error_log("Update profile error: " . $e->getMessage());
    $response['message'] = 'An error occurred while updating your profile';
}

echo json_encode($response);
?>
