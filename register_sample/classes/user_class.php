<?php
require_once '../settings/db_class.php';

/**
 * User Class - Model
 * Handles all user-related database operations
 */
class User extends db_connection {
    private $user_id;
    private $full_name;
    private $email;
    private $password;
    private $country;
    private $city;
    private $contact_number;
    private $image;
    private $user_role;
    private $created_at;
    private $updated_at;

    public function __construct($user_id = null) {
        parent::__construct(); // Fixed: call parent constructor properly
        if ($user_id) {
            $this->user_id = $user_id;
            $this->loadUser();
        }
    }

    /**
     * Load user data from database
     */
    private function loadUser($user_id = null) {
        if ($user_id) {
            $this->user_id = $user_id;
        }
        if (!$this->user_id) {
            return false;
        }
        
        $stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            $this->full_name = $result['customer_name'];
            $this->email = $result['customer_email'];
            $this->password = $result['customer_pass'];
            $this->country = $result['customer_country'] ?? null;
            $this->city = $result['customer_city'] ?? null;
            $this->contact_number = $result['customer_contact'];
            $this->image = $result['customer_image'] ?? null;
            $this->user_role = $result['user_role'];
            $this->created_at = $result['date_created'] ?? null;
        }
        $stmt->close();
        return $result ? true : false;
    }

    /**
     * Create a new user
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
            // Validate required fields
            if (empty($name) || empty($email) || empty($password) || empty($phone_number)) {
                throw new Exception("Required fields are missing");
            }
            
            // Check if email already exists
            if ($this->emailExists($email)) {
                throw new Exception("Email already exists");
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("INSERT INTO customer (customer_name, customer_email, customer_pass, customer_contact, customer_country, customer_city, user_role, date_created) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("ssssssi", $name, $email, $hashed_password, $phone_number, $country, $city, $role);
            
            if ($stmt->execute()) {
                $user_id = $this->db->insert_id;
                $stmt->close();
                return $user_id;
            } else {
                $stmt->close();
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
        } catch (Exception $e) {
            error_log("User creation error: " . $e->getMessage());
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
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return null;
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    /**
     * Get user by ID
     * @param int $id
     * @return array|null
     */
    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return null;
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    /**
     * Check if email exists
     * @param string $email
     * @return bool
     */
    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT customer_id FROM customer WHERE customer_email = ? LIMIT 1");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    /**
     * Verify user credentials
     * @param string $email
     * @param string $password
     * @return array|false
     */
    public function verifyUser($email, $password) {
        $user = $this->getUserByEmail($email);
        
        if ($user && password_verify($password, $user['customer_pass'])) {
            // Remove password from returned data
            unset($user['customer_pass']);
            return $user;
        }
        
        return false;
    }

    /**
     * Update user information
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function editUser($id, $data) {
        try {
            $sql = "UPDATE customer SET 
                    customer_name = ?, 
                    customer_country = ?, 
                    customer_city = ?, 
                    customer_contact = ?, 
                    customer_image = ?
                    WHERE customer_id = ?";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("sssssi", 
                $data['full_name'],
                $data['country'],
                $data['city'],
                $data['contact_number'],
                $data['image'] ?? null,
                $id
            );
            
            $result = $stmt->execute() && $stmt->affected_rows > 0;
            $stmt->close();
            return $result;
            
        } catch (Exception $e) {
            error_log("User update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user
     * @param int $id
     * @return bool
     */
    public function deleteUser($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM customer WHERE customer_id = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("i", $id);
            $result = $stmt->execute() && $stmt->affected_rows > 0;
            $stmt->close();
            return $result;
            
        } catch (Exception $e) {
            error_log("User deletion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all users
     * @return array
     */
    public function getAllUsers() {
        $sql = "SELECT customer_id, customer_name, customer_email, customer_country, customer_city, customer_contact, user_role, date_created FROM customer ORDER BY date_created DESC";
        $result = $this->db->query($sql);
        
        $users = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        
        return $users;
    }

    // Getters
    public function getUserId() { return $this->user_id; }
    public function getFullName() { return $this->full_name; }
    public function getEmail() { return $this->email; }
    public function getCountry() { return $this->country; }
    public function getCity() { return $this->city; }
    public function getContactNumber() { return $this->contact_number; }
    public function getImage() { return $this->image; }
    public function getUserRole() { return $this->user_role; }
    public function getCreatedAt() { return $this->created_at; }
}
?>