-- ============================================================================
-- Cart System Improvements SQL Script
-- This script adds constraints and ensures data integrity for the cart system
-- ============================================================================
-- Database: pharmavault_db
-- Run this script to ensure proper cart functionality with stock validation
-- ============================================================================

USE pharmavault_db;

-- ============================================================================
-- 1. Add Foreign Key Constraints to Cart Table
-- ============================================================================

-- Add foreign key constraint for product_id (if not exists)
-- This ensures that only valid products can be added to cart
ALTER TABLE cart
ADD CONSTRAINT fk_cart_product
FOREIGN KEY (p_id) REFERENCES products(product_id)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- Add foreign key constraint for customer_id (if not exists)
-- This ensures that only valid customers can have items in cart
ALTER TABLE cart
ADD CONSTRAINT fk_cart_customer
FOREIGN KEY (c_id) REFERENCES customer(customer_id)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- ============================================================================
-- 2. Add Check Constraint to Ensure Positive Quantity
-- ============================================================================

-- Ensure quantity is always positive (greater than 0)
ALTER TABLE cart
ADD CONSTRAINT chk_cart_qty_positive
CHECK (qty > 0);

-- ============================================================================
-- 3. Add Unique Constraint to Prevent Duplicate Cart Entries
-- ============================================================================

-- Prevent duplicate cart entries for the same product and customer/IP
-- Note: MySQL doesn't support unique constraints with NULL values well
-- So we'll create a unique index instead
ALTER TABLE cart
ADD UNIQUE KEY unique_cart_entry (p_id, c_id, ip_add);

-- ============================================================================
-- 4. Add Indexes for Better Performance
-- ============================================================================

-- Index on customer_id for faster cart retrieval
CREATE INDEX idx_cart_customer ON cart(c_id);

-- Index on ip_address for guest cart retrieval
CREATE INDEX idx_cart_ip ON cart(ip_add);

-- Index on product_id for faster lookups
CREATE INDEX idx_cart_product ON cart(p_id);

-- ============================================================================
-- 5. Add Timestamp Columns to Track Cart Activity
-- ============================================================================

-- Add created_at and updated_at columns to cart table
ALTER TABLE cart
ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;

-- ============================================================================
-- 6. Create Stored Procedure for Adding/Updating Cart with Stock Validation
-- ============================================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS sp_add_to_cart$$

CREATE PROCEDURE sp_add_to_cart(
    IN p_product_id INT,
    IN p_customer_id INT,
    IN p_ip_address VARCHAR(50),
    IN p_quantity INT,
    IN p_set_quantity BOOLEAN,
    OUT p_success BOOLEAN,
    OUT p_message VARCHAR(255),
    OUT p_final_quantity INT
)
BEGIN
    DECLARE v_stock INT;
    DECLARE v_current_qty INT;
    DECLARE v_new_qty INT;

    -- Initialize output parameters
    SET p_success = FALSE;
    SET p_message = 'Unknown error';
    SET p_final_quantity = 0;

    -- Get product stock
    SELECT product_stock INTO v_stock
    FROM products
    WHERE product_id = p_product_id;

    -- Check if product exists
    IF v_stock IS NULL THEN
        SET p_message = 'Product not found';
        LEAVE sp_add_to_cart;
    END IF;

    -- Check if product is in stock
    IF v_stock <= 0 THEN
        SET p_message = 'Product is out of stock';
        LEAVE sp_add_to_cart;
    END IF;

    -- Get current quantity in cart
    SELECT COALESCE(qty, 0) INTO v_current_qty
    FROM cart
    WHERE p_id = p_product_id
    AND (c_id = p_customer_id OR ip_add = p_ip_address)
    LIMIT 1;

    -- Calculate new quantity
    IF p_set_quantity THEN
        SET v_new_qty = p_quantity;
    ELSE
        SET v_new_qty = v_current_qty + p_quantity;
    END IF;

    -- Cap quantity at available stock
    IF v_new_qty > v_stock THEN
        SET v_new_qty = v_stock;
        SET p_message = CONCAT('Quantity capped at available stock: ', v_stock);
    ELSE
        SET p_message = 'Cart updated successfully';
    END IF;

    -- Ensure minimum quantity
    IF v_new_qty < 1 THEN
        SET v_new_qty = 1;
    END IF;

    -- Update or insert cart entry
    IF v_current_qty > 0 THEN
        -- Update existing cart entry
        UPDATE cart
        SET qty = v_new_qty,
            updated_at = CURRENT_TIMESTAMP
        WHERE p_id = p_product_id
        AND (c_id = p_customer_id OR ip_add = p_ip_address);
    ELSE
        -- Insert new cart entry
        INSERT INTO cart (p_id, c_id, ip_add, qty)
        VALUES (p_product_id, p_customer_id, p_ip_address, v_new_qty);
    END IF;

    SET p_success = TRUE;
    SET p_final_quantity = v_new_qty;

END$$

DELIMITER ;

-- ============================================================================
-- 7. Create View for Cart Items with Stock Validation
-- ============================================================================

DROP VIEW IF EXISTS v_cart_with_stock;

CREATE VIEW v_cart_with_stock AS
SELECT
    c.p_id,
    c.c_id,
    c.ip_add,
    c.qty AS cart_qty,
    c.created_at AS added_at,
    c.updated_at AS modified_at,
    p.product_title,
    p.product_price,
    p.product_image,
    p.product_stock,
    -- Check if cart quantity exceeds available stock
    CASE
        WHEN c.qty > p.product_stock THEN p.product_stock
        ELSE c.qty
    END AS valid_qty,
    -- Calculate item total
    p.product_price * CASE
        WHEN c.qty > p.product_stock THEN p.product_stock
        ELSE c.qty
    END AS item_total,
    -- Stock status
    CASE
        WHEN p.product_stock = 0 THEN 'out_of_stock'
        WHEN c.qty > p.product_stock THEN 'qty_exceeds_stock'
        WHEN p.product_stock <= 10 THEN 'low_stock'
        ELSE 'in_stock'
    END AS stock_status,
    cat.cat_name,
    b.brand_name
FROM cart c
INNER JOIN products p ON c.p_id = p.product_id
LEFT JOIN categories cat ON p.product_cat = cat.cat_id
LEFT JOIN brands b ON p.product_brand = b.brand_id;

-- ============================================================================
-- 8. Create Trigger to Clean Up Invalid Cart Entries
-- ============================================================================

DELIMITER $$

DROP TRIGGER IF EXISTS tr_cart_cleanup_on_product_delete$$

CREATE TRIGGER tr_cart_cleanup_on_product_delete
BEFORE DELETE ON products
FOR EACH ROW
BEGIN
    -- Remove cart entries for products being deleted
    DELETE FROM cart WHERE p_id = OLD.product_id;
END$$

DELIMITER ;

-- ============================================================================
-- 9. Create Function to Get Cart Count
-- ============================================================================

DELIMITER $$

DROP FUNCTION IF EXISTS fn_get_cart_count$$

CREATE FUNCTION fn_get_cart_count(
    p_customer_id INT,
    p_ip_address VARCHAR(50)
)
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_count INT;

    SELECT COALESCE(SUM(qty), 0) INTO v_count
    FROM cart
    WHERE (c_id = p_customer_id OR ip_add = p_ip_address);

    RETURN v_count;
END$$

DELIMITER ;

-- ============================================================================
-- 10. Create Function to Get Cart Total
-- ============================================================================

DELIMITER $$

DROP FUNCTION IF EXISTS fn_get_cart_total$$

CREATE FUNCTION fn_get_cart_total(
    p_customer_id INT,
    p_ip_address VARCHAR(50)
)
RETURNS DECIMAL(10,2)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_total DECIMAL(10,2);

    SELECT COALESCE(SUM(p.product_price * c.qty), 0.00) INTO v_total
    FROM cart c
    INNER JOIN products p ON c.p_id = p.product_id
    WHERE (c.c_id = p_customer_id OR c.ip_add = p_ip_address);

    RETURN v_total;
END$$

DELIMITER ;

-- ============================================================================
-- 11. Clean Up Existing Cart Entries with Invalid Quantities
-- ============================================================================

-- Remove cart entries where quantity exceeds product stock
-- (Optional: You can cap them instead of removing)
DELETE c FROM cart c
INNER JOIN products p ON c.p_id = p.product_id
WHERE c.qty > p.product_stock;

-- Alternative: Cap quantities at available stock instead of deleting
-- UPDATE cart c
-- INNER JOIN products p ON c.p_id = p.product_id
-- SET c.qty = p.product_stock
-- WHERE c.qty > p.product_stock;

-- ============================================================================
-- 12. Create Event to Clean Up Old Guest Carts (Optional)
-- ============================================================================

-- Enable event scheduler (if not already enabled)
SET GLOBAL event_scheduler = ON;

DELIMITER $$

DROP EVENT IF EXISTS evt_cleanup_old_guest_carts$$

CREATE EVENT evt_cleanup_old_guest_carts
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    -- Delete guest cart entries (c_id IS NULL) older than 7 days
    DELETE FROM cart
    WHERE c_id IS NULL
    AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
END$$

DELIMITER ;

-- ============================================================================
-- END OF SCRIPT
-- ============================================================================

-- Verification Queries (Run these to verify the changes)
-- ============================================================================

-- Check cart table structure
-- DESCRIBE cart;

-- Check cart constraints
-- SELECT
--     CONSTRAINT_NAME,
--     CONSTRAINT_TYPE
-- FROM information_schema.TABLE_CONSTRAINTS
-- WHERE TABLE_SCHEMA = 'pharmavault_db'
-- AND TABLE_NAME = 'cart';

-- Check cart with stock validation
-- SELECT * FROM v_cart_with_stock;

-- Test cart count function
-- SELECT fn_get_cart_count(1, '127.0.0.1') AS cart_count;

-- Test cart total function
-- SELECT fn_get_cart_total(1, '127.0.0.1') AS cart_total;
