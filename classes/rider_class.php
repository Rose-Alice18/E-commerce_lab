<?php
/**
 * Rider Class
 * Handles all rider-related database operations
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Rider extends db_connection {

    /**
     * Add a new rider
     */
    public function add_rider($name, $phone, $email, $license, $vehicle_number, $address, $latitude = null, $longitude = null) {
        $sql = "INSERT INTO riders (rider_name, rider_phone, rider_email, rider_license, vehicle_number, rider_address, rider_latitude, rider_longitude)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("ssssssdd", $name, $phone, $email, $license, $vehicle_number, $address, $latitude, $longitude);
        return $stmt->execute();
    }

    /**
     * Get all riders
     */
    public function get_all_riders() {
        $sql = "SELECT * FROM riders ORDER BY created_at DESC";
        $result = $this->db_conn()->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Get active riders
     */
    public function get_active_riders() {
        $sql = "SELECT * FROM riders WHERE status = 'active' ORDER BY rider_name ASC";
        $result = $this->db_conn()->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Get rider by ID
     */
    public function get_rider_by_id($rider_id) {
        $sql = "SELECT * FROM riders WHERE rider_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $rider_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    /**
     * Update rider information
     */
    public function update_rider($rider_id, $name, $phone, $email, $license, $vehicle_number, $address, $latitude = null, $longitude = null) {
        $sql = "UPDATE riders
                SET rider_name = ?, rider_phone = ?, rider_email = ?, rider_license = ?,
                    vehicle_number = ?, rider_address = ?, rider_latitude = ?, rider_longitude = ?
                WHERE rider_id = ?";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("ssssssddi", $name, $phone, $email, $license, $vehicle_number, $address, $latitude, $longitude, $rider_id);
        return $stmt->execute();
    }

    /**
     * Update rider status
     */
    public function update_rider_status($rider_id, $status) {
        $sql = "UPDATE riders SET status = ? WHERE rider_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("si", $status, $rider_id);
        return $stmt->execute();
    }

    /**
     * Delete rider
     */
    public function delete_rider($rider_id) {
        // Check if rider has pending deliveries
        $check_sql = "SELECT COUNT(*) as count FROM delivery_assignments
                      WHERE rider_id = ? AND status NOT IN ('delivered', 'cancelled')";
        $stmt = $this->db_conn()->prepare($check_sql);
        $stmt->bind_param("i", $rider_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            return false; // Cannot delete rider with pending deliveries
        }

        $sql = "DELETE FROM riders WHERE rider_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $rider_id);
        return $stmt->execute();
    }

    /**
     * Get rider statistics
     */
    public function get_rider_stats($rider_id) {
        $sql = "SELECT
                    r.rider_id,
                    r.rider_name,
                    r.total_deliveries,
                    r.rating,
                    COUNT(CASE WHEN da.status = 'assigned' THEN 1 END) as pending_deliveries,
                    COUNT(CASE WHEN da.status = 'in_transit' THEN 1 END) as in_transit,
                    COUNT(CASE WHEN da.status = 'delivered' THEN 1 END) as completed_deliveries,
                    AVG(da.customer_rating) as avg_rating
                FROM riders r
                LEFT JOIN delivery_assignments da ON r.rider_id = da.rider_id
                WHERE r.rider_id = ?
                GROUP BY r.rider_id";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $rider_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    /**
     * Assign delivery to rider
     */
    public function assign_delivery($order_id, $rider_id, $customer_id, $pharmacy_id, $pickup_address, $delivery_address, $delivery_lat, $delivery_lng, $distance_km, $delivery_fee) {
        $sql = "INSERT INTO delivery_assignments
                (order_id, rider_id, customer_id, pharmacy_id, pickup_address, delivery_address, delivery_latitude, delivery_longitude, distance_km, delivery_fee)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("iiiissdddd", $order_id, $rider_id, $customer_id, $pharmacy_id, $pickup_address, $delivery_address, $delivery_lat, $delivery_lng, $distance_km, $delivery_fee);
        $result = $stmt->execute();

        if ($result) {
            // Update rider status to busy
            $this->update_rider_status($rider_id, 'busy');
        }

        return $result;
    }

    /**
     * Get all delivery assignments
     */
    public function get_all_deliveries() {
        $sql = "SELECT
                    da.*,
                    r.rider_name, r.rider_phone,
                    c.customer_name, c.customer_phone,
                    p.customer_name as pharmacy_name,
                    o.order_status
                FROM delivery_assignments da
                INNER JOIN riders r ON da.rider_id = r.rider_id
                INNER JOIN customer c ON da.customer_id = c.customer_id
                LEFT JOIN customer p ON da.pharmacy_id = p.customer_id
                INNER JOIN orders o ON da.order_id = o.order_id
                ORDER BY da.assigned_at DESC";

        $result = $this->db_conn()->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Get deliveries by rider
     */
    public function get_deliveries_by_rider($rider_id) {
        $sql = "SELECT
                    da.*,
                    c.customer_name, c.customer_contact as customer_phone,
                    p.customer_name as pharmacy_name,
                    o.order_status, o.total_amount as order_total
                FROM delivery_assignments da
                INNER JOIN customer c ON da.customer_id = c.customer_id
                LEFT JOIN customer p ON da.pharmacy_id = p.customer_id
                INNER JOIN orders o ON da.order_id = o.order_id
                WHERE da.rider_id = ?
                ORDER BY da.assigned_at DESC";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $rider_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Update delivery status
     */
    public function update_delivery_status($assignment_id, $status) {
        $timestamp_field = '';

        switch ($status) {
            case 'picked_up':
                $timestamp_field = 'picked_up_at';
                break;
            case 'delivered':
                $timestamp_field = 'delivered_at';
                break;
            case 'cancelled':
                $timestamp_field = 'cancelled_at';
                break;
        }

        if ($timestamp_field) {
            $sql = "UPDATE delivery_assignments SET status = ?, {$timestamp_field} = NOW() WHERE assignment_id = ?";
        } else {
            $sql = "UPDATE delivery_assignments SET status = ? WHERE assignment_id = ?";
        }

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("si", $status, $assignment_id);
        $result = $stmt->execute();

        // If delivered or cancelled, update rider status and total deliveries
        if ($result && ($status == 'delivered' || $status == 'cancelled')) {
            $assignment = $this->get_delivery_by_id($assignment_id);

            // Check if rider has more pending deliveries
            $pending_sql = "SELECT COUNT(*) as count FROM delivery_assignments
                            WHERE rider_id = ? AND status NOT IN ('delivered', 'cancelled')";
            $stmt2 = $this->db_conn()->prepare($pending_sql);
            $stmt2->bind_param("i", $assignment['rider_id']);
            $stmt2->execute();
            $pending_result = $stmt2->get_result();
            $pending = $pending_result->fetch_assoc();

            if ($pending['count'] == 0) {
                // No more pending deliveries, set rider to active
                $this->update_rider_status($assignment['rider_id'], 'active');
            }

            // Update total deliveries count
            if ($status == 'delivered') {
                $update_count_sql = "UPDATE riders SET total_deliveries = total_deliveries + 1 WHERE rider_id = ?";
                $stmt3 = $this->db_conn()->prepare($update_count_sql);
                $stmt3->bind_param("i", $assignment['rider_id']);
                $stmt3->execute();
            }
        }

        return $result;
    }

    /**
     * Get delivery by ID
     */
    public function get_delivery_by_id($assignment_id) {
        $sql = "SELECT * FROM delivery_assignments WHERE assignment_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $assignment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    /**
     * Get delivery statistics
     */
    public function get_delivery_statistics() {
        $sql = "SELECT
                    COUNT(*) as total_deliveries,
                    COUNT(CASE WHEN status = 'assigned' THEN 1 END) as pending,
                    COUNT(CASE WHEN status = 'picked_up' THEN 1 END) as picked_up,
                    COUNT(CASE WHEN status = 'in_transit' THEN 1 END) as in_transit,
                    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
                    SUM(delivery_fee) as total_fees,
                    AVG(customer_rating) as avg_rating
                FROM delivery_assignments";

        $result = $this->db_conn()->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }
}
?>
