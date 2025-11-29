# Google Maps Integration Guide for PharmaVault

This document explains how to set up and use the Google Maps integration for displaying pharmacy locations and calculating distances.

## Features

✅ **Interactive Maps** - Display all pharmacies on an interactive Google Map
✅ **Individual Pharmacy Maps** - Show specific pharmacy locations on detail pages
✅ **User Location Detection** - Get user's current location using browser geolocation
✅ **Distance Calculation** - Calculate and display distances between user and pharmacies
✅ **Get Directions** - Direct link to Google Maps for turn-by-turn directions
✅ **Custom Markers** - Beautiful custom markers for pharmacies and user location
✅ **Info Windows** - Rich popup information when clicking markers
✅ **Responsive Design** - Works perfectly on mobile, tablet, and desktop

## Setup Instructions

### Step 1: Get Google Maps API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the following APIs:
   - **Maps JavaScript API** (for displaying maps)
   - **Geocoding API** (optional, for address to coordinates conversion)
4. Create credentials:
   - Click "Create Credentials" → "API Key"
   - Copy the API key
5. **IMPORTANT**: Restrict your API key for security:
   - Click on the API key
   - Under "Application restrictions", select "HTTP referrers"
   - Add your domain(s): `yourdomain.com/*`, `localhost/*`
   - Under "API restrictions", select "Restrict key"
   - Select only the APIs you need

### Step 2: Configure API Key

1. Open `settings/google_maps_config.php`
2. Replace `YOUR_GOOGLE_MAPS_API_KEY` with your actual API key:

```php
define('GOOGLE_MAPS_API_KEY', 'AIzaSyC..._your_actual_key_here');
```

3. Optionally, adjust default map settings:
   - `DEFAULT_MAP_CENTER_LAT` - Default latitude (currently set to Accra, Ghana)
   - `DEFAULT_MAP_CENTER_LNG` - Default longitude
   - `DEFAULT_MAP_ZOOM` - Default zoom level (12 = city level)

### Step 3: Run Database Migration

Run the SQL script to add location fields to the database:

```sql
-- Navigate to phpMyAdmin or use command line
mysql -u root -p pharmavault_db < db/add_google_maps_support.sql
```

Or manually run the SQL in phpMyAdmin:
1. Open phpMyAdmin
2. Select `pharmavault_db` database
3. Go to SQL tab
4. Paste contents from `db/add_google_maps_support.sql`
5. Click "Go"

This adds the following fields to the `customer` table:
- `customer_address` - Full street address
- `customer_latitude` - Latitude coordinate
- `customer_longitude` - Longitude coordinate

### Step 4: Add Pharmacy Coordinates

You need to add coordinates for each pharmacy. There are several ways to do this:

#### Option A: Manual Entry via phpMyAdmin

1. Go to phpMyAdmin → pharmavault_db → customer table
2. Find pharmacies (user_role = 1)
3. Edit each pharmacy and add:
   - `customer_address` - Full address
   - `customer_latitude` - Latitude (e.g., 5.6037 for Accra)
   - `customer_longitude` - Longitude (e.g., -0.1870 for Accra)

#### Option B: Use Google Maps to Find Coordinates

1. Go to [Google Maps](https://www.google.com/maps)
2. Search for the pharmacy address
3. Right-click on the location
4. Click on the coordinates (e.g., "5.6037, -0.1870")
5. Coordinates are copied to clipboard
6. Paste into database

#### Option C: Use a Geocoding Service

For batch conversion of addresses to coordinates, you can use:
- Google Geocoding API
- OpenStreetMap Nominatim
- Other geocoding services

Example SQL update:
```sql
UPDATE customer
SET customer_address = '123 Main Street, Accra, Ghana',
    customer_latitude = 5.6037,
    customer_longitude = -0.1870
WHERE customer_id = 3 AND user_role = 1;
```

## File Structure

```
register_sample/
├── settings/
│   └── google_maps_config.php          # API key configuration
├── js/
│   └── google-maps.js                   # Google Maps JavaScript utilities
├── db/
│   └── add_google_maps_support.sql     # Database migration
├── view/
│   ├── pharmacies.php                   # Updated with map section
│   └── pharmacy_view.php                # Updated with individual map
└── docs/
    └── GOOGLE_MAPS_INTEGRATION.md      # This file
```

## Usage

### For End Users (Customers)

1. **View All Pharmacies on Map**:
   - Navigate to Pharmacies page
   - See all pharmacies displayed on an interactive map
   - Click on pharmacy markers to see details

2. **Show Your Location**:
   - Click "Show My Location" button
   - Allow browser location access when prompted
   - Your location appears as a green marker
   - Distances to pharmacies are automatically calculated

3. **Get Directions**:
   - Click on any pharmacy marker
   - Click "View Products" to go to pharmacy detail page
   - Click "Get Directions" button
   - Opens Google Maps with turn-by-turn directions

### For Pharmacy Admins

Pharmacy admins should ensure their location information is accurate:

1. Contact system administrator to update:
   - Full street address
   - Latitude coordinate
   - Longitude coordinate

2. Accurate coordinates ensure:
   - Customers can find your pharmacy
   - Distance calculations are correct
   - Directions work properly

## Features Explained

### Distance Calculation

The system uses the **Haversine formula** to calculate distances between two points on Earth:

- Accounts for Earth's curvature
- Returns distance in kilometers
- Accurate for distances up to ~20,000 km

Formula:
```
a = sin²(Δlat/2) + cos(lat1) × cos(lat2) × sin²(Δlong/2)
c = 2 × atan2(√a, √(1−a))
distance = R × c (where R = 6371 km)
```

### Geolocation

Uses the browser's built-in `navigator.geolocation` API:

- Requires user permission
- Works on HTTPS sites (or localhost)
- More accurate on mobile devices with GPS
- Falls back to IP-based location on desktop

### Custom Markers

- **Pharmacy Markers** (Purple): Show pharmacy locations
- **User Location Marker** (Green): Shows where you are
- **Info Windows**: Click markers for detailed information

## Troubleshooting

### Maps Not Showing

1. **Check API Key**:
   - Ensure you replaced `YOUR_GOOGLE_MAPS_API_KEY` in config
   - Verify API key is valid in Google Cloud Console
   - Check API key restrictions aren't blocking your domain

2. **Check Browser Console**:
   - Open Developer Tools (F12)
   - Look for errors in Console tab
   - Common errors:
     - `InvalidKeyMapError` - Invalid API key
     - `RefererNotAllowedMapError` - Domain not whitelisted

3. **Verify APIs are Enabled**:
   - Go to Google Cloud Console
   - APIs & Services → Library
   - Ensure "Maps JavaScript API" is enabled

### Location Not Detected

1. **Browser Permissions**:
   - User must allow location access
   - Check browser settings → Site permissions
   - Some browsers block geolocation on HTTP (use HTTPS)

2. **HTTPS Required**:
   - Modern browsers require HTTPS for geolocation
   - Works on `localhost` without HTTPS

### No Pharmacies on Map

1. **Check Database**:
   - Verify pharmacies have latitude/longitude values
   - Run: `SELECT * FROM customer WHERE user_role = 1`
   - Ensure coordinates are not NULL

2. **Check SQL Query**:
   - Verify query includes location fields
   - Check `view/pharmacies.php` line 19-22

### Distance Not Calculating

1. **Location Permission**:
   - User must click "Show My Location" first
   - Permission must be granted

2. **Coordinate Format**:
   - Latitude: -90 to 90
   - Longitude: -180 to 180
   - Use decimal degrees (not DMS format)

## API Usage and Costs

### Free Tier
Google Maps provides a generous free tier:
- **$200 free credit per month**
- Maps JavaScript API: ~28,000 loads/month free
- Geocoding API: ~40,000 requests/month free

### Cost After Free Tier
- Maps JavaScript API: $7 per 1,000 loads
- Geocoding API: $5 per 1,000 requests

### Best Practices to Minimize Costs
1. Restrict API key to your domain
2. Cache geocoding results in database
3. Use maps only where needed
4. Set up budget alerts in Google Cloud

## Security Best Practices

1. **Restrict API Key**:
   - Add HTTP referrer restrictions
   - Limit to specific APIs
   - Never commit API keys to public repositories

2. **Environment Variables** (Recommended):
   ```php
   // Instead of hardcoding, use environment variables
   define('GOOGLE_MAPS_API_KEY', getenv('GOOGLE_MAPS_API_KEY'));
   ```

3. **Rate Limiting**:
   - Implement rate limiting for location requests
   - Cache results where possible

## Future Enhancements

Potential features to add:

1. **Geocoding Integration**:
   - Auto-convert addresses to coordinates
   - Let pharmacy admins enter addresses only

2. **Pharmacy Search by Distance**:
   - "Find pharmacies within 5km"
   - Sort results by distance

3. **Clustering**:
   - Group nearby pharmacies into clusters
   - Improves performance with many pharmacies

4. **Route Visualization**:
   - Show route on map
   - Estimated travel time

5. **Multiple Location Support**:
   - Pharmacies with multiple branches
   - Show all branches on map

## Support

For issues or questions:
1. Check this documentation first
2. Review browser console for errors
3. Verify API key is valid
4. Check Google Cloud Console quotas
5. Contact system administrator

## Additional Resources

- [Google Maps JavaScript API Documentation](https://developers.google.com/maps/documentation/javascript)
- [Geocoding API Documentation](https://developers.google.com/maps/documentation/geocoding)
- [API Key Best Practices](https://developers.google.com/maps/api-security-best-practices)
- [Google Maps Platform Pricing](https://mapsplatform.google.com/pricing/)

---

**Last Updated**: November 2025
**Version**: 1.0
**Author**: PharmaVault Development Team
