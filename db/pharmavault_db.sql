-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2025 at 03:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pharmavault_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(100) NOT NULL,
  `cat_id` int(11) NOT NULL COMMENT 'Which category this brand belongs to',
  `brand_description` text DEFAULT NULL,
  `user_id` int(11) NOT NULL COMMENT 'Admin who created this brand',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `cat_id`, `brand_description`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'Panadol', 1, 'Leading paracetamol brand by GSK', 1, '2025-10-26 20:26:51', NULL),
(2, 'Brufen', 1, 'Ibuprofen pain relief brand', 1, '2025-10-26 20:26:51', NULL),
(3, 'Voltaren', 1, 'Diclofenac topical and oral pain relief', 1, '2025-10-26 20:26:51', NULL),
(4, 'Aspirin', 1, 'Classic aspirin brand', 1, '2025-10-26 20:26:51', NULL),
(5, 'Augmentin', 2, 'Amoxicillin/Clavulanic acid brand by GSK', 1, '2025-10-26 20:26:51', NULL),
(6, 'Cipro', 2, 'Ciprofloxacin antibiotic brand', 1, '2025-10-26 20:26:51', NULL),
(7, 'Zithromax', 2, 'Azithromycin brand by Pfizer', 1, '2025-10-26 20:26:51', NULL),
(8, 'Amoxil', 2, 'Amoxicillin brand', 1, '2025-10-26 20:26:51', NULL),
(9, 'Centrum', 3, 'Leading multivitamin brand', 1, '2025-10-26 20:26:51', NULL),
(10, 'Nature\'s Bounty', 3, 'Premium vitamin supplements', 1, '2025-10-26 20:26:51', NULL),
(11, 'Seven Seas', 3, 'Cod liver oil and multivitamins', 1, '2025-10-26 20:26:51', NULL),
(12, 'Vitabiotics', 3, 'Specialist vitamin formulations', 1, '2025-10-26 20:26:51', NULL),
(13, 'Johnson & Johnson Baby', 4, 'Trusted baby care products', 1, '2025-10-26 20:26:51', NULL),
(14, 'Cussons Baby', 4, 'Baby skincare and toiletries', 1, '2025-10-26 20:26:51', NULL),
(15, 'Calpol', 4, 'Infant pain and fever relief', 1, '2025-10-26 20:26:51', NULL),
(16, 'Pampers', 4, 'Diapers and baby wipes', 1, '2025-10-26 20:26:51', NULL),
(17, 'Dettol', 5, 'Antiseptic and wound care', 1, '2025-10-26 20:26:51', NULL),
(18, 'Savlon', 5, 'Antiseptic cream and solutions', 1, '2025-10-26 20:26:51', NULL),
(19, 'Band-Aid', 5, 'Adhesive bandages', 1, '2025-10-26 20:26:51', NULL),
(20, 'Carex', 6, 'Hand wash and sanitizers', 1, '2025-10-26 20:26:51', NULL),
(21, 'Lifebuoy', 6, 'Antibacterial soap', 1, '2025-10-26 20:26:51', NULL),
(22, 'Benylin', 7, 'Cough and cold remedies', 1, '2025-10-26 20:26:51', NULL),
(23, 'Strepsils', 7, 'Throat lozenges', 1, '2025-10-26 20:26:51', NULL),
(24, 'Vicks', 7, 'Decongestants and vapor rubs', 1, '2025-10-26 20:26:51', NULL),
(25, 'Gaviscon', 8, 'Antacid and heartburn relief', 1, '2025-10-26 20:26:51', NULL),
(26, 'Imodium', 8, 'Anti-diarrheal medication', 1, '2025-10-26 20:26:51', NULL),
(27, 'Norvasc', 9, 'Amlodipine blood pressure medication', 1, '2025-10-26 20:26:51', NULL),
(28, 'Lipitor', 9, 'Atorvastatin cholesterol medication', 1, '2025-10-26 20:26:51', NULL),
(29, 'Glucophage', 10, 'Metformin brand', 1, '2025-10-26 20:26:51', NULL),
(30, 'Cetaphil', 11, 'Gentle skincare products', 1, '2025-10-26 20:26:51', NULL),
(31, 'Nivea', 11, 'Body creams and lotions', 1, '2025-10-26 20:26:51', NULL),
(32, 'Zyrtec', 13, 'Cetirizine allergy relief', 1, '2025-10-26 20:26:51', NULL),
(33, 'Claritin', 13, 'Loratadine allergy medication', 1, '2025-10-26 20:26:51', NULL),
(34, 'Panadol', 16, NULL, 6, '2025-10-26 23:58:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `p_id` int(11) NOT NULL,
  `ip_add` varchar(50) NOT NULL,
  `c_id` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `cat_id` int(11) NOT NULL,
  `cat_name` varchar(100) NOT NULL,
  `cat_description` text DEFAULT NULL,
  `user_id` int(11) NOT NULL COMMENT 'Admin who created this category',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`cat_id`, `cat_name`, `cat_description`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'Pain Relief Medications', 'Over-the-counter and prescription pain relievers', 1, '2025-10-26 20:26:51', NULL),
(2, 'Antibiotics', 'Prescription antibiotics for bacterial infections', 1, '2025-10-26 20:26:51', NULL),
(3, 'Vitamins & Supplements', 'Nutritional supplements and multivitamins', 1, '2025-10-26 20:26:51', NULL),
(4, 'Baby Care Products', 'Products for infants and toddlers', 1, '2025-10-26 20:26:51', NULL),
(5, 'First Aid & Medical Supplies', 'Bandages, antiseptics, and medical equipment', 1, '2025-10-26 20:26:51', NULL),
(6, 'Personal Care & Hygiene', 'Soap, sanitizers, toiletries', 1, '2025-10-26 20:26:51', NULL),
(7, 'Cold & Flu Remedies', 'Cough syrups, decongestants, throat lozenges', 1, '2025-10-26 20:26:51', NULL),
(8, 'Digestive Health', 'Antacids, laxatives, anti-diarrheal medications', 1, '2025-10-26 20:26:51', NULL),
(9, 'Heart & Blood Pressure', 'Cardiovascular medications', 1, '2025-10-26 20:26:51', NULL),
(10, 'Diabetes Care', 'Blood sugar monitoring and medications', 1, '2025-10-26 20:26:51', NULL),
(11, 'Skincare Products', 'Creams, ointments, moisturizers', 1, '2025-10-26 20:26:51', NULL),
(12, 'Eye & Ear Care', 'Drops and solutions', 1, '2025-10-26 20:26:51', NULL),
(13, 'Allergy Medications', 'Antihistamines and allergy relief', 1, '2025-10-26 20:26:51', NULL),
(14, 'Sexual Wellness', 'Contraceptives and related products', 1, '2025-10-26 20:26:51', NULL),
(15, 'Great', NULL, 1, '2025-10-26 23:51:52', NULL),
(16, 'Prescription', NULL, 6, '2025-10-26 23:56:26', NULL),
(17, 'Heart monitor', NULL, 6, '2025-10-26 23:56:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(50) NOT NULL,
  `customer_pass` varchar(150) NOT NULL,
  `customer_country` varchar(30) NOT NULL,
  `customer_city` varchar(30) NOT NULL,
  `customer_contact` varchar(15) NOT NULL,
  `customer_image` varchar(100) DEFAULT NULL,
  `user_role` int(11) NOT NULL COMMENT '0=Super Admin, 1=Pharmacy Owner, 2=Customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `customer_name`, `customer_email`, `customer_pass`, `customer_country`, `customer_city`, `customer_contact`, `customer_image`, `user_role`) VALUES
(1, 'Nam Temba', 'namtemba@gmail.com', '$2y$10$T3WzJ11iLYjk5IC7w64oYOHTR1dmu8XropMiBfBV/iYiqJnWoSBF6', 'Ghana', 'Accra', '+233244535378', NULL, 0),
(2, 'Roseline Tsatsu', 'roseline@gmail.com', '$2y$10$p4KoehW/6cPExE89ZUvog.wGbPrSQqbO25hkjubk4/LEaNvtRObye', 'Ghana', 'Accra', '+233591765159', NULL, 2),
(3, 'Faith Pharmacy', 'faith@gmail.com', '$2y$10$0UYZCunJ4.JndssalPCFK.EIUMToOczmShy5gLiRNmkoQkGu1NNIa', 'Ghana', 'Accra', '+233244535378', NULL, 1),
(4, 'Bennetta', 'bennetta@gmail.com', '$2y$10$SbmNMO.nBd2aO6HAa.myIeq21u4gE2sLAJ3T4KEC.aI4sjOk808qW', 'Ghana', 'Accra', '+233 8945778', NULL, 2),
(5, 'Super Admin', 'admin@gmail.com', '$2y$10$vQHSkGFaichXKLPz6M3BLeYRKXg5DvJF2vTqL2yLjhR.h/O8J7z4S', 'Ghana', 'Accra', '+233200000000', NULL, 0),
(6, 'Perp', 'perp@gmail.com', '$2y$10$cqoxHgfnn15/H/qrCM/iPOolo/cFhytRt2S8.X4WZtUw2slcVX08S', 'Ghana', 'Accra Ghana', '+233 09076860', NULL, 1),
(7, 'Super Admin', 'superadmin@gmail.com', 'y\0/H53MiS3UuTSxeF9OoR.fjh7qgtm4vehYNWJPLyHlwjxQ/PFR6y', 'Ghana', 'Accra', '+233200000000', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `orderdetails`
--

CREATE TABLE `orderdetails` (
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `invoice_no` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `order_status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `pay_id` int(11) NOT NULL,
  `amt` double NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `currency` text NOT NULL,
  `payment_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `pharmacy_id` int(11) NOT NULL COMMENT 'Which pharmacy is selling this product',
  `product_cat` int(11) NOT NULL,
  `product_brand` int(11) NOT NULL,
  `product_title` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_desc` text DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `product_keywords` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `product_stock` int(11) DEFAULT 0 COMMENT 'Available stock quantity'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `pharmacy_id`, `product_cat`, `product_brand`, `product_title`, `product_price`, `product_desc`, `product_image`, `product_keywords`, `created_at`, `updated_at`, `product_stock`) VALUES
(2, 3, 1, 2, 'Brufen Tablets 400mg Pack of 24', 22.00, 'Ibuprofen anti-inflammatory for pain and fever', 'uploads/u3/p2/image_1761953655_69054777802d8.jpg', 'brufen, ibuprofen, pain, inflammation', '2025-10-26 20:26:51', '2025-10-31 23:34:15', 8),
(3, 3, 1, 3, 'Voltaren Emulgel 1% 50g Tube', 35.00, 'Topical gel for muscle and joint pain', NULL, 'voltaren, gel, pain relief, muscle', '2025-10-26 20:26:51', NULL, 0),
(4, 3, 2, 5, 'Augmentin 625mg Tablets Pack of 21', 65.00, 'Co-amoxiclav antibiotic for infections', NULL, 'augmentin, antibiotic, infection', '2025-10-26 20:26:51', NULL, 0),
(5, 3, 2, 8, 'Amoxil 500mg Capsules Pack of 21', 45.00, 'Amoxicillin antibiotic for bacterial infections', NULL, 'amoxil, amoxicillin, antibiotic', '2025-10-26 20:26:51', NULL, 0),
(6, 3, 3, 9, 'Centrum Multivitamin Adults 60 Tablets', 55.00, 'Complete daily multivitamin with minerals', NULL, 'centrum, multivitamin, wellness', '2025-10-26 20:26:51', NULL, 0),
(7, 3, 3, 10, 'Nature\'s Bounty Vitamin C 1000mg (100 Tablets)', 45.00, 'High-potency Vitamin C for immune support', NULL, 'vitamin c, immune, antioxidant', '2025-10-26 20:26:51', NULL, 0),
(8, 3, 4, 13, 'Johnson\'s Baby Lotion 500ml', 32.00, 'Gentle baby lotion. Hypoallergenic', NULL, 'johnsons, baby lotion, skincare', '2025-10-26 20:26:51', NULL, 0),
(9, 3, 4, 15, 'Calpol Infant Suspension 100ml', 24.00, 'Paracetamol for babies from 2 months', NULL, 'calpol, baby paracetamol, fever', '2025-10-26 20:26:51', NULL, 0),
(10, 3, 5, 17, 'Dettol Antiseptic Liquid 500ml', 28.00, 'Multi-purpose antiseptic for wound cleaning', NULL, 'dettol, antiseptic, disinfectant', '2025-10-26 20:26:51', NULL, 0),
(11, 3, 13, 29, 'Zyrtec Tablets 10mg (30s)', 35.00, 'Non-drowsy 24-hour allergy relief', NULL, 'zyrtec, cetirizine, allergy, antihistamine', '2025-10-26 20:26:51', NULL, 0),
(12, 3, 1, 8, 'Brufen Tablets 400mg Pack of 24', 6.00, 'Relief', 'uploads/products/product_1761676512_69010ce03bbde.jpg', 'Relief', '2025-10-28 18:35:12', NULL, 45),
(13, 3, 6, 29, 'Paracetamol', 8.00, 're', '', 'head', '2025-10-31 11:13:11', NULL, 9),
(19, 3, 8, 15, 'Paracetamol', 4.00, 'ohhbuhh', 'uploads/u3/p19/image_1761949635_690537c37f723.jpg', 'Relief', '2025-10-31 22:27:15', '2025-10-31 22:27:15', 4),
(20, 3, 1, 26, 'Brofen', 4.00, 'Relief', 'uploads/u3/p20/image_1761953376_69054660042aa.jpg', 'Head', '2025-10-31 23:29:36', '2025-10-31 23:29:36', 7);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0 COMMENT '1=Primary image, 0=Additional image',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `image_path`, `is_primary`, `uploaded_at`) VALUES
(1, 19, 'uploads/u3/p19/image_1761949635_690537c37f723.jpg', 1, '2025-10-31 22:27:15'),
(2, 20, 'uploads/u3/p20/image_1761953376_69054660042aa.jpg', 1, '2025-10-31 23:29:36'),
(4, 2, 'uploads/u3/p2/image_1761953655_69054777802d8.jpg', 1, '2025-10-31 23:34:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`),
  ADD UNIQUE KEY `unique_brand_per_category` (`brand_name`,`cat_id`),
  ADD KEY `idx_category` (`cat_id`),
  ADD KEY `idx_created_by` (`user_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD KEY `p_id` (`p_id`),
  ADD KEY `c_id` (`c_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`cat_id`),
  ADD UNIQUE KEY `cat_name` (`cat_name`),
  ADD KEY `idx_created_by` (`user_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `customer_email` (`customer_email`);

--
-- Indexes for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`pay_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_pharmacy` (`pharmacy_id`),
  ADD KEY `idx_category` (`product_cat`),
  ADD KEY `idx_brand` (`product_brand`),
  ADD KEY `idx_title` (`product_title`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `pay_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `brands`
--
ALTER TABLE `brands`
  ADD CONSTRAINT `brands_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `categories` (`cat_id`) ON DELETE CASCADE,
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
  ADD CONSTRAINT `orderdetails_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `orderdetails_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`),
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`product_cat`) REFERENCES `categories` (`cat_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`product_brand`) REFERENCES `brands` (`brand_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_ibfk_3` FOREIGN KEY (`pharmacy_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
