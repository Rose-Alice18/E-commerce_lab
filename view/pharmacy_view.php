<?php
/**
 * Single Pharmacy View
 * Display pharmacy details and their products
 */

session_start();
require_once('../settings/core.php');
require_once('../controllers/product_controller.php');
require_once('../settings/db_class.php');

$pharmacy_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($pharmacy_id <= 0) {
    header("Location: pharmacies.php");
    exit();
}

// Get pharmacy details
$db = new db_connection();
$conn = $db->db_conn();

$stmt = $conn->prepare("SELECT * FROM customer WHERE customer_id = ? AND user_role = 1");
$stmt->bind_param("i", $pharmacy_id);
$stmt->execute();
$result = $stmt->get_result();
$pharmacy = $result->fetch_assoc();

if (!$pharmacy) {
    header("Location: pharmacies.php");
    exit();
}

// Get pharmacy products
require_once('../classes/product_class.php');
$product_obj = new Product();
$products = $product_obj->get_products_by_pharmacy($pharmacy_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pharmacy['customer_name']); ?> - PharmaVault</title>

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
            --success: #10b981;
            --sidebar-width: <?php echo (isLoggedIn() && isRegularCustomer()) ? '280px' : '0px'; ?>;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }

        .pharmacy-banner {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }

        .pharmacy-logo-large {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            font-weight: 700;
            color: var(--primary);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            margin: 0 auto 1.5rem;
        }

        .pharmacy-logo-large img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .pharmacy-info-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .products-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .product-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            border-color: var(--primary);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        }

        .product-body {
            padding: 1.25rem;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--success);
            margin-bottom: 1rem;
        }

        .btn-add-cart {
            width: 100%;
            padding: 0.75rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-add-cart:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
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
            margin: 1rem 0;
        }

        .back-btn:hover {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary-dark);
        }

        /* Toast Notifications */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 99999;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 1024px) {
            .main-content { margin-left: 70px; }
        }

        /* Map Styles */
        .pharmacy-map-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .pharmacy-map-container {
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-top: 1rem;
        }

        .location-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .location-info i {
            font-size: 2rem;
            color: var(--primary);
        }

        .get-directions-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .get-directions-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
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

        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .products-grid { grid-template-columns: 1fr; }
            .pharmacy-map-container { height: 300px; }
        }
    </style>
</head>
<body>
    <?php if (isLoggedIn() && isRegularCustomer()): ?>
        <?php include('components/sidebar_customer.php'); ?>
    <?php endif; ?>

    <div class="main-content">
        <div class="container mt-3">
            <a href="pharmacies.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Pharmacies
            </a>
        </div>

        <!-- Pharmacy Banner -->
        <div class="pharmacy-banner">
            <div class="container text-center">
                <div class="pharmacy-logo-large">
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
                <h1 class="mb-2"><?php echo htmlspecialchars($pharmacy['customer_name']); ?></h1>
                <p class="mb-0"><i class="fas fa-store me-2"></i>Trusted Partner Pharmacy</p>
            </div>
        </div>

        <div class="container">
            <!-- Pharmacy Map Section -->
            <?php if (!empty($pharmacy['customer_latitude']) && !empty($pharmacy['customer_longitude'])): ?>
            <div class="pharmacy-map-section">
                <h3 class="mb-3"><i class="fas fa-map-marked-alt me-2"></i>Pharmacy Location</h3>
                <div class="location-info">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <strong><?php echo htmlspecialchars($pharmacy['customer_address'] ?? $pharmacy['customer_city'] . ', ' . $pharmacy['customer_country']); ?></strong>
                        <div class="text-muted small" id="distanceInfo"></div>
                    </div>
                </div>
                <div class="d-flex gap-2 mb-3">
                    <button class="btn-map-control" onclick="showUserLocationOnMap()">
                        <i class="fas fa-location-arrow"></i>Show My Location
                    </button>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo $pharmacy['customer_latitude']; ?>,<?php echo $pharmacy['customer_longitude']; ?>"
                       target="_blank"
                       class="get-directions-btn">
                        <i class="fas fa-directions"></i>Get Directions
                    </a>
                </div>
                <div id="singlePharmacyMap" class="pharmacy-map-container"></div>
            </div>
            <?php endif; ?>

            <!-- Pharmacy Information -->
            <div class="pharmacy-info-card">
                <h3 class="mb-3"><i class="fas fa-info-circle me-2"></i>Pharmacy Information</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Location</div>
                                <strong><?php echo htmlspecialchars($pharmacy['customer_city'] . ', ' . $pharmacy['customer_country']); ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Contact</div>
                                <strong><?php echo htmlspecialchars($pharmacy['customer_contact'] ?? 'N/A'); ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Email</div>
                                <strong><?php echo htmlspecialchars($pharmacy['customer_email']); ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Products Available</div>
                                <strong><?php echo count($products); ?> Products</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <div class="products-section">
                <h3 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Available Products</h3>

                <?php if (count($products) > 0): ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card" onclick="window.location.href='single_product.php?id=<?php echo $product['product_id']; ?>'">
                                <img src="../<?php echo !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : 'uploads/products/placeholder.png'; ?>"
                                     alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                     class="product-image"
                                     onerror="this.src='../uploads/products/placeholder.png'">
                                <div class="product-body">
                                    <div class="product-title"><?php echo htmlspecialchars($product['product_title']); ?></div>
                                    <div class="product-price">GH₵ <?php echo number_format($product['product_price'], 2); ?></div>
                                    <button class="btn-add-cart" onclick="event.stopPropagation(); addToCart(<?php echo $product['product_id']; ?>)">
                                        <i class="fas fa-cart-plus me-1"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-box-open" style="font-size: 5rem; color: #cbd5e1; margin-bottom: 1.5rem;"></i>
                        <h4>No Products Available</h4>
                        <p class="text-muted">This pharmacy hasn't listed any products yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="height: 3rem;"></div>
    </div>

    <?php if (isLoggedIn() && isRegularCustomer()): ?>
        <script src="../js/sidebar.js"></script>
    <?php endif; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../js/cart-wishlist.js"></script>

    <?php if (!empty($pharmacy['customer_latitude']) && !empty($pharmacy['customer_longitude'])): ?>
    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initSinglePharmacyMap" async defer></script>

    <!-- Google Maps Custom JS -->
    <script src="../js/google-maps.js"></script>

    <script>
        // Pharmacy data
        const pharmacyData = {
            id: <?php echo $pharmacy['customer_id']; ?>,
            name: <?php echo json_encode($pharmacy['customer_name']); ?>,
            lat: <?php echo $pharmacy['customer_latitude']; ?>,
            lng: <?php echo $pharmacy['customer_longitude']; ?>,
            address: <?php echo json_encode($pharmacy['customer_address'] ?? $pharmacy['customer_city'] . ', ' . $pharmacy['customer_country']); ?>,
            phone: <?php echo json_encode($pharmacy['customer_contact']); ?>,
            email: <?php echo json_encode($pharmacy['customer_email']); ?>
        };

        // Initialize the map when Google Maps API is loaded
        function initSinglePharmacyMap() {
            // Initialize map centered on pharmacy
            initMap('singlePharmacyMap', {
                center: { lat: pharmacyData.lat, lng: pharmacyData.lng },
                zoom: 15
            });

            // Add pharmacy marker
            addPharmacyMarker(pharmacyData);
        }

        // Show user's current location
        function showUserLocationOnMap() {
            getUserLocation(
                (lat, lng) => {
                    addUserLocationMarker(lat, lng);
                    showNotification('Your location has been detected!', 'success');

                    // Calculate and display distance
                    const distance = calculateDistance(lat, lng, pharmacyData.lat, pharmacyData.lng);
                    document.getElementById('distanceInfo').innerHTML =
                        `<i class="fas fa-route"></i> ${distance.toFixed(2)} km away from you`;

                    // Fit map to show both markers
                    fitMapToMarkers();
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
    <?php endif; ?>
</body>

</body>:
<?php
// Include chatbot for customer support
include 'components/chatbot.php';
?>
</html>
