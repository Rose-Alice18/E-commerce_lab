<?php
/**
 * Suggestion Class
 * Handles category and brand suggestions from pharmacy admins
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Suggestion extends db_connection {

    /**
     * Submit a category suggestion
     */
    public function suggest_category($suggested_name, $suggested_by, $pharmacy_name, $reason = '') {
        $sql = "INSERT INTO category_suggestions (suggested_name, suggested_by, pharmacy_name, reason, status)
                VALUES (?, ?, ?, ?, 'pending')";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("siss", $suggested_name, $suggested_by, $pharmacy_name, $reason);
        return $stmt->execute();
    }

    /**
     * Submit a brand suggestion
     */
    public function suggest_brand($suggested_name, $suggested_by, $pharmacy_name, $reason = '') {
        $sql = "INSERT INTO brand_suggestions (suggested_name, suggested_by, pharmacy_name, reason, status)
                VALUES (?, ?, ?, ?, 'pending')";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("siss", $suggested_name, $suggested_by, $pharmacy_name, $reason);
        return $stmt->execute();
    }

    /**
     * Get all pending category suggestions (for super admin)
     */
    public function get_pending_category_suggestions() {
        $sql = "SELECT cs.*, c.customer_name, c.customer_email
                FROM category_suggestions cs
                INNER JOIN customer c ON cs.suggested_by = c.customer_id
                WHERE cs.status = 'pending'
                ORDER BY cs.created_at DESC";
        $result = $this->db_conn()->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all pending brand suggestions (for super admin)
     */
    public function get_pending_brand_suggestions() {
        $sql = "SELECT bs.*, c.customer_name, c.customer_email
                FROM brand_suggestions bs
                INNER JOIN customer c ON bs.suggested_by = c.customer_id
                WHERE bs.status = 'pending'
                ORDER BY bs.created_at DESC";
        $result = $this->db_conn()->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all category suggestions (with filters)
     */
    public function get_all_category_suggestions($status = null) {
        if ($status) {
            $sql = "SELECT cs.*, c.customer_name, c.customer_email,
                    r.customer_name as reviewer_name
                    FROM category_suggestions cs
                    INNER JOIN customer c ON cs.suggested_by = c.customer_id
                    LEFT JOIN customer r ON cs.reviewed_by = r.customer_id
                    WHERE cs.status = ?
                    ORDER BY cs.created_at DESC";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("s", $status);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql = "SELECT cs.*, c.customer_name, c.customer_email,
                    r.customer_name as reviewer_name
                    FROM category_suggestions cs
                    INNER JOIN customer c ON cs.suggested_by = c.customer_id
                    LEFT JOIN customer r ON cs.reviewed_by = r.customer_id
                    ORDER BY cs.created_at DESC";
            $result = $this->db_conn()->query($sql);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all brand suggestions (with filters)
     */
    public function get_all_brand_suggestions($status = null) {
        if ($status) {
            $sql = "SELECT bs.*, c.customer_name, c.customer_email,
                    r.customer_name as reviewer_name
                    FROM brand_suggestions bs
                    INNER JOIN customer c ON bs.suggested_by = c.customer_id
                    LEFT JOIN customer r ON bs.reviewed_by = r.customer_id
                    WHERE bs.status = ?
                    ORDER BY bs.created_at DESC";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("s", $status);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql = "SELECT bs.*, c.customer_name, c.customer_email,
                    r.customer_name as reviewer_name
                    FROM brand_suggestions bs
                    INNER JOIN customer c ON bs.suggested_by = c.customer_id
                    LEFT JOIN customer r ON bs.reviewed_by = r.customer_id
                    ORDER BY bs.created_at DESC";
            $result = $this->db_conn()->query($sql);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get pharmacy's own suggestions
     */
    public function get_pharmacy_category_suggestions($pharmacy_id) {
        $sql = "SELECT * FROM category_suggestions
                WHERE suggested_by = ?
                ORDER BY created_at DESC";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $pharmacy_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get pharmacy's own brand suggestions
     */
    public function get_pharmacy_brand_suggestions($pharmacy_id) {
        $sql = "SELECT * FROM brand_suggestions
                WHERE suggested_by = ?
                ORDER BY created_at DESC";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $pharmacy_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Approve category suggestion and create the category
     */
    public function approve_category_suggestion($suggestion_id, $reviewed_by, $review_comment = '') {
        // Start transaction
        $this->db_conn()->begin_transaction();

        try {
            // Get suggestion details
            $sql = "SELECT * FROM category_suggestions WHERE suggestion_id = ?";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("i", $suggestion_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $suggestion = $result->fetch_assoc();

            if (!$suggestion) {
                throw new Exception("Suggestion not found");
            }

            // Create the category
            $insert_sql = "INSERT INTO categories (cat_name) VALUES (?)";
            $insert_stmt = $this->db_conn()->prepare($insert_sql);
            $insert_stmt->bind_param("s", $suggestion['suggested_name']);

            if (!$insert_stmt->execute()) {
                throw new Exception("Failed to create category");
            }

            // Update suggestion status
            $update_sql = "UPDATE category_suggestions
                          SET status = 'approved',
                              reviewed_by = ?,
                              reviewed_at = NOW(),
                              review_comment = ?
                          WHERE suggestion_id = ?";
            $update_stmt = $this->db_conn()->prepare($update_sql);
            $update_stmt->bind_param("isi", $reviewed_by, $review_comment, $suggestion_id);

            if (!$update_stmt->execute()) {
                throw new Exception("Failed to update suggestion status");
            }

            $this->db_conn()->commit();
            return true;

        } catch (Exception $e) {
            $this->db_conn()->rollback();
            error_log("Approve category suggestion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Approve brand suggestion and create the brand
     */
    public function approve_brand_suggestion($suggestion_id, $reviewed_by, $review_comment = '') {
        // Start transaction
        $this->db_conn()->begin_transaction();

        try {
            // Get suggestion details
            $sql = "SELECT * FROM brand_suggestions WHERE suggestion_id = ?";
            $stmt = $this->db_conn()->prepare($sql);
            $stmt->bind_param("i", $suggestion_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $suggestion = $result->fetch_assoc();

            if (!$suggestion) {
                throw new Exception("Suggestion not found");
            }

            // Create the brand
            $insert_sql = "INSERT INTO brands (brand_name) VALUES (?)";
            $insert_stmt = $this->db_conn()->prepare($insert_sql);
            $insert_stmt->bind_param("s", $suggestion['suggested_name']);

            if (!$insert_stmt->execute()) {
                throw new Exception("Failed to create brand");
            }

            // Update suggestion status
            $update_sql = "UPDATE brand_suggestions
                          SET status = 'approved',
                              reviewed_by = ?,
                              reviewed_at = NOW(),
                              review_comment = ?
                          WHERE suggestion_id = ?";
            $update_stmt = $this->db_conn()->prepare($update_sql);
            $update_stmt->bind_param("isi", $reviewed_by, $review_comment, $suggestion_id);

            if (!$update_stmt->execute()) {
                throw new Exception("Failed to update suggestion status");
            }

            $this->db_conn()->commit();
            return true;

        } catch (Exception $e) {
            $this->db_conn()->rollback();
            error_log("Approve brand suggestion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject a category suggestion
     */
    public function reject_category_suggestion($suggestion_id, $reviewed_by, $review_comment) {
        $sql = "UPDATE category_suggestions
                SET status = 'rejected',
                    reviewed_by = ?,
                    reviewed_at = NOW(),
                    review_comment = ?
                WHERE suggestion_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("isi", $reviewed_by, $review_comment, $suggestion_id);
        return $stmt->execute();
    }

    /**
     * Reject a brand suggestion
     */
    public function reject_brand_suggestion($suggestion_id, $reviewed_by, $review_comment) {
        $sql = "UPDATE brand_suggestions
                SET status = 'rejected',
                    reviewed_by = ?,
                    reviewed_at = NOW(),
                    review_comment = ?
                WHERE suggestion_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("isi", $reviewed_by, $review_comment, $suggestion_id);
        return $stmt->execute();
    }

    /**
     * Get count of pending suggestions (for notifications)
     */
    public function get_pending_suggestions_count() {
        $sql = "SELECT
                (SELECT COUNT(*) FROM category_suggestions WHERE status = 'pending') as category_count,
                (SELECT COUNT(*) FROM brand_suggestions WHERE status = 'pending') as brand_count";
        $result = $this->db_conn()->query($sql);
        return $result->fetch_assoc();
    }
}
?>
