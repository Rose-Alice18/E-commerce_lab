-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 01, 2025 at 05:21 AM
-- Server version: 8.0.44-0ubuntu0.24.04.1
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

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `cat_id`, `brand_description`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'Panadol', 1, 'Leading paracetamol brand by GSK', 7, '2025-11-06 11:48:14', NULL),
(2, 'Brufen', 1, 'Ibuprofen pain relief brand', 7, '2025-11-06 11:48:14', NULL),
(3, 'Voltaren', 1, 'Diclofenac topical and oral pain relief', 7, '2025-11-06 11:48:14', NULL),
(4, 'Aspirin', 1, 'Classic aspirin brand', 7, '2025-11-06 11:48:14', NULL),
(5, 'Augmentin', 2, 'Amoxicillin/Clavulanic acid brand by GSK', 7, '2025-11-06 11:48:14', NULL),
(6, 'Cipro', 2, 'Ciprofloxacin antibiotic brand', 7, '2025-11-06 11:48:14', NULL),
(7, 'Zithromax', 2, 'Azithromycin brand by Pfizer', 7, '2025-11-06 11:48:14', NULL),
(8, 'Amoxil', 2, 'Amoxicillin brand', 7, '2025-11-06 11:48:14', NULL),
(9, 'Centrum', 3, 'Leading multivitamin brand', 7, '2025-11-06 11:48:14', NULL),
(10, 'Nature\'s Bounty', 3, 'Premium vitamin supplements', 7, '2025-11-06 11:48:14', NULL),
(11, 'Seven Seas', 3, 'Cod liver oil and multivitamins', 7, '2025-11-06 11:48:14', NULL),
(12, 'Vitabiotics', 3, 'Specialist vitamin formulations', 7, '2025-11-06 11:48:14', NULL),
(13, 'Johnson & Johnson Baby', 4, 'Trusted baby care products', 7, '2025-11-06 11:48:14', NULL),
(14, 'Cussons Baby', 4, 'Baby skincare and toiletries', 7, '2025-11-06 11:48:14', NULL),
(15, 'Calpol', 4, 'Infant pain and fever relief', 7, '2025-11-06 11:48:14', NULL),
(16, 'Pampers', 4, 'Diapers and baby wipes', 7, '2025-11-06 11:48:14', NULL),
(17, 'Dettol', 5, 'Antiseptic and wound care', 7, '2025-11-06 11:48:14', NULL),
(18, 'Savlon', 5, 'Antiseptic cream and solutions', 7, '2025-11-06 11:48:14', NULL),
(19, 'Band-Aid', 5, 'Adhesive bandages', 7, '2025-11-06 11:48:14', NULL),
(20, 'Carex', 6, 'Hand wash and sanitizers', 7, '2025-11-06 11:48:14', NULL),
(21, 'Lifebuoy', 6, 'Antibacterial soap', 7, '2025-11-06 11:48:14', NULL),
(22, 'Benylin', 7, 'Cough and cold remedies', 7, '2025-11-06 11:48:14', NULL),
(23, 'Strepsils', 7, 'Throat lozenges', 7, '2025-11-06 11:48:14', NULL),
(24, 'Vicks', 7, 'Decongestants and vapor rubs', 7, '2025-11-06 11:48:14', NULL),
(25, 'Gaviscon', 8, 'Antacid and heartburn relief', 7, '2025-11-06 11:48:14', NULL),
(26, 'Imodium', 8, 'Anti-diarrheal medication', 7, '2025-11-06 11:48:14', NULL),
(27, 'Norvasc', 9, 'Amlodipine blood pressure medication', 7, '2025-11-06 11:48:14', NULL),
(28, 'Lipitor', 9, 'Atorvastatin cholesterol medication', 7, '2025-11-06 11:48:14', NULL),
(29, 'Glucophage', 10, 'Metformin brand', 7, '2025-11-06 11:48:14', NULL),
(30, 'Cetaphil', 11, 'Gentle skincare products', 7, '2025-11-06 11:48:14', NULL),
(31, 'Nivea', 11, 'Body creams and lotions', 7, '2025-11-06 11:48:14', NULL),
(32, 'Zyrtec', 13, 'Cetirizine allergy relief', 7, '2025-11-06 11:48:14', NULL),
(33, 'Claritin', 13, 'Loratadine allergy medication', 7, '2025-11-06 11:48:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `brand_suggestions`
--

CREATE TABLE `brand_suggestions` (
  `suggestion_id` int NOT NULL,
  `suggested_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `suggested_by` int NOT NULL COMMENT 'Pharmacy admin who suggested',
  `pharmacy_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci COMMENT 'Why this brand is needed',
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `reviewed_by` int DEFAULT NULL COMMENT 'Super admin who reviewed',
  `reviewed_at` datetime DEFAULT NULL,
  `review_comment` text COLLATE utf8mb4_unicode_ci COMMENT 'Admin feedback on suggestion',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Brand suggestions from pharmacy admins';

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

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `p_id`, `c_id`, `ip_add`, `qty`, `added_at`) VALUES
(1, 2, 4, '10.205.0.254', 1, '2025-11-16 17:46:21'),
(2, 1, 4, '10.205.0.254', 1, '2025-11-16 17:46:23');

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

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`cat_id`, `cat_name`, `cat_description`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'Pain Relief Medications', 'Over-the-counter and prescription pain relievers', 7, '2025-11-06 11:48:13', NULL),
(2, 'Antibiotics', 'Prescription antibiotics for bacterial infections', 7, '2025-11-06 11:48:13', NULL),
(3, 'Vitamins & Supplements', 'Nutritional supplements and multivitamins', 7, '2025-11-06 11:48:13', NULL),
(4, 'Baby Care Products', 'Products for infants and toddlers', 7, '2025-11-06 11:48:13', NULL),
(5, 'First Aid & Medical Supplies', 'Bandages, antiseptics, and medical equipment', 7, '2025-11-06 11:48:13', NULL),
(6, 'Personal Care & Hygiene', 'Soap, sanitizers, toiletries', 7, '2025-11-06 11:48:13', NULL),
(7, 'Cold & Flu Remedies', 'Cough syrups, decongestants, throat lozenges', 7, '2025-11-06 11:48:13', NULL),
(8, 'Digestive Health', 'Antacids, laxatives, anti-diarrheal medications', 7, '2025-11-06 11:48:13', NULL),
(9, 'Heart & Blood Pressure', 'Cardiovascular medications', 7, '2025-11-06 11:48:13', NULL),
(10, 'Diabetes Care', 'Blood sugar monitoring and medications', 7, '2025-11-06 11:48:13', NULL),
(11, 'Skincare Products', 'Creams, ointments, moisturizers', 7, '2025-11-06 11:48:13', NULL),
(12, 'Eye & Ear Care', 'Drops and solutions', 7, '2025-11-06 11:48:13', NULL),
(13, 'Allergy Medications', 'Antihistamines and allergy relief', 7, '2025-11-06 11:48:13', NULL),
(14, 'Sexual Wellness', 'Contraceptives and related products', 7, '2025-11-06 11:48:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `category_suggestions`
--

CREATE TABLE `category_suggestions` (
  `suggestion_id` int NOT NULL,
  `suggested_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `suggested_by` int NOT NULL COMMENT 'Pharmacy admin who suggested',
  `pharmacy_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci COMMENT 'Why this category is needed',
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `reviewed_by` int DEFAULT NULL COMMENT 'Super admin who reviewed',
  `reviewed_at` datetime DEFAULT NULL,
  `review_comment` text COLLATE utf8mb4_unicode_ci COMMENT 'Admin feedback on suggestion',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Category suggestions from pharmacy admins';

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
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `offers_delivery` tinyint(1) DEFAULT '0' COMMENT 'Does pharmacy offer delivery?',
  `delivery_fee` decimal(10,2) DEFAULT '0.00' COMMENT 'Pharmacy delivery fee',
  `delivery_radius_km` decimal(6,2) DEFAULT '10.00' COMMENT 'Delivery radius in kilometers',
  `min_order_for_delivery` decimal(10,2) DEFAULT '0.00' COMMENT 'Minimum order amount for delivery',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'Pharmacy location latitude',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'Pharmacy location longitude'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `customer_name`, `customer_email`, `customer_pass`, `customer_country`, `customer_city`, `customer_contact`, `customer_image`, `user_role`, `created_at`, `updated_at`, `offers_delivery`, `delivery_fee`, `delivery_radius_km`, `min_order_for_delivery`, `latitude`, `longitude`) VALUES
(1, 'Platform Administrator', 'admin@gmail.com', '$2y$10$vQHSkGFaichXKLPz6M3BLeYRKXg5DvJF2vTqL2yLjhR.h/O8J7z4S', 'Ghana', 'Accra', '+233200000000', NULL, 0, '2025-11-01 16:13:44', NULL, 0, 0.00, 10.00, 0.00, NULL, NULL),
(2, 'Faith Pharmacy', 'faith@gmail.com', '$2y$10$vQHSkGFaichXKLPz6M3BLeYRKXg5DvJF2vTqL2yLjhR.h/O8J7z4S', 'Ghana', 'Accra', '+233201234567', NULL, 1, '2025-11-01 16:13:44', NULL, 0, 0.00, 10.00, 0.00, NULL, NULL),
(3, 'Bennetta Avaga', 'bennetta@gmail.com', '$2y$10$vQHSkGFaichXKLPz6M3BLeYRKXg5DvJF2vTqL2yLjhR.h/O8J7z4S', 'Ghana', 'Kumasi', '+233209876543', NULL, 2, '2025-11-01 16:13:44', NULL, 0, 0.00, 10.00, 0.00, NULL, NULL),
(4, 'Roseline Tsatsu', 'roseline@gmail.com', '$2y$10$Q1.rUateFeh70qNdrayRjeeVgdeImP0Bqd348xw2rklrfLihyI2tG', 'Ghana', 'Accra Ghana', '+233 09076860', NULL, 2, '2025-11-01 16:16:23', NULL, 0, 0.00, 10.00, 0.00, NULL, NULL),
(5, 'Vero', 'vero@gmail.com', '$2y$10$t5pSxpquZO0YzCv2PFoo7Ob7OfoKJnuGBCFN9KvG5xI2MbwNTm7gm', 'Ghana', 'Accra Ghana', '+233 8945778', NULL, 1, '2025-11-02 23:18:15', NULL, 0, 0.00, 10.00, 0.00, NULL, NULL),
(6, 'Namtembea', 'namtembea@gmail.com', '$2y$10$T3WzJ11iLYjk5IC7w64oYOHTR1dmu8XropMiBfBV/iYiqJnWoSBF6', 'Ghana', 'Accra', '+233200000000', NULL, 0, '2025-11-05 11:51:18', NULL, 0, 0.00, 10.00, 0.00, NULL, NULL),
(7, 'Nam Temba', 'namtemba@gmail.com', '$2y$10$T3WzJ11iLYjk5IC7w64oYOHTR1dmu8XropMiBfBV/iYiqJnWoSBF6', 'Ghana', 'Accra', '+233244535378', NULL, 0, '2025-11-05 12:04:52', NULL, 0, 0.00, 10.00, 0.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_assignments`
--

CREATE TABLE `delivery_assignments` (
  `assignment_id` int NOT NULL,
  `order_id` int NOT NULL COMMENT 'Order being delivered',
  `rider_id` int NOT NULL COMMENT 'Assigned rider',
  `customer_id` int NOT NULL COMMENT 'Customer receiving delivery',
  `pharmacy_id` int DEFAULT NULL COMMENT 'Pharmacy fulfilling order',
  `pickup_address` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Where rider picks up order',
  `delivery_address` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Customer delivery address',
  `delivery_latitude` decimal(10,8) DEFAULT NULL COMMENT 'Delivery location latitude',
  `delivery_longitude` decimal(11,8) DEFAULT NULL COMMENT 'Delivery location longitude',
  `distance_km` decimal(6,2) DEFAULT NULL COMMENT 'Distance from pickup to delivery',
  `delivery_fee` decimal(10,2) DEFAULT '0.00' COMMENT 'Delivery fee for this assignment',
  `status` enum('assigned','picked_up','in_transit','delivered','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'assigned' COMMENT 'Delivery status',
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When delivery was assigned',
  `picked_up_at` timestamp NULL DEFAULT NULL COMMENT 'When rider picked up order',
  `delivered_at` timestamp NULL DEFAULT NULL COMMENT 'When order was delivered',
  `cancelled_at` timestamp NULL DEFAULT NULL COMMENT 'When delivery was cancelled',
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci COMMENT 'Why delivery was cancelled',
  `customer_rating` int DEFAULT NULL COMMENT 'Customer rating (1-5)',
  `customer_feedback` text COLLATE utf8mb4_unicode_ci COMMENT 'Customer feedback on delivery'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Delivery assignments to platform riders';

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int NOT NULL,
  `sender_id` int NOT NULL COMMENT 'User who sent the message',
  `receiver_id` int NOT NULL COMMENT 'User who receives the message',
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0' COMMENT '0=unread, 1=read',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Direct messaging between pharmacy and super admin';

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
  `delivery_method` enum('pharmacy','platform_rider','pickup') COLLATE utf8mb4_unicode_ci DEFAULT 'pharmacy' COMMENT 'pharmacy=pharmacy delivers, platform_rider=platform rider delivers, pickup=customer pickup',
  `delivery_notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Special delivery instructions',
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
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `prescription_id` int NOT NULL,
  `customer_id` int NOT NULL COMMENT 'Customer who uploaded prescription',
  `prescription_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prescription_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path to uploaded prescription image',
  `doctor_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doctor_license` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('pending','verified','rejected','expired') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `verified_by` int DEFAULT NULL COMMENT 'Admin who verified',
  `verified_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `allow_pharmacy_access` tinyint(1) DEFAULT '1' COMMENT '1=pharmacies can see, 0=private',
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer prescription uploads';

-- --------------------------------------------------------

--
-- Table structure for table `prescription_history`
--

CREATE TABLE `prescription_history` (
  `history_id` int NOT NULL,
  `prescription_id` int NOT NULL,
  `action` enum('uploaded','verified','rejected','updated') COLLATE utf8mb4_unicode_ci NOT NULL,
  `performed_by` int NOT NULL COMMENT 'User who performed the action',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Additional notes about the action',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit trail for prescription verification';

-- --------------------------------------------------------

--
-- Table structure for table `prescription_items`
--

CREATE TABLE `prescription_items` (
  `prescription_item_id` int NOT NULL,
  `prescription_id` int NOT NULL,
  `medication_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dosage` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g., 500mg',
  `quantity` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g., 30 tablets',
  `frequency` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g., 3 times daily',
  `duration` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g., 7 days',
  `instructions` text COLLATE utf8mb4_unicode_ci COMMENT 'Special instructions'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Medications listed in prescriptions';

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

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `pharmacy_id`, `product_cat`, `product_brand`, `product_title`, `product_price`, `product_desc`, `product_image`, `product_keywords`, `product_stock`, `created_at`, `updated_at`) VALUES
(1, 5, 5, 16, 'Paracetamol', 5.00, 'Pain relief', 'uploads/u5/p1/image_1762429809_690c8b718c24c.jpg', 'Pain relief', 8, '2025-11-06 11:50:09', '2025-11-06 11:50:09'),
(2, 5, 14, 24, 'Brufen Tablets 400mg Pack of 24', 5.00, 'Sex', 'uploads/u5/p2/image_1762432431_690c95af079b5.jpg', 'Relief', 9, '2025-11-06 11:51:02', '2025-11-06 12:33:51');

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
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `image_path`, `is_primary`, `uploaded_at`) VALUES
(1, 1, 'uploads/u5/p1/image_1762429809_690c8b718c24c.jpg', 1, '2025-11-06 11:50:09'),
(2, 2, 'uploads/u5/p2/image_1762429862_690c8ba66bd18.jpg', 1, '2025-11-06 11:51:02'),
(3, 2, 'uploads/u5/p2/image_1762432390_690c958652b2f.jpg', 0, '2025-11-06 12:33:10'),
(4, 2, 'uploads/u5/p2/image_1762432431_690c95af079b5.jpg', 0, '2025-11-06 12:33:51');

-- --------------------------------------------------------

--
-- Table structure for table `riders`
--

CREATE TABLE `riders` (
  `rider_id` int NOT NULL,
  `rider_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rider_phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rider_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rider_license` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Motorcycle license number',
  `vehicle_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Motorcycle registration number',
  `rider_address` text COLLATE utf8mb4_unicode_ci,
  `rider_latitude` decimal(10,8) DEFAULT NULL COMMENT 'Rider home/base latitude',
  `rider_longitude` decimal(11,8) DEFAULT NULL COMMENT 'Rider home/base longitude',
  `status` enum('active','inactive','busy') COLLATE utf8mb4_unicode_ci DEFAULT 'active' COMMENT 'active=available, inactive=offline, busy=on delivery',
  `total_deliveries` int DEFAULT '0' COMMENT 'Total completed deliveries',
  `rating` decimal(3,2) DEFAULT '5.00' COMMENT 'Average customer rating (0-5)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Motorcycle riders for platform delivery service';

-- --------------------------------------------------------

--
-- Table structure for table `suggestions`
--

CREATE TABLE `suggestions` (
  `suggestion_id` int NOT NULL,
  `user_id` int NOT NULL,
  `suggestion_type` enum('category','brand') COLLATE utf8mb4_unicode_ci NOT NULL,
  `suggestion_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `suggestion_description` text COLLATE utf8mb4_unicode_ci,
  `related_category_id` int DEFAULT NULL COMMENT 'For brand suggestions, which category it belongs to',
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `ticket_id` int NOT NULL,
  `customer_id` int NOT NULL COMMENT 'User who created the ticket',
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `assigned_to` int DEFAULT NULL COMMENT 'Admin assigned to handle ticket',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer support ticket system';

-- --------------------------------------------------------

--
-- Table structure for table `ticket_responses`
--

CREATE TABLE `ticket_responses` (
  `response_id` int NOT NULL,
  `ticket_id` int NOT NULL,
  `user_id` int NOT NULL COMMENT 'User who wrote the response',
  `response_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Responses to support tickets';

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `product_id` int NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
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
-- Indexes for table `brand_suggestions`
--
ALTER TABLE `brand_suggestions`
  ADD PRIMARY KEY (`suggestion_id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_pharmacy` (`suggested_by`);

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
-- Indexes for table `category_suggestions`
--
ALTER TABLE `category_suggestions`
  ADD PRIMARY KEY (`suggestion_id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_pharmacy` (`suggested_by`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `customer_email` (`customer_email`),
  ADD KEY `idx_user_role` (`user_role`),
  ADD KEY `idx_email` (`customer_email`);

--
-- Indexes for table `delivery_assignments`
--
ALTER TABLE `delivery_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_rider_id` (`rider_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_pharmacy_id` (`pharmacy_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_assigned_at` (`assigned_at`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_sender` (`sender_id`),
  ADD KEY `idx_receiver` (`receiver_id`),
  ADD KEY `idx_read` (`is_read`);

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
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`prescription_id`),
  ADD UNIQUE KEY `prescription_number` (`prescription_number`),
  ADD KEY `verified_by` (`verified_by`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_prescription_number` (`prescription_number`);

--
-- Indexes for table `prescription_history`
--
ALTER TABLE `prescription_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `performed_by` (`performed_by`),
  ADD KEY `idx_prescription` (`prescription_id`),
  ADD KEY `idx_action` (`action`);

--
-- Indexes for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD PRIMARY KEY (`prescription_item_id`),
  ADD KEY `idx_prescription` (`prescription_id`);

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
-- Indexes for table `riders`
--
ALTER TABLE `riders`
  ADD PRIMARY KEY (`rider_id`),
  ADD UNIQUE KEY `rider_phone` (`rider_phone`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_rating` (`rating`);

--
-- Indexes for table `suggestions`
--
ALTER TABLE `suggestions`
  ADD PRIMARY KEY (`suggestion_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`suggestion_type`),
  ADD KEY `suggestions_ibfk_2` (`reviewed_by`),
  ADD KEY `suggestions_ibfk_3` (`related_category_id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_customer` (`customer_id`);

--
-- Indexes for table `ticket_responses`
--
ALTER TABLE `ticket_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_ticket` (`ticket_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `unique_customer_product` (`customer_id`,`product_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `brand_suggestions`
--
ALTER TABLE `brand_suggestions`
  MODIFY `suggestion_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `cat_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `category_suggestions`
--
ALTER TABLE `category_suggestions`
  MODIFY `suggestion_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `delivery_assignments`
--
ALTER TABLE `delivery_assignments`
  MODIFY `assignment_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `prescription_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescription_history`
--
ALTER TABLE `prescription_history`
  MODIFY `history_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescription_items`
--
ALTER TABLE `prescription_items`
  MODIFY `prescription_item_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `riders`
--
ALTER TABLE `riders`
  MODIFY `rider_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suggestions`
--
ALTER TABLE `suggestions`
  MODIFY `suggestion_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `ticket_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_responses`
--
ALTER TABLE `ticket_responses`
  MODIFY `response_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `brand_suggestions`
--
ALTER TABLE `brand_suggestions`
  ADD CONSTRAINT `brand_suggestions_ibfk_1` FOREIGN KEY (`suggested_by`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `brand_suggestions_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL;

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
-- Constraints for table `category_suggestions`
--
ALTER TABLE `category_suggestions`
  ADD CONSTRAINT `category_suggestions_ibfk_1` FOREIGN KEY (`suggested_by`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `category_suggestions_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL;

--
-- Constraints for table `delivery_assignments`
--
ALTER TABLE `delivery_assignments`
  ADD CONSTRAINT `delivery_assignments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_assignments_ibfk_2` FOREIGN KEY (`rider_id`) REFERENCES `riders` (`rider_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_assignments_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_assignments_ibfk_4` FOREIGN KEY (`pharmacy_id`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE;

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
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL;

--
-- Constraints for table `prescription_history`
--
ALTER TABLE `prescription_history`
  ADD CONSTRAINT `prescription_history_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`prescription_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescription_history_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD CONSTRAINT `prescription_items_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`prescription_id`) ON DELETE CASCADE;

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

--
-- Constraints for table `suggestions`
--
ALTER TABLE `suggestions`
  ADD CONSTRAINT `suggestions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `suggestions_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `suggestions_ibfk_3` FOREIGN KEY (`related_category_id`) REFERENCES `categories` (`cat_id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_tickets_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL;

--
-- Constraints for table `ticket_responses`
--
ALTER TABLE `ticket_responses`
  ADD CONSTRAINT `ticket_responses_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`ticket_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_responses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
