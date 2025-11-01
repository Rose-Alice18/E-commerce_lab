# Server Deployment Checklist - Quick Reference

## 📋 Your Server Details
- **Server IP**: 169.239.251.102
- **SSH Port**: 422
- **SSH User**: roseline.tsatsu
- **Database Name**: `ecommerce_2025A_roseline_tsatsu` ✅
- **SSH Command**: `ssh -C roseline.tsatsu@169.239.251.102 -p 422`

---

## 🚀 Step-by-Step Deployment

### Step 1: Prepare Local Code
```bash
cd c:\xampp\htdocs\register_sample

# Delete sensitive file
del LOGIN_CREDENTIALS.md

# Check status
git status

# Add all changes
git add .

# Commit
git commit -m "Production ready: Role-based access, color themes, responsive design"

# Push to Git
git push origin main
```

### Step 2: SSH into Server
```bash
ssh -C roseline.tsatsu@169.239.251.102 -p 422
```

### Step 3: Update Code on Server
```bash
# Navigate to your web directory
cd ~/public_html/pharmavault
# (or wherever your project is located)

# Pull latest changes
git pull origin main

# Set directory permissions
chmod 755 uploads
chmod 755 uploads/products
mkdir -p logs
chmod 755 logs
```

### Step 4: Update Database Credentials on Server
```bash
# Edit db_cred.php on the server
nano settings/db_cred.php
# OR
vi settings/db_cred.php
```

**Change the DATABASE constant to:**
```php
define("DATABASE", "ecommerce_2025A_roseline_tsatsu");  // Changed from pharmavault_db
```

**Update username and password to your server's MySQL credentials.**

### Step 5: Import Database
```bash
# Navigate to db folder
cd ~/public_html/pharmavault/db

# Import SQL file (it already uses "USE ecommerce;")
mysql -u your_mysql_username -p ecommerce_2025A_roseline_tsatsu < pharmavault_production.sql

# Enter your MySQL password when prompted
```

### Step 6: Delete Sensitive Files from Server
```bash
cd ~/public_html/pharmavault

# Delete deployment files after import
rm -f db/pharmavault_production.sql
rm -f deploy.bat
rm -f SERVER_DEPLOYMENT_CHECKLIST.md  # This file (optional)
```

### Step 7: Test Your Application
Open browser and visit your site:
```
http://169.239.251.102/pharmavault/
# OR
http://your-domain.com/
```

**Test Login Credentials:**
| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@gmail.com | Password@123 |
| Pharmacy Admin | faith@gmail.com | Password@123 |
| Customer | bennetta@gmail.com | Password@123 |

### Step 8: Verify Features
- [ ] Super Admin sees purple theme on category/brand pages
- [ ] Pharmacy Admin sees green theme on category/brand pages
- [ ] Super Admin can view ALL categories/brands (platform-wide)
- [ ] Pharmacy Admin can only view THEIR categories/brands
- [ ] Dashboard shows real statistics
- [ ] Sidebar doesn't overlap content
- [ ] Responsive design works on mobile
- [ ] Image uploads work

### Step 9: Security
- [ ] Change all default passwords immediately
- [ ] Verify `settings/db_cred.php` has correct server credentials
- [ ] Ensure `uploads/` folder has proper permissions (755)
- [ ] Verify `.htaccess` is working (if applicable)

---

## 🔧 Important Server Configuration

### Make sure `settings/db_cred.php` on server has:
```php
<?php
if (!defined("SERVER")) {
    define("SERVER", "localhost");
}

if (!defined("USERNAME")) {
    define("USERNAME", "your_server_mysql_username");  // UPDATE THIS!
}

if (!defined("PASSWD")) {
    define("PASSWD", "your_server_mysql_password");  // UPDATE THIS!
}

if (!defined("DATABASE")) {
    define("DATABASE", "ecommerce_2025A_roseline_tsatsu");  // ✅ This is correct for your server
}
?>
```

---

## 🆘 Troubleshooting

### Database Connection Error?
```bash
# Test connection
mysql -u your_username -p ecommerce -e "SHOW TABLES;"
```

### Permission Issues?
```bash
# Fix permissions
chmod -R 755 uploads
chown -R www-data:www-data uploads  # Replace www-data with your web user
```

### Can't Pull from Git?
```bash
# Check remote
git remote -v

# Force pull (CAREFUL - overwrites local changes)
git fetch origin
git reset --hard origin/main
```

### 500 Internal Server Error?
```bash
# Check error logs
tail -f logs/php_errors.log
# OR
tail -f /var/log/apache2/error.log
```

---

## 📝 Quick Commands Reference

```bash
# SSH into server
ssh -C roseline.tsatsu@169.239.251.102 -p 422

# Navigate to project
cd ~/public_html/pharmavault

# Pull updates
git pull origin main

# Import database
mysql -u username -p ecommerce_2025A_roseline_tsatsu < db/pharmavault_production.sql

# Check database
mysql -u username -p ecommerce -e "SHOW TABLES;"

# View error logs
tail -f logs/php_errors.log

# Set permissions
chmod 755 uploads uploads/products logs
```

---

## ✅ Deployment Complete!

After successful deployment:
1. ✅ Test all three user logins
2. ✅ Verify role-based colors work
3. ✅ Verify role-based data access works
4. ✅ Change default passwords
5. ✅ Delete `db/pharmavault_production.sql` from server
6. ✅ Monitor error logs for first 24 hours

---

## 🔄 Future Updates

When you make changes locally:
```bash
# Local
git add .
git commit -m "Description of changes"
git push origin main

# Server
ssh -C roseline.tsatsu@169.239.251.102 -p 422
cd ~/public_html/pharmavault
git pull origin main
```

If database changes, create a migration SQL file and run:
```bash
mysql -u username -p ecommerce_2025A_roseline_tsatsu < db/migration_YYYYMMDD.sql
```

---

**🎉 You're ready to deploy!**
