-- =====================================================
-- SYNC LIVE SERVER DATABASE TO MATCH LOCALHOST
-- =====================================================
-- This will add missing columns to your live server database
-- so it matches your localhost database structure
--
-- Database: ecommerce_2025A_roseline_tsatsu
-- Date: December 1, 2025
-- =====================================================

USE ecommerce_2025A_roseline_tsatsu;

-- Add customer_address column (missing on live server)
ALTER TABLE `customer`
ADD COLUMN `customer_address` TEXT NULL COMMENT 'Customer/Pharmacy full address';

-- Rename latitude to customer_latitude (to match localhost)
ALTER TABLE `customer`
CHANGE COLUMN `latitude` `customer_latitude` DECIMAL(10,8) NULL COMMENT 'Pharmacy location latitude';

-- Rename longitude to customer_longitude (to match localhost)
ALTER TABLE `customer`
CHANGE COLUMN `longitude` `customer_longitude` DECIMAL(11,8) NULL COMMENT 'Pharmacy location longitude';

-- Verify the changes
SELECT 'Changes applied successfully!' AS Status;

-- Show the updated structure
DESCRIBE customer;
