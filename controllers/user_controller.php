<?php
require_once '../classes/user_class.php';

/**
 * User Controller
 * Creates an instance of the user class and runs the methods
 */

/**
 * Register user controller function
 * @param string $name
 * @param string $email
 * @param string $password
 * @param string $phone_number
 * @param string $country
 * @param string $city
 * @param int $role
 * @return array Response with success status and data
 */
function register_user_ctr($name, $email, $password, $phone_number, $country, $city, $role) {
    try {
        // Create user instance
        $user = new User();
        
        // Invoke user_class::createUser() method
        $user_id = $user->createUser($name, $email, $password, $phone_number, $country, $city, $role);
        
        if ($user_id) {
            return [
                'success' => true,
                'message' => 'User registered successfully',
                'user_id' => $user_id,
                'redirect' => 'login/login.php'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to register user. Email might already exist.'
            ];
        }
        
    } catch (Exception $e) {
        error_log("Register user controller error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Registration failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Login customer controller function
 * @param array $kwargs - Login data (email, password)
 * @return array Response with success status and user data
 */
function login_customer_ctr($kwargs) {
    try {
        // Validate required fields
        if (empty($kwargs['email']) || empty($kwargs['password'])) {
            return [
                'success' => false,
                'message' => 'Email and password are required'
            ];
        }
        
        // Additional validations
        if (!filter_var($kwargs['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Invalid email format'
            ];
        }
        
        // Create user instance
        $user = new User();
        
        // Invoke customer_class::getCustomerByEmailAndPassword() method
        $customer_data = $user->getCustomerByEmailAndPassword($kwargs['email'], $kwargs['password']);
        
        if ($customer_data) {
            return [
                'success' => true,
                'message' => 'Login successful',
                'user_data' => $customer_data
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Invalid email or password'
            ];
        }
        
    } catch (Exception $e) {
        error_log("Login customer controller error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Login failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Edit user controller function
 * @param int $user_id
 * @param array $data
 * @return bool
 */
function edit_user_ctr($user_id, $data) {
    try {
        $user = new User();
        return $user->editUser($user_id, $data);
    } catch (Exception $e) {
        error_log("Edit user controller error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete user controller function
 * @param int $user_id
 * @return bool
 */
function delete_user_ctr($user_id) {
    try {
        $user = new User();
        return $user->deleteUser($user_id);
    } catch (Exception $e) {
        error_log("Delete user controller error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user by email controller function
 * @param string $email
 * @return array|null
 */
function get_user_by_email_ctr($email) {
    try {
        $user = new User();
        return $user->getUserByEmail($email);
    } catch (Exception $e) {
        error_log("Get user by email controller error: " . $e->getMessage());
        return null;
    }
}

/**
 * Check email availability controller function
 * @param string $email
 * @return bool True if available, false if taken
 */
function check_email_availability_ctr($email) {
    try {
        $user = new User();
        return !$user->emailExists($email);
    } catch (Exception $e) {
        error_log("Check email availability controller error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all users controller function
 * @return array
 */
function get_all_users_ctr() {
    try {
        $user = new User();
        return $user->getAllUsers();
    } catch (Exception $e) {
        error_log("Get all users controller error: " . $e->getMessage());
        return [];
    }
}
?>