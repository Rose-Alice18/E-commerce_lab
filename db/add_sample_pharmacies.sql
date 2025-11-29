-- ============================================
-- Add Sample Pharmacy Users to Database
-- This adds 3 sample pharmacies for testing
-- ============================================

USE pharmavault_db;

-- Check if pharmacies already exist
SELECT '=== Current Pharmacies ===' AS info;
SELECT customer_id, customer_name, customer_email, user_role
FROM customer
WHERE user_role = 2;

-- Add sample pharmacies (only if they don't exist)
-- Password for all: "password"

-- Pharmacy 1: City Pharmacy (Accra)
INSERT INTO customer (
    customer_name,
    customer_email,
    customer_pass,
    customer_contact,
    customer_country,
    customer_city,
    user_role
) VALUES (
    'City Pharmacy',
    'citypharmacy@pharmavault.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '0201234567',
    'Ghana',
    'Accra',
    2
)
ON DUPLICATE KEY UPDATE customer_name = customer_name;

-- Pharmacy 2: Health Plus Pharmacy (Kumasi)
INSERT INTO customer (
    customer_name,
    customer_email,
    customer_pass,
    customer_contact,
    customer_country,
    customer_city,
    user_role
) VALUES (
    'Health Plus Pharmacy',
    'healthplus@pharmavault.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '0241234567',
    'Ghana',
    'Kumasi',
    2
)
ON DUPLICATE KEY UPDATE customer_name = customer_name;

-- Pharmacy 3: Wellness Pharmacy (Takoradi)
INSERT INTO customer (
    customer_name,
    customer_email,
    customer_pass,
    customer_contact,
    customer_country,
    customer_city,
    user_role
) VALUES (
    'Wellness Pharmacy',
    'wellness@pharmavault.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '0261234567',
    'Ghana',
    'Takoradi',
    2
)
ON DUPLICATE KEY UPDATE customer_name = customer_name;

-- Show results
SELECT '=== Pharmacies Added Successfully! ===' AS status;
SELECT customer_id, customer_name, customer_email, customer_contact, customer_city, user_role
FROM customer
WHERE user_role = 2;

-- Show login credentials
SELECT '=== Login Credentials ===' AS info;
SELECT
    'All pharmacies' AS note,
    'password' AS password,
    'Use email from list above' AS email;
