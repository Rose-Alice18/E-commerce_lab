-- Missing tables for production database: ecommerce_2025A_roseline_tsatsu
-- Run this SQL file on your live database to add missing tables

-- Create wishlist table
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

-- Create suggestions table (if needed for category/brand suggestions)
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
  CONSTRAINT `suggestions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  CONSTRAINT `suggestions_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL,
  CONSTRAINT `suggestions_ibfk_3` FOREIGN KEY (`related_category_id`) REFERENCES `categories` (`cat_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verify tables were created
SELECT 'Tables created successfully!' AS status;
