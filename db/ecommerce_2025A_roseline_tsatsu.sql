-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 05, 2025 at 02:25 PM
-- Server version: 8.0.43-0ubuntu0.24.04.2
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecommerce_2025A_roseline_tsatsu`
--

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `brand_id` int NOT NULL,
  `brand_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cat_id` int NOT NULL COMMENT 'Category this brand belongs to',
  `brand_description` text COLLATE utf8mb4_unicode_ci,
  `user_id` int NOT NULL COMMENT 'Admin who created this brand',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int NOT NULL,
  `p_id` int NOT NULL,
  `c_id` int DEFAULT NULL,
  `ip_add` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'For guest users',
  `qty` int NOT NULL DEFAULT '1',
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `cat_id` int NOT NULL,
  `cat_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cat_description` text COLLATE utf8mb4_unicode_ci,
  `user_id` int NOT NULL COMMENT 'Admin who created this category',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int NOT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_email` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_pass` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_country` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_city` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_contact` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_image` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_role` int NOT NULL DEFAULT '2' COMMENT '0=Super Admin, 1=Pharmacy Admin, 2=Customer',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `customer_name`, `customer_email`, `customer_pass`, `customer_country`, `customer_city`, `customer_contact`, `customer_image`, `user_role`, `created_at`, `updated_at`) VALUES
(1, 'Platform Administrator', 'admin@gmail.com', '$2y$10$vQHSkGFaichXKLPz6M3BLeYRKXg5DvJF2vTqL2yLjhR.h/O8J7z4S', 'Ghana', 'Accra', '+233200000000', NULL, 0, '2025-11-01 16:13:44', NULL),
(2, 'Faith Pharmacy', 'faith@gmail.com', '$2y$10$vQHSkGFaichXKLPz6M3BLeYRKXg5DvJF2vTqL2yLjhR.h/O8J7z4S', 'Ghana', 'Accra', '+233201234567', NULL, 1, '2025-11-01 16:13:44', NULL),
(3, 'Bennetta Avaga', 'bennetta@gmail.com', '$2y$10$vQHSkGFaichXKLPz6M3BLeYRKXg5DvJF2vTqL2yLjhR.h/O8J7z4S', 'Ghana', 'Kumasi', '+233209876543', NULL, 2, '2025-11-01 16:13:44', NULL),
(4, 'Roseline Tsatsu', 'roseline@gmail.com', '$2y$10$Q1.rUateFeh70qNdrayRjeeVgdeImP0Bqd348xw2rklrfLihyI2tG', 'Ghana', 'Accra Ghana', '+233 09076860', NULL, 2, '2025-11-01 16:16:23', NULL),
(5, 'Vero', 'vero@gmail.com', '$2y$10$t5pSxpquZO0YzCv2PFoo7Ob7OfoKJnuGBCFN9KvG5xI2MbwNTm7gm', 'Ghana', 'Accra Ghana', '+233 8945778', NULL, 1, '2025-11-02 23:18:15', NULL),
(6, 'Namtembea', 'namtembea@gmail.com', '$2y$10$T3WzJ11iLYjk5IC7w64oYOHTR1dmu8XropMiBfBV/iYiqJnWoSBF6', 'Ghana', 'Accra', '+233200000000', NULL, 0, '2025-11-05 11:51:18', NULL),
(7, 'Nam Temba', 'namtemba@gmail.com', '$2y$10$T3WzJ11iLYjk5IC7w64oYOHTR1dmu8XropMiBfBV/iYiqJnWoSBF6', 'Ghana', 'Accra', '+233244535378', NULL, 0, '2025-11-05 12:04:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orderdetails`
--

CREATE TABLE `orderdetails` (
  `orderdetail_id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `qty` int NOT NULL,
  `price` decimal(10,2) NOT NULL COMMENT 'Price at time of purchase'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `invoice_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `order_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending, processing, shipped, delivered, cancelled',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `delivery_address` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `pay_id` int NOT NULL,
  `order_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `amt` decimal(10,2) NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GHS',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'momo, card, cash',
  `payment_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'pending' COMMENT 'pending, completed, failed',
  `payment_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `transaction_ref` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int NOT NULL,
  `pharmacy_id` int NOT NULL COMMENT 'Which pharmacy/admin is selling this product',
  `product_cat` int NOT NULL,
  `product_brand` int NOT NULL,
  `product_title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_desc` text COLLATE utf8mb4_unicode_ci,
  `product_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Main product image path',
  `product_keywords` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_stock` int DEFAULT '0' COMMENT 'Available stock quantity',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int NOT NULL,
  `product_id` int NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0' COMMENT '1=Primary image, 0=Additional image',
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`),
  ADD UNIQUE KEY `unique_brand_cat_user` (`brand_name`,`cat_id`,`user_id`),
  ADD KEY `idx_cat_id` (`cat_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_brand_name` (`brand_name`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `idx_p_id` (`p_id`),
  ADD KEY `idx_c_id` (`c_id`),
  ADD KEY `idx_ip_add` (`ip_add`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`cat_id`),
  ADD UNIQUE KEY `unique_cat_user` (`cat_name`,`user_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_cat_name` (`cat_name`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `customer_email` (`customer_email`),
  ADD KEY `idx_user_role` (`user_role`),
  ADD KEY `idx_email` (`customer_email`);

--
-- Indexes for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD PRIMARY KEY (`orderdetail_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_order_status` (`order_status`),
  ADD KEY `idx_order_date` (`order_date`),
  ADD KEY `idx_invoice_no` (`invoice_no`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`pay_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_payment_status` (`payment_status`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_pharmacy_id` (`pharmacy_id`),
  ADD KEY `idx_product_cat` (`product_cat`),
  ADD KEY `idx_product_brand` (`product_brand`),
  ADD KEY `idx_product_price` (`product_price`),
  ADD KEY `idx_product_title` (`product_title`);
ALTER TABLE `products` ADD FULLTEXT KEY `ft_product_search` (`product_title`,`product_desc`,`product_keywords`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_is_primary` (`is_primary`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `cat_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orderdetails`
--
ALTER TABLE `orderdetails`
  MODIFY `orderdetail_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `pay_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `brands`
--
ALTER TABLE `brands`
  ADD CONSTRAINT `brands_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `categories` (`cat_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `brands_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`p_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`c_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD CONSTRAINT `orderdetails_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orderdetails_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`pharmacy_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`product_cat`) REFERENCES `categories` (`cat_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `products_ibfk_3` FOREIGN KEY (`product_brand`) REFERENCES `brands` (`brand_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
