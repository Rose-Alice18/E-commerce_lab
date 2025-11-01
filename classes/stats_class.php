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
     * @return int - Number of recent registrations
     */
    public function get_recent_registrations() {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT COUNT(*) as total FROM customer
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                return 0;
            }

            $stmt->execute();
            $stmt->bind_result($total);
            $stmt->fetch();
            $stmt->close();

            return $total ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get total count of brands across all pharmacies
     * @return int - Total number of brands
     */
    public function get_total_brands() {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT COUNT(*) as total FROM brands";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                return 0;
            }

            $stmt->execute();
            $stmt->bind_result($total);
            $stmt->fetch();
            $stmt->close();

            return $total ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get total count of products across all pharmacies
     * @return int - Total number of products
     */
    public function get_total_products() {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT COUNT(*) as total FROM products";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                return 0;
            }

            $stmt->execute();
            $stmt->bind_result($total);
            $stmt->fetch();
            $stmt->close();

            return $total ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get list of all pharmacies with their stats
     * @return array - Array of pharmacies with product counts
     */
    public function get_pharmacy_stats() {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT
                        c.customer_id,
                        c.customer_name,
                        c.customer_email,
                        c.customer_city,
                        c.created_at,
                        COUNT(DISTINCT cat.cat_id) as category_count,
                        COUNT(DISTINCT b.brand_id) as brand_count,
                        COUNT(DISTINCT p.product_id) as product_count
                    FROM customer c
                    LEFT JOIN categories cat ON c.customer_id = cat.user_id
                    LEFT JOIN brands b ON c.customer_id = b.user_id
                    LEFT JOIN products p ON c.customer_id = p.pharmacy_id
                    WHERE c.user_role = 1
                    GROUP BY c.customer_id
                    ORDER BY product_count DESC";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                return [];
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $pharmacies = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $pharmacies;
        } catch (Exception $e) {
            error_log("Error getting pharmacy stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get category distribution (how many brands per category)
     * @return array - Array of categories with brand counts
     */
    public function get_category_distribution() {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT
                        c.cat_name,
                        COUNT(DISTINCT b.brand_id) as brand_count,
                        COUNT(DISTINCT p.product_id) as product_count
                    FROM categories c
                    LEFT JOIN brands b ON c.cat_id = b.cat_id
                    LEFT JOIN products p ON c.cat_id = p.product_cat
                    GROUP BY c.cat_id
                    ORDER BY product_count DESC
                    LIMIT 10";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                return [];
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $categories = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $categories;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get recent activity summary
     * @return array - Array with recent additions
     */
    public function get_recent_activity() {
        try {
            $conn = $this->db_conn();

            // Recent categories (last 7 days)
            $sql_cat = "SELECT COUNT(*) as count FROM categories
                        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $stmt = $conn->prepare($sql_cat);
            $stmt->execute();
            $stmt->bind_result($recent_categories);
            $stmt->fetch();
            $stmt->close();

            // Recent brands (last 7 days)
            $sql_brand = "SELECT COUNT(*) as count FROM brands
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $stmt = $conn->prepare($sql_brand);
            $stmt->execute();
            $stmt->bind_result($recent_brands);
            $stmt->fetch();
            $stmt->close();

            // Recent products (last 7 days)
            $sql_prod = "SELECT COUNT(*) as count FROM products
                        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $stmt = $conn->prepare($sql_prod);
            $stmt->execute();
            $stmt->bind_result($recent_products);
            $stmt->fetch();
            $stmt->close();

            return [
                'recent_categories' => $recent_categories ?? 0,
                'recent_brands' => $recent_brands ?? 0,
                'recent_products' => $recent_products ?? 0
            ];
        } catch (Exception $e) {
            return [
                'recent_categories' => 0,
                'recent_brands' => 0,
                'recent_products' => 0
            ];
        }
    }

    /**
     * Get super admin dashboard data
     * @return array - Complete dashboard statistics
     */
    public function get_super_admin_dashboard() {
        $activity = $this->get_recent_activity();

        return [
            // Overall stats
            'total_pharmacies' => $this->get_total_pharmacies(),
            'total_customers' => $this->get_total_customers(),
            'total_categories' => $this->get_total_categories(),
            'total_brands' => $this->get_total_brands(),
            'total_products' => $this->get_total_products(),
            'total_users' => $this->get_total_users(),
            'recent_registrations' => $this->get_recent_registrations(),

            // Recent activity
            'recent_categories' => $activity['recent_categories'],
            'recent_brands' => $activity['recent_brands'],
            'recent_products' => $activity['recent_products'],

            // Detailed breakdowns
            'pharmacy_stats' => $this->get_pharmacy_stats(),
            'category_distribution' => $this->get_category_distribution()
        ];
    }
}
?>