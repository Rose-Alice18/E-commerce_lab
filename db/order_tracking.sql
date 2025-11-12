-- ============================================
-- Order Status Tracking System
-- PharmaVault - Manual Status Updates
-- ============================================

-- Update orders table to have proper status tracking
ALTER TABLE orders
ADD COLUMN order_status_updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
ADD COLUMN order_notes TEXT NULL,
ADD COLUMN updated_by INT NULL COMMENT 'User ID who last updated the status';

-- Create order status history table to track all status changes
CREATE TABLE IF NOT EXISTS order_status_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50) NOT NULL,
    notes TEXT NULL,
    updated_by INT NOT NULL COMMENT 'User ID who made the change',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES customer(customer_id),
    INDEX idx_order_status_history_order (order_id),
    INDEX idx_order_status_history_date (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Standard order status values
-- Pending: Order placed, waiting for pharmacy to process
-- Processing: Pharmacy is preparing the order
-- Out for Delivery: Order has left the pharmacy
-- Delivered: Order has been delivered to customer
-- Cancelled: Order was cancelled

-- Add index on order_status for faster queries
CREATE INDEX idx_orders_status ON orders(order_status);
CREATE INDEX idx_orders_customer ON orders(customer_id);

-- Sample status update trigger (optional - logs all status changes automatically)
DELIMITER $$

CREATE TRIGGER after_order_status_update
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF OLD.order_status != NEW.order_status THEN
        INSERT INTO order_status_history (order_id, old_status, new_status, updated_by, notes)
        VALUES (NEW.order_id, OLD.order_status, NEW.order_status, NEW.updated_by, NEW.order_notes);
    END IF;
END$$

DELIMITER ;

-- View to get order details with latest status
CREATE OR REPLACE VIEW order_tracking_view AS
SELECT
    o.order_id,
    o.customer_id,
    c.customer_name,
    c.customer_email,
    c.customer_contact,
    o.invoice_no,
    o.order_date,
    o.order_status,
    o.order_status_updated_at,
    o.order_notes,
    p.amt as total_amount,
    (SELECT COUNT(*) FROM orderdetails WHERE order_id = o.order_id) as total_items,
    (SELECT GROUP_CONCAT(pr.product_name SEPARATOR ', ')
     FROM orderdetails od
     JOIN products pr ON od.product_id = pr.product_id
     WHERE od.order_id = o.order_id) as product_names
FROM orders o
LEFT JOIN customer c ON o.customer_id = c.customer_id
LEFT JOIN payment p ON o.order_id = p.order_id
ORDER BY o.order_date DESC, o.order_id DESC;

-- ============================================
-- USAGE NOTES:
-- ============================================
-- 1. Run this SQL file in your pharmavault_db database
-- 2. Existing orders will keep their current status
-- 3. New columns allow tracking who updated the status and when
-- 4. History table keeps a complete audit trail
-- 5. Trigger automatically logs all status changes
-- ============================================
