-- Sample SQL to update pharmacy coordinates
-- Replace these with actual pharmacy locations

USE pharmavault_db;

-- Example: Update Faith Pharmacy (Accra, Ghana)
UPDATE customer
SET customer_address = 'Ring Road Central, Accra, Ghana',
    customer_latitude = 5.6037,
    customer_longitude = -0.1870
WHERE customer_email = 'faith@gmail.com' AND user_role = 1;

-- Example: Update Perp Pharmacy (Accra, Ghana)
UPDATE customer
SET customer_address = 'Osu Oxford Street, Accra, Ghana',
    customer_latitude = 5.5558,
    customer_longitude = -0.1827
WHERE customer_email = 'perp@gmail.com' AND user_role = 1;

-- To find coordinates for your pharmacies:
-- 1. Go to https://www.google.com/maps
-- 2. Search for the pharmacy address
-- 3. Right-click on the exact location
-- 4. Click on the coordinates shown (e.g., "5.6037, -0.1870")
-- 5. Coordinates are copied to your clipboard
-- 6. Paste them in the UPDATE statements above

-- Major cities in Ghana coordinates (for reference):
-- Accra: 5.6037, -0.1870
-- Kumasi: 6.6885, -1.6244
-- Takoradi: 4.8967, -1.7533
-- Tamale: 9.4034, -0.8424
-- Cape Coast: 5.1053, -1.2466
-- Tema: 5.6698, -0.0166

-- To verify pharmacies without coordinates:
SELECT customer_id, customer_name, customer_email, customer_city,
       customer_latitude, customer_longitude
FROM customer
WHERE user_role = 1
  AND (customer_latitude IS NULL OR customer_longitude IS NULL);

-- To see all pharmacies with coordinates:
SELECT customer_id, customer_name, customer_address,
       customer_latitude, customer_longitude
FROM customer
WHERE user_role = 1
  AND customer_latitude IS NOT NULL
  AND customer_longitude IS NOT NULL;
