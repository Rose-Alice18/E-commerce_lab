# PharmaVault - Deployment Guide to Live Server

## Server Information
- **Host**: 169.239.251.102
- **SSH Port**: 422
- **SSH User**: roseline.tsatsu
- **SSH Command**: `ssh -C roseline.tsatsu@169.239.251.102 -p 422`

---

## Pre-Deployment Checklist

### 1. Local Preparation
- ✅ All code changes committed to Git
- ✅ Database schema finalized (`db/pharmavault_production.sql`)
- ✅ Tested locally on XAMPP
- ✅ Verified all features work (categories, brands, products)
- ✅ `.gitignore` configured properly

### 2. What NOT to Commit to Git
Make sure these are in your `.gitignore`:
```
/uploads/*
!/uploads/.htaccess
settings/db_cred.php
.env
*.log
```

---

## Deployment Steps

### Step 1: Commit and Push Your Code

```bash
# Navigate to your project directory
cd c:\xampp\htdocs\register_sample

# Check current status
git status

# Add all changes
git add .

# Commit with descriptive message
git commit -m "Production ready: Role-based categories/brands, color themes, responsive design"

# Push to remote repository
git push origin main
```

### Step 2: SSH into Your Server

```bash
# Connect to server
ssh -C roseline.tsatsu@169.239.251.102 -p 422

# Enter your password when prompted
```

### Step 3: Navigate to Web Directory

```bash
# Find your web root directory (common locations):
cd ~/public_html
# OR
cd ~/www
# OR
cd /var/www/html

# List directories to confirm location
ls -la
```

### Step 4: Clone or Pull from Git

#### If this is first deployment (Clone):
```bash
# Clone your repository
git clone https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git pharmavault

# Navigate into directory
cd pharmavault
```

#### If updating existing deployment (Pull):
```bash
# Navigate to existing directory
cd pharmavault

# Pull latest changes
git pull origin main
```

### Step 5: Set Up Directory Permissions

```bash
# Create uploads directory if it doesn't exist
mkdir -p uploads/products
mkdir -p logs

# Set proper permissions
chmod 755 uploads
chmod 755 uploads/products
chmod 755 logs

# If needed, set ownership (replace 'www-data' with your server's web user)
# chown -R www-data:www-data uploads
# chown -R www-data:www-data logs
```

### Step 6: Deploy Database

```bash
# Option 1: Using MySQL command line
mysql -u your_db_username -p your_database_name < db/pharmavault_production.sql

# Option 2: Using phpMyAdmin
# 1. Access phpMyAdmin on your server
# 2. Select your database
# 3. Go to "Import" tab
# 4. Upload db/pharmavault_production.sql
# 5. Click "Go"
```

### Step 7: Verify Database Connection

```bash
# Test database credentials
php -r "
\$conn = new mysqli('localhost', 'your_username', 'your_password', 'pharmavault_db');
if (\$conn->connect_error) {
    die('Connection failed: ' . \$conn->connect_error);
}
echo 'Database connection successful!';
\$conn->close();
"
```

### Step 8: Configure Web Server

#### For Apache (.htaccess)
Create/verify `.htaccess` in root directory:

```apache
# Enable rewrite engine
RewriteEngine On

# Redirect to HTTPS (if SSL is configured)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Prevent directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "^(db_cred\.php|\.git.*|\.env)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# PHP security settings
php_flag display_errors Off
php_flag log_errors On
php_value error_log logs/php_errors.log
```

### Step 9: Test the Deployment

1. **Visit your website**: http://169.239.251.102/pharmavault (or your domain)
2. **Test login pages**: `/login/login.php`
3. **Test all three account types**:
   - Super Admin: admin@pharmavault.com / Password@123
   - Pharmacy Admin: pharmacy@pharmavault.com / Password@123
   - Customer: customer@pharmavault.com / Password@123
4. **Test key features**:
   - Category management (verify role-based colors)
   - Brand management (verify super admin sees all, pharmacy admin sees only theirs)
   - Product management
   - Dashboard statistics
   - Responsive design (mobile, tablet, desktop)

### Step 10: Security Hardening

```bash
# Change default passwords immediately!
# 1. Log in as super admin
# 2. Go to profile settings
# 3. Change password

# Restrict file permissions
chmod 644 settings/db_cred.php
chmod 755 actions/*.php
chmod 755 admin/*.php

# Remove unnecessary files
rm -f db/pharmavault_production.sql  # After successful import
rm -f DEPLOYMENT_GUIDE.md  # Optional, after reading
```

---

## Updating the Live Site (Future Changes)

### Local Development
1. Make changes locally
2. Test thoroughly on XAMPP
3. Commit to Git:
   ```bash
   git add .
   git commit -m "Description of changes"
   git push origin main
   ```

### Server Update
1. SSH into server:
   ```bash
   ssh -C roseline.tsatsu@169.239.251.102 -p 422
   ```

2. Navigate to project:
   ```bash
   cd ~/public_html/pharmavault  # Adjust path as needed
   ```

3. Pull latest changes:
   ```bash
   git pull origin main
   ```

4. If database changes, run SQL:
   ```bash
   mysql -u your_username -p your_database < db/update_YYYYMMDD.sql
   ```

5. Clear cache if needed:
   ```bash
   php -r "opcache_reset();"  # If OpCache is enabled
   ```

---

## Quick Git Commands Reference

```bash
# Check status of files
git status

# View changes before committing
git diff

# Add specific files
git add file1.php file2.php

# Add all changes
git add .

# Commit with message
git commit -m "Your message here"

# Push to remote
git push origin main

# Pull from remote
git pull origin main

# View commit history
git log --oneline

# Discard local changes (BE CAREFUL!)
git checkout -- filename.php

# Create new branch for features
git checkout -b feature-name
```

---

## Troubleshooting

### Issue: Database connection failed
**Solution**:
- Verify credentials in `settings/db_cred.php`
- Check if database exists: `mysql -u username -p -e "SHOW DATABASES;"`
- Ensure MySQL service is running: `sudo systemctl status mysql`

### Issue: Permission denied on uploads
**Solution**:
```bash
chmod -R 755 uploads
chown -R www-data:www-data uploads  # Replace www-data with your web user
```

### Issue: 500 Internal Server Error
**Solution**:
- Check error logs: `tail -f logs/php_errors.log`
- Check Apache logs: `tail -f /var/log/apache2/error.log`
- Verify `.htaccess` syntax
- Check PHP version compatibility (requires PHP 7.4+)

### Issue: Git pull conflicts
**Solution**:
```bash
# Stash local changes
git stash

# Pull from remote
git pull origin main

# Reapply stashed changes
git stash pop

# Or discard local changes and use remote version
git reset --hard origin/main
```

### Issue: Changes not reflecting
**Solution**:
- Clear browser cache (Ctrl+Shift+R)
- Clear PHP OpCache if enabled
- Verify file permissions are correct
- Check if correct branch is deployed

---

## Important Files on Server

```
pharmavault/                    # Your project root
├── settings/
│   ├── db_cred.php            # Database credentials (DO NOT commit!)
│   ├── core.php               # Session & auth functions
│   └── db_class.php           # Database connection class
├── uploads/                   # User uploaded files
│   └── products/              # Product images
├── logs/                      # Error logs
│   └── php_errors.log         # PHP error log
├── admin/                     # Admin pages
│   ├── dashboard.php          # Super admin dashboard
│   ├── pharmacy_dashboard.php # Pharmacy admin dashboard
│   ├── category.php           # Category management
│   ├── brand.php              # Brand management
│   └── *.php                  # Other admin pages
├── actions/                   # Backend API endpoints
├── db/                        # Database files
│   └── pharmavault_production.sql  # Production schema
└── .htaccess                  # Apache configuration
```

---

## Post-Deployment Checklist

- [ ] Website accessible via browser
- [ ] All three user roles can log in
- [ ] Super admin sees purple theme on category/brand pages
- [ ] Pharmacy admin sees green theme on category/brand pages
- [ ] Super admin can see ALL categories/brands
- [ ] Pharmacy admin can see only THEIR categories/brands
- [ ] Image uploads work correctly
- [ ] Dashboard shows real statistics
- [ ] Responsive design works on mobile
- [ ] Default passwords changed
- [ ] SSL certificate installed (HTTPS)
- [ ] Automatic backups configured
- [ ] Error logging enabled
- [ ] `.gitignore` properly configured

---

## Support & Resources

- **SSH Issues**: Contact hosting provider for firewall/port access
- **Database Issues**: Check phpMyAdmin or contact DB admin
- **PHP Errors**: Check `logs/php_errors.log`
- **Git Issues**: Check `.git/config` for remote URL

---

## Backup Strategy

### Daily Backup Command (Run via cron)
```bash
#!/bin/bash
# backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR=~/backups/pharmavault

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u username -ppassword pharmavault_db > $BACKUP_DIR/db_$DATE.sql

# Backup uploaded files
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz uploads/

# Keep only last 7 days of backups
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: $DATE"
```

### Add to crontab for daily 2 AM backup:
```bash
crontab -e
# Add this line:
0 2 * * * /path/to/backup.sh >> ~/backups/backup.log 2>&1
```

---

**Good luck with your deployment! 🚀**
