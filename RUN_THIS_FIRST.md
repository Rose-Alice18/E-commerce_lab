# ⚠️ IMPORTANT: Run Database Migration First!

Before you can use the Google Maps integration, you need to run the database migration.

## Quick Fix (Choose One Method):

### Method 1: Run via Browser (Easiest)
1. Open your browser
2. Navigate to: `http://localhost/register_sample/db/run_migration.php`
3. You should see "✓ DATABASE MIGRATION COMPLETED SUCCESSFULLY!"

### Method 2: Run via phpMyAdmin
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select `pharmavault_db` database
3. Click on "SQL" tab
4. Copy and paste the contents from `db/add_google_maps_support.sql`
5. Click "Go"

### Method 3: Run via Command Line (if MySQL is in PATH)
```bash
mysql -u root -p pharmavault_db < db/add_google_maps_support.sql
```

## After Running Migration:

1. **Add Your Google Maps API Key**
   - Open `settings/google_maps_config.php`
   - Replace `YOUR_GOOGLE_MAPS_API_KEY` with your actual key
   - Get key from: https://console.cloud.google.com/

2. **Update Pharmacy Coordinates** (Optional but recommended)
   - Use `db/sample_pharmacy_coordinates.sql` as a template
   - Find real coordinates using Google Maps
   - Update each pharmacy with their actual location

3. **Test the Integration**
   - Visit `http://localhost/register_sample/view/pharmacies.php`
   - You should see an interactive map with pharmacy markers!

## What the Migration Does:

- Adds `customer_address` column (TEXT)
- Adds `customer_latitude` column (DECIMAL)
- Adds `customer_longitude` column (DECIMAL)
- Creates an index for faster location queries
- Sets default Accra coordinates for existing pharmacies

---

**Need Help?** Check `GOOGLE_MAPS_QUICK_START.md` or `docs/GOOGLE_MAPS_INTEGRATION.md`
