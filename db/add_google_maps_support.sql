-- Add Google Maps support to customer table
-- This adds latitude, longitude, and full address fields for pharmacies

USE pharmavault_db;

-- Add location fields to customer table
ALTER TABLE `customer`
ADD COLUMN `customer_address` TEXT DEFAULT NULL COMMENT 'Full street address' AFTER `customer_city`,
ADD COLUMN `customer_latitude` DECIMAL(10, 8) DEFAULT NULL COMMENT 'Latitude coordinate' AFTER `customer_address`,
ADD COLUMN `customer_longitude` DECIMAL(11, 8) DEFAULT NULL COMMENT 'Longitude coordinate' AFTER `customer_latitude`;

-- Add index for location queries
CREATE INDEX idx_location ON customer(customer_latitude, customer_longitude);

-- Sample update for existing pharmacies (you can update these with actual coordinates)
-- Accra, Ghana coordinates as example
UPDATE `customer`
SET `customer_latitude` = 5.6037,
    `customer_longitude` = -0.1870,
    `customer_address` = 'Accra, Ghana'
WHERE `user_role` = 1 AND `customer_latitude` IS NULL;
