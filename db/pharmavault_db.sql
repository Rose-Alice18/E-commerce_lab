-- PharmaVault Database Schema
-- Updated for Brand and Product Management

-- Create database
CREATE DATABASE IF NOT EXISTS pharmavault_db;
USE pharmavault_db;

-- ===================================
-- TABLE: customer (users)
-- ===================================
CREATE TABLE IF NOT EXISTS `customer` (
  `customer_id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_email` VARCHAR(50) NOT NULL UNIQUE,
  `customer_pass` VARCHAR(150) NOT NULL,
  `customer_country` VARCHAR(30) NOT NULL,
  `customer_city` VARCHAR(30) NOT NULL,
  `customer_contact` VARCHAR(15) NOT NULL,
  `customer_image` VARCHAR(100) DEFAULT NULL,
  `user_role` INT(11) NOT NULL DEFAULT 2 COMMENT '0=Super Admin, 1=Pharmacy Owner/Admin, 2=Regular Customer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`),
  INDEX `idx_user_role` (`user_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- TABLE: categories
-- ===================================
CREATE TABLE IF NOT EXISTS `categories` (
  `cat_id` INT(11) NOT NULL AUTO_INCREMENT,
  `cat_name` VARCHAR(100) NOT NULL,
  `cat_description` VARCHAR(255) DEFAULT NULL,
  `user_id` INT(11) NOT NULL COMMENT 'Admin who created this category',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cat_id`),
  INDEX `idx_user_id` (`user_id`),
  UNIQUE KEY `unique_cat_user` (`cat_name`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- TABLE: brands
-- ===================================
CREATE TABLE IF NOT EXISTS `brands` (
  `brand_id` INT(11) NOT NULL AUTO_INCREMENT,
  `brand_name` VARCHAR(100) NOT NULL,
  `cat_id` INT(11) NOT NULL COMMENT 'Category this brand belongs to',
  `brand_description` VARCHAR(255) DEFAULT NULL,
  `user_id` INT(11) NOT NULL COMMENT 'Admin who created this brand',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`brand_id`),
  INDEX `idx_cat_id` (`cat_id`),
  INDEX `idx_user_id` (`user_id`),
  UNIQUE KEY `unique_brand_cat_user` (`brand_name`, `cat_id`, `user_id`),
  FOREIGN KEY (`cat_id`) REFERENCES `categories`(`cat_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- TABLE: products
-- ===================================
CREATE TABLE IF NOT EXISTS `products` (
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
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  INDEX `idx_pharmacy_id` (`pharmacy_id`),
  INDEX `idx_product_cat` (`product_cat`),
  INDEX `idx_product_brand` (`product_brand`),
  INDEX `idx_product_price` (`product_price`),
  FOREIGN KEY (`pharmacy_id`) REFERENCES `customer`(`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_cat`) REFERENCES `categories`(`cat_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`product_brand`) REFERENCES `brands`(`brand_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- TABLE: product_images
-- ===================================
CREATE TABLE IF NOT EXISTS `product_images` (
  `image_id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0 COMMENT '1=Primary image, 0=Additional image',
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`image_id`),
  INDEX `idx_product_id` (`product_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- TABLE: cart
-- ===================================
CREATE TABLE IF NOT EXISTS `cart` (
  `cart_id` INT(11) NOT NULL AUTO_INCREMENT,
  `p_id` INT(11) NOT NULL,
  `c_id` INT(11) DEFAULT NULL,
  `ip_add` VARCHAR(50) DEFAULT NULL COMMENT 'For guest users',
  `qty` INT(11) NOT NULL DEFAULT 1,
  `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cart_id`),
  INDEX `idx_p_id` (`p_id`),
  INDEX `idx_c_id` (`c_id`),
  FOREIGN KEY (`p_id`) REFERENCES `products`(`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`c_id`) REFERENCES `customer`(`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- TABLE: orders
-- ===================================
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) NOT NULL,
  `invoice_no` VARCHAR(50) NOT NULL UNIQUE,
  `order_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `order_status` VARCHAR(50) NOT NULL DEFAULT 'pending' COMMENT 'pending, processing, shipped, delivered, cancelled',
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `delivery_address` TEXT DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_order_status` (`order_status`),
  FOREIGN KEY (`customer_id`) REFERENCES `customer`(`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- TABLE: orderdetails
-- ===================================
CREATE TABLE IF NOT EXISTS `orderdetails` (
  `orderdetail_id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `qty` INT(11) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL COMMENT 'Price at time of purchase',
  PRIMARY KEY (`orderdetail_id`),
  INDEX `idx_order_id` (`order_id`),
  INDEX `idx_product_id` (`product_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- TABLE: payment
-- ===================================
CREATE TABLE IF NOT EXISTS `payment` (
  `pay_id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `amt` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'GHS',
  `payment_method` VARCHAR(50) DEFAULT NULL COMMENT 'momo, card, cash',
  `payment_status` VARCHAR(50) DEFAULT 'pending' COMMENT 'pending, completed, failed',
  `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `transaction_ref` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`pay_id`),
  INDEX `idx_order_id` (`order_id`),
  INDEX `idx_customer_id` (`customer_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customer`(`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Sample Data - 3 User Roles for Testing
-- ===================================
-- All accounts use the same password: Password@123

-- ROLE 0: Super Admin (Platform Administrator)
-- Email: admin@gmail.com | Password: Password@123
-- Can access: Dashboard, manage all data, view all pharmacies
INSERT INTO `customer` (`customer_name`, `customer_email`, `customer_pass`, `customer_country`, `customer_city`, `customer_contact`, `user_role`)
VALUES ('Super Admin', 'admin@gmail.com', '$2y$10$vQHSkGFaichXKLPz6M3BLeYRKXg5DvJF2vTqL2yLjhR.h/O8J7z4S', 'Ghana', 'Accra', '+233200000000', 0)
ON DUPLICATE KEY UPDATE customer_name=customer_name;

-- ROLE 1: Pharmacy Admin (Pharmacy Owner/Seller)
-- Email: pharmacy@gmail.com | Password: Password@123
-- Can access: Their own categories, brands, products only
INSERT INTO `customer` (`customer_name`, `customer_email`, `customer_pass`, `customer_country`, `customer_city`, `customer_contact`, `user_role`)
VALUES ('Ernest Chemists', 'pharmacy@gmail.com', '$2y$10$vQHSkGFaichXKLPz6M3BLeYRKXg5DvJF2vTqL2yLjhR.h/O8J7z4S', 'Ghana', 'Accra', '+233201234567', 1)
ON DUPLICATE KEY UPDATE customer_name=customer_name;

-- ROLE 2: Regular Customer (Shopper/Buyer)
-- Email: customer@gmail.com | Password: Password@123
-- Can access: Browse products, add to cart, place orders
INSERT INTO `customer` (`customer_name`, `customer_email`, `customer_pass`, `customer_country`, `customer_city`, `customer_contact`, `user_role`)
VALUES ('John Doe', 'customer@gmail.com', '$2y$10$vQHSkGFaichXKLPz6M3BLeYRKXg5DvJF2vTqL2yLjhR.h/O8J7z4S', 'Ghana', 'Kumasi', '+233209876543', 2)
ON DUPLICATE KEY UPDATE customer_name=customer_name;
