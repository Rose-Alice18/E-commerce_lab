<?php
/**
 * Medicine Availability Search Page
 * Search for medicines across all pharmacies with distance calculation
 */

session_start();
require_once('../settings/core.php');
require_once('../settings/google_maps_config.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Availability - PharmaVault</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            padding: 2rem;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 3rem 0;
            margin: -2rem -2rem 2rem -2rem;
        }

        .search-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 1rem 3.5rem 1rem 3.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            font-size: 1.1rem;
            transition: all 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 1.3rem;
        }

        .location-btn {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            padding: 0.6rem 1.2rem;
            background: var(--success);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .location-btn:hover {
            background: #059669;
            transform: translateY(-50%) scale(1.05);
        }

        .results-summary {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .medicine-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }

        .medicine-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .medicine-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .medicine-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 15px;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        }

        .medicine-info {
            flex: 1;
        }

        .medicine-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .pharmacy-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .price-tag {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--success);
        }

        .distance-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border-radius: 10px;
            font-weight: 600;
        }

        .stock-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border-radius: 10px;
            font-weight: 600;
        }

        .medicine-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
        }

        .detail-item i {
            color: var(--primary);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-view {
            flex: 1;
            padding: 0.8rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .btn-directions {
            flex: 1;
            padding: 0.8rem;
            background: white;
            color: var(--success);
            border: 2px solid var(--success);
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .btn-directions:hover {
            background: var(--success);
            color: white;
        }

        .loading {
            text-align: center;
            padding: 3rem;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .empty-icon {
            font-size: 5rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 1024px) {
            .main-content { margin-left: 70px; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; }
            .medicine-header { flex-direction: column; text-align: center; }
            .action-buttons { flex-direction: column; }
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
                <h1><i class="fas fa-pills"></i> Medicine Availability</h1>
                <p class="mb-0">Search for medicines across all partner pharmacies</p>
            </div>
        </div>

        <div class="container">
            <!-- Search Section -->
            <div class="search-section">
                <h3 class="mb-3">Search Medicine</h3>
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text"
                           id="searchInput"
                           class="search-input"
                           placeholder="Enter medicine name (e.g., Paracetamol, Ibuprofen)..."
                           autocomplete="off">
                    <button class="location-btn" id="useLocationBtn">
                        <i class="fas fa-location-arrow"></i> Use My Location
                    </button>
                </div>
                <div id="locationStatus" class="mt-2 text-muted small"></div>
            </div>

            <!-- Results Summary -->
            <div id="resultsSummary" style="display: none;" class="results-summary">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-0">
                            <i class="fas fa-search me-2"></i>
                            <span id="resultsCount">0</span> results found for "<span id="searchQuery"></span>"
                        </h4>
                        <p class="mb-0 mt-2 text-muted">
                            Available in <strong id="pharmacyCount">0</strong> pharmacies
                            • Total stock: <strong id="totalStock">0</strong> units
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Results
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loadingState" style="display: none;">
                <div class="loading">
                    <div class="loading-spinner"></div>
                    <p>Searching for medicine...</p>
                </div>
            </div>

            <!-- Results Container -->
            <div id="resultsContainer"></div>

            <!-- Initial State -->
            <div id="initialState" class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Search for Medicine Availability</h3>
                <p class="text-muted">
                    Enter a medicine name to see which pharmacies have it in stock<br>
                    and how far they are from your location
                </p>
            </div>
        </div>
    </div>

    <?php if (isLoggedIn() && isRegularCustomer()): ?>
        <script src="../js/sidebar.js"></script>
    <?php endif; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        let userLat = null;
        let userLng = null;
        let searchTimeout = null;

        const searchInput = document.getElementById('searchInput');
        const useLocationBtn = document.getElementById('useLocationBtn');
        const locationStatus = document.getElementById('locationStatus');
        const resultsContainer = document.getElementById('resultsContainer');
        const loadingState = document.getElementById('loadingState');
        const initialState = document.getElementById('initialState');
        const resultsSummary = document.getElementById('resultsSummary');

        // Search on input
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    searchMedicine(query);
                }, 500);
            } else {
                showInitialState();
            }
        });

        // Use location button
        useLocationBtn.addEventListener('click', function() {
            getUserLocation();
        });

        // Get user location
        function getUserLocation() {
            if (navigator.geolocation) {
                locationStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Getting your location...';
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        userLat = position.coords.latitude;
                        userLng = position.coords.longitude;
                        locationStatus.innerHTML = '<i class="fas fa-check-circle text-success"></i> Location detected! Results will show distances.';
                        useLocationBtn.style.background = '#10b981';

                        // Re-search if there's a query
                        const query = searchInput.value.trim();
                        if (query.length >= 2) {
                            searchMedicine(query);
                        }
                    },
                    (error) => {
                        locationStatus.innerHTML = '<i class="fas fa-exclamation-circle text-danger"></i> Could not get location. Enable location access in your browser.';
                    }
                );
            } else {
                locationStatus.innerHTML = '<i class="fas fa-exclamation-circle text-danger"></i> Geolocation is not supported by your browser.';
            }
        }

        // Search medicine
        function searchMedicine(query) {
            loadingState.style.display = 'block';
            resultsContainer.style.display = 'none';
            initialState.style.display = 'none';
            resultsSummary.style.display = 'none';

            let url = `../actions/search_medicine_availability.php?q=${encodeURIComponent(query)}`;
            if (userLat && userLng) {
                url += `&lat=${userLat}&lng=${userLng}`;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    loadingState.style.display = 'none';

                    if (data.success && data.results.length > 0) {
                        displayResults(data);
                    } else {
                        displayNoResults(query);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    loadingState.style.display = 'none';
                    resultsContainer.innerHTML = '<div class="alert alert-danger">Error searching for medicine. Please try again.</div>';
                    resultsContainer.style.display = 'block';
                });
        }

        // Display results
        function displayResults(data) {
            document.getElementById('searchQuery').textContent = data.query;
            document.getElementById('resultsCount').textContent = data.results.length;
            document.getElementById('pharmacyCount').textContent = data.total_pharmacies;
            document.getElementById('totalStock').textContent = data.total_stock;

            let html = '';
            data.results.forEach(item => {
                const distanceBadge = item.distance_km !== null
                    ? `<span class="distance-badge"><i class="fas fa-route"></i> ${item.distance_km} km away</span>`
                    : '';

                const directionsBtn = item.pharmacy.latitude && item.pharmacy.longitude
                    ? `<a href="https://www.google.com/maps/dir/?api=1&destination=${item.pharmacy.latitude},${item.pharmacy.longitude}"
                          target="_blank"
                          class="btn-directions">
                          <i class="fas fa-directions"></i> Get Directions
                       </a>`
                    : '';

                html += `
                    <div class="medicine-card">
                        <div class="medicine-header">
                            <img src="../${item.product_image || 'uploads/products/placeholder.png'}"
                                 alt="${item.product_title}"
                                 class="medicine-image"
                                 onerror="this.src='../uploads/products/placeholder.png'">
                            <div class="medicine-info">
                                <div class="medicine-title">${item.product_title}</div>
                                <span class="pharmacy-badge">
                                    <i class="fas fa-store"></i> ${item.pharmacy.name}
                                </span>
                            </div>
                            <div class="text-end">
                                <div class="price-tag">GH₵ ${parseFloat(item.product_price).toFixed(2)}</div>
                                ${distanceBadge}
                            </div>
                        </div>

                        <div class="medicine-details">
                            <div class="detail-item">
                                <i class="fas fa-boxes"></i>
                                <span><strong>Stock:</strong> ${item.product_stock} units available</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>${item.pharmacy.location}</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-phone"></i>
                                <span>${item.pharmacy.contact || 'N/A'}</span>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <a href="single_product.php?id=${item.product_id}" class="btn-view">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            ${directionsBtn}
                        </div>
                    </div>
                `;
            });

            resultsContainer.innerHTML = html;
            resultsContainer.style.display = 'block';
            resultsSummary.style.display = 'block';
        }

        // Display no results
        function displayNoResults(query) {
            resultsContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>No Results Found</h3>
                    <p class="text-muted">
                        No pharmacies have "${query}" in stock at the moment.<br>
                        Try searching with a different name or check back later.
                    </p>
                </div>
            `;
            resultsContainer.style.display = 'block';
        }

        // Show initial state
        function showInitialState() {
            resultsContainer.style.display = 'none';
            loadingState.style.display = 'none';
            resultsSummary.style.display = 'none';
            initialState.style.display = 'block';
        }
    </script>
</body>

</body>:
<?php
// Include chatbot for customer support
include 'components/chatbot.php';
?>

</html>
