<?php
require_once '../settings/db_class.php';

/**
 * User Class - Model
 * Extends database connection and contains customer methods
 */
class User extends db_connection {
    
    public function __construct() {
        parent::db_connect();
    }
    
    /**
     * Add a new customer to the database
     * @param string $name
     * @param string $email
     * @param string $password
     * @param string $phone_number
     * @param string $country
     * @param string $city
     * @param int $role
     * @return int|false User ID on success, false on failure
     */
    public function createUser($name, $email, $password, $phone_number, $country, $city, $role) {
        try {
            // Check if email already exists
            if ($this->emailExists($email)) {
                return false;
            }
            
            // Encrypt password before adding to database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("INSERT INTO customer (customer_name, customer_email, customer_pass, customer_contact, customer_country, customer_city, user_role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssi", $name, $email, $hashed_password, $phone_number, $country, $city, $role);
            
            if ($stmt->execute()) {
                return $this->db->insert_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("User creation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get customer by email and verify password
     * @param string $email
     * @param string $password
     * @return array|false Customer data on success, false on failure
     */
    public function getCustomerByEmailAndPassword($email, $password) {
        try {
            // Get customer by email
            $customer = $this->getUserByEmail($email);
            
            if ($customer) {
                // Check if password input matches the password stored
                if (password_verify($password, $customer['customer_pass'])) {
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
            error_log("Login verification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Edit customer information
     * @param int $customer_id
     * @param array $data
     * @return bool
     */
    public function editUser($customer_id, $data) {
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
                $data['name'],
                $data['country'],
                $data['city'],
                $data['contact'],
                $data['image'] ?? null,
                $customer_id
            );
            
            return $stmt->execute() && $stmt->affected_rows > 0;
            
        } catch (Exception $e) {
            error_log("User edit error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete customer
     * @param int $customer_id
     * @return bool
     */
    public function deleteUser($customer_id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM customer WHERE customer_id = ?");
            $stmt->bind_param("i", $customer_id);
            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("User delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user by email
     * @param string $email
     * @return array|null
     */
    public function getUserByEmail($email) {
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
     * Get user by ID
     * @param int $customer_id
     * @return array|null
     */
    public function getUserById($customer_id) {
        $stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Get all users
     * @return array
     */
    public function getAllUsers() {
        $sql = "SELECT customer_id, customer_name, customer_email, customer_country, customer_city, customer_contact, user_role FROM customer ORDER BY customer_id DESC";
        $result = $this->db->query($sql);
        
        $users = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        
        return $users;
    }
}
?>