<?php
/**
 * Stats Class
 * Handles database queries for platform statistics
 */

// Include the database connection class
require_once(dirname(__FILE__) . '/../settings/db_class.php');

class Stats extends db_connection {
    
    /**
     * Get total count of verified pharmacies (admin users)
     * @return int - Total number of pharmacy admins
     */
    public function get_total_pharmacies() {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT COUNT(*) as total FROM customer WHERE user_role = 1";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                return 0;
            }
            
            $stmt->execute();
            
            // CORRECT MySQLi way: bind result, then fetch
            $stmt->bind_result($total);
            $stmt->fetch();
            $stmt->close();
            
            return $total ?? 0;
        } catch (Exception $e) {
            error_log("Error getting total pharmacies: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get total count of customers
     * @return int - Total number of customers
     */
    public function get_total_customers() {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT COUNT(*) as total FROM customer WHERE user_role = 2";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                return 0;
            }
            
            $stmt->execute();
            
            // Bind result then fetch
            $stmt->bind_result($total);
            $stmt->fetch();
            $stmt->close();
            
            return $total ?? 0;
        } catch (Exception $e) {
            error_log("Error getting total customers: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get total count of categories across all pharmacies
     * @return int - Total number of categories
     */
    public function get_total_categories() {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT COUNT(*) as total FROM categories";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                return 0;
            }
            
            $stmt->execute();
            
            // Bind result then fetch
            $stmt->bind_result($total);
            $stmt->fetch();
            $stmt->close();
            
            return $total ?? 0;
        } catch (Exception $e) {
            error_log("Error getting total categories: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get total count of all users (customers + pharmacies)
     * @return int - Total number of users
     */
    public function get_total_users() {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT COUNT(*) as total FROM customer";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                return 0;
            }
            
            $stmt->execute();
            
            // Bind result then fetch
            $stmt->bind_result($total);
            $stmt->fetch();
            $stmt->close();
            
            return $total ?? 0;
        } catch (Exception $e) {
            error_log("Error getting total users: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get all platform statistics in one call
     * @return array - Array containing all statistics
     */
    public function get_all_stats() {
        return [
            'total_pharmacies' => $this->get_total_pharmacies(),
            'total_customers' => $this->get_total_customers(),
            'total_categories' => $this->get_total_categories(),
            'total_users' => $this->get_total_users()
        ];
    }
    
    /**
     * Get recent user registrations (last 30 days)
     * Note: Requires a created_at or registration_date column
     * @return int - Number of recent registrations
     */
    public function get_recent_registrations() {
        try {
            $conn = $this->db_conn();
            // Adjust column name if your table has a different timestamp column
            $sql = "SELECT COUNT(*) as total FROM customer 
                    WHERE DATE(customer_date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                return 0;
            }
            
            $stmt->execute();
            
            // Bind result then fetch
            $stmt->bind_result($total);
            $stmt->fetch();
            $stmt->close();
            
            return $total ?? 0;
        } catch (Exception $e) {
            error_log("Error getting recent registrations: " . $e->getMessage());
            return 0;
        }
    }
}
?>