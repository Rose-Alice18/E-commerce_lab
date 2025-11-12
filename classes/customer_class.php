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

    /**
     * Update customer profile
     * @param array $data - Profile data to update
     * @return bool True on success, false on failure
     */
    public function update_profile($data) {
        try {
            $customer_id = $data['customer_id'];

            // Build the update query dynamically based on provided fields
            $update_parts = [];
            $params = [];
            $types = '';

            if (isset($data['customer_name'])) {
                $update_parts[] = "customer_name = ?";
                $params[] = $data['customer_name'];
                $types .= 's';
            }

            if (isset($data['customer_email'])) {
                // Check if email is already taken by another user
                $stmt = $this->db->prepare("SELECT customer_id FROM customer WHERE customer_email = ? AND customer_id != ?");
                $stmt->bind_param("si", $data['customer_email'], $customer_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    return false; // Email already taken
                }

                $update_parts[] = "customer_email = ?";
                $params[] = $data['customer_email'];
                $types .= 's';
            }

            if (isset($data['customer_contact'])) {
                $update_parts[] = "customer_contact = ?";
                $params[] = $data['customer_contact'];
                $types .= 's';
            }

            if (isset($data['customer_country'])) {
                $update_parts[] = "customer_country = ?";
                $params[] = $data['customer_country'];
                $types .= 's';
            }

            if (isset($data['customer_city'])) {
                $update_parts[] = "customer_city = ?";
                $params[] = $data['customer_city'];
                $types .= 's';
            }

            if (isset($data['customer_image'])) {
                $update_parts[] = "customer_image = ?";
                $params[] = $data['customer_image'];
                $types .= 's';
            }

            if (empty($update_parts)) {
                return false; // Nothing to update
            }

            // Add customer_id to params for WHERE clause
            $params[] = $customer_id;
            $types .= 'i';

            $sql = "UPDATE customer SET " . implode(", ", $update_parts) . " WHERE customer_id = ?";
            $stmt = $this->db->prepare($sql);

            // Bind parameters dynamically
            $stmt->bind_param($types, ...$params);

            return $stmt->execute() && $stmt->affected_rows > 0;

        } catch (Exception $e) {
            error_log("Update profile error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update customer password
     * @param int $customer_id - Customer ID
     * @param string $new_password - New password (will be hashed)
     * @return bool True on success, false on failure
     */
    public function update_password($customer_id, $new_password) {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("UPDATE customer SET customer_pass = ? WHERE customer_id = ?");
            $stmt->bind_param("si", $hashed_password, $customer_id);

            return $stmt->execute() && $stmt->affected_rows > 0;

        } catch (Exception $e) {
            error_log("Update password error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify customer password
     * @param int $customer_id - Customer ID
     * @param string $password - Password to verify
     * @return bool True if password matches, false otherwise
     */
    public function verify_password($customer_id, $password) {
        try {
            $stmt = $this->db->prepare("SELECT customer_pass FROM customer WHERE customer_id = ?");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return password_verify($password, $row['customer_pass']);
            }

            return false;

        } catch (Exception $e) {
            error_log("Verify password error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all users by role
     * @param int $role - User role (0=Super Admin, 1=Pharmacy Admin, 2=Customer)
     * @return array List of users
     */
    public function getAllUsersByRole($role) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM customer WHERE user_role = ? ORDER BY customer_name ASC");
            $stmt->bind_param("i", $role);
            $stmt->execute();
            $result = $stmt->get_result();

            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }

            return $users;

        } catch (Exception $e) {
            error_log("Get users by role error: " . $e->getMessage());
            return [];
        }
    }
}