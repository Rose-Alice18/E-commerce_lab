<?php
/**
 * All Products Page - Week 7 Activity
 * PDF Requirement: Display all products with filtering by category and brand
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core functions and controllers
require_once('../settings/core.php');
require_once('../controllers/product_controller.php');
require_once('../controllers/category_controller.php');
require_once('../controllers/brand_controller.php');

// Get all products using PDF-required method
require_once('../classes/product_class.php');
$product_obj = new Product();

// Get categories and brands for filtering
require_once('../classes/category_class.php');
require_once('../classes/brand_class.php');
$category_obj = new Category();
$brand_obj = new Brand();
$categories = $category_obj->fetch_all_categories();
$brands = $brand_obj->get_all_brands(null);

// Get filter parameters
$filter_category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$filter_brand = isset($_GET['brand']) ? intval($_GET['brand']) : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Apply filters using PDF-required methods
// Priority: Search > Category > Brand > All Products
if (!empty($search_query)) {
    // If there's a search query, use search_products method
    $all_products = $product_obj->search_products($search_query);
} elseif ($filter_category > 0) {
    // Filter by category
    $all_products = $product_obj->filter_products_by_category($filter_category);
} elseif ($filter_brand > 0) {
    // Filter by brand
    $all_products = $product_obj->filter_products_by_brand($filter_brand);
} else {
    // Show all products
    $all_products = $product_obj->view_all_products();
}

// Pagination (PDF requirement: if more than 10 products)
$products_per_page = 10;
$total_products = count($all_products);
$total_pages = ceil($total_products / $products_per_page);
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $products_per_page;
$products_to_display = array_slice($all_products, $offset, $products_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - PharmaVault</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Sidebar CSS -->
    <?php if (isLoggedIn() && isRegularCustomer()): ?>
        <link rel="stylesheet" href="../css/sidebar.css?v=2.2">
    <?php endif; ?>

    <!-- Google Maps API -->
    <?php
    require_once('../settings/google_maps_config.php');
    $google_maps_api_key = GOOGLE_MAPS_API_KEY;
    ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $google_maps_api_key; ?>&libraries=places"></script>
    <script src="../js/google-maps.js"></script>

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #10b981;
            --sidebar-width: <?php echo (isLoggedIn() && isRegularCustomer()) ? '280px' : '0px'; ?>;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
            padding: 0;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-body {
            padding: 1.25rem;
        }

        .product-category {
            font-size: 0.75rem;
            color: var(--primary-color);
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .product-brand {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0.75rem;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--success-color);
            margin-bottom: 1rem;
        }

        .btn-add-cart {
            width: 100%;
            padding: 0.75rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-add-cart:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .pagination {
            justify-content: center;
            margin: 2rem 0;
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 70px !important;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
            }
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
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
            min-width: 300px;
            max-width: 500px;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        /* Pharmacy Information Styles */
        .pharmacy-info {
            background: #f8fafc;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
        }

        .pharmacy-name {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pharmacy-location {
            color: #64748b;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .pharmacy-distance {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.5rem;
        }

        .pharmacy-contact {
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.25rem;
        }

        .location-btn {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 10px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .location-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }

        .location-btn i {
            font-size: 1rem;
        }

        .location-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f0fdf4;
            border-radius: 8px;
            color: var(--success-color);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .calculating-distances {
            display: none;
            padding: 0.5rem 1rem;
            background: #eff6ff;
            border-radius: 8px;
            color: #3b82f6;
            font-weight: 600;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar (only if logged in as customer) -->
    <?php if (isLoggedIn() && isRegularCustomer()): ?>
        <?php include('components/sidebar_customer.php'); ?>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Back Button -->
        <div style="padding: 1rem 2rem;">
            <button onclick="goBack()" style="color: #667eea; background: none; border: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 1rem; padding: 0.5rem 0; transition: all 0.3s ease;" onmouseover="this.style.color='#764ba2'" onmouseout="this.style.color='#667eea'">
                <i class="fas fa-arrow-left"></i> Back
            </button>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <h1><i class="fas fa-store"></i> All Products</h1>
                <p class="mb-0">Browse our complete collection of healthcare products</p>
            </div>
        </div>

        <div class="container">
            <!-- Filter Section (PDF Requirement: dropdowns for category and brand + search) -->
            <div class="filter-section">
                <!-- Location Section -->
                <div class="row mb-3">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <button type="button" class="location-btn" id="useLocationBtn" onclick="enableUserLocation()">
                            <i class="fas fa-crosshairs"></i> Use My Location
                        </button>
                        <span class="location-status" id="locationStatus" style="display: none;">
                            <i class="fas fa-check-circle"></i> Location detected
                        </span>
                        <span class="calculating-distances" id="calculatingStatus">
                            <i class="fas fa-spinner fa-spin"></i> Calculating distances...
                        </span>
                    </div>
                </div>

                <form method="GET" action="all_product.php" class="row g-3 align-items-end">
                    <!-- Search Box -->
                    <div class="col-md-12 mb-2">
                        <label class="form-label fw-bold">Search Products</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="search"
                                   placeholder="Search by product name, description, or keywords..."
                                   value="<?php echo htmlspecialchars($search_query); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Filter by Category</label>
                        <select class="form-select" name="category">
                            <option value="0">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['cat_id']; ?>"
                                        <?php echo $filter_category == $category['cat_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['cat_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Brand Filter -->
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Filter by Brand</label>
                        <select class="form-select" name="brand">
                            <option value="0">All Brands</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['brand_id']; ?>"
                                        <?php echo $filter_brand == $brand['brand_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Apply/Clear Buttons -->
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-filter"></i> Apply
                        </button>
                        <?php if ($filter_category > 0 || $filter_brand > 0 || !empty($search_query)): ?>
                            <a href="all_product.php" class="btn btn-secondary w-100">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Products Count -->
            <div class="mb-3">
                <p class="text-muted">
                    <strong><?php echo $total_products; ?></strong>
                    <?php echo $total_products == 1 ? 'product' : 'products'; ?>
                    <?php if (!empty($search_query)): ?>
                        found for "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                    <?php elseif ($filter_category > 0): ?>
                        in selected category
                    <?php elseif ($filter_brand > 0): ?>
                        for selected brand
                    <?php else: ?>
                        found
                    <?php endif; ?>
                    <?php if ($total_pages > 1): ?>
                        (Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>)
                    <?php endif; ?>
                </p>
            </div>

            <!-- Product Grid (PDF Requirement: Display product info) -->
            <?php if (count($products_to_display) > 0): ?>
                <div class="product-grid">
                    <?php foreach ($products_to_display as $product): ?>
                        <div class="product-card" onclick="window.location.href='single_product.php?id=<?php echo $product['product_id']; ?>'"
                             data-pharmacy-lat="<?php echo htmlspecialchars($product['pharmacy_latitude'] ?? ''); ?>"
                             data-pharmacy-lng="<?php echo htmlspecialchars($product['pharmacy_longitude'] ?? ''); ?>"
                             data-product-id="<?php echo $product['product_id']; ?>">
                            <!-- Product Image (PDF Requirement) -->
                            <div class="product-image">
                                <?php if (!empty($product['product_image'])): ?>
                                    <img src="../<?php echo htmlspecialchars($product['product_image']); ?>"
                                         alt="<?php echo htmlspecialchars($product['product_title']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-pills" style="font-size: 4rem; color: #cbd5e1;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="product-body">
                                <!-- Product Category (PDF Requirement) -->
                                <div class="product-category">
                                    <i class="fas fa-tag me-1"></i>
                                    <?php echo htmlspecialchars($product['cat_name'] ?? 'Uncategorized'); ?>
                                </div>

                                <!-- Product Title (PDF Requirement) -->
                                <h3 class="product-title">
                                    <?php echo htmlspecialchars($product['product_title']); ?>
                                </h3>

                                <!-- Product Brand (PDF Requirement) -->
                                <div class="product-brand">
                                    <i class="fas fa-certificate me-1"></i>
                                    <?php echo htmlspecialchars($product['brand_name'] ?? 'Generic'); ?>
                                </div>

                                <!-- Pharmacy Information -->
                                <div class="pharmacy-info">
                                    <div class="pharmacy-name">
                                        <i class="fas fa-hospital"></i>
                                        <?php echo htmlspecialchars($product['pharmacy_name'] ?? 'Unknown Pharmacy'); ?>
                                    </div>
                                    <?php if (!empty($product['pharmacy_city'])): ?>
                                        <div class="pharmacy-location">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php
                                            $location_parts = [];
                                            if (!empty($product['pharmacy_city'])) {
                                                $location_parts[] = $product['pharmacy_city'];
                                            }
                                            if (!empty($product['pharmacy_country'])) {
                                                $location_parts[] = $product['pharmacy_country'];
                                            }
                                            echo htmlspecialchars(implode(', ', $location_parts));
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($product['pharmacy_contact'])): ?>
                                        <div class="pharmacy-contact">
                                            <i class="fas fa-phone"></i>
                                            <?php echo htmlspecialchars($product['pharmacy_contact']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Distance will be calculated and inserted here by JavaScript -->
                                    <div class="distance-container-<?php echo $product['product_id']; ?>"></div>
                                </div>

                                <!-- Product Price (PDF Requirement) -->
                                <div class="product-price">
                                    GH₵ <?php echo number_format($product['product_price'], 2); ?>
                                </div>

                                <!-- Product ID hidden for URL purposes (PDF Requirement) -->
                                <input type="hidden" value="<?php echo $product['product_id']; ?>">

                                <!-- Add to Cart Button -->
                                <button class="btn-add-cart" onclick="event.stopPropagation(); addToCart(<?php echo $product['product_id']; ?>);">
                                    <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination (PDF Requirement: if more than 10 products) -->
                <?php if ($total_pages > 1): ?>
                    <?php
                    // Build query string for pagination
                    $query_params = [];
                    if (!empty($search_query)) $query_params[] = 'search=' . urlencode($search_query);
                    if ($filter_category > 0) $query_params[] = 'category=' . $filter_category;
                    if ($filter_brand > 0) $query_params[] = 'brand=' . $filter_brand;
                    $query_string = !empty($query_params) ? '&' . implode('&', $query_params) : '';
                    ?>
                    <nav aria-label="Product pagination">
                        <ul class="pagination">
                            <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo $query_string; ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $query_string; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo $query_string; ?>">
                                        Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center p-5">
                    <i class="fas fa-search" style="font-size: 5rem; color: #cbd5e1; margin-bottom: 1.5rem;"></i>
                    <h3 style="color: #64748b;">No Products Found</h3>
                    <?php if (!empty($search_query)): ?>
                        <p style="color: #94a3b8;">
                            No products found matching "<strong><?php echo htmlspecialchars($search_query); ?></strong>"<br>
                            Try different keywords or browse all products
                        </p>
                    <?php else: ?>
                        <p style="color: #94a3b8;">Try adjusting your filters or search with different criteria</p>
                    <?php endif; ?>
                    <?php if ($filter_category > 0 || $filter_brand > 0 || !empty($search_query)): ?>
                        <a href="all_product.php" class="btn btn-primary mt-3">
                            <i class="fas fa-th"></i> View All Products
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="height: 3rem;"></div>
    </div>

    <!-- Sidebar JS -->
    <?php if (isLoggedIn() && isRegularCustomer()): ?>
        <script src="../js/sidebar.js"></script>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <!-- Cart & Wishlist JS -->
    <script src="../js/cart-wishlist.js"></script>

    <!-- Back Button Functionality -->
    <script>
        function goBack() {
            // Check if there's history to go back to
            if (document.referrer && document.referrer !== "") {
                window.history.back();
            } else {
                // No history, go to home page
                window.location.href = '../index.php';
            }
        }
    </script>

    <!-- Location and Distance Calculation -->
    <script>
        let userLocation = null;

        // Function to enable user location
        function enableUserLocation() {
            const btn = document.getElementById('useLocationBtn');
            const statusEl = document.getElementById('locationStatus');
            const calculatingEl = document.getElementById('calculatingStatus');

            // Update button state
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Getting location...';
            btn.disabled = true;

            // Get user location using the Google Maps utility
            getUserLocation()
                .then(position => {
                    userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    // Update UI
                    btn.style.display = 'none';
                    statusEl.style.display = 'inline-flex';
                    calculatingEl.style.display = 'inline-flex';

                    // Calculate and display distances
                    calculateAllDistances();
                })
                .catch(error => {
                    console.error('Error getting location:', error);

                    // Reset button
                    btn.innerHTML = '<i class="fas fa-crosshairs"></i> Use My Location';
                    btn.disabled = false;

                    // Show error message
                    showToast('error', 'Location Error', 'Unable to get your location. Please check your browser permissions.');
                });
        }

        // Function to calculate distances for all products
        function calculateAllDistances() {
            if (!userLocation) return;

            const productCards = document.querySelectorAll('.product-card');
            let processedCount = 0;

            productCards.forEach(card => {
                const pharmacyLat = parseFloat(card.dataset.pharmacyLat);
                const pharmacyLng = parseFloat(card.dataset.pharmacyLng);
                const productId = card.dataset.productId;

                if (pharmacyLat && pharmacyLng && !isNaN(pharmacyLat) && !isNaN(pharmacyLng)) {
                    // Calculate distance using the Haversine formula from google-maps.js
                    const distance = calculateDistance(
                        userLocation.lat,
                        userLocation.lng,
                        pharmacyLat,
                        pharmacyLng
                    );

                    // Display the distance in the product card
                    const distanceContainer = card.querySelector('.distance-container-' + productId);
                    if (distanceContainer) {
                        distanceContainer.innerHTML = `
                            <div class="pharmacy-distance">
                                <i class="fas fa-route"></i>
                                ${distance.toFixed(1)} km away
                            </div>
                        `;
                    }
                }

                processedCount++;
            });

            // Hide calculating status
            setTimeout(() => {
                document.getElementById('calculatingStatus').style.display = 'none';
            }, 500);
        }

        // Helper function to show toast notifications
        function showToast(type, title, message) {
            const toast = document.createElement('div');
            toast.className = 'toast-notification';

            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            const color = type === 'success' ? '#10b981' : '#ef4444';

            toast.innerHTML = `
                <i class="fas ${icon}" style="font-size: 1.5rem; color: ${color};"></i>
                <div>
                    <strong style="display: block; margin-bottom: 0.25rem;">${title}</strong>
                    <span style="color: #64748b; font-size: 0.9rem;">${message}</span>
                </div>
            `;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        // Auto-enable location if user previously granted permission
        document.addEventListener('DOMContentLoaded', function() {
            // Check if geolocation is available
            if ('geolocation' in navigator) {
                // Try to get location without prompting if already granted
                navigator.permissions.query({ name: 'geolocation' }).then(function(result) {
                    if (result.state === 'granted') {
                        // Permission already granted, auto-enable location
                        enableUserLocation();
                    }
                }).catch(function(error) {
                    // Permissions API not supported, do nothing
                    console.log('Permissions API not supported');
                });
            }
        });
    </script>
</body>
</html>
