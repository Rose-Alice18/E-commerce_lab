-- ============================================
-- Add ALL Missing Tables to pharmavault_db
-- Run this file in phpMyAdmin SQL tab
-- ============================================

USE pharmavault_db;

-- ============================================
-- 1. WISHLIST TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `wishlist` (
  `wishlist_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `product_id` int NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`wishlist_id`),
  UNIQUE KEY `unique_customer_product` (`customer_id`, `product_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_product_id` (`product_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. PRESCRIPTIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `prescriptions` (
  `prescription_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `prescription_number` varchar(50) NOT NULL,
  `doctor_name` varchar(200) DEFAULT NULL,
  `doctor_license` varchar(100) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `prescription_image` varchar(255) DEFAULT NULL,
  `prescription_notes` text DEFAULT NULL,
  `status` enum('pending','verified','rejected','expired') NOT NULL DEFAULT 'pending',
  `verified_by` int DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`prescription_id`),
  UNIQUE KEY `prescription_number` (`prescription_number`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_status` (`status`),
  KEY `idx_prescription_status_customer` (`status`, `customer_id`),
  CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. PRESCRIPTION ITEMS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `prescription_items` (
  `prescription_item_id` int NOT NULL AUTO_INCREMENT,
  `prescription_id` int NOT NULL,
  `medication_name` varchar(200) NOT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `quantity` int NOT NULL,
  `frequency` varchar(100) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `fulfilled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`prescription_item_id`),
  KEY `idx_prescription_id` (`prescription_id`),
  KEY `idx_product_id` (`product_id`),
  CONSTRAINT `prescription_items_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`prescription_id`) ON DELETE CASCADE,
  CONSTRAINT `prescription_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. ORDER PRESCRIPTIONS LINK TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `order_prescriptions` (
  `order_prescription_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `prescription_id` int NOT NULL,
  `attached_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_prescription_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_prescription_id` (`prescription_id`),
  CONSTRAINT `order_prescriptions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_prescriptions_ibfk_2` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`prescription_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. PRESCRIPTION VERIFICATION LOG TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `prescription_verification_log` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `prescription_id` int NOT NULL,
  `action` enum('uploaded','verified','rejected','expired','resubmitted') NOT NULL,
  `performed_by` int NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_prescription_id` (`prescription_id`),
  KEY `idx_performed_by` (`performed_by`),
  CONSTRAINT `prescription_verification_log_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`prescription_id`) ON DELETE CASCADE,
  CONSTRAINT `prescription_verification_log_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. PRESCRIPTION IMAGES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `prescription_images` (
  `image_id` int NOT NULL AUTO_INCREMENT,
  `prescription_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `image_type` enum('front','back','detail','other') DEFAULT 'other',
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`image_id`),
  KEY `idx_prescription_id` (`prescription_id`),
  CONSTRAINT `prescription_images_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`prescription_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. SUGGESTIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `suggestions` (
  `suggestion_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `suggestion_type` enum('category','brand') NOT NULL,
  `suggestion_name` varchar(100) NOT NULL,
  `suggestion_description` text,
  `related_category_id` int DEFAULT NULL COMMENT 'For brand suggestions, which category it belongs to',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int DEFAULT NULL,
  PRIMARY KEY (`suggestion_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`suggestion_type`),
  KEY `idx_reviewed_by` (`reviewed_by`),
  KEY `idx_related_category` (`related_category_id`),
  CONSTRAINT `suggestions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  CONSTRAINT `suggestions_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL,
  CONSTRAINT `suggestions_ibfk_3` FOREIGN KEY (`related_category_id`) REFERENCES `categories` (`cat_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. ADD PRESCRIPTION_REQUIRED COLUMN TO PRODUCTS
-- ============================================
ALTER TABLE `products`
ADD COLUMN IF NOT EXISTS `prescription_required` tinyint(1) NOT NULL DEFAULT 0
COMMENT 'Does this product require a prescription?'
AFTER `product_stock`;

-- Add index for prescription_required
CREATE INDEX IF NOT EXISTS `idx_prescription_required` ON `products` (`prescription_required`);

-- ============================================
-- VERIFICATION QUERY
-- ============================================
SELECT
    'All missing tables created successfully!' AS status,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'pharmavault_db') AS total_tables;

-- Show all tables in the database
SHOW TABLES;
