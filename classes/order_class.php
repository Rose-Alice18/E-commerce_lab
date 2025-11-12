<?php
/**
 * Order Class
 * Handles all order-related database operations
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Order extends db_connection {

    /**
     * Create a new order
     * Returns the newly created order_id on success, false on failure
     */
    public function create_order($customer_id, $invoice_no, $order_status = 'pending') {
        $order_date = date('Y-m-d');

        $sql = "INSERT INTO orders (customer_id, invoice_no, order_date, order_status)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("iiss", $customer_id, $invoice_no, $order_date, $order_status);

        if ($stmt->execute()) {
            return $this->db_conn()->insert_id;
        }
        return false;
    }

    /**
     * Add order details (product items) to an order
     */
    public function add_order_details($order_id, $product_id, $qty) {
        $sql = "INSERT INTO orderdetails (order_id, product_id, qty)
                VALUES (?, ?, ?)";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("iii", $order_id, $product_id, $qty);
        return $stmt->execute();
    }

    /**
     * Record a payment for an order
     */
    public function record_payment($order_id, $customer_id, $amount, $currency = 'GHS') {
        $payment_date = date('Y-m-d');

        $sql = "INSERT INTO payment (order_id, customer_id, amt, currency, payment_date)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("iidss", $order_id, $customer_id, $amount, $currency, $payment_date);
        return $stmt->execute();
    }

    /**
     * Get all orders for a specific customer
     */
    public function get_customer_orders($customer_id) {
        $sql = "SELECT o.*,
                COALESCE(p.amt, 0) as payment_amount,
                COALESCE(p.currency, 'GHS') as currency,
                p.payment_date
                FROM orders o
                LEFT JOIN payment p ON o.order_id = p.order_id
                WHERE o.customer_id = ?
                ORDER BY o.order_date DESC";
        $stmt = $this->db_conn()->prepare($sql);
        if (!$stmt) {
            error_log("MySQL prepare failed: " . $this->db_conn()->error);
            return [];
        }
        $stmt->bind_param("i", $customer_id);
        if (!$stmt->execute()) {
            error_log("MySQL execute failed: " . $stmt->error);
            return [];
        }
        $result = $stmt->get_result();
        if (!$result) {
            error_log("MySQL get_result failed: " . $stmt->error);
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get order details (items) for a specific order
     */
    public function get_order_details($order_id) {
        $sql = "SELECT od.*, p.product_title, p.product_price, p.product_image,
                cat.cat_name, b.brand_name
                FROM orderdetails od
                INNER JOIN products p ON od.product_id = p.product_id
                LEFT JOIN categories cat ON p.product_cat = cat.cat_id
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE od.order_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a single order by ID
     */
    public function get_order($order_id) {
        $sql = "SELECT o.*, p.amt as payment_amount, p.currency, p.payment_date,
                c.customer_name, c.customer_email, c.customer_contact
                FROM orders o
                LEFT JOIN payment p ON o.order_id = p.order_id
                LEFT JOIN customer c ON o.customer_id = c.customer_id
                WHERE o.order_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Update order status
     */
    public function update_order_status($order_id, $status) {
        $sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("si", $status, $order_id);
        return $stmt->execute();
    }

    /**
     * Get all orders (for admin)
     */
    public function get_all_orders() {
        $sql = "SELECT o.*, p.amt as payment_amount, p.currency, p.payment_date,
                c.customer_name, c.customer_email
                FROM orders o
                LEFT JOIN payment p ON o.order_id = p.order_id
                LEFT JOIN customer c ON o.customer_id = c.customer_id
                ORDER BY o.order_date DESC";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Generate a unique invoice number
     */
    public function generate_invoice_number() {
        // Format: INV-YYYYMMDD-XXXXX (e.g., INV-20251110-00001)
        $date_part = date('Ymd');

        // Get the last invoice number for today
        $sql = "SELECT MAX(invoice_no) as max_invoice FROM orders WHERE DATE(order_date) = CURDATE()";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['max_invoice']) {
            $next_number = $row['max_invoice'] + 1;
        } else {
            // Start from a base number (date + 00001)
            $next_number = intval($date_part . '00001');
        }

        return $next_number;
    }

    /**
     * Get order total amount
     */
    public function get_order_total($order_id) {
        $sql = "SELECT SUM(p.product_price * od.qty) as total
                FROM orderdetails od
                INNER JOIN products p ON od.product_id = p.product_id
                WHERE od.order_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
}
?>
