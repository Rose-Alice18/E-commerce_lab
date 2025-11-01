-- =====================================================
-- PharmaVault - COMPLETE SERVER OVERRIDE
-- =====================================================
-- ⚠️ WARNING: This will DROP ALL EXISTING TABLES!
-- This file will completely reset your database structure
-- Use this when you need to sync server with localhost
-- =====================================================

USE ecommerce_2025A_roseline_tsatsu;

-- Disable foreign key checks to allow dropping tables
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- DROP ALL EXISTING TABLES (IN CORRECT ORDER)
-- =====================================================
DROP TABLE IF EXISTS `payment`;
DROP TABLE IF EXISTS `orderdetails`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `brands`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `customer`;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- CREATE TABLE: customer (users)
-- =====================================================
CREATE TABLE `customer` (
  `customer_id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_email` VARCHAR(50) NOT NULL,
  `customer_pass` VARCHAR(150) NOT NULL,
  `customer_country` VARCHAR(30) NOT NULL,
  `customer_city` VARCHAR(30) NOT NULL,
  `customer_contact` VARCHAR(15) NOT NULL,
  `customer_image` VARCHAR(100) DEFAULT NULL,
  `user_role` INT(11) NOT NULL DEFAULT 2 COMMENT '0=Super Admin, 1=Pharmacy Admin, 2=Customer',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `customer_email` (`customer_email`),
  KEY `idx_user_role` (`user_role`),
  KEY `idx_email` (`customer_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CREATE TABLE: categories
-- =====================================================
CREATE TABLE `categories` (
  `cat_id` INT(11) NOT NULL AUTO_INCREMENT,
  `cat_name` VARCHAR(100) NOT NULL,
  `cat_description` TEXT DEFAULT NULL,
  `user_id` INT(11) NOT NULL COMMENT 'Admin who created this category',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `unique_cat_user` (`cat_name`, `user_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_cat_name` (`cat_name`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CREATE TABLE: brands
-- =====================================================
CREATE TABLE `brands` (
  `brand_id` INT(11) NOT NULL AUTO_INCREMENT,
  `brand_name` VARCHAR(100) NOT NULL,
  `cat_id` INT(11) NOT NULL COMMENT 'Category this brand belongs to',
  `brand_description` TEXT DEFAULT NULL,
  `user_id` INT(11) NOT NULL COMMENT 'Admin who created this brand',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`brand_id`),
  UNIQUE KEY `unique_brand_cat_user` (`brand_name`, `cat_id`, `user_id`),
  KEY `idx_cat_id` (`cat_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_brand_name` (`brand_name`),
  CONSTRAINT `brands_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `categories` (`cat_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `brands_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CREATE TABLE: products
-- =====================================================
CREATE TABLE `products` (
  `product_id` INT(11) NOT NULL AUTO_INCREMENT,
  `pharmacy_id` INT(11) NOT NULL COMMENT 'Which pharmacy/admin is selling this product',
  `product_cat` INT(11) NOT NULL,
  `product_brand` INT(11) NOT NULL,
  `product_title` VARCHAR(200) NOT NULL,
  `product_price` DECIMAL(10,2) NOT NULL,
  `product_desc` TEXT DEFAULT NULL,
  `product_image` VARCHAR(255) DEFAULT NULL COMMENT 'Main product image path',
  `product_keywords` VARCHAR(200) DEFAULT NULL,
  `product_stock` INT(11) DEFAULT 0 COMMENT 'Available stock quantity',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  KEY `idx_pharmacy_id` (`pharmacy_id`),
  KEY `idx_product_cat` (`product_cat`),
  KEY `idx_product_brand` (`product_brand`),
  KEY `idx_product_price` (`product_price`),
  KEY `idx_product_title` (`product_title`),
  FULLTEXT KEY `ft_product_search` (`product_title`, `product_desc`, `product_keywords`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`pharmacy_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`product_cat`) REFERENCES `categories` (`cat_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `products_ibfk_3` FOREIGN KEY (`product_brand`) REFERENCES `brands` (`brand_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CREATE TABLE: product_images
-- =====================================================
CREATE TABLE `product_images` (
  `image_id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0 COMMENT '1=Primary image, 0=Additional image',
  `uploaded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`image_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_is_primary` (`is_primary`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CREATE TABLE: cart
-- =====================================================
CREATE TABLE `cart` (
  `cart_id` INT(11) NOT NULL AUTO_INCREMENT,
  `p_id` INT(11) NOT NULL,
  `c_id` INT(11) DEFAULT NULL,
  `ip_add` VARCHAR(50) DEFAULT NULL COMMENT 'For guest users',
  `qty` INT(11) NOT NULL DEFAULT 1,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cart_id`),
  KEY `idx_p_id` (`p_id`),
  KEY `idx_c_id` (`c_id`),
  KEY `idx_ip_add` (`ip_add`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`p_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`c_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CREATE TABLE: orders
-- =====================================================
CREATE TABLE `orders` (
  `order_id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) NOT NULL,
  `invoice_no` VARCHAR(50) NOT NULL,
  `order_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `order_status` VARCHAR(50) NOT NULL DEFAULT 'pending' COMMENT 'pending, processing, shipped, delivered, cancelled',
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `delivery_address` TEXT DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_order_status` (`order_status`),
  KEY `idx_order_date` (`order_date`),
  KEY `idx_invoice_no` (`invoice_no`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CREATE TABLE: orderdetails
-- =====================================================
CREATE TABLE `orderdetails` (
  `orderdetail_id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `qty` INT(11) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL COMMENT 'Price at time of purchase',
  PRIMARY KEY (`orderdetail_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_product_id` (`product_id`),
  CONSTRAINT `orderdetails_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `orderdetails_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CREATE TABLE: payment
-- =====================================================
CREATE TABLE `payment` (
  `pay_id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `amt` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'GHS',
  `payment_method` VARCHAR(50) DEFAULT NULL COMMENT 'momo, card, cash',
  `payment_status` VARCHAR(50) DEFAULT 'pending' COMMENT 'pending, completed, failed',
  `payment_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `transaction_ref` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`pay_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_payment_status` (`payment_status`),
  CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERT DEFAULT ACCOUNTS
-- =====================================================
-- Password for all accounts: Password@123
-- Hash: $2y$10$vQHSkGFaichXKLPz6M3BLeYRKXg5DvJF2vTqL2yLjhR.h/O8J7z4S

-- Super Admin Account
INSERT INTO `customer` (`customer_name`, `customer_email`, `customer_pass`, `customer_country`, `customer_city`, `customer_contact`, `user_role`)
VALUES ('Platform Administrator', 'admin@gmail.com', '$2y$10$vQHSkGFaichXKLPz6M3BLeYRKXg5DvJF2vTqL2yLjhR.h/O8J7z4S', 'Ghana', 'Accra', '+233200000000', 0);

-- Pharmacy Admin Account
INSERT INTO `customer` (`customer_name`, `customer_email`, `customer_pass`, `customer_country`, `customer_city`, `customer_contact`, `user_role`)
VALUES ('Faith Pharmacy', 'faith@gmail.com', '$2y$10$vQHSkGFaichXKLPz6M3BLeYRKXg5DvJF2vTqL2yLjhR.h/O8J7z4S', 'Ghana', 'Accra', '+233201234567', 1);

-- Customer Account
INSERT INTO `customer` (`customer_name`, `customer_email`, `customer_pass`, `customer_country`, `customer_city`, `customer_contact`, `user_role`)
VALUES ('Bennetta Avaga', 'bennetta@gmail.com', '$2y$10$vQHSkGFaichXKLPz6M3BLeYRKXg5DvJF2vTqL2yLjhR.h/O8J7z4S', 'Ghana', 'Kumasi', '+233209876543', 2);

-- =====================================================
-- DEPLOYMENT COMPLETE
-- =====================================================
-- ✅ All tables dropped and recreated
-- ✅ Foreign keys properly configured
-- ✅ Indexes optimized
-- ✅ Default accounts created
--
-- Login Credentials:
-- Super Admin: admin@gmail.com / Password@123
-- Pharmacy Admin: faith@gmail.com / Password@123
-- Customer: bennetta@gmail.com / Password@123
--
-- ⚠️ CHANGE THESE PASSWORDS IMMEDIATELY AFTER FIRST LOGIN!
-- =====================================================
