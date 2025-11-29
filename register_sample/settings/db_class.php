<?php
require_once 'db_cred.php';

/**
 * Database Connection Class
 * Handles database connection and basic operations
 */
class db_connection {
    public $db;
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->db_connect();
    }
    
    /**
     * Connect to database
     * @return bool Connection success status
     */
    public function db_connect() {
        try {
            $this->db = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);
            
            // Check connection
            if ($this->db->connect_error) {
                throw new Exception("Connection failed: " . $this->db->connect_error);
            }
            
            // Set charset to utf8
            $this->db->set_charset("utf8");
            
            return true;
            
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    
    /**
     * Close database connection
     */
    public function db_close() {
        if ($this->db) {
            $this->db->close();
        }
    }
    
    /**
     * Destructor - Close connection when object is destroyed
     */
    public function __destruct() {
        $this->db_close();
    }
}