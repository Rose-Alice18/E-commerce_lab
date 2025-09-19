<?php
require_once '../classes/customer_class.php';

/**
 * Customer Controller
 * Creates an instance of the customer class and runs the methods
 */

/**
 * Register customer controller function
 * @param array $kwargs - Customer registration data
 * @return array Response with success status and data
 */
function register_customer_ctr($kwargs) {
    try {
        // Create customer instance
        $customer = new Customer();
        
        // Invoke customer_class::add($args) method
        $customer_id = $customer->add($kwargs);
        
        if ($customer_id) {
            return [
                'success' => true,
                'message' => 'Customer registered successfully',
                'customer_id' => $customer_id,
                'redirect' => 'login/login.php'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to register customer. Email might already exist.'
            ];
        }
        
    } catch (Exception $e) {
        error_log("Register customer controller error: " . $e->getMessage());
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
        
        // Create customer instance
        $customer = new Customer();
        
        // Invoke customer_class::get($args) method
        $customer_data = $customer->get($kwargs);
        
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
 * Edit customer controller function
 * @param int $customer_id
 * @param array $kwargs
 * @return bool
 */
function edit_customer_ctr($customer_id, $kwargs) {
    try {
        $customer = new Customer();
        return $customer->editCustomer($customer_id, $kwargs);
    } catch (Exception $e) {
        error_log("Edit customer controller error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete customer controller function
 * @param int $customer_id
 * @return bool
 */
function delete_customer_ctr($customer_id) {
    try {
        $customer = new Customer();
        return $customer->deleteCustomer($customer_id);
    } catch (Exception $e) {
        error_log("Delete customer controller error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get customer by email controller function
 * @param string $email
 * @return array|null
 */
function get_customer_by_email_ctr($email) {
    try {
        $customer = new Customer();
        return $customer->getCustomerByEmail($email);
    } catch (Exception $e) {
        error_log("Get customer by email controller error: " . $e->getMessage());
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
        $customer = new Customer();
        return !$customer->emailExists($email);
    } catch (Exception $e) {
        error_log("Check email availability controller error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all customers controller function
 * @return array
 */
function get_all_customers_ctr() {
    try {
        $customer = new Customer();
        return $customer->getAllCustomers();
    } catch (Exception $e) {
        error_log("Get all customers controller error: " . $e->getMessage());
        return [];
    }
}
?>