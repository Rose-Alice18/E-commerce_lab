<?php
require_once '../settings/db_class.php';

/**
 * Customer Class - Model
 * Extends database connection and contains customer methods: add customer, edit customer, delete customer, etc.
 */
class Customer extends db_connection {
    
    public function __construct() {
        parent::db_connect();
    }
    
    /**
     * Add customer method
     * @param array $args - Customer data
     * @return int|false Customer ID on success, false on failure
     */
    public function add($args) {
        try {
            // Check if email already exists
            if ($this->emailExists($args['email'])) {
                return false;
            }
            
            // Encrypt password before adding to database
            $hashed_password = password_hash($args['password'], PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("INSERT INTO customer (customer_name, customer_email, customer_pass, customer_contact, customer_country, customer_city, user_role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssi", 
                $args['name'], 
                $args['email'], 
                $hashed_password, 
                $args['phone_number'], 
                $args['country'], 
                $args['city'], 
                $args['role']
            );
            
            if ($stmt->execute()) {
                return $this->db->insert_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Customer add error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get customer by email address and check password
     * @param array $args - Contains email and password
     * @return array|false Customer data on success, false on failure
     */
    public function get($args) {
        try {
            // Get customer by email
            $customer = $this->getCustomerByEmail($args['email']);
            
            if ($customer) {
                // Check if password input matches the password stored
                if (password_verify($args['password'], $customer['customer_pass'])) {
                    // Remove password from returned data for security
                    unset($customer['customer_pass']);
                    return $customer;
                } else {
                    return false; // Password doesn't match
                }
            } else {
                return false; // Customer not found
            }
            
        } catch (Exception $e) {
            error_log("Customer get error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Edit customer
     * @param int $customer_id
     * @param array $args
     * @return bool
     */
    public function editCustomer($customer_id, $args) {
        try {
            $sql = "UPDATE customer SET 
                    customer_name = ?, 
                    customer_country = ?, 
                    customer_city = ?, 
                    customer_contact = ?, 
                    customer_image = ?
                    WHERE customer_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("sssssi", 
                $args['name'],
                $args['country'],
                $args['city'],
                $args['contact'],
                $args['image'] ?? null,
                $customer_id
            );
            
            return $stmt->execute() && $stmt->affected_rows > 0;
            
        } catch (Exception $e) {
            error_log("Customer edit error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete customer
     * @param int $customer_id
     * @return bool
     */
    public function deleteCustomer($customer_id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM customer WHERE customer_id = ?");
            $stmt->bind_param("i", $customer_id);
            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Customer delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get customer by email
     * @param string $email
     * @return array|null
     */
    public function getCustomerByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Check if email exists - Check if email is available before adding new customer
     * @param string $email
     * @return bool
     */
    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT customer_id FROM customer WHERE customer_email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    /**
     * Get customer by ID
     * @param int $customer_id
     * @return array|null
     */
    public function getCustomerById($customer_id) {
        $stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Get all customers
     * @return array
     */
    public function getAllCustomers() {
        $sql = "SELECT customer_id, customer_name, customer_email, customer_country, customer_city, customer_contact, user_role FROM customer ORDER BY customer_id DESC";
        $result = $this->db->query($sql);
        
        $customers = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $customers[] = $row;
            }
        }
        
        return $customers;
    }
}
?>