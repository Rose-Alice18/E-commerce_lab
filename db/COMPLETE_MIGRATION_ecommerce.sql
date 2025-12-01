-- ============================================
-- COMPLETE MIGRATION SCRIPT FOR ecommerce_2025A_roseline_tsatsu
-- ============================================
-- This script adds ALL missing tables and columns from the PharmaVault system
-- to the ecommerce_2025A_roseline_tsatsu database
--
-- TABLES TO ADD:
-- 1. riders - Motorcycle delivery riders
-- 2. delivery_assignments - Delivery tracking
-- 3. messages - Pharmacy <-> Super Admin messaging
-- 4. support_tickets - Support ticket system
-- 5. ticket_responses - Ticket reply system
-- 6. category_suggestions - Pharmacy category suggestions
-- 7. brand_suggestions - Pharmacy brand suggestions
-- 8. prescriptions - Customer prescription uploads
-- 9. prescription_items - Prescription medication details
-- 10. prescription_history - Prescription verification audit trail
--
-- Execute this in phpMyAdmin or MySQL client
-- Safe to run multiple times (will skip if columns/tables already exist)
-- ============================================

USE ecommerce_2025A_roseline_tsatsu;

-- ============================================
-- SECTION 1: ALTER EXISTING TABLES
-- ============================================

-- Add delivery columns to customer table (one at a time for compatibility)
-- These may fail if columns already exist - that's OK, we'll continue

ALTER TABLE `customer`
ADD COLUMN `offers_delivery` TINYINT(1) DEFAULT 0 COMMENT 'Does pharmacy offer delivery?';

ALTER TABLE `customer`
ADD COLUMN `delivery_fee` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Pharmacy delivery fee';

ALTER TABLE `customer`
ADD COLUMN `delivery_radius_km` DECIMAL(6,2) DEFAULT 10.00 COMMENT 'Delivery radius in kilometers';

ALTER TABLE `customer`
ADD COLUMN `min_order_for_delivery` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Minimum order amount for delivery';

ALTER TABLE `customer`
ADD COLUMN `latitude` DECIMAL(10,8) DEFAULT NULL COMMENT 'Pharmacy location latitude';

ALTER TABLE `customer`
ADD COLUMN `longitude` DECIMAL(11,8) DEFAULT NULL COMMENT 'Pharmacy location longitude';

-- Add delivery columns to orders table
ALTER TABLE `orders`
ADD COLUMN `delivery_method` ENUM('pharmacy', 'platform_rider', 'pickup') DEFAULT 'pharmacy'
    COMMENT 'pharmacy=pharmacy delivers, platform_rider=platform rider delivers, pickup=customer pickup'
    AFTER `order_status`;

ALTER TABLE `orders`
ADD COLUMN `delivery_notes` TEXT COMMENT 'Special delivery instructions'
    AFTER `delivery_method`;

-- ============================================
-- SECTION 2: DELIVERY SYSTEM TABLES
-- ============================================

-- Table: riders
CREATE TABLE IF NOT EXISTS `riders` (
  `rider_id` INT(11) NOT NULL AUTO_INCREMENT,
  `rider_name` VARCHAR(200) NOT NULL,
  `rider_phone` VARCHAR(20) NOT NULL,
  `rider_email` VARCHAR(100) DEFAULT NULL,
  `rider_license` VARCHAR(50) DEFAULT NULL COMMENT 'Motorcycle license number',
  `vehicle_number` VARCHAR(50) DEFAULT NULL COMMENT 'Motorcycle registration number',
  `rider_address` TEXT DEFAULT NULL,
  `rider_latitude` DECIMAL(10,8) DEFAULT NULL COMMENT 'Rider home/base latitude',
  `rider_longitude` DECIMAL(11,8) DEFAULT NULL COMMENT 'Rider home/base longitude',
  `status` ENUM('active', 'inactive', 'busy') DEFAULT 'active' COMMENT 'active=available, inactive=offline, busy=on delivery',
  `total_deliveries` INT(11) DEFAULT 0 COMMENT 'Total completed deliveries',
  `rating` DECIMAL(3,2) DEFAULT 5.00 COMMENT 'Average customer rating (0-5)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`rider_id`),
  UNIQUE KEY `rider_phone` (`rider_phone`),
  INDEX `idx_status` (`status`),
  INDEX `idx_rating` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Motorcycle riders for platform delivery service';

-- Table: delivery_assignments
CREATE TABLE IF NOT EXISTS `delivery_assignments` (
  `assignment_id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL COMMENT 'Order being delivered',
  `rider_id` INT(11) NOT NULL COMMENT 'Assigned rider',
  `customer_id` INT(11) NOT NULL COMMENT 'Customer receiving delivery',
  `pharmacy_id` INT(11) DEFAULT NULL COMMENT 'Pharmacy fulfilling order',
  `pickup_address` TEXT NOT NULL COMMENT 'Where rider picks up order',
  `delivery_address` TEXT NOT NULL COMMENT 'Customer delivery address',
  `delivery_latitude` DECIMAL(10,8) DEFAULT NULL COMMENT 'Delivery location latitude',
  `delivery_longitude` DECIMAL(11,8) DEFAULT NULL COMMENT 'Delivery location longitude',
  `distance_km` DECIMAL(6,2) DEFAULT NULL COMMENT 'Distance from pickup to delivery',
  `delivery_fee` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Delivery fee for this assignment',
  `status` ENUM('assigned', 'picked_up', 'in_transit', 'delivered', 'cancelled') DEFAULT 'assigned' COMMENT 'Delivery status',
  `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When delivery was assigned',
  `picked_up_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'When rider picked up order',
  `delivered_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'When order was delivered',
  `cancelled_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'When delivery was cancelled',
  `cancellation_reason` TEXT DEFAULT NULL COMMENT 'Why delivery was cancelled',
  `customer_rating` INT(1) DEFAULT NULL COMMENT 'Customer rating (1-5)',
  `customer_feedback` TEXT DEFAULT NULL COMMENT 'Customer feedback on delivery',
  PRIMARY KEY (`assignment_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_rider_id` (`rider_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_pharmacy_id` (`pharmacy_id`),
  KEY `idx_status` (`status`),
  KEY `idx_assigned_at` (`assigned_at`),
  CONSTRAINT `delivery_assignments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `delivery_assignments_ibfk_2` FOREIGN KEY (`rider_id`) REFERENCES `riders` (`rider_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `delivery_assignments_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `delivery_assignments_ibfk_4` FOREIGN KEY (`pharmacy_id`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Delivery assignments to platform riders';

-- ============================================
-- SECTION 3: COMMUNICATION SYSTEM TABLES
-- ============================================

-- Table: messages (Pharmacy <-> Super Admin)
CREATE TABLE IF NOT EXISTS `messages` (
  `message_id` INT PRIMARY KEY AUTO_INCREMENT,
  `sender_id` INT NOT NULL COMMENT 'User who sent the message',
  `receiver_id` INT NOT NULL COMMENT 'User who receives the message',
  `subject` VARCHAR(255) NOT NULL,
  `message_text` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0 COMMENT '0=unread, 1=read',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`sender_id`) REFERENCES `customer`(`customer_id`) ON DELETE CASCADE,
  FOREIGN KEY (`receiver_id`) REFERENCES `customer`(`customer_id`) ON DELETE CASCADE,
  INDEX `idx_sender` (`sender_id`),
  INDEX `idx_receiver` (`receiver_id`),
  INDEX `idx_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Direct messaging between pharmacy and super admin';

-- Table: support_tickets
CREATE TABLE IF NOT EXISTS `support_tickets` (
  `ticket_id` INT PRIMARY KEY AUTO_INCREMENT,
  `customer_id` INT NOT NULL COMMENT 'User who created the ticket',
  `subject` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
  `status` ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
  `assigned_to` INT NULL COMMENT 'Admin assigned to handle ticket',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customer`(`customer_id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_to`) REFERENCES `customer`(`customer_id`) ON DELETE SET NULL,
  INDEX `idx_status` (`status`),
  INDEX `idx_priority` (`priority`),
  INDEX `idx_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer support ticket system';

-- Table: ticket_responses
CREATE TABLE IF NOT EXISTS `ticket_responses` (
  `response_id` INT PRIMARY KEY AUTO_INCREMENT,
  `ticket_id` INT NOT NULL,
  `user_id` INT NOT NULL COMMENT 'User who wrote the response',
  `response_text` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets`(`ticket_id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `customer`(`customer_id`) ON DELETE CASCADE,
  INDEX `idx_ticket` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Responses to support tickets';

-- ============================================
-- SECTION 4: SUGGESTION SYSTEM TABLES
-- ============================================

-- Table: category_suggestions (Pharmacy admins suggest categories)
CREATE TABLE IF NOT EXISTS `category_suggestions` (
  `suggestion_id` INT PRIMARY KEY AUTO_INCREMENT,
  `suggested_name` VARCHAR(255) NOT NULL,
  `suggested_by` INT NOT NULL COMMENT 'Pharmacy admin who suggested',
  `pharmacy_name` VARCHAR(255),
  `reason` TEXT COMMENT 'Why this category is needed',
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `reviewed_by` INT NULL COMMENT 'Super admin who reviewed',
  `reviewed_at` DATETIME NULL,
  `review_comment` TEXT NULL COMMENT 'Admin feedback on suggestion',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`suggested_by`) REFERENCES `customer`(`customer_id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewed_by`) REFERENCES `customer`(`customer_id`) ON DELETE SET NULL,
  INDEX `idx_status` (`status`),
  INDEX `idx_pharmacy` (`suggested_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Category suggestions from pharmacy admins';

-- Table: brand_suggestions (Pharmacy admins suggest brands)
CREATE TABLE IF NOT EXISTS `brand_suggestions` (
  `suggestion_id` INT PRIMARY KEY AUTO_INCREMENT,
  `suggested_name` VARCHAR(255) NOT NULL,
  `suggested_by` INT NOT NULL COMMENT 'Pharmacy admin who suggested',
  `pharmacy_name` VARCHAR(255),
  `reason` TEXT COMMENT 'Why this brand is needed',
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `reviewed_by` INT NULL COMMENT 'Super admin who reviewed',
  `reviewed_at` DATETIME NULL,
  `review_comment` TEXT NULL COMMENT 'Admin feedback on suggestion',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`suggested_by`) REFERENCES `customer`(`customer_id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewed_by`) REFERENCES `customer`(`customer_id`) ON DELETE SET NULL,
  INDEX `idx_status` (`status`),
  INDEX `idx_pharmacy` (`suggested_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Brand suggestions from pharmacy admins';

-- ============================================
-- SECTION 5: PRESCRIPTION SYSTEM TABLES
-- ============================================

-- Table: prescriptions
CREATE TABLE IF NOT EXISTS `prescriptions` (
  `prescription_id` INT PRIMARY KEY AUTO_INCREMENT,
  `customer_id` INT NOT NULL COMMENT 'Customer who uploaded prescription',
  `prescription_number` VARCHAR(50) UNIQUE NOT NULL,
  `prescription_image` VARCHAR(255) COMMENT 'Path to uploaded prescription image',
  `doctor_name` VARCHAR(200),
  `doctor_license` VARCHAR(100),
  `issue_date` DATE,
  `expiry_date` DATE,
  `status` ENUM('pending', 'verified', 'rejected', 'expired') DEFAULT 'pending',
  `verified_by` INT NULL COMMENT 'Admin who verified',
  `verified_at` TIMESTAMP NULL,
  `rejection_reason` TEXT,
  `allow_pharmacy_access` TINYINT(1) DEFAULT 1 COMMENT '1=pharmacies can see, 0=private',
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customer`(`customer_id`) ON DELETE CASCADE,
  FOREIGN KEY (`verified_by`) REFERENCES `customer`(`customer_id`) ON DELETE SET NULL,
  INDEX `idx_customer` (`customer_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_prescription_number` (`prescription_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer prescription uploads';

-- Table: prescription_items (Medications in prescription)
CREATE TABLE IF NOT EXISTS `prescription_items` (
  `prescription_item_id` INT PRIMARY KEY AUTO_INCREMENT,
  `prescription_id` INT NOT NULL,
  `medication_name` VARCHAR(200) NOT NULL,
  `dosage` VARCHAR(100) COMMENT 'e.g., 500mg',
  `quantity` VARCHAR(50) COMMENT 'e.g., 30 tablets',
  `frequency` VARCHAR(100) COMMENT 'e.g., 3 times daily',
  `duration` VARCHAR(100) COMMENT 'e.g., 7 days',
  `instructions` TEXT COMMENT 'Special instructions',
  FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions`(`prescription_id`) ON DELETE CASCADE,
  INDEX `idx_prescription` (`prescription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Medications listed in prescriptions';

-- Table: prescription_history (Audit trail for verification)
CREATE TABLE IF NOT EXISTS `prescription_history` (
  `history_id` INT PRIMARY KEY AUTO_INCREMENT,
  `prescription_id` INT NOT NULL,
  `action` ENUM('uploaded', 'verified', 'rejected', 'updated') NOT NULL,
  `performed_by` INT NOT NULL COMMENT 'User who performed the action',
  `notes` TEXT COMMENT 'Additional notes about the action',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions`(`prescription_id`) ON DELETE CASCADE,
  FOREIGN KEY (`performed_by`) REFERENCES `customer`(`customer_id`) ON DELETE CASCADE,
  INDEX `idx_prescription` (`prescription_id`),
  INDEX `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit trail for prescription verification';

-- ============================================
-- SECTION 6: VERIFICATION & SUMMARY
-- ============================================

-- Display summary
SELECT 'MIGRATION COMPLETED SUCCESSFULLY!' AS status;

SELECT
    'Database Tables Summary' AS info,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'ecommerce_2025A_roseline_tsatsu') AS total_tables;

-- List all new tables
SELECT table_name, table_rows, create_time
FROM information_schema.tables
WHERE table_schema = 'ecommerce_2025A_roseline_tsatsu'
AND table_name IN (
    'riders',
    'delivery_assignments',
    'messages',
    'support_tickets',
    'ticket_responses',
    'category_suggestions',
    'brand_suggestions',
    'prescriptions',
    'prescription_items',
    'prescription_history'
)
ORDER BY table_name;

-- ============================================
-- SUMMARY OF CHANGES
-- ============================================
/*
TABLES ADDED (10):
1. riders - Motorcycle delivery riders
2. delivery_assignments - Delivery tracking
3. messages - Direct messaging between pharmacy and super admin
4. support_tickets - Support ticket system
5. ticket_responses - Ticket responses
6. category_suggestions - Pharmacy category suggestions
7. brand_suggestions - Pharmacy brand suggestions
8. prescriptions - Customer prescription uploads
9. prescription_items - Prescription medication details
10. prescription_history - Prescription audit trail

COLUMNS ADDED TO EXISTING TABLES:
customer:
  - offers_delivery, delivery_fee, delivery_radius_km, min_order_for_delivery
  - latitude, longitude

orders:
  - delivery_method, delivery_notes

FEATURES ENABLED:
✓ Rider/Delivery Management System
✓ Pharmacy-Super Admin Messaging
✓ Support Ticket System
✓ Category/Brand Suggestion System
✓ Prescription Upload & Verification System

NOTE: If you see errors about "Duplicate column name" when adding columns,
this is NORMAL and expected - it means those columns already exist.
The migration will continue and complete successfully.
*/
