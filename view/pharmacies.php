<?php
/**
 * Pharmacies Listing Page
 * Display all pharmacies for customers to browse
 */

session_start();
require_once('../settings/core.php');
require_once('../settings/db_class.php');

// Get all pharmacies (users with user_role = 1)
$db = new db_connection();
$conn = $db->db_conn();

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$location_filter = isset($_GET['location']) ? trim($_GET['location']) : '';

// Build query - include location fields
$sql = "SELECT customer_id, customer_name, customer_email, customer_contact,
               customer_country, customer_city, customer_image,
               customer_address, customer_latitude, customer_longitude
        FROM customer
        WHERE user_role = 1";

$params = [];
$types = "";

if (!empty($search_query)) {
    $sql .= " AND (customer_name LIKE ? OR customer_city LIKE ? OR customer_country LIKE ?)";
    $search_param = "%{$search_query}%";
    $params[] = &$search_param;
    $params[] = &$search_param;
    $params[] = &$search_param;
    $types .= "sss";
}

if (!empty($location_filter)) {
    $sql .= " AND (customer_city LIKE ? OR customer_country LIKE ?)";
    $location_param = "%{$location_filter}%";
    $params[] = &$location_param;
    $params[] = &$location_param;
    $types .= "ss";
}

$sql .= " ORDER BY customer_name ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    array_unshift($params, $types);
    call_user_func_array([$stmt, 'bind_param'], $params);
}
$stmt->execute();
$result = $stmt->get_result();
$pharmacies = $result->fetch_all(MYSQLI_ASSOC);

// Get unique locations for filter
$locations_sql = "SELECT DISTINCT customer_city, customer_country FROM customer WHERE user_role = 1 ORDER BY customer_country, customer_city";
$locations_result = $conn->query($locations_sql);
$locations = $locations_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Pharmacies - PharmaVault</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <?php
    // Load Google Maps configuration
    require_once('../settings/google_maps_config.php');
    ?>

    <?php if (isLoggedIn() && isRegularCustomer()): ?>
        <link rel="stylesheet" href="../css/sidebar.css?v=2.2">
    <?php endif; ?>

    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #764ba2;
            --sidebar-width: <?php echo (isLoggedIn() && isRegularCustomer()) ? '280px' : '0px'; ?>;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            padding: 0;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            transition: all 0.3s;
            margin: 1rem 2rem;
        }

        .back-btn:hover {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary-dark);
            transform: translateX(-5px);
        }

        .search-filter-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .pharmacies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .pharmacy-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s;
            cursor: pointer;
        }

        .pharmacy-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .pharmacy-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .pharmacy-logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .pharmacy-logo img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .pharmacy-name {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .pharmacy-body {
            padding: 1.5rem;
        }

        .pharmacy-info-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
        }

        .pharmacy-info-item:last-child {
            border-bottom: none;
        }

        .pharmacy-info-item i {
            color: var(--primary);
            width: 20px;
            text-align: center;
        }

        .pharmacy-actions {
            padding: 1.5rem;
            background: #f8f9fa;
            display: flex;
            gap: 1rem;
        }

        .btn-view-pharmacy {
            flex: 1;
            padding: 0.75rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-view-pharmacy:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .empty-state-icon {
            font-size: 5rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }

        /* Map Styles */
        .map-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .map-container {
            height: 500px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .map-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .btn-map-control {
            padding: 0.75rem 1.5rem;
            background: white;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-map-control:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .btn-map-control i {
            margin-right: 0.5rem;
        }

        .pharmacy-distance {
            display: none;
            color: var(--success);
            font-weight: 600;
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        @media (max-width: 1024px) {
            .main-content { margin-left: 70px; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; }
            .pharmacies-grid { grid-template-columns: 1fr; }
            .map-container { height: 400px; }
        }
    </style>
</head>
<body>
    <?php if (isLoggedIn() && isRegularCustomer()): ?>
        <?php include('components/sidebar_customer.php'); ?>
    <?php endif; ?>

    <div class="main-content">
        <div class="page-header">
            <div class="container">
                <h1><i class="fas fa-hospital"></i> Our Partner Pharmacies</h1>
                <p class="mb-0">Find trusted pharmacies near you</p>
            </div>
        </div>

        <div class="container">
            <!-- Map Section -->
            <div class="map-section">
                <h3 class="mb-3"><i class="fas fa-map-marked-alt me-2"></i>Pharmacy Locations</h3>
                <div class="map-controls">
                    <button class="btn-map-control" onclick="showUserLocation()">
                        <i class="fas fa-location-arrow"></i>Show My Location
                    </button>
                    <button class="btn-map-control" onclick="fitMapToMarkers()">
                        <i class="fas fa-expand"></i>View All Pharmacies
                    </button>
                </div>
                <div id="pharmacyMap" class="map-container"></div>
            </div>

            <!-- Search & Filter Section -->
            <div class="search-filter-section">
                <form method="GET" action="pharmacies.php" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Search Pharmacies</label>
                        <input type="text" class="form-control" name="search"
                               placeholder="Search by name or location..."
                               value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Filter by Location</label>
                        <select class="form-select" name="location">
                            <option value="">All Locations</option>
                            <?php
                            $seen = [];
                            foreach ($locations as $loc):
                                $location_str = $loc['customer_city'] . ', ' . $loc['customer_country'];
                                if (!in_array($location_str, $seen)):
                                    $seen[] = $location_str;
                            ?>
                                <option value="<?php echo htmlspecialchars($loc['customer_city']); ?>"
                                        <?php echo $location_filter == $loc['customer_city'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($location_str); ?>
                                </option>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>

            <!-- Pharmacies Grid -->
            <?php if (count($pharmacies) > 0): ?>
                <div class="pharmacies-grid">
                    <?php foreach ($pharmacies as $pharmacy): ?>
                        <div class="pharmacy-card" onclick="window.location.href='pharmacy_view.php?id=<?php echo $pharmacy['customer_id']; ?>'">
                            <div class="pharmacy-header">
                                <div class="pharmacy-logo">
                                    <?php if (!empty($pharmacy['customer_image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($pharmacy['customer_image']); ?>"
                                             alt="<?php echo htmlspecialchars($pharmacy['customer_name']); ?>">
                                    <?php else: ?>
                                        <?php
                                        $initials = '';
                                        $words = explode(' ', $pharmacy['customer_name']);
                                        foreach ($words as $word) {
                                            $initials .= strtoupper(substr($word, 0, 1));
                                        }
                                        echo substr($initials, 0, 2);
                                        ?>
                                    <?php endif; ?>
                                </div>
                                <div class="pharmacy-name"><?php echo htmlspecialchars($pharmacy['customer_name']); ?></div>
                            </div>
                            <div class="pharmacy-body">
                                <div class="pharmacy-info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($pharmacy['customer_city'] . ', ' . $pharmacy['customer_country']); ?></span>
                                </div>
                                <div class="pharmacy-info-item">
                                    <i class="fas fa-phone"></i>
                                    <span><?php echo htmlspecialchars($pharmacy['customer_contact'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="pharmacy-info-item">
                                    <i class="fas fa-envelope"></i>
                                    <span><?php echo htmlspecialchars($pharmacy['customer_email']); ?></span>
                                </div>
                            </div>
                            <div class="pharmacy-distance" data-pharmacy-id="<?php echo $pharmacy['customer_id']; ?>"></div>
                            <div class="pharmacy-actions">
                                <a href="pharmacy_view.php?id=<?php echo $pharmacy['customer_id']; ?>"
                                   class="btn-view-pharmacy"
                                   onclick="event.stopPropagation();">
                                    <i class="fas fa-store"></i> View Products
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>No Pharmacies Found</h3>
                    <p class="text-muted">
                        <?php if (!empty($search_query) || !empty($location_filter)): ?>
                            Try adjusting your search or filters
                        <?php else: ?>
                            No pharmacies are currently registered
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($search_query) || !empty($location_filter)): ?>
                        <a href="pharmacies.php" class="btn btn-primary mt-3">
                            <i class="fas fa-th"></i> View All Pharmacies
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isLoggedIn() && isRegularCustomer()): ?>
        <script src="../js/sidebar.js"></script>
    <?php endif; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initPharmacyMap" async defer></script>

    <!-- Google Maps Custom JS -->
    <script src="../js/google-maps.js"></script>

    <script>
        // Pharmacy data from PHP
        const pharmacies = <?php echo json_encode($pharmacies); ?>;

        // Initialize the map when Google Maps API is loaded
        function initPharmacyMap() {
            // Check if there are pharmacies with valid coordinates
            const pharmaciesWithCoords = pharmacies.filter(p => p.customer_latitude && p.customer_longitude);

            let centerLat = <?php echo DEFAULT_MAP_CENTER_LAT; ?>;
            let centerLng = <?php echo DEFAULT_MAP_CENTER_LNG; ?>;

            // If we have pharmacies with coordinates, center on the first one
            if (pharmaciesWithCoords.length > 0) {
                centerLat = parseFloat(pharmaciesWithCoords[0].customer_latitude);
                centerLng = parseFloat(pharmaciesWithCoords[0].customer_longitude);
            }

            // Initialize map
            initMap('pharmacyMap', {
                center: { lat: centerLat, lng: centerLng },
                zoom: <?php echo DEFAULT_MAP_ZOOM; ?>
            });

            // Add markers for all pharmacies
            pharmacies.forEach(pharmacy => {
                if (pharmacy.customer_latitude && pharmacy.customer_longitude) {
                    addPharmacyMarker({
                        id: pharmacy.customer_id,
                        name: pharmacy.customer_name,
                        lat: parseFloat(pharmacy.customer_latitude),
                        lng: parseFloat(pharmacy.customer_longitude),
                        address: pharmacy.customer_address || `${pharmacy.customer_city}, ${pharmacy.customer_country}`,
                        phone: pharmacy.customer_contact,
                        email: pharmacy.customer_email
                    });
                }
            });

            // Fit map to show all markers if we have multiple pharmacies
            if (pharmaciesWithCoords.length > 1) {
                fitMapToMarkers();
            }
        }

        // Show user's current location
        function showUserLocation() {
            getUserLocation(
                (lat, lng) => {
                    addUserLocationMarker(lat, lng);
                    showNotification('Your location has been detected!', 'success');

                    // Update distance for all pharmacies
                    pharmacies.forEach(pharmacy => {
                        if (pharmacy.customer_latitude && pharmacy.customer_longitude) {
                            updatePharmacyDistance(
                                pharmacy.customer_id,
                                parseFloat(pharmacy.customer_latitude),
                                parseFloat(pharmacy.customer_longitude)
                            );
                        }
                    });

                    // Re-add all markers to update info windows with distance
                    clearMarkers();
                    pharmacies.forEach(pharmacy => {
                        if (pharmacy.customer_latitude && pharmacy.customer_longitude) {
                            addPharmacyMarker({
                                id: pharmacy.customer_id,
                                name: pharmacy.customer_name,
                                lat: parseFloat(pharmacy.customer_latitude),
                                lng: parseFloat(pharmacy.customer_longitude),
                                address: pharmacy.customer_address || `${pharmacy.customer_city}, ${pharmacy.customer_country}`,
                                phone: pharmacy.customer_contact,
                                email: pharmacy.customer_email
                            });
                        }
                    });
                },
                (error) => {
                    let message = 'Unable to get your location. ';
                    if (error.code === error.PERMISSION_DENIED) {
                        message += 'Please allow location access in your browser.';
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        message += 'Location information is unavailable.';
                    } else if (error.code === error.TIMEOUT) {
                        message += 'Location request timed out.';
                    }
                    showNotification(message, 'error');
                }
            );
        }
    </script>
</body>
</html>
