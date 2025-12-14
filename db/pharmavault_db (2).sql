-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2025 at 05:54 PM
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
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `status` enum('draft','active','archived') DEFAULT 'draft',
  `target_audience` enum('all','customers','pharmacies','admins') DEFAULT 'all',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='System announcements and notifications';

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `title`, `content`, `priority`, `status`, `target_audience`, `created_by`, `created_at`, `updated_at`, `expires_at`) VALUES
(1, 'Welcome to PharmaVault', 'We are excited to announce the launch of our new pharmacy platform!', 'high', 'active', 'all', 1, '2025-12-01 15:25:51', '2025-12-01 15:25:51', NULL);

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
-- Table structure for table `brand_suggestions`
--

CREATE TABLE `brand_suggestions` (
  `suggestion_id` int(11) NOT NULL,
  `suggested_name` varchar(255) NOT NULL,
  `suggested_by` int(11) NOT NULL,
  `pharmacy_name` varchar(255) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`p_id`, `ip_add`, `c_id`, `qty`) VALUES
(12, '::1', 4, 1);

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
-- Table structure for table `category_suggestions`
--

CREATE TABLE `category_suggestions` (
  `suggestion_id` int(11) NOT NULL,
  `suggested_name` varchar(255) NOT NULL,
  `suggested_by` int(11) NOT NULL,
  `pharmacy_name` varchar(255) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `customer_address` text DEFAULT NULL COMMENT 'Full street address',
  `customer_latitude` decimal(10,8) DEFAULT NULL COMMENT 'Latitude coordinate',
  `customer_longitude` decimal(11,8) DEFAULT NULL COMMENT 'Longitude coordinate',
  `customer_contact` varchar(15) NOT NULL,
  `customer_image` varchar(100) DEFAULT NULL,
  `user_role` int(11) NOT NULL COMMENT '0=Super Admin, 1=Pharmacy Owner, 2=Customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `offers_delivery` tinyint(1) DEFAULT 0,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `delivery_radius_km` decimal(6,2) DEFAULT 10.00,
  `min_order_for_delivery` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `customer_name`, `customer_email`, `customer_pass`, `customer_country`, `customer_city`, `customer_address`, `customer_latitude`, `customer_longitude`, `customer_contact`, `customer_image`, `user_role`, `created_at`, `updated_at`, `offers_delivery`, `delivery_fee`, `delivery_radius_km`, `min_order_for_delivery`) VALUES
(1, 'Nam Temba', 'namtemba@gmail.com', '$2y$10$T3WzJ11iLYjk5IC7w64oYOHTR1dmu8XropMiBfBV/iYiqJnWoSBF6', 'Ghana', 'Accra', NULL, NULL, NULL, '+233244535378', NULL, 0, '2025-12-01 15:24:45', NULL, 0, 0.00, 10.00, 0.00),
(2, 'Roseline Tsatsu', 'roseline@gmail.com', '$2y$10$p4KoehW/6cPExE89ZUvog.wGbPrSQqbO25hkjubk4/LEaNvtRObye', 'Ghana', 'Accra', NULL, NULL, NULL, '+233591765159', NULL, 2, '2025-12-01 15:24:45', NULL, 0, 0.00, 10.00, 0.00),
(3, 'Faith Pharmacy', 'faith@gmail.com', '$2y$10$0UYZCunJ4.JndssalPCFK.EIUMToOczmShy5gLiRNmkoQkGu1NNIa', 'Ghana', 'Accra', 'Ring Road Central, Accra, Ghana', 5.60370000, -0.18700000, '+233244535378', NULL, 1, '2025-12-01 15:24:45', NULL, 1, 30.00, 10.00, 1.00),
(4, 'Bennetta', 'bennetta@gmail.com', '$2y$10$SbmNMO.nBd2aO6HAa.myIeq21u4gE2sLAJ3T4KEC.aI4sjOk808qW', 'Ghana', 'Kwabenya', NULL, NULL, NULL, '+233 8945778', NULL, 2, '2025-12-01 15:24:45', NULL, 0, 0.00, 10.00, 0.00),
(6, 'Perp', 'perp@gmail.com', '$2y$10$cqoxHgfnn15/H/qrCM/iPOolo/cFhytRt2S8.X4WZtUw2slcVX08S', 'Ghana', 'Accra Ghana', 'Osu Oxford Street, Accra, Ghana', 5.55580000, -0.18270000, '+233 09076860', NULL, 1, '2025-12-01 15:24:45', NULL, 0, 0.00, 10.00, 0.00),
(7, 'Super Admin', 'superadmin@gmail.com', 'y\0/H53MiS3UuTSxeF9OoR.fjh7qgtm4vehYNWJPLyHlwjxQ/PFR6y', 'Ghana', 'Accra', NULL, NULL, NULL, '+233200000000', NULL, 0, '2025-12-01 15:24:45', NULL, 0, 0.00, 10.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `customer_feedback`
--

CREATE TABLE `customer_feedback` (
  `feedback_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `type` enum('complaint','suggestion','praise','other') DEFAULT 'other',
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','reviewed','actioned') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL COMMENT 'Admin user_id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Customer feedback and suggestions';

-- --------------------------------------------------------

--
-- Table structure for table `delivery_assignments`
--

CREATE TABLE `delivery_assignments` (
  `assignment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `rider_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `pharmacy_id` int(11) DEFAULT NULL,
  `pickup_address` text NOT NULL,
  `delivery_address` text NOT NULL,
  `delivery_latitude` decimal(10,8) DEFAULT NULL,
  `delivery_longitude` decimal(11,8) DEFAULT NULL,
  `distance_km` decimal(6,2) DEFAULT NULL,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `status` enum('assigned','picked_up','in_transit','delivered','cancelled') DEFAULT 'assigned',
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `picked_up_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `customer_rating` int(1) DEFAULT NULL,
  `customer_feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Delivery assignments to riders';

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `subject`, `message_text`, `is_read`, `created_at`) VALUES
(1, 3, 1, 'Thank you', 'I would like to thank you so much', 1, '2025-12-01 04:43:26');

-- --------------------------------------------------------

--
-- Table structure for table `orderdetails`
--

CREATE TABLE `orderdetails` (
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderdetails`
--

INSERT INTO `orderdetails` (`order_id`, `product_id`, `qty`) VALUES
(1, 2, 1),
(1, 19, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `invoice_no` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `order_status` varchar(100) NOT NULL,
  `delivery_method` enum('pharmacy','platform_rider','pickup') DEFAULT 'pharmacy',
  `delivery_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `invoice_no`, `order_date`, `order_status`, `delivery_method`, `delivery_notes`) VALUES
(1, 4, 0, '2025-11-27', 'Processing', 'pharmacy', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_prescriptions`
--

CREATE TABLE `order_prescriptions` (
  `order_prescription_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `prescription_id` int(11) NOT NULL,
  `attached_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`pay_id`, `amt`, `customer_id`, `order_id`, `currency`, `payment_date`) VALUES
(1, 32, 4, 1, 'GHS', '2025-11-27');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `prescription_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `prescription_number` varchar(50) NOT NULL,
  `doctor_name` varchar(200) DEFAULT NULL,
  `doctor_license` varchar(100) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `prescription_image` varchar(255) DEFAULT NULL,
  `prescription_notes` text DEFAULT NULL,
  `status` enum('pending','verified','rejected','expired') NOT NULL DEFAULT 'pending',
  `allow_pharmacy_access` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Allow pharmacies to view this prescription',
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer prescriptions with privacy controls. Superadmin (role 0,7) can always view. Pharmacies (role 1) need customer permission.';

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`prescription_id`, `customer_id`, `prescription_number`, `doctor_name`, `doctor_license`, `issue_date`, `expiry_date`, `prescription_image`, `prescription_notes`, `status`, `allow_pharmacy_access`, `verified_by`, `verified_at`, `rejection_reason`, `uploaded_at`, `updated_at`) VALUES
(1, 4, 'RX-20251127-00001', NULL, NULL, NULL, NULL, 'uploads/prescriptions/u4/rx_1764207616_6927ac00c893a.png', NULL, 'pending', 1, NULL, NULL, NULL, '2025-11-27 01:40:16', NULL),
(2, 4, 'RX-20251129-00001', NULL, NULL, NULL, NULL, 'uploads/prescriptions/u4/rx_1764445103_692b4baf7fe34.JPG', NULL, 'pending', 1, NULL, NULL, NULL, '2025-11-29 19:38:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `prescription_images`
--

CREATE TABLE `prescription_images` (
  `image_id` int(11) NOT NULL,
  `prescription_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `image_type` enum('front','back','detail','other') DEFAULT 'other',
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prescription_images`
--

INSERT INTO `prescription_images` (`image_id`, `prescription_id`, `image_path`, `image_type`, `is_primary`, `uploaded_at`) VALUES
(1, 1, 'uploads/prescriptions/u4/rx_1764207616_6927ac00c893a.png', 'front', 1, '2025-11-27 01:40:16'),
(2, 2, 'uploads/prescriptions/u4/rx_1764445103_692b4baf7fe34.JPG', 'front', 1, '2025-11-29 19:38:23');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_items`
--

CREATE TABLE `prescription_items` (
  `prescription_item_id` int(11) NOT NULL,
  `prescription_id` int(11) NOT NULL,
  `medication_name` varchar(200) NOT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `frequency` varchar(100) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `fulfilled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescription_verification_log`
--

CREATE TABLE `prescription_verification_log` (
  `log_id` int(11) NOT NULL,
  `prescription_id` int(11) NOT NULL,
  `action` enum('uploaded','verified','rejected','expired','resubmitted') NOT NULL,
  `performed_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prescription_verification_log`
--

INSERT INTO `prescription_verification_log` (`log_id`, `prescription_id`, `action`, `performed_by`, `notes`, `created_at`) VALUES
(1, 1, 'uploaded', 4, 'Prescription uploaded by customer', '2025-11-27 01:40:16'),
(2, 2, 'uploaded', 4, 'Prescription uploaded by customer', '2025-11-29 19:38:23');

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
  `product_stock` int(11) DEFAULT 0 COMMENT 'Available stock quantity',
  `prescription_required` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Does this product require a prescription?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `pharmacy_id`, `product_cat`, `product_brand`, `product_title`, `product_price`, `product_desc`, `product_image`, `product_keywords`, `created_at`, `updated_at`, `product_stock`, `prescription_required`) VALUES
(2, 3, 1, 2, 'Brufen Tablets 400mg Pack of 24', 22.00, 'Ibuprofen anti-inflammatory for pain and fever', 'uploads/u3/p2/image_1761953655_69054777802d8.jpg', 'brufen, ibuprofen, pain, inflammation', '2025-10-26 20:26:51', '2025-11-27 15:13:39', 7, 0),
(3, 3, 1, 3, 'Voltaren Emulgel 1% 50g Tube', 35.00, 'Topical gel for muscle and joint pain', 'uploads/u3/p3/image_1764461300_692b8af478acc.png', 'voltaren, gel, pain relief, muscle', '2025-10-26 20:26:51', '2025-11-30 00:08:20', 50, 0),
(4, 3, 2, 5, 'Augmentin 625mg Tablets Pack of 21', 65.00, 'Co-amoxiclav antibiotic for infections', 'uploads/u3/p4/image_1764461317_692b8b05c4d4c.JPG', 'augmentin, antibiotic, infection', '2025-10-26 20:26:51', '2025-11-30 00:08:37', 57, 0),
(5, 3, 2, 8, 'Amoxil 500mg Capsules Pack of 21', 45.00, 'Amoxicillin antibiotic for bacterial infections', NULL, 'amoxil, amoxicillin, antibiotic', '2025-10-26 20:26:51', NULL, 0, 0),
(6, 3, 3, 9, 'Centrum Multivitamin Adults 60 Tablets', 55.00, 'Complete daily multivitamin with minerals', 'uploads/u3/p6/image_1764461343_692b8b1f6396e.jpeg', 'centrum, multivitamin, wellness', '2025-10-26 20:26:51', '2025-11-30 00:09:03', 68, 0),
(7, 3, 3, 10, 'Nature\'s Bounty Vitamin C 1000mg (100 Tablets)', 45.00, 'High-potency Vitamin C for immune support', NULL, 'vitamin c, immune, antioxidant', '2025-10-26 20:26:51', NULL, 0, 0),
(8, 3, 4, 13, 'Johnson\'s Baby Lotion 500ml', 32.00, 'Gentle baby lotion. Hypoallergenic', 'uploads/u3/p8/image_1764461361_692b8b311338f.jpeg', 'johnsons, baby lotion, skincare', '2025-10-26 20:26:51', '2025-11-30 00:09:21', 49, 0),
(9, 3, 4, 15, 'Calpol Infant Suspension 100ml', 24.00, 'Paracetamol for babies from 2 months', 'uploads/u3/p9/image_1764461380_692b8b44292ba.png', 'calpol, baby paracetamol, fever', '2025-10-26 20:26:51', '2025-11-30 00:09:40', 46, 0),
(10, 3, 5, 17, 'Dettol Antiseptic Liquid 500ml', 28.00, 'Multi-purpose antiseptic for wound cleaning', 'uploads/u3/p10/image_1764461404_692b8b5c13b37.jpeg', 'dettol, antiseptic, disinfectant', '2025-10-26 20:26:51', '2025-11-30 00:10:04', 58, 0),
(11, 3, 13, 29, 'Zyrtec Tablets 10mg (30s)', 35.00, 'Non-drowsy 24-hour allergy relief', NULL, 'zyrtec, cetirizine, allergy, antihistamine', '2025-10-26 20:26:51', NULL, 0, 0),
(12, 3, 1, 8, 'Brufen Tablets 400mg Pack of 24', 6.00, 'Relief', 'uploads/products/product_1761676512_69010ce03bbde.jpg', 'Relief', '2025-10-28 18:35:12', NULL, 45, 0),
(13, 3, 6, 29, 'Paracetamol', 8.00, 're', '', 'head', '2025-10-31 11:13:11', NULL, 9, 0),
(19, 3, 8, 15, 'Paracetamol', 4.00, 'ohhbuhh', 'uploads/u3/p19/image_1764461277_692b8add2fe7b.JPG', 'Relief', '2025-10-31 22:27:15', '2025-11-30 00:07:57', 50, 0),
(20, 3, 1, 26, 'Brofen', 4.00, 'Relief', 'uploads/u3/p20/image_1761953376_69054660042aa.jpg', 'Head', '2025-10-31 23:29:36', '2025-10-31 23:29:36', 7, 0);

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
(4, 2, 'uploads/u3/p2/image_1761953655_69054777802d8.jpg', 1, '2025-10-31 23:34:15'),
(5, 19, 'uploads/u3/p19/image_1764461277_692b8add2fe7b.JPG', 0, '2025-11-30 00:07:57'),
(6, 3, 'uploads/u3/p3/image_1764461300_692b8af478acc.png', 1, '2025-11-30 00:08:20'),
(7, 4, 'uploads/u3/p4/image_1764461317_692b8b05c4d4c.JPG', 1, '2025-11-30 00:08:37'),
(8, 6, 'uploads/u3/p6/image_1764461343_692b8b1f6396e.jpeg', 1, '2025-11-30 00:09:03'),
(9, 8, 'uploads/u3/p8/image_1764461361_692b8b311338f.jpeg', 1, '2025-11-30 00:09:21'),
(10, 9, 'uploads/u3/p9/image_1764461380_692b8b44292ba.png', 1, '2025-11-30 00:09:40'),
(11, 10, 'uploads/u3/p10/image_1764461404_692b8b5c13b37.jpeg', 1, '2025-11-30 00:10:04');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_title` varchar(200) DEFAULT NULL,
  `review_text` text DEFAULT NULL,
  `is_verified_purchase` tinyint(1) DEFAULT 0,
  `helpful_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `riders`
--

CREATE TABLE `riders` (
  `rider_id` int(11) NOT NULL,
  `rider_name` varchar(200) NOT NULL,
  `rider_phone` varchar(20) NOT NULL,
  `rider_email` varchar(100) DEFAULT NULL,
  `rider_license` varchar(50) DEFAULT NULL,
  `vehicle_number` varchar(50) DEFAULT NULL,
  `rider_address` text DEFAULT NULL,
  `rider_latitude` decimal(10,8) DEFAULT NULL,
  `rider_longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('active','inactive','busy') DEFAULT 'active',
  `total_deliveries` int(11) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 5.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Motorcycle riders for delivery service';

--
-- Dumping data for table `riders`
--

INSERT INTO `riders` (`rider_id`, `rider_name`, `rider_phone`, `rider_email`, `rider_license`, `vehicle_number`, `rider_address`, `rider_latitude`, `rider_longitude`, `status`, `total_deliveries`, `rating`, `created_at`, `updated_at`) VALUES
(1, 'Kwame Mensah', '0244123456', 'kwame.rider@piharma.com', 'DL-123456', 'GR-2456-20', 'East Legon, Accra', NULL, NULL, 'active', 0, 5.00, '2025-12-01 04:02:42', NULL),
(2, 'Kofi Owusu', '0554234567', 'kofi.rider@piharma.com', 'DL-234567', 'GR-3567-21', 'Osu, Accra', NULL, NULL, 'active', 0, 5.00, '2025-12-01 04:02:42', NULL),
(3, 'Ama Asante', '0203345678', 'ama.rider@piharma.com', 'DL-345678', 'GR-4678-21', 'Tema, Greater Accra', NULL, NULL, 'active', 0, 5.00, '2025-12-01 04:02:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `suggestions`
--

CREATE TABLE `suggestions` (
  `suggestion_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `suggestion_type` enum('category','brand') NOT NULL,
  `suggestion_name` varchar(100) NOT NULL,
  `suggestion_description` text DEFAULT NULL,
  `related_category_id` int(11) DEFAULT NULL COMMENT 'For brand suggestions, which category it belongs to',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `ticket_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`ticket_id`, `customer_id`, `subject`, `description`, `priority`, `status`, `assigned_to`, `created_at`, `updated_at`) VALUES
(1, 2, 'Order Delivery Issue', 'My order has not arrived yet. It was supposed to be delivered yesterday.', 'high', 'open', NULL, '2025-12-01 16:14:05', '2025-12-01 16:14:05'),
(2, 2, 'Order Delivery Issue', 'My order has not arrived yet. It was supposed to be delivered yesterday.', 'high', 'open', NULL, '2025-12-01 16:23:59', '2025-12-01 16:23:59'),
(3, 2, 'Order Delivery Issue', 'My order has not arrived yet. It was supposed to be delivered yesterday.', 'high', 'open', NULL, '2025-12-01 16:29:21', '2025-12-01 16:29:21');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `log_id` int(11) NOT NULL,
  `log_level` enum('info','warning','error') DEFAULT 'info',
  `message` text NOT NULL,
  `action` varchar(100) DEFAULT NULL COMMENT 'Action performed',
  `user_id` int(11) DEFAULT NULL COMMENT 'User who performed action',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='System activity and error logs';

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='System-wide configuration settings';

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`, `updated_by`) VALUES
(1, 'site_name', 'PharmaVault', 'text', 'The name of your pharmacy platform', '2025-12-01 15:25:51', NULL),
(2, 'site_email', 'admin@pharmavault.com', 'text', 'Primary email for customer communications', '2025-12-01 15:25:51', NULL),
(3, 'site_phone', '+233 123 456 789', 'text', 'Primary phone number for support', '2025-12-01 15:25:51', NULL),
(4, 'maintenance_mode', '0', 'boolean', 'Temporarily disable the site for maintenance', '2025-12-01 15:25:51', NULL),
(5, 'allow_registration', '1', 'boolean', 'Allow new users to register accounts', '2025-12-01 15:25:51', NULL),
(6, 'require_email_verification', '1', 'boolean', 'Users must verify email before accessing account', '2025-12-01 15:25:51', NULL),
(7, 'max_upload_size', '5', 'number', 'Maximum file size for uploads (MB)', '2025-12-01 15:25:51', NULL),
(8, 'delivery_fee_per_km', '2.50', 'number', 'Base delivery charge per kilometer', '2025-12-01 15:25:51', NULL),
(9, 'tax_percentage', '12.5', 'number', 'Default tax rate applied to orders (%)', '2025-12-01 15:25:51', NULL),
(10, 'currency_symbol', 'GH₵', 'text', 'Symbol used for displaying prices', '2025-12-01 15:25:51', NULL),
(11, 'prescription_verification_required', '1', 'boolean', 'Admin approval required for prescriptions', '2025-12-01 15:25:51', NULL),
(12, 'auto_approve_products', '0', 'boolean', 'Automatically approve pharmacy product listings', '2025-12-01 15:25:51', NULL),
(13, 'smtp_enabled', '0', 'boolean', 'Use SMTP server for sending emails', '2025-12-01 15:25:51', NULL),
(14, 'smtp_host', '', 'text', 'SMTP server hostname', '2025-12-01 15:25:51', NULL),
(15, 'smtp_port', '587', 'number', 'SMTP server port', '2025-12-01 15:25:51', NULL),
(16, 'smtp_username', '', 'text', 'Email account for sending', '2025-12-01 15:25:51', NULL),
(17, 'items_per_page', '20', 'number', 'Number of items shown in lists', '2025-12-01 15:25:51', NULL),
(18, 'session_timeout', '30', 'number', 'User session expiration time (minutes)', '2025-12-01 15:25:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_responses`
--

CREATE TABLE `ticket_responses` (
  `response_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `response_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wishlist_id`, `customer_id`, `product_id`, `added_at`) VALUES
(1, 4, 19, '2025-11-25 20:11:06'),
(2, 4, 13, '2025-11-25 20:11:07'),
(3, 4, 2, '2025-11-25 20:11:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_target_audience` (`target_audience`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`),
  ADD UNIQUE KEY `unique_brand_per_category` (`brand_name`,`cat_id`),
  ADD KEY `idx_category` (`cat_id`),
  ADD KEY `idx_created_by` (`user_id`);

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
  ADD KEY `idx_location` (`customer_latitude`,`customer_longitude`);

--
-- Indexes for table `customer_feedback`
--
ALTER TABLE `customer_feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `delivery_assignments`
--
ALTER TABLE `delivery_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `pharmacy_id` (`pharmacy_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_rider` (`rider_id`),
  ADD KEY `idx_order` (`order_id`);

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
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_prescriptions`
--
ALTER TABLE `order_prescriptions`
  ADD PRIMARY KEY (`order_prescription_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_prescription_id` (`prescription_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`pay_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`prescription_id`),
  ADD UNIQUE KEY `prescription_number` (`prescription_number`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_prescription_status_customer` (`status`,`customer_id`),
  ADD KEY `prescriptions_ibfk_2` (`verified_by`),
  ADD KEY `idx_pharmacy_access` (`allow_pharmacy_access`);

--
-- Indexes for table `prescription_images`
--
ALTER TABLE `prescription_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_prescription_id` (`prescription_id`);

--
-- Indexes for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD PRIMARY KEY (`prescription_item_id`),
  ADD KEY `idx_prescription_id` (`prescription_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `prescription_verification_log`
--
ALTER TABLE `prescription_verification_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_prescription_id` (`prescription_id`),
  ADD KEY `idx_performed_by` (`performed_by`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_pharmacy` (`pharmacy_id`),
  ADD KEY `idx_category` (`product_cat`),
  ADD KEY `idx_brand` (`product_brand`),
  ADD KEY `idx_title` (`product_title`),
  ADD KEY `idx_prescription_required` (`prescription_required`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_customer_product_review` (`product_id`,`customer_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_rating` (`rating`);

--
-- Indexes for table `riders`
--
ALTER TABLE `riders`
  ADD PRIMARY KEY (`rider_id`),
  ADD UNIQUE KEY `rider_phone` (`rider_phone`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `suggestions`
--
ALTER TABLE `suggestions`
  ADD PRIMARY KEY (`suggestion_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`suggestion_type`),
  ADD KEY `idx_reviewed_by` (`reviewed_by`),
  ADD KEY `idx_related_category` (`related_category_id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_level` (`log_level`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_setting_key` (`setting_key`);

--
-- Indexes for table `ticket_responses`
--
ALTER TABLE `ticket_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `brand_suggestions`
--
ALTER TABLE `brand_suggestions`
  MODIFY `suggestion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `category_suggestions`
--
ALTER TABLE `category_suggestions`
  MODIFY `suggestion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `customer_feedback`
--
ALTER TABLE `customer_feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_assignments`
--
ALTER TABLE `delivery_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_prescriptions`
--
ALTER TABLE `order_prescriptions`
  MODIFY `order_prescription_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `pay_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `prescription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `prescription_images`
--
ALTER TABLE `prescription_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `prescription_items`
--
ALTER TABLE `prescription_items`
  MODIFY `prescription_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescription_verification_log`
--
ALTER TABLE `prescription_verification_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `riders`
--
ALTER TABLE `riders`
  MODIFY `rider_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `suggestions`
--
ALTER TABLE `suggestions`
  MODIFY `suggestion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `ticket_responses`
--
ALTER TABLE `ticket_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `brands`
--
ALTER TABLE `brands`
  ADD CONSTRAINT `brands_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `categories` (`cat_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `brands_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `brand_suggestions`
--
ALTER TABLE `brand_suggestions`
  ADD CONSTRAINT `brand_suggestions_ibfk_1` FOREIGN KEY (`suggested_by`) REFERENCES `customer` (`customer_id`),
  ADD CONSTRAINT `brand_suggestions_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `customer` (`customer_id`);

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
  ADD CONSTRAINT `category_suggestions_ibfk_1` FOREIGN KEY (`suggested_by`) REFERENCES `customer` (`customer_id`),
  ADD CONSTRAINT `category_suggestions_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `customer` (`customer_id`);

--
-- Constraints for table `customer_feedback`
--
ALTER TABLE `customer_feedback`
  ADD CONSTRAINT `customer_feedback_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_assignments`
--
ALTER TABLE `delivery_assignments`
  ADD CONSTRAINT `delivery_assignments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delivery_assignments_ibfk_2` FOREIGN KEY (`rider_id`) REFERENCES `riders` (`rider_id`),
  ADD CONSTRAINT `delivery_assignments_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delivery_assignments_ibfk_4` FOREIGN KEY (`pharmacy_id`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `customer` (`customer_id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `customer` (`customer_id`);

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
-- Constraints for table `order_prescriptions`
--
ALTER TABLE `order_prescriptions`
  ADD CONSTRAINT `order_prescriptions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_prescriptions_ibfk_2` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`prescription_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`),
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL;

--
-- Constraints for table `prescription_images`
--
ALTER TABLE `prescription_images`
  ADD CONSTRAINT `prescription_images_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`prescription_id`) ON DELETE CASCADE;

--
-- Constraints for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD CONSTRAINT `prescription_items_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`prescription_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescription_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL;

--
-- Constraints for table `prescription_verification_log`
--
ALTER TABLE `prescription_verification_log`
  ADD CONSTRAINT `prescription_verification_log_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`prescription_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescription_verification_log_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE;

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

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`),
  ADD CONSTRAINT `support_tickets_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `customer` (`customer_id`);

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL;

--
-- Constraints for table `ticket_responses`
--
ALTER TABLE `ticket_responses`
  ADD CONSTRAINT `ticket_responses_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`ticket_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_responses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `customer` (`customer_id`);

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
