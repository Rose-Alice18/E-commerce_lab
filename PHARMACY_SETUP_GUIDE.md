# 🏥 PHARMACY SETUP GUIDE

## ✅ Current Status

The **Pharmacies page is working correctly** and displays **REAL DATA from the database**.

**It does NOT hallucinate or show fake data.**

---

## 🔍 How It Works

### Database Query (Line 19-22):
```php
$sql = "SELECT customer_id, customer_name, customer_email, customer_contact,
               customer_country, customer_city, customer_image
        FROM customer
        WHERE user_role = 2";  // Only pharmacy users
```

### Display Logic (Line 323-388):
```php
<?php if (count($pharmacies) > 0): ?>
    <!-- Loop through REAL pharmacies from database -->
    <?php foreach ($pharmacies as $pharmacy): ?>
        <div class="pharmacy-card">
            <!-- Display actual pharmacy data -->
            <div class="pharmacy-name"><?php echo $pharmacy['customer_name']; ?></div>
            <span><?php echo $pharmacy['customer_city']; ?>, <?php echo $pharmacy['customer_country']; ?></span>
            <span><?php echo $pharmacy['customer_contact']; ?></span>
            <span><?php echo $pharmacy['customer_email']; ?></span>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No pharmacies are currently registered</p>
<?php endif; ?>
```

**Summary:** The page shows **EXACTLY what's in your customer table** where `user_role = 2`.

---

## 🎯 What You're Probably Seeing

### Scenario 1: "No pharmacies are currently registered"
**Cause:** No users in database have `user_role = 2`

**Solution:** You need to add pharmacy users

---

### Scenario 2: Some pharmacies show but incomplete info
**Cause:** Pharmacy users exist but missing fields (contact, city, etc.)

**Solution:** Update pharmacy user records with complete information

---

## 🧪 CHECK YOUR DATABASE

### Run Test Script:
```
1. Open browser: http://localhost/register_sample/test_check_pharmacies.php
2. This will show:
   ✅ All users in your database
   ✅ Which users are pharmacies (user_role = 2)
   ✅ What data each pharmacy has
   ✅ What fields are missing
```

---

## 🔧 HOW TO ADD PHARMACY USERS

### Option 1: Register Pharmacy Via Registration Page (Recommended)

**If you have a pharmacy registration page:**
```
1. Go to pharmacy registration page
2. Fill in pharmacy details:
   - Pharmacy Name
   - Email
   - Password
   - Contact Number
   - City
   - Country
3. Ensure user_role is set to 2 during registration
```

---

### Option 2: Manually Update Existing User to Pharmacy

**Via phpMyAdmin:**
```sql
-- Change an existing user to pharmacy role
UPDATE customer
SET user_role = 2
WHERE customer_id = [ID];  -- Replace with actual customer ID

-- Example: Change user ID 5 to pharmacy
UPDATE customer
SET user_role = 2
WHERE customer_id = 5;
```

**Steps:**
```
1. Open phpMyAdmin
2. Select pharmavault_db database
3. Click "customer" table
4. Find the user you want to make a pharmacy
5. Click "Edit" on that row
6. Change "user_role" from 3 to 2
7. Click "Go" to save
```

---

### Option 3: Insert New Pharmacy User Directly

```sql
-- Insert a new pharmacy user
INSERT INTO customer (
    customer_name,
    customer_email,
    customer_pass,
    customer_contact,
    customer_country,
    customer_city,
    user_role
) VALUES (
    'City Pharmacy',                                    -- Pharmacy name
    'citypharmacy@example.com',                        -- Email
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Password: "password"
    '0201234567',                                      -- Contact
    'Ghana',                                           -- Country
    'Accra',                                           -- City
    2                                                  -- user_role = 2 (Pharmacy)
);

-- Add another pharmacy
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
    'healthplus@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '0241234567',
    'Ghana',
    'Kumasi',
    2
);
```

**Password:** The hashed password above is `"password"` - users can login with this

---

## 📋 USER ROLES EXPLAINED

| user_role | Role Name | Description |
|-----------|-----------|-------------|
| 1 | Superadmin | Full system access |
| 2 | **Pharmacy** | Pharmacy partner accounts |
| 3 | Regular Customer | Normal customers |

---

## 🎨 WHAT CUSTOMERS SEE

### When Pharmacies Exist:
```
┌──────────────────────────────────────┐
│  🏥 Our Partner Pharmacies           │
├──────────────────────────────────────┤
│                                      │
│  ┌────────────────────────────────┐ │
│  │        CP                      │ │  (Initials if no logo)
│  │   City Pharmacy                │ │
│  │                                │ │
│  │  📍 Accra, Ghana               │ │
│  │  📞 0201234567                 │ │
│  │  ✉️  citypharmacy@example.com  │ │
│  │                                │ │
│  │  [🏪 View Products]            │ │
│  └────────────────────────────────┘ │
│                                      │
│  ┌────────────────────────────────┐ │
│  │        HP                      │ │
│  │   Health Plus Pharmacy         │ │
│  │                                │ │
│  │  📍 Kumasi, Ghana              │ │
│  │  📞 0241234567                 │ │
│  │  ✉️  healthplus@example.com    │ │
│  │                                │ │
│  │  [🏪 View Products]            │ │
│  └────────────────────────────────┘ │
└──────────────────────────────────────┘
```

### When NO Pharmacies Exist:
```
┌──────────────────────────────────────┐
│  🏥 Our Partner Pharmacies           │
├──────────────────────────────────────┤
│                                      │
│           🔍                         │
│    (Large search icon)               │
│                                      │
│    No Pharmacies Found               │
│    No pharmacies are currently       │
│    registered                        │
│                                      │
└──────────────────────────────────────┘
```

---

## ✅ VERIFICATION CHECKLIST

After adding pharmacy users:

- [ ] Run test script: `test_check_pharmacies.php`
- [ ] Verify pharmacy users show with role = 2
- [ ] Check pharmacy has name, email, contact, city, country
- [ ] Visit `view/pharmacies.php`
- [ ] Verify pharmacies display on page
- [ ] Click "View Products" on a pharmacy
- [ ] Verify pharmacy detail page loads
- [ ] Check pharmacy products display (if any)

---

## 🔍 DEBUGGING

### If Pharmacies Page Shows Empty:

**Step 1:** Run test script
```
http://localhost/register_sample/test_check_pharmacies.php
```

**Step 2:** Check for pharmacies in database
```sql
SELECT customer_id, customer_name, customer_email, user_role
FROM customer
WHERE user_role = 2;
```

**Step 3:** If no results, add pharmacy users (see options above)

**Step 4:** Refresh pharmacies page

---

## 📊 DATABASE FIELDS DISPLAYED

The pharmacies page shows these fields from the `customer` table:

| Field | Required | Example | Displayed As |
|-------|----------|---------|---------------|
| customer_id | ✅ Yes | 5 | (Hidden - used for links) |
| customer_name | ✅ Yes | "City Pharmacy" | Pharmacy name |
| customer_email | ✅ Yes | "citypharmacy@example.com" | Email icon + text |
| customer_contact | ⚠️ Optional | "0201234567" | Phone icon + number |
| customer_city | ✅ Yes | "Accra" | Location icon + city |
| customer_country | ✅ Yes | "Ghana" | Location icon + country |
| customer_image | ⚠️ Optional | "uploads/pharmacy.jpg" | Logo or initials |
| user_role | ✅ Must be 2 | 2 | (Used in query filter) |

---

## 🎯 SUMMARY

**The pharmacies page is working correctly.**

It shows **REAL data from your database** - not fake/hallucinated data.

**If you see "No pharmacies found":**
- It means your database has no users with `user_role = 2`
- You need to add pharmacy users (see options above)

**If you see pharmacies but incomplete info:**
- Update the pharmacy user records with missing fields
- Ensure contact, city, country are filled in

**Next Steps:**
1. Run: `test_check_pharmacies.php`
2. Check how many pharmacies exist
3. Add pharmacies if needed (use SQL INSERT or update existing users)
4. Refresh pharmacies page
5. ✅ You should see your pharmacy users displayed

---

**Generated:** November 27, 2025
**File:** view/pharmacies.php
**Status:** ✅ Working correctly - displays real database data
