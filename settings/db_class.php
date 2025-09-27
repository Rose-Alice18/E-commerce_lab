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
     * Get database connection - THIS IS THE METHOD YOUR CATEGORY CLASS NEEDS
     * @return mysqli The database connection object
     */
    public function db_conn() {
        return $this->db;
    }
    
    /**
     * Alternative method name for getting connection
     * @return mysqli The database connection object
     */
    public function getConnection() {
        return $this->db;
    }
    
    /**
     * Execute a query
     * @param string $query SQL query
     * @return mysqli_result|bool Query result
     */
    public function query($query) {
        try {
            return $this->db->query($query);
        } catch (Exception $e) {
            error_log("Query error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Prepare a statement
     * @param string $query SQL query with placeholders
     * @return mysqli_stmt|false Prepared statement
     */
    public function prepare($query) {
        try {
            return $this->db->prepare($query);
        } catch (Exception $e) {
            error_log("Prepare error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get last insert ID
     * @return int Last inserted ID
     */
    public function getLastInsertId() {
        return $this->db->insert_id;
    }
    
    /**
     * Get affected rows
     * @return int Number of affected rows
     */
    public function getAffectedRows() {
        return $this->db->affected_rows;
    }
    
    /**
     * Escape string for safe queries
     * @param string $string String to escape
     * @return string Escaped string
     */
    public function escapeString($string) {
        return $this->db->real_escape_string($string);
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