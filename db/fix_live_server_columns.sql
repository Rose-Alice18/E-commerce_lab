-- =====================================================
-- FIX LIVE SERVER - Only Rename Latitude/Longitude
-- =====================================================
-- customer_address already exists on live server
-- We only need to rename latitude and longitude
-- =====================================================

USE ecommerce_2025A_roseline_tsatsu;

-- Rename latitude to customer_latitude (to match localhost)
ALTER TABLE customer
CHANGE COLUMN latitude customer_latitude DECIMAL(10,8) NULL COMMENT 'Pharmacy location latitude';

-- Rename longitude to customer_longitude (to match localhost)
ALTER TABLE customer
CHANGE COLUMN longitude customer_longitude DECIMAL(11,8) NULL COMMENT 'Pharmacy location longitude';

-- Verify the changes
SELECT 'Columns renamed successfully!' AS Status;

-- Show the updated structure
DESCRIBE customer;
