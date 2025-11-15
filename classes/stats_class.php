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
            'category_distribution' => $this->get_category_distribution(),

            // Platform revenue analytics
            'total_orders' => $this->get_platform_total_orders(),
            'total_revenue' => $this->get_platform_total_revenue(),
            'monthly_revenue' => $this->get_platform_monthly_revenue(),
            'revenue_by_month_chart' => $this->get_revenue_by_month_chart(6)
        ];
    }

    /**
     * Get pharmacy-specific dashboard analytics
     * @param int $pharmacy_id - Pharmacy ID
     * @return array - Pharmacy dashboard statistics
     */
    public function get_pharmacy_dashboard($pharmacy_id) {
        return [
            // Product stats
            'total_products' => $this->get_pharmacy_product_count($pharmacy_id),
            'low_stock_count' => $this->get_pharmacy_low_stock_count($pharmacy_id),
            'out_of_stock_count' => $this->get_pharmacy_out_of_stock_count($pharmacy_id),
            'total_stock_value' => $this->get_pharmacy_stock_value($pharmacy_id),

            // Order stats
            'total_orders' => $this->get_pharmacy_total_orders($pharmacy_id),
            'pending_orders' => $this->get_pharmacy_pending_orders($pharmacy_id),
            'completed_orders' => $this->get_pharmacy_completed_orders($pharmacy_id),
            'cancelled_orders' => $this->get_pharmacy_cancelled_orders($pharmacy_id),

            // Revenue stats
            'total_revenue' => $this->get_pharmacy_total_revenue($pharmacy_id),
            'monthly_revenue' => $this->get_pharmacy_monthly_revenue($pharmacy_id),
            'today_revenue' => $this->get_pharmacy_today_revenue($pharmacy_id),
            'weekly_revenue' => $this->get_pharmacy_weekly_revenue($pharmacy_id),

            // Charts data
            'sales_by_month' => $this->get_pharmacy_sales_by_month($pharmacy_id, 6),
            'top_products' => $this->get_pharmacy_top_products($pharmacy_id, 5),
            'recent_orders' => $this->get_pharmacy_recent_orders($pharmacy_id, 5),
            'low_stock_items' => $this->get_pharmacy_low_stock_items($pharmacy_id, 5),
            'order_status_distribution' => $this->get_pharmacy_order_status_distribution($pharmacy_id)
        ];
    }

    // ==================== PHARMACY ANALYTICS METHODS ====================

    /**
     * Get total product count for pharmacy
     */
    public function get_pharmacy_product_count($pharmacy_id) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT COUNT(*) as total FROM products WHERE pharmacy_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pharmacy_id);
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
     * Get low stock count (stock <= 10)
     */
    public function get_pharmacy_low_stock_count($pharmacy_id) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT COUNT(*) as total FROM products
                    WHERE pharmacy_id = ? AND product_stock > 0 AND product_stock <= 10";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pharmacy_id);
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
     * Get out of stock count
     */
    public function get_pharmacy_out_of_stock_count($pharmacy_id) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT COUNT(*) as total FROM products
                    WHERE pharmacy_id = ? AND product_stock = 0";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pharmacy_id);
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
     * Get total stock value for pharmacy
     */
    public function get_pharmacy_stock_value($pharmacy_id) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT SUM(product_stock * product_price) as total_value
                    FROM products WHERE pharmacy_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pharmacy_id);
            $stmt->execute();
            $stmt->bind_result($total_value);
            $stmt->fetch();
            $stmt->close();
            return $total_value ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get total orders for pharmacy
     */
    public function get_pharmacy_total_orders($pharmacy_id) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT COUNT(DISTINCT o.order_id) as total
                    FROM orders o
                    INNER JOIN orderdetails od ON o.order_id = od.order_id
                    INNER JOIN products p ON od.product_id = p.product_id
                    WHERE p.pharmacy_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pharmacy_id);
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
     * Get pending orders for pharmacy
     */
    public function get_pharmacy_pending_orders($pharmacy_id) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT COUNT(DISTINCT o.order_id) as total
                    FROM orders o
                    INNER JOIN orderdetails od ON o.order_id = od.order_id
                    INNER JOIN products p ON od.product_id = p.product_id
                    WHERE p.pharmacy_id = ? AND o.order_status = 'pending'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pharmacy_id);
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
     * Get completed orders for pharmacy
     */
    public function get_pharmacy_completed_orders($pharmacy_id) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT COUNT(DISTINCT o.order_id) as total
                    FROM orders o
                    INNER JOIN orderdetails od ON o.order_id = od.order_id
                    INNER JOIN products p ON od.product_id = p.product_id
                    WHERE p.pharmacy_id = ? AND o.order_status = 'completed'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pharmacy_id);
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
     * Get cancelled orders for pharmacy
     */
    public function get_pharmacy_cancelled_orders($pharmacy_id) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT COUNT(DISTINCT o.order_id) as total
                    FROM orders o
                    INNER JOIN orderdetails od ON o.order_id = od.order_id
                    INNER JOIN products p ON od.product_id = p.product_id
                    WHERE p.pharmacy_id = ? AND o.order_status = 'cancelled'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pharmacy_id);
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
     * Get total revenue for pharmacy
     */
    public function get_pharmacy_total_revenue($pharmacy_id) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT SUM(p.product_price * od.qty) as total_revenue
                    FROM orders o
                    INNER JOIN orderdetails od ON o.order_id = od.order_id
                    INNER JOIN products p ON od.product_id = p.product_id
                    WHERE p.pharmacy_id = ? AND o.order_status != 'cancelled'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pharmacy_id);
            $stmt->execute();
            $stmt->bind_result($total_revenue);
            $stmt->fetch();
            $stmt->close();
            return $total_revenue ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get monthly revenue for pharmacy (current month)
     */
    public function get_pharmacy_monthly_revenue($pharmacy_id) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT SUM(p.product_price * od.qty) as monthly_revenue
                    FROM orders o
                    INNER JOIN orderdetails od ON o.order_id = od.order_id
                    INNER JOIN products p ON od.product_id = p.product_id
                    WHERE p.pharmacy_id = ?
                    AND MONTH(o.order_date) = MONTH(CURDATE())
                    AND YEAR(o.order_date) = YEAR(CURDATE())
                    AND o.order_status != 'cancelled'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pharmacy_id);
            $stmt->execute();
            $stmt->bind_result($monthly_revenue);
            $stmt->fetch();
            $stmt->close();
            return $monthly_revenue ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get today's revenue for pharmacy
     */
    public function get_pharmacy_today_revenue($pharmacy_id) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT SUM(p.product_price * od.qty) as today_revenue
                    FROM orders o
                    INNER JOIN orderdetails od ON o.order_id = od.order_id
                    INNER JOIN products p ON od.product_id = p.product_id
                    WHERE p.pharmacy_id = ?
                    AND DATE(o.order_date) = CURDATE()
                    AND o.order_status != 'cancelled'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pharmacy_id);
            $stmt->execute();
            $stmt->bind_result($today_revenue);
            $stmt->fetch();
            $stmt->close();
            return $today_revenue ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get weekly revenue for pharmacy
     */
    public function get_pharmacy_weekly_revenue($pharmacy_id) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT SUM(p.product_price * od.qty) as weekly_revenue
                    FROM orders o
                    INNER JOIN orderdetails od ON o.order_id = od.order_id
                    INNER JOIN products p ON od.product_id = p.product_id
                    WHERE p.pharmacy_id = ?
                    AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    AND o.order_status != 'cancelled'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pharmacy_id);
            $stmt->execute();
            $stmt->bind_result($weekly_revenue);
            $stmt->fetch();
            $stmt->close();
            return $weekly_revenue ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get sales by month for pharmacy (for charts)
     */
    public function get_pharmacy_sales_by_month($pharmacy_id, $months = 6) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT
                        DATE_FORMAT(o.order_date, '%Y-%m') as month,
                        DATE_FORMAT(o.order_date, '%b %Y') as month_label,
                        SUM(p.product_price * od.qty) as revenue,
                        COUNT(DISTINCT o.order_id) as order_count
                    FROM orders o
                    INNER JOIN orderdetails od ON o.order_id = od.order_id
                    INNER JOIN products p ON od.product_id = p.product_id
                    WHERE p.pharmacy_id = ?
                    AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                    AND o.order_status != 'cancelled'
                    GROUP BY DATE_FORMAT(o.order_date, '%Y-%m')
                    ORDER BY month ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $pharmacy_id, $months);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get top selling products for pharmacy
     */
    public function get_pharmacy_top_products($pharmacy_id, $limit = 5) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT
                        p.product_id,
                        p.product_title,
                        p.product_image,
                        SUM(od.qty) as total_sold,
                        SUM(p.product_price * od.qty) as total_revenue
                    FROM orderdetails od
                    INNER JOIN products p ON od.product_id = p.product_id
                    INNER JOIN orders o ON od.order_id = o.order_id
                    WHERE p.pharmacy_id = ? AND o.order_status != 'cancelled'
                    GROUP BY p.product_id
                    ORDER BY total_sold DESC
                    LIMIT ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $pharmacy_id, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get recent orders for pharmacy
     */
    public function get_pharmacy_recent_orders($pharmacy_id, $limit = 5) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT DISTINCT
                        o.order_id,
                        o.invoice_no,
                        o.order_date,
                        o.order_status,
                        c.customer_name,
                        SUM(p.product_price * od.qty) as order_total
                    FROM orders o
                    INNER JOIN orderdetails od ON o.order_id = od.order_id
                    INNER JOIN products p ON od.product_id = p.product_id
                    INNER JOIN customer c ON o.customer_id = c.customer_id
                    WHERE p.pharmacy_id = ?
                    GROUP BY o.order_id
                    ORDER BY o.order_date DESC
                    LIMIT ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $pharmacy_id, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get low stock items for pharmacy
     */
    public function get_pharmacy_low_stock_items($pharmacy_id, $limit = 5) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT
                        product_id,
                        product_title,
                        product_image,
                        product_stock,
                        product_price
                    FROM products
                    WHERE pharmacy_id = ? AND product_stock > 0 AND product_stock <= 10
                    ORDER BY product_stock ASC
                    LIMIT ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $pharmacy_id, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get order status distribution for pharmacy (for pie chart)
     */
    public function get_pharmacy_order_status_distribution($pharmacy_id) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT
                        o.order_status,
                        COUNT(DISTINCT o.order_id) as count
                    FROM orders o
                    INNER JOIN orderdetails od ON o.order_id = od.order_id
                    INNER JOIN products p ON od.product_id = p.product_id
                    WHERE p.pharmacy_id = ?
                    GROUP BY o.order_status";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pharmacy_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }

    // ==================== PLATFORM/SUPERADMIN ANALYTICS METHODS ====================

    /**
     * Get total orders on platform
     */
    public function get_platform_total_orders() {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT COUNT(*) as total FROM orders";
            $stmt = $conn->prepare($sql);
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
     * Get total platform revenue
     */
    public function get_platform_total_revenue() {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT SUM(amt) as total_revenue FROM payment";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $stmt->bind_result($total_revenue);
            $stmt->fetch();
            $stmt->close();
            return $total_revenue ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get monthly platform revenue
     */
    public function get_platform_monthly_revenue() {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT SUM(amt) as monthly_revenue
                    FROM payment
                    WHERE MONTH(payment_date) = MONTH(CURDATE())
                    AND YEAR(payment_date) = YEAR(CURDATE())";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $stmt->bind_result($monthly_revenue);
            $stmt->fetch();
            $stmt->close();
            return $monthly_revenue ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get revenue by month for chart (last N months)
     */
    public function get_revenue_by_month_chart($months = 6) {
        try {
            $conn = $this->db_conn();
            $sql = "SELECT
                        DATE_FORMAT(payment_date, '%Y-%m') as month,
                        DATE_FORMAT(payment_date, '%b %Y') as month_label,
                        SUM(amt) as revenue
                    FROM payment
                    WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                    GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
                    ORDER BY month ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $months);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }
}
?>