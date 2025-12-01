# UPLOAD TO LIVE SERVER - URGENT FIX
**Errors Fixed:**
1. ❌ Unknown column 'cu.customer_address' in product_class.php:98
2. ❌ HTTP ERROR 500 on medicine_availability.php (duplicate closing body tag)
3. ❌ Blank product page (caused by error #1)

**Status:** ✅ All fixed locally, ⏳ Waiting for upload to live server

---

## FILES TO UPLOAD (5 FILES)

Upload these **5 fixed files** to your live server at `http://169.239.251.102:442/~roseline.tsatsu/`:

### 1. classes/product_class.php ⭐ CRITICAL
**Local path:** `c:\xampp\htdocs\register_sample\classes\product_class.php`
**Live path:** `/home/roseline.tsatsu/public_html/classes/product_class.php`
**Why:** This is causing the fatal error on line 98 AND the blank product page

### 2. classes/rider_class.php
**Local path:** `c:\xampp\htdocs\register_sample\classes\rider_class.php`
**Live path:** `/home/roseline.tsatsu/public_html/classes/rider_class.php`
**Why:** Prevents future errors in rider delivery system

### 3. view/pharmacies.php
**Local path:** `c:\xampp\htdocs\register_sample\view\pharmacies.php`
**Live path:** `/home/roseline.tsatsu/public_html/view/pharmacies.php`
**Why:** Fixes pharmacy map display

### 4. actions/search_medicine_availability.php
**Local path:** `c:\xampp\htdocs\register_sample\actions\search_medicine_availability.php`
**Live path:** `/home/roseline.tsatsu/public_html/actions/search_medicine_availability.php`
**Why:** Fixes medicine search functionality

### 5. view/medicine_availability.php ⭐ NEW FIX
**Local path:** `c:\xampp\htdocs\register_sample\view\medicine_availability.php`
**Live path:** `/home/roseline.tsatsu/public_html/view/medicine_availability.php`
**Why:** Fixes HTTP ERROR 500 (removed duplicate `</body>` tag)

---

## UPLOAD METHODS

### Option 1: Using FileZilla (Recommended)

1. **Connect to your server:**
   - Host: `169.239.251.102`
   - Username: `roseline.tsatsu`
   - Port: `22` (SFTP)

2. **Navigate to directories:**
   - Left side (local): `c:\xampp\htdocs\register_sample\`
   - Right side (remote): `/home/roseline.tsatsu/public_html/`

3. **Upload files one by one:**
   - Drag `classes/product_class.php` from left to right
   - Drag `classes/rider_class.php` from left to right
   - Drag `view/pharmacies.php` from left to right
   - Drag `actions/search_medicine_availability.php` from left to right
   - Drag `view/medicine_availability.php` from left to right

4. **Overwrite when prompted:** Click "Yes" to replace existing files

---

### Option 2: Using cPanel File Manager

1. **Login to cPanel:**
   - URL: `http://169.239.251.102:442/cpanel`
   - Username: `roseline.tsatsu`

2. **Navigate to File Manager**

3. **Go to public_html directory**

4. **Upload each file:**
   - Click "Upload" button
   - Select file from your computer
   - Upload to correct directory:
     - `classes/product_class.php` → upload to `public_html/classes/`
     - `classes/rider_class.php` → upload to `public_html/classes/`
     - `view/pharmacies.php` → upload to `public_html/view/`
     - `actions/search_medicine_availability.php` → upload to `public_html/actions/`
     - `view/medicine_availability.php` → upload to `public_html/view/`

5. **Overwrite existing files:** Click "Overwrite" when prompted

---

### Option 3: Using WinSCP

1. **Connect:**
   - Host: `169.239.251.102`
   - Username: `roseline.tsatsu`
   - Protocol: SFTP

2. **Navigate to `/home/roseline.tsatsu/public_html/`**

3. **Upload files:**
   - Right-click on local file → Upload
   - Or drag files from left panel to right panel

4. **Confirm overwrite:** Click "Yes" when asked

---

### Option 4: Using Command Line (SSH)

If you have SSH access:

```bash
# Connect to server
ssh roseline.tsatsu@169.239.251.102

# Go to public_html directory
cd /home/roseline.tsatsu/public_html/

# Create backup (optional but recommended)
cp classes/product_class.php classes/product_class.php.backup
cp classes/rider_class.php classes/rider_class.php.backup
cp view/pharmacies.php view/pharmacies.php.backup
cp actions/search_medicine_availability.php actions/search_medicine_availability.php.backup
cp view/medicine_availability.php view/medicine_availability.php.backup

# Now upload the fixed files using your preferred method
# (you'll need to transfer files from local to server)
```

---

## AFTER UPLOAD - VERIFICATION STEPS

### Step 1: Clear Server Cache (if needed)

If your server has OpCache enabled:

```bash
# SSH into server
ssh roseline.tsatsu@169.239.251.102

# Clear OpCache
php -r "opcache_reset();"
```

Or create a temporary PHP file to clear cache:

```php
<?php
// Save as clear_cache.php in public_html
opcache_reset();
echo "Cache cleared!";
?>
```

Then visit: `http://169.239.251.102:442/~roseline.tsatsu/clear_cache.php`

---

### Step 2: Test the Fix

Visit your customer dashboard:
```
http://169.239.251.102:442/~roseline.tsatsu/admin/customer_dashboard.php
```

**Expected result:** Page loads without errors ✅

**If still getting error:**
- Verify files were uploaded to correct locations
- Check file permissions (should be 644)
- Clear browser cache (Ctrl + F5)
- Check server error logs

---

### Step 3: Test Other Pages

Test these pages to confirm everything works:

1. ✅ **Customer Dashboard:** `admin/customer_dashboard.php`
2. ✅ **Products Page:** `view/product.php`
3. ✅ **Pharmacy Map:** `view/pharmacies.php`
4. ✅ **Medicine Search:** (test search functionality)
5. ✅ **Rider Management:** `admin/rider_management.php`

---

## QUICK VERIFICATION COMMANDS

### Check if files were uploaded:

```bash
# SSH into server
ssh roseline.tsatsu@169.239.251.102

# Check file modification times (should be recent)
ls -lh /home/roseline.tsatsu/public_html/classes/product_class.php
ls -lh /home/roseline.tsatsu/public_html/classes/rider_class.php
ls -lh /home/roseline.tsatsu/public_html/view/pharmacies.php
ls -lh /home/roseline.tsatsu/public_html/actions/search_medicine_availability.php
ls -lh /home/roseline.tsatsu/public_html/view/medicine_availability.php

# Check if 'customer_address' exists in file (should return nothing)
grep -n "customer_address" /home/roseline.tsatsu/public_html/classes/product_class.php
```

**Expected output:** No results (empty), which means the column reference was removed ✅

---

## FILE CHECKSUMS (To Verify Upload)

Use these to verify the files uploaded correctly:

### On Windows (local):
```cmd
cd c:\xampp\htdocs\register_sample
certutil -hashfile classes\product_class.php MD5
certutil -hashfile classes\rider_class.php MD5
certutil -hashfile view\pharmacies.php MD5
certutil -hashfile actions\search_medicine_availability.php MD5
certutil -hashfile view\medicine_availability.php MD5
```

### On Linux (server):
```bash
cd /home/roseline.tsatsu/public_html
md5sum classes/product_class.php
md5sum classes/rider_class.php
md5sum view/pharmacies.php
md5sum actions/search_medicine_availability.php
md5sum view/medicine_availability.php
```

**Compare:** Checksums should match between local and live files.

---

## TROUBLESHOOTING

### Error persists after upload?

**1. File not uploaded correctly:**
```bash
# Check file size
ls -lh /home/roseline.tsatsu/public_html/classes/product_class.php

# Check if file contains the fix
grep -n "latitude as pharmacy_latitude" /home/roseline.tsatsu/public_html/classes/product_class.php
```

Should show line with `cu.latitude as pharmacy_latitude` ✅

---

**2. Wrong file permissions:**
```bash
# Fix permissions
chmod 644 /home/roseline.tsatsu/public_html/classes/product_class.php
chmod 644 /home/roseline.tsatsu/public_html/classes/rider_class.php
chmod 644 /home/roseline.tsatsu/public_html/view/pharmacies.php
chmod 644 /home/roseline.tsatsu/public_html/actions/search_medicine_availability.php
chmod 644 /home/roseline.tsatsu/public_html/view/medicine_availability.php
```

---

**3. Cached old version:**
```php
// Create clear_cache.php
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OpCache cleared!";
}
if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "APC cache cleared!";
}
echo "Caches cleared!";
?>
```

Visit: `http://169.239.251.102:442/~roseline.tsatsu/clear_cache.php`

---

**4. Check error logs:**
```bash
# View error log
tail -n 50 /home/roseline.tsatsu/public_html/error_log

# Or
tail -n 50 /var/log/apache2/error.log
```

---

## UPLOAD CHECKLIST

Mark off as you complete:

- [ ] ✅ **Step 1:** Connect to live server (FileZilla/cPanel/WinSCP)
- [ ] ✅ **Step 2:** Upload `classes/product_class.php` to `/home/roseline.tsatsu/public_html/classes/`
- [ ] ✅ **Step 3:** Upload `classes/rider_class.php` to `/home/roseline.tsatsu/public_html/classes/`
- [ ] ✅ **Step 4:** Upload `view/pharmacies.php` to `/home/roseline.tsatsu/public_html/view/`
- [ ] ✅ **Step 5:** Upload `actions/search_medicine_availability.php` to `/home/roseline.tsatsu/public_html/actions/`
- [ ] ✅ **Step 6:** Upload `view/medicine_availability.php` to `/home/roseline.tsatsu/public_html/view/`
- [ ] ✅ **Step 7:** Verify files uploaded (check timestamps)
- [ ] ✅ **Step 8:** Clear server cache (if needed)
- [ ] ✅ **Step 9:** Test customer dashboard page
- [ ] ✅ **Step 10:** Test product page (should no longer be blank)
- [ ] ✅ **Step 11:** Test medicine availability page (should no longer show HTTP 500)
- [ ] ✅ **Step 12:** Verify no errors in browser

---

## SUMMARY

**Problems Fixed:**
1. Live server fatal error due to `customer_address` column that doesn't exist
2. Blank product page (caused by error #1)
3. HTTP ERROR 500 on medicine_availability.php (duplicate `</body>` tag)

**Solution:** Upload 5 fixed files that use correct column names and proper HTML structure

**Critical files:**
- `classes/product_class.php` (causing error on line 98 AND blank product page)
- `view/medicine_availability.php` (causing HTTP 500 error)

**Next step:** Upload the 5 files using one of the methods above

---

**Questions?** Check the [LIVE_SERVER_FIX_SUMMARY.md](LIVE_SERVER_FIX_SUMMARY.md) for technical details.

**Generated:** December 1, 2025
**Local files:** ✅ Fixed
**Live server:** ⏳ Waiting for upload
