<?php
// Temporary error display for debugging - REMOVE after fixing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug checkpoint 1
echo "<!-- Checkpoint 1: Session started -->\n";

// Include core functions and controllers
try {
    require_once('../settings/core.php');
    echo "<!-- Checkpoint 2: core.php loaded -->\n";
} catch (Exception $e) {
    die("ERROR loading core.php: " . $e->getMessage());
}

try {
    require_once('../controllers/product_controller.php');
    echo "<!-- Checkpoint 3: product_controller.php loaded -->\n";
} catch (Exception $e) {
    die("ERROR loading product_controller.php: " . $e->getMessage());
}

// Check if user is logged in
if (!function_exists('isLoggedIn')) {
    die("ERROR: isLoggedIn function not found");
}

if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit();
}

echo "<!-- Checkpoint 4: User is logged in -->\n";

// Check if user is CUSTOMER (role 2 only)
if (!function_exists('isRegularCustomer')) {
    die("ERROR: isRegularCustomer function not found");
}

if (!isRegularCustomer()) {
    header("Location: ../index.php");
    exit();
}

echo "<!-- Checkpoint 5: User is regular customer -->\n";

$user_id = getUserId();
$user_name = $_SESSION['user_name'] ?? getUserName();
$user_email = $_SESSION['user_email'] ?? '';
$user_city = $_SESSION['user_city'] ?? 'Ghana';
$user_role = getUserRoleName();

echo "<!-- Checkpoint 6: User data retrieved, ID: $user_id -->\n";

// Get customer stats from database
if (!function_exists('get_customer_stats_ctr')) {
    die("ERROR: get_customer_stats_ctr function not found in product_controller.php");
}

$stats_result = get_customer_stats_ctr($user_id);
echo "<!-- Checkpoint 7: Stats retrieved -->\n";
$stats = $stats_result['data'] ?? [];
$cart_items = $stats['cart_items'] ?? 0;
$total_orders = $stats['total_orders'] ?? 0;
$pending_deliveries = $stats['pending_deliveries'] ?? 0;
$wishlist_items = $stats['wishlist_items'] ?? 0;

// Get featured products from database (limit to 6)
$products_result = get_all_products_ctr(6);
$featured_products = $products_result['data'] ?? [];

// Get current hour for personalized greeting
$current_hour = date('H');
if ($current_hour < 12) {
    $greeting = "Good Morning";
    $greeting_icon = "☀️";
} elseif ($current_hour < 18) {
    $greeting = "Good Afternoon";
    $greeting_icon = "🌤️";
} else {
    $greeting = "Good Evening";
    $greeting_icon = "🌙";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - PharmaVault</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebar.css?v=2.5">
    <!-- Cart & Wishlist CSS -->
    <link rel="stylesheet" href="../css/cart-wishlist.css?v=1.0">

    <style>
        :root {
            --primary-color: #667eea;
            --primary-dark: #764ba2;
            --primary-light: #a78bfa;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
        }

        /* Search Hero Section */
        .search-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 3rem 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .search-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .search-hero h2 {
            color: white;
            margin: 0 0 1rem 0;
            font-size: 2rem;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .search-hero p {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        /* Search Bar */
        .search-container {
            display: flex;
            gap: 1rem;
            max-width: 800px;
            position: relative;
            z-index: 1;
        }

        .search-wrapper {
            flex: 1;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3.5rem;
            border: none;
            border-radius: 15px;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .search-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 1.25rem;
        }

        .search-btn {
            padding: 1rem 2rem;
            background: white;
            color: var(--primary-color);
            border: none;
            border-radius: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.3);
        }

        /* Prescription Upload Card */
        .prescription-card {
            background: linear-gradient(135deg, #a78bfa 0%, #8b5cf6 100%);
            padding: 2rem;
            border-radius: 20px;
            color: white;
            margin-bottom: 2rem;
            box-shadow: 0 8px 24px rgba(167, 139, 250, 0.3);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
        }

        .prescription-content h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.5rem;
        }

        .prescription-content p {
            margin: 0;
            opacity: 0.9;
        }

        .upload-btn {
            padding: 1rem 2rem;
            background: white;
            color: #8b5cf6;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.3);
        }

        /* Quick Stats */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-box:nth-child(1) .stat-icon { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-box:nth-child(2) .stat-icon { background: linear-gradient(135deg, #a78bfa, #8b5cf6); }
        .stat-box:nth-child(3) .stat-icon { background: linear-gradient(135deg, #c084fc, #9333ea); }
        .stat-box:nth-child(4) .stat-icon { background: linear-gradient(135deg, #d8b4fe, #a855f7); }

        .stat-info h4 {
            margin: 0;
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .stat-info p {
            margin: 0.25rem 0 0 0;
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
        }

        /* Products Section */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h3 {
            margin: 0;
            font-size: 1.5rem;
            color: #1e293b;
        }

        .view-all-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .view-all-link:hover {
            color: var(--primary-dark);
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: #cbd5e1;
            position: relative;
        }

        .product-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .product-info {
            padding: 1.25rem;
        }

        .product-category {
            color: #64748b;
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .product-name {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 0.5rem 0;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0.75rem 0;
        }

        .product-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-cart {
            flex: 1;
            padding: 0.75rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cart:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-wishlist {
            width: 45px;
            height: 45px;
            background: #f1f5f9;
            border: none;
            border-radius: 10px;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-wishlist:hover {
            background: #fee2e2;
            color: #ef4444;
            transform: translateY(-2px);
        }

        /* Categories Quick Access */
        .categories-strip {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding: 1rem 0;
            margin-bottom: 2rem;
            scrollbar-width: thin;
        }

        .category-chip {
            background: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            white-space: nowrap;
            font-weight: 600;
            color: #64748b;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .category-chip:hover,
        .category-chip.active {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        @media (max-width: 768px) {
            .search-container {
                flex-direction: column;
            }

            .prescription-card {
                flex-direction: column;
                text-align: center;
            }

            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include('../view/components/sidebar_customer.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Back to Home Link -->
        <div style="margin-bottom: 1rem;">
            <a href="../index.php" style="color: #667eea; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>

        <!-- Search Hero Section -->
        <div class="search-hero">
            <h2><?php echo $greeting_icon; ?> <?php echo $greeting; ?>, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>!</h2>
            <p>Welcome to PharmaVault - Search from thousands of quality healthcare products</p>

            <div class="search-container">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search for medicines, vitamins, supplements..." id="productSearch">
                </div>
                <button class="search-btn" onclick="searchProducts()">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>

        <!-- Prescription Upload Card -->
        <div class="prescription-card">
            <div class="prescription-content">
                <h3><i class="fas fa-file-medical"></i> Have a Prescription?</h3>
                <p>Upload your prescription and we'll prepare your order</p>
            </div>
            <button class="upload-btn" onclick="openPrescriptionUpload()">
                <i class="fas fa-cloud-upload-alt"></i> Upload Prescription
            </button>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-box">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h4>Cart Items</h4>
                    <p><?php echo $cart_items; ?></p>
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <h4>Total Orders</h4>
                    <p><?php echo $total_orders; ?></p>
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-icon">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <div class="stat-info">
                    <h4>Pending</h4>
                    <p><?php echo $pending_deliveries; ?></p>
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-info">
                    <h4>Wishlist</h4>
                    <p><?php echo $wishlist_items; ?></p>
                </div>
            </div>
        </div>

        <!-- Categories Quick Access -->
        <div class="categories-strip">
            <div class="category-chip active" onclick="filterCategory('all')">All Products</div>
            <div class="category-chip" onclick="filterCategory('pain-relief')">Pain Relief</div>
            <div class="category-chip" onclick="filterCategory('vitamins')">Vitamins</div>
            <div class="category-chip" onclick="filterCategory('supplements')">Supplements</div>
            <div class="category-chip" onclick="filterCategory('first-aid')">First Aid</div>
            <div class="category-chip" onclick="filterCategory('skincare')">Skincare</div>
            <div class="category-chip" onclick="filterCategory('healthcare')">Healthcare</div>
        </div>

        <!-- Featured Products -->
        <div class="section-header">
            <h3><i class="fas fa-star"></i> Featured Products</h3>
            <a href="../view/product.php" class="view-all-link">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="product-grid" id="productGrid">
            <?php if (empty($featured_products)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #64748b;">
                    <i class="fas fa-pills" style="font-size: 4rem; margin-bottom: 1rem; display: block; opacity: 0.3;"></i>
                    <h3 style="color: #64748b;">No products available yet</h3>
                    <p>Check back soon for new products from pharmacies!</p>
                </div>
            <?php else: ?>
                <?php foreach ($featured_products as $product): ?>
                    <div class="product-card" data-category="<?php echo strtolower($product['cat_name'] ?? 'general'); ?>">
                        <div class="product-image">
                            <?php if (!empty($product['product_image'])): ?>
                                <img src="../<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-capsules"></i>
                            <?php endif; ?>
                            <span class="product-badge">New</span>
                        </div>
                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($product['cat_name'] ?? 'General'); ?></div>
                            <h4 class="product-name"><?php echo htmlspecialchars($product['product_title']); ?></h4>
                            <div style="font-size: 0.8rem; color: #10b981; margin: 0.25rem 0;">
                                <i class="fas fa-store"></i> <?php echo htmlspecialchars($product['pharmacy_name'] ?? 'PharmaVault'); ?>
                            </div>
                            <div class="product-price">GHS <?php echo number_format($product['product_price'], 2); ?></div>
                            <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.75rem;">
                                <?php echo $product['product_stock'] > 0 ? "In Stock ({$product['product_stock']} available)" : "Out of Stock"; ?>
                            </div>
                            <div class="product-actions">
                                <button class="btn-cart" onclick="addToCart(<?php echo $product['product_id']; ?>)" <?php echo $product['product_stock'] <= 0 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                                <button class="btn-wishlist" onclick="addToWishlist(<?php echo $product['product_id']; ?>)">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js"></script>
    <!-- Cart and Wishlist JS (for sidebar counts) -->
    <script src="../js/cart-wishlist.js"></script>

    <script>
        function searchProducts() {
            const searchTerm = document.getElementById('productSearch').value;
            if (searchTerm.trim()) {
                // Implement search functionality
                console.log('Searching for:', searchTerm);
                alert('Search functionality will be implemented with backend');
            }
        }

        function openPrescriptionUpload() {
            // Create file input dynamically
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*,.pdf';
            input.onchange = (e) => {
                const file = e.target.files[0];
                if (file) {
                    alert('Prescription upload feature will be implemented with backend.\nFile: ' + file.name);
                    // Implement upload to server
                }
            };
            input.click();
        }

        function addToCart(productId) {
            console.log('Adding product to cart:', productId);

            // Send AJAX request to add to cart
            fetch('../actions/cart_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showToast('success', data.message);

                    // Update cart count in sidebar if it exists
                    updateCartCount();
                } else {
                    showToast('error', data.message || 'Failed to add to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'An error occurred. Please try again.');
            });
        }

        function showToast(type, message) {
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"
                   style="color: ${type === 'success' ? '#10b981' : '#ef4444'}; font-size: 1.5rem;"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function updateCartCount() {
            fetch('../actions/cart_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_count'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cartBadge = document.querySelector('.badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                    }
                }
            });
        }

        function addToWishlist(productId) {
            console.log('Adding product to wishlist:', productId);
            alert('Wishlist functionality will be implemented with backend');
        }

        function filterCategory(category) {
            const chips = document.querySelectorAll('.category-chip');
            const productGrid = document.getElementById('productGrid');

            chips.forEach(chip => chip.classList.remove('active'));
            event.target.classList.add('active');

            productGrid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #3b82f6;"></i><p>Loading...</p></div>';

            fetch(`../actions/get_products_by_category_action.php?category=${category}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        let html = '';
                        data.data.forEach(product => {
                            const inStock = product.product_stock > 0;
                            html += `
                                <div class="product-card">
                                    <div class="product-image">
                                        ${product.product_image ? `<img src="../${product.product_image}" alt="${product.product_title}" style="width: 100%; height: 100%; object-fit: cover;">` : '<i class="fas fa-capsules"></i>'}
                                        <span class="product-badge">New</span>
                                    </div>
                                    <div class="product-info">
                                        <div class="product-category">${product.cat_name || 'General'}</div>
                                        <h4 class="product-name">${product.product_title}</h4>
                                        <div style="font-size: 0.8rem; color: #10b981; margin: 0.25rem 0;">
                                            <i class="fas fa-store"></i> ${product.pharmacy_name || 'PharmaVault'}
                                        </div>
                                        <div class="product-price">GHS ${parseFloat(product.product_price).toFixed(2)}</div>
                                        <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.75rem;">
                                            ${inStock ? `In Stock (${product.product_stock} available)` : 'Out of Stock'}
                                        </div>
                                        <div class="product-actions">
                                            <button class="btn-cart" onclick="addToCart(${product.product_id})" ${!inStock ? 'disabled' : ''}>
                                                <i class="fas fa-cart-plus"></i> Add to Cart
                                            </button>
                                            <button class="btn-wishlist" onclick="addToWishlist(${product.product_id})">
                                                <i class="far fa-heart"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        productGrid.innerHTML = html;
                    } else {
                        productGrid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #64748b;"><i class="fas fa-pills" style="font-size: 4rem; margin-bottom: 1rem; display: block; opacity: 0.3;"></i><h3>No products in this category</h3></div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    productGrid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #ef4444;"><i class="fas fa-exclamation-triangle" style="font-size: 4rem; margin-bottom: 1rem; display: block;"></i><h3>Error loading products</h3></div>';
                });
        }

        // Enable search on Enter key
        document.getElementById('productSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchProducts();
            }
        });
    </script>
</body>
</html>
