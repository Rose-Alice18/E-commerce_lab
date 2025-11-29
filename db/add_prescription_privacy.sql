-- ============================================
-- Add Privacy Permission to Prescriptions
-- This allows customers to control who can view their prescriptions
-- ============================================

USE pharmavault_db;

-- Add privacy column to prescriptions table
ALTER TABLE `prescriptions`
ADD COLUMN `allow_pharmacy_access` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Allow pharmacies to view this prescription'
AFTER `status`;

-- Add index for faster queries
ALTER TABLE `prescriptions`
ADD INDEX `idx_pharmacy_access` (`allow_pharmacy_access`);

-- Update comment for the table
ALTER TABLE `prescriptions`
COMMENT = 'Customer prescriptions with privacy controls. Superadmin (role 0,7) can always view. Pharmacies (role 1) need customer permission.';

-- Show result
SELECT 'Prescription privacy column added successfully!' AS status;
SELECT 'Privacy Roles:' AS info;
SELECT '- Role 0 or 7: Superadmin (can view ALL prescriptions)' AS role_info
UNION ALL
SELECT '- Role 1: Pharmacy (can view only if allow_pharmacy_access = 1)'
UNION ALL
SELECT '- Role 2: Customer (can view only their own prescriptions)';
