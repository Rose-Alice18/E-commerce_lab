<?php
/**
 * Prescription Class
 * Handles all prescription-related database operations
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Prescription extends db_connection {

    /**
     * Generate unique prescription number
     * Format: RX-YYYYMMDD-NNNNN
     */
    public function generate_prescription_number() {
        // Generate a simple unique prescription number
        // Format: RX-YYYYMMDD-NNNNN
        $date_part = date('Ymd');

        // Get count of today's prescriptions to generate sequence
        $sql = "SELECT COUNT(*) as count FROM prescriptions WHERE DATE(uploaded_at) = CURDATE()";
        $result = $this->db_conn()->query($sql);

        if ($result) {
            $row = $result->fetch_assoc();
            $sequence = str_pad(($row['count'] + 1), 5, '0', STR_PAD_LEFT);
        } else {
            $sequence = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        }

        return 'RX-' . $date_part . '-' . $sequence;
    }

    /**
     * Upload a new prescription
     */
    public function upload_prescription($data) {
        $sql = "INSERT INTO prescriptions (
                    customer_id,
                    prescription_number,
                    doctor_name,
                    doctor_license,
                    issue_date,
                    expiry_date,
                    prescription_image,
                    prescription_notes,
                    status,
                    allow_pharmacy_access
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";

        $allow_pharmacy_access = isset($data['allow_pharmacy_access']) ? $data['allow_pharmacy_access'] : 1;

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param(
            "isssssssi",
            $data['customer_id'],
            $data['prescription_number'],
            $data['doctor_name'],
            $data['doctor_license'],
            $data['issue_date'],
            $data['expiry_date'],
            $data['prescription_image'],
            $data['prescription_notes'],
            $allow_pharmacy_access
        );

        if ($stmt->execute()) {
            $prescription_id = $stmt->insert_id;

            // Log the upload
            $this->log_prescription_action($prescription_id, 'uploaded', $data['customer_id'], 'Prescription uploaded by customer');

            return $prescription_id;
        }

        return false;
    }

    /**
     * Add prescription item (medication)
     */
    public function add_prescription_item($prescription_id, $item_data) {
        $sql = "INSERT INTO prescription_items (
                    prescription_id,
                    medication_name,
                    dosage,
                    quantity,
                    frequency,
                    duration,
                    product_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param(
            "ississi",
            $prescription_id,
            $item_data['medication_name'],
            $item_data['dosage'],
            $item_data['quantity'],
            $item_data['frequency'],
            $item_data['duration'],
            $item_data['product_id']
        );

        return $stmt->execute();
    }

    /**
     * Get prescription by ID
     */
    public function get_prescription($prescription_id) {
        $sql = "SELECT p.*,
                       c.customer_name,
                       c.customer_email,
                       c.customer_contact,
                       v.customer_name as verified_by_name
                FROM prescriptions p
                INNER JOIN customer c ON p.customer_id = c.customer_id
                LEFT JOIN customer v ON p.verified_by = v.customer_id
                WHERE p.prescription_id = ?";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $prescription_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get prescription items
     */
    public function get_prescription_items($prescription_id) {
        $sql = "SELECT pi.*,
                       p.product_title,
                       p.product_price,
                       p.product_stock
                FROM prescription_items pi
                LEFT JOIN products p ON pi.product_id = p.product_id
                WHERE pi.prescription_id = ?
                ORDER BY pi.prescription_item_id";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $prescription_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get customer's prescriptions
     */
    public function get_customer_prescriptions($customer_id, $status = null) {
        if ($status) {
            $sql = "SELECT p.*,
                           COUNT(pi.prescription_item_id) as item_count
                    FROM prescriptions p
                    LEFT JOIN prescription_items pi ON p.prescription_id = pi.prescription_id
                    WHERE p.customer_id = ? AND p.status = ?
                    GROUP BY p.prescription_id
                    ORDER BY p.uploaded_at DESC";

            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("is", $customer_id, $status);
        } else {
            $sql = "SELECT p.*,
                           COUNT(pi.prescription_item_id) as item_count
                    FROM prescriptions p
                    LEFT JOIN prescription_items pi ON p.prescription_id = pi.prescription_id
                    WHERE p.customer_id = ?
                    GROUP BY p.prescription_id
                    ORDER BY p.uploaded_at DESC";

            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("i", $customer_id);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all pending prescriptions (for pharmacies and admin)
     */
    public function get_pending_prescriptions() {
        $sql = "SELECT p.*,
                       c.customer_name,
                       c.customer_email,
                       COUNT(pi.prescription_item_id) as item_count
                FROM prescriptions p
                INNER JOIN customer c ON p.customer_id = c.customer_id
                LEFT JOIN prescription_items pi ON p.prescription_id = pi.prescription_id
                WHERE p.status = 'pending'
                GROUP BY p.prescription_id
                ORDER BY p.uploaded_at DESC";
        $result = $this->db_conn()->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Get all prescriptions (for admin overview)
     */
    public function get_all_prescriptions($status = null, $limit = null) {
        $sql = "SELECT p.*,
                       c.customer_name,
                       c.customer_email,
                       COUNT(pi.prescription_item_id) as item_count
                FROM prescriptions p
                INNER JOIN customer c ON p.customer_id = c.customer_id
                LEFT JOIN prescription_items pi ON p.prescription_id = pi.prescription_id";

        if ($status) {
            $sql .= " WHERE p.status = ?";
        }

        $sql .= " GROUP BY p.prescription_id ORDER BY p.uploaded_at DESC";

        if ($limit) {
            $sql .= " LIMIT ?";
        }

        if ($status || $limit) {
            $stmt = $this->db_conn()->prepare($sql);
            if ($status && $limit) {
                $stmt->bind_param("si", $status, $limit);
            } elseif ($status) {
                $stmt->bind_param("s", $status);
            } else {
                $stmt->bind_param("i", $limit);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $result = $this->db_conn()->query($sql);
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }
    }

    /**
     * Verify prescription (by pharmacy or admin)
     */
    public function verify_prescription($prescription_id, $verified_by, $notes = null) {
        $sql = "UPDATE prescriptions
                SET status = 'verified',
                    verified_by = ?,
                    verified_at = NOW(),
                    updated_at = NOW()
                WHERE prescription_id = ?";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("ii", $verified_by, $prescription_id);

        if ($stmt->execute()) {
            // Log the verification
            $this->log_prescription_action($prescription_id, 'verified', $verified_by, $notes ?? 'Prescription verified');
            return true;
        }

        return false;
    }

    /**
     * Reject prescription (by pharmacy or admin)
     */
    public function reject_prescription($prescription_id, $rejected_by, $reason) {
        $sql = "UPDATE prescriptions
                SET status = 'rejected',
                    verified_by = ?,
                    verified_at = NOW(),
                    rejection_reason = ?,
                    updated_at = NOW()
                WHERE prescription_id = ?";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("isi", $rejected_by, $reason, $prescription_id);

        if ($stmt->execute()) {
            // Log the rejection
            $this->log_prescription_action($prescription_id, 'rejected', $rejected_by, $reason);
            return true;
        }

        return false;
    }

    /**
     * Link prescription to order
     */
    public function link_prescription_to_order($order_id, $prescription_id) {
        $sql = "INSERT INTO order_prescriptions (order_id, prescription_id)
                VALUES (?, ?)";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("ii", $order_id, $prescription_id);
        return $stmt->execute();
    }

    /**
     * Get prescription by order
     */
    public function get_order_prescription($order_id) {
        $sql = "SELECT p.*
                FROM prescriptions p
                INNER JOIN order_prescriptions op ON p.prescription_id = op.prescription_id
                WHERE op.order_id = ?";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Mark prescription item as fulfilled
     */
    public function mark_item_fulfilled($prescription_item_id) {
        $sql = "UPDATE prescription_items
                SET fulfilled = 1
                WHERE prescription_item_id = ?";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $prescription_item_id);
        return $stmt->execute();
    }

    /**
     * Check if product requires prescription
     */
    public function product_requires_prescription($product_id) {
        $sql = "SELECT prescription_required FROM products WHERE product_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? (bool)$row['prescription_required'] : false;
    }

    /**
     * Get all prescription-required products
     */
    public function get_prescription_required_products() {
        $sql = "SELECT p.*,
                       c.cat_name,
                       b.brand_name
                FROM products p
                INNER JOIN categories c ON p.product_cat = c.cat_id
                INNER JOIN brands b ON p.product_brand = b.brand_id
                WHERE p.prescription_required = 1
                ORDER BY p.product_title";
        $result = $this->db_conn()->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Update product prescription requirement
     */
    public function set_product_prescription_requirement($product_id, $required = true) {
        $sql = "UPDATE products
                SET prescription_required = ?
                WHERE product_id = ?";

        $stmt = $this->db_conn()->prepare($sql);
        $required_val = $required ? 1 : 0;
        $stmt->bind_param("ii", $required_val, $product_id);
        return $stmt->execute();
    }

    /**
     * Log prescription action (audit trail)
     */
    public function log_prescription_action($prescription_id, $action, $performed_by, $notes = null) {
        $sql = "INSERT INTO prescription_verification_log
                (prescription_id, action, performed_by, notes)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("isis", $prescription_id, $action, $performed_by, $notes);
        return $stmt->execute();
    }

    /**
     * Get prescription verification history
     */
    public function get_prescription_history($prescription_id) {
        $sql = "SELECT pvl.*,
                       c.customer_name as performed_by_name,
                       c.user_role
                FROM prescription_verification_log pvl
                INNER JOIN customer c ON pvl.performed_by = c.customer_id
                WHERE pvl.prescription_id = ?
                ORDER BY pvl.created_at DESC";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $prescription_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Expire old prescriptions (to be run via cron)
     */
    public function expire_old_prescriptions() {
        $sql = "UPDATE prescriptions
                SET status = 'expired'
                WHERE status = 'verified'
                AND expiry_date IS NOT NULL
                AND expiry_date < CURDATE()";
        return $this->db_conn()->query($sql);
    }

    /**
     * Get prescription statistics for admin
     */
    public function get_prescription_stats() {
        $sql = "SELECT
                    COUNT(*) as total_prescriptions,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified_count,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
                    SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired_count
                FROM prescriptions";

        $result = $this->db_conn()->query($sql);
        return $result ? $result->fetch_assoc() : [];
    }

    /**
     * Search prescriptions by prescription number or customer name
     */
    public function search_prescriptions($search_term) {
        $sql = "SELECT p.*,
                       c.customer_name,
                       c.customer_email,
                       COUNT(pi.prescription_item_id) as item_count
                FROM prescriptions p
                INNER JOIN customer c ON p.customer_id = c.customer_id
                LEFT JOIN prescription_items pi ON p.prescription_id = pi.prescription_id
                WHERE p.prescription_number LIKE ?
                   OR c.customer_name LIKE ?
                   OR p.doctor_name LIKE ?
                GROUP BY p.prescription_id
                ORDER BY p.uploaded_at DESC";

        $search_pattern = "%$search_term%";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("sss", $search_pattern, $search_pattern, $search_pattern);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Delete prescription (admin only)
     */
    public function delete_prescription($prescription_id, $deleted_by) {
        // Log deletion first
        $this->log_prescription_action($prescription_id, 'deleted', $deleted_by, 'Prescription deleted by admin');

        $sql = "DELETE FROM prescriptions WHERE prescription_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $prescription_id);
        return $stmt->execute();
    }
}
?>
