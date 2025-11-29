# Google Maps Integration - Quick Start Guide

## 🚀 Quick Setup (5 minutes)

### Step 1: Get Your Google Maps API Key

1. Go to https://console.cloud.google.com/
2. Create a project (or select existing)
3. Enable **Maps JavaScript API**
4. Create an API Key

### Step 2: Configure Your API Key

Open `settings/google_maps_config.php` and replace:

```php
define('GOOGLE_MAPS_API_KEY', 'YOUR_ACTUAL_API_KEY_HERE');
```

### Step 3: Run Database Migration

In phpMyAdmin or MySQL command line:

```bash
# Option A: Command line
mysql -u root -p pharmavault_db < db/add_google_maps_support.sql

# Option B: phpMyAdmin
# - Select pharmavault_db database
# - Go to SQL tab
# - Paste contents from db/add_google_maps_support.sql
# - Click "Go"
```

### Step 4: Add Pharmacy Coordinates

For each pharmacy, add their location coordinates:

```sql
UPDATE customer
SET customer_address = 'Full Street Address',
    customer_latitude = 5.6037,    -- Replace with actual latitude
    customer_longitude = -0.1870   -- Replace with actual longitude
WHERE customer_id = X AND user_role = 1;
```

**How to find coordinates:**
1. Go to https://www.google.com/maps
2. Search for the pharmacy address
3. Right-click on the exact location
4. Click the coordinates (e.g., "5.6037, -0.1870")
5. Coordinates are copied to clipboard!

### Step 5: Test It!

1. Navigate to `/view/pharmacies.php`
2. You should see an interactive map with pharmacy markers
3. Click "Show My Location" to test geolocation
4. Click markers to see pharmacy details

## ✅ What You Get

- **Interactive Maps** on pharmacy listing page
- **Individual Maps** on each pharmacy detail page
- **Distance Calculation** from user to pharmacies
- **Get Directions** button linking to Google Maps
- **User Location Detection** with browser geolocation
- **Beautiful Custom Markers** for pharmacies and user location

## 📁 Files Created/Modified

### New Files:
- `settings/google_maps_config.php` - Configuration
- `js/google-maps.js` - JavaScript utilities
- `db/add_google_maps_support.sql` - Database migration
- `docs/GOOGLE_MAPS_INTEGRATION.md` - Full documentation
- `GOOGLE_MAPS_QUICK_START.md` - This file

### Modified Files:
- `view/pharmacies.php` - Added map section
- `view/pharmacy_view.php` - Added individual map

## 🔒 Security Tips

1. **Restrict your API key** in Google Cloud Console:
   - HTTP referrer restrictions: `yourdomain.com/*`
   - API restrictions: Only enable Maps JavaScript API

2. **For production**, use environment variables:
   ```php
   define('GOOGLE_MAPS_API_KEY', getenv('GOOGLE_MAPS_API_KEY'));
   ```

## 💰 Cost Information

- Google provides **$200 free credit monthly**
- This covers ~28,000 map loads per month
- For most small-to-medium sites, you'll stay within free tier

## 🐛 Troubleshooting

### Map not showing?
- Check browser console (F12) for errors
- Verify API key is correct
- Ensure APIs are enabled in Google Cloud Console

### Location not detected?
- User must allow location permission
- Requires HTTPS (or localhost)
- Check browser settings → Site permissions

### No pharmacies on map?
- Verify pharmacies have latitude/longitude in database
- Check: `SELECT * FROM customer WHERE user_role = 1`

## 📚 Full Documentation

For detailed information, see: `docs/GOOGLE_MAPS_INTEGRATION.md`

## 🎯 Example Coordinates for Ghana

- **Accra**: 5.6037, -0.1870
- **Kumasi**: 6.6885, -1.6244
- **Takoradi**: 4.8967, -1.7533
- **Tamale**: 9.4034, -0.8424

---

**Need Help?** Check the full documentation in `docs/GOOGLE_MAPS_INTEGRATION.md`
