# ✅ CORRECTED USER ROLES - PHARMAVAULT

## 🎯 ACTUAL USER ROLES IN YOUR SYSTEM

Based on `settings/core.php`, here are the **CORRECT** user roles:

| user_role | Role Name | Description |
|-----------|-----------|-------------|
| **0** or **7** | **Super Admin** | Full system access, platform management |
| **1** | **Pharmacy Admin** | Pharmacy owners/managers |
| **2** | **Regular Customer** | Normal shoppers |

---

## 🏥 PHARMACIES PAGE - CORRECTED

### ✅ Fixed Query:

**File:** `view/pharmacies.php`

**Before (WRONG):**
```php
WHERE user_role = 2  // This was showing customers!
```

**After (CORRECT):**
```php
WHERE user_role = 1  // Now shows pharmacies!
```

---

## 📊 YOUR SCREENSHOT EXPLAINED

**What you saw:**
- Bennetta (bennetta@gmail.com)
- Roseline Tsatsu (roseline@gmail.com)

**Why they appeared as pharmacies:**
- These users have `user_role = 2` in the database
- But role 2 = **Regular Customer** (not pharmacy!)
- The old query was: `WHERE user_role = 2` ❌
- So it showed customers instead of pharmacies

**The fix:**
- Changed query to: `WHERE user_role = 1` ✅
- Now it will show ONLY pharmacy admin accounts

---

## 🔍 WHAT YOU NEED NOW

### Check Your Database:

Run this query in phpMyAdmin:
```sql
-- Check users by role
SELECT customer_id, customer_name, customer_email, user_role,
       CASE user_role
           WHEN 0 THEN 'Super Admin'
           WHEN 7 THEN 'Super Admin'
           WHEN 1 THEN 'PHARMACY ADMIN'
           WHEN 2 THEN 'Customer'
           ELSE 'Unknown'
       END AS role_name
FROM customer
ORDER BY user_role, customer_name;
```

---

## ➕ ADD PHARMACY USERS

### Option 1: Change Existing Users to Pharmacy Admin

If Bennetta or Roseline should be pharmacies:

```sql
-- Make Bennetta a pharmacy admin
UPDATE customer
SET user_role = 1
WHERE customer_email = 'bennetta@gmail.com';

-- Make Roseline a pharmacy admin
UPDATE customer
SET user_role = 1
WHERE customer_email = 'roseline@gmail.com';
```

### Option 2: Create New Pharmacy Accounts

```sql
-- Add a pharmacy business
INSERT INTO customer (
    customer_name,
    customer_email,
    customer_pass,
    customer_contact,
    customer_country,
    customer_city,
    user_role
) VALUES (
    'MediCare Pharmacy',
    'info@medicarepharmacy.gh',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '0302123456',
    'Ghana',
    'Accra',
    1  -- Pharmacy Admin role
);
```

Password: `password`

---

## 🎨 AFTER FIX

### What Happens Now:

1. **Pharmacies Page** (`view/pharmacies.php`):
   - Query: `WHERE user_role = 1`
   - Shows: ONLY pharmacy admin accounts
   - Bennetta/Roseline won't show (they're role 2 = customers)

2. **If No Pharmacies Show:**
   - Means no users have `user_role = 1`
   - You need to create pharmacy accounts

3. **To Test:**
   - Create/update users with `user_role = 1`
   - Refresh pharmacies page
   - Should see pharmacy cards

---

## 🔒 PRESCRIPTION PRIVACY ROLES (CORRECTED)

**File:** `db/add_prescription_privacy.sql`

### Access Control:

| Role | user_role | Prescription Access |
|------|-----------|-------------------|
| **Super Admin** | 0 or 7 | Can view ALL prescriptions |
| **Pharmacy Admin** | 1 | Can view if `allow_pharmacy_access = 1` |
| **Customer** | 2 | Can view ONLY their own prescriptions |

### Implementation:

**For Pharmacy Admins (role 1):**
```php
// Pharmacies can ONLY see prescriptions where customer allowed access
$sql = "SELECT * FROM prescriptions
        WHERE allow_pharmacy_access = 1
        AND status = 'pending'
        ORDER BY uploaded_at DESC";
```

**For Super Admin (role 0 or 7):**
```php
// Superadmin can see ALL prescriptions
$sql = "SELECT * FROM prescriptions
        ORDER BY uploaded_at DESC";
```

**For Customers (role 2):**
```php
// Customers can only see their OWN prescriptions
$sql = "SELECT * FROM prescriptions
        WHERE customer_id = ?
        ORDER BY uploaded_at DESC";
```

---

## 📋 SUMMARY OF FIXES

1. ✅ **Pharmacies Page:** Changed `user_role = 2` → `user_role = 1`
2. ✅ **User Roles Clarified:** 0/7=Admin, 1=Pharmacy, 2=Customer
3. ✅ **Prescription Privacy:** Updated to use correct roles
4. ✅ **Documentation:** All docs now use correct roles

---

## 🧪 TESTING

### Step 1: Check Current Pharmacies
```sql
SELECT customer_id, customer_name, customer_email, user_role
FROM customer
WHERE user_role = 1;
```

**Expected:** List of pharmacy admin accounts
**If empty:** No pharmacies in database yet

### Step 2: Add/Update Pharmacy Users

Choose one:
- **Option A:** Change existing user to role 1
- **Option B:** Create new pharmacy account with role 1

### Step 3: Verify Pharmacies Page

Visit: `http://localhost/register_sample/view/pharmacies.php`

**Should show:** Pharmacy cards for users with `user_role = 1`

---

## ✅ FINAL STATUS

- ✅ Pharmacies page query corrected
- ✅ User roles documented
- ✅ Prescription privacy uses correct roles
- ✅ Ready to add pharmacy accounts

**Next:** Add pharmacy users with `user_role = 1` and refresh the page!

---

**Generated:** November 27, 2025
**Status:** ✅ Corrected
**Pharmacy Role:** `user_role = 1` (NOT 2!)
