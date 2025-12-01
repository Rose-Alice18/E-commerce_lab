# FIX LIVE SERVER DATABASE SCHEMA MISMATCH
**Date:** December 1, 2025
**Issue:** Database schemas are different between localhost and live server
**Status:** ⚠️ URGENT - Needs immediate action

---

## THE REAL PROBLEM DISCOVERED

Your **localhost** and **live server** databases have **different column names**!

### Localhost Database (pharmavault_db) - WORKING ✅
```sql
customer_address          TEXT
customer_latitude         DECIMAL(10,8)
customer_longitude        DECIMAL(11,8)
```

### Live Server Database (ecommerce_2025A_roseline_tsatsu) - BROKEN ❌
```sql
NO customer_address column
latitude                  DECIMAL(10,8)  -- Missing "customer_" prefix
longitude                 DECIMAL(11,8)  -- Missing "customer_" prefix
```

**This is why it works on localhost but fails on live server!**

---

## TWO SOLUTIONS

### Solution 1: Sync Live Server to Match Localhost (RECOMMENDED ✅)

This adds the missing columns to your live server so it matches localhost.

**Why this is better:**
- Your code already works with these column names
- No need to change any PHP files
- More descriptive column names
- Faster to implement

**Steps:**

1. **Upload SQL file to live server:**
   - File: `db/sync_live_server_to_localhost.sql`
   - Upload to: `/home/roseline.tsatsu/public_html/db/`

2. **Run the SQL via phpMyAdmin:**
   - Login to phpMyAdmin on live server
   - Select database: `ecommerce_2025A_roseline_tsatsu`
   - Click "SQL" tab
   - Paste the contents of `sync_live_server_to_localhost.sql`
   - Click "Go"

3. **Revert the PHP file changes I made:**
   Since your localhost code is already correct, you need to **undo** the changes I made to these files:
   - `classes/product_class.php` - Revert to original
   - `classes/rider_class.php` - Revert to original
   - `view/pharmacies.php` - Revert to original
   - `actions/search_medicine_availability.php` - Revert to original

   **OR** just upload your localhost versions of these files to live server!

4. **Keep the medicine_availability.php fix:**
   - Still upload `view/medicine_availability.php` (the HTML fix is still needed)

---

### Solution 2: Keep My Code Changes (Alternative ⚠️)

Keep the PHP changes I made and update your **localhost** database to match live server.

**Why this is NOT recommended:**
- Requires changing localhost database
- Less descriptive column names
- More work overall

**If you choose this option:**
Run this on your **localhost** database:
```sql
USE pharmavault_db;

-- Remove customer_address column
ALTER TABLE customer DROP COLUMN customer_address;

-- Rename customer_latitude to latitude
ALTER TABLE customer CHANGE COLUMN customer_latitude latitude DECIMAL(10,8);

-- Rename customer_longitude to longitude
ALTER TABLE customer CHANGE COLUMN customer_longitude longitude DECIMAL(11,8);
```

Then keep all my PHP file changes and upload them.

---

## RECOMMENDED STEPS (Solution 1)

### Step 1: Run SQL on Live Server

**Via phpMyAdmin:**
1. Login: `http://169.239.251.102:442/~roseline.tsatsu/phpmyadmin`
2. Select database: `ecommerce_2025A_roseline_tsatsu`
3. Click "SQL" tab
4. Copy/paste this SQL:

```sql
USE ecommerce_2025A_roseline_tsatsu;

-- Add customer_address column
ALTER TABLE customer
ADD COLUMN customer_address TEXT NULL COMMENT 'Customer/Pharmacy full address';

-- Rename latitude to customer_latitude
ALTER TABLE customer
CHANGE COLUMN latitude customer_latitude DECIMAL(10,8) NULL;

-- Rename longitude to customer_longitude
ALTER TABLE customer
CHANGE COLUMN longitude customer_longitude DECIMAL(11,8) NULL;
```

5. Click "Go"

---

### Step 2: Revert PHP Files (Use Localhost Versions)

**Option A: Upload localhost files**
Upload these files from your **localhost** to **live server** (they're already correct):
- `classes/product_class.php`
- `classes/rider_class.php`
- `view/pharmacies.php`
- `actions/search_medicine_availability.php`

**Option B: Use git to revert**
If you're using git:
```bash
git checkout HEAD -- classes/product_class.php
git checkout HEAD -- classes/rider_class.php
git checkout HEAD -- view/pharmacies.php
git checkout HEAD -- actions/search_medicine_availability.php
```

---

### Step 3: Upload Medicine Availability Fix

Still upload the fixed `view/medicine_availability.php` (HTML fix for duplicate body tag).

---

## VERIFICATION

After applying Solution 1, verify on live server:

```sql
-- Check the customer table structure
DESCRIBE customer;

-- Should show:
-- customer_address       TEXT
-- customer_latitude      DECIMAL(10,8)
-- customer_longitude     DECIMAL(11,8)
```

Then test:
- ✅ Customer dashboard should load
- ✅ Product page should display products
- ✅ Medicine availability should work (after uploading fixed file)
- ✅ Pharmacy map should show locations

---

## WHY THIS HAPPENED

You likely imported different SQL dumps to localhost vs live server:
- **Localhost:** Uses older/different schema with `customer_` prefixed columns
- **Live server:** Uses newer schema without `customer_` prefix

The SQL files in your `db` folder show both versions exist in your codebase.

---

## SUMMARY

**Problem:** Database column names differ between localhost and live server

**Quick Fix:**
1. Run SQL migration on live server (adds missing columns)
2. Upload your localhost PHP files (they already use correct names)
3. Upload fixed medicine_availability.php

**Time:** 5 minutes

---

**Questions?** Ask me which solution you prefer and I'll guide you through it step by step!
