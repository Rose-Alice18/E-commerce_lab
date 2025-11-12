<?php
/**
 * Product Display Page
 * PDF Requirement: Week 6 Activity - Part 2
 * "Display created products. Further functionality will follow in future labs."
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

// Get all products (for customers to browse)
require_once('../classes/product_class.php');
$product_obj = new Product();
$all_products = $product_obj->get_all_products();

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

// Apply filters
$filtered_products = $all_products;

if ($filter_category > 0) {
    $filtered_products = array_filter($filtered_products, function($product) use ($filter_category) {
        return $product['product_cat'] == $filter_category;
    });
}

if ($filter_brand > 0) {
    $filtered_products = array_filter($filtered_products, function($product) use ($filter_brand) {
        return $product['product_brand'] == $filter_brand;
    });
}

if (!empty($search_query)) {
    $filtered_products = array_filter($filtered_products, function($product) use ($search_query) {
        $search_lower = strtolower($search_query);
        return stripos($product['product_title'], $search_query) !== false ||
               stripos($product['product_desc'], $search_query) !== false ||
               stripos($product['product_keywords'], $search_query) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Products - PharmaVault</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebar.css?v=2.2">

    <!-- Cart & Wishlist CSS -->
    <link rel="stylesheet" href="../css/cart-wishlist.css">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --sidebar-width: 280px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        /* Main content container with sidebar spacing */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
            padding: 0;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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

        .filter-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
            display: block;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
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

        .product-image-placeholder {
            font-size: 4rem;
            color: #cbd5e1;
        }

        .product-body {
            padding: 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-category {
            font-size: 0.75rem;
            color: var(--primary-color);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .product-brand {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0.75rem;
        }

        .product-description {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 1rem;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--success-color);
        }

        .product-stock {
            font-size: 0.85rem;
            color: #64748b;
        }

        .stock-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .stock-in {
            background: #dcfce7;
            color: #166534;
        }

        .stock-low {
            background: #fef3c7;
            color: #92400e;
        }

        .stock-out {
            background: #fee2e2;
            color: #991b1b;
        }

        .no-products {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
        }

        .no-products i {
            font-size: 5rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }

        .no-products h3 {
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .no-products p {
            color: #94a3b8;
        }

        .btn-filter {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-clear {
            background: #f1f5f9;
            color: #64748b;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-clear:hover {
            background: #e2e8f0;
        }

        /* Responsive design for sidebar */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 70px !important;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
            }

            .page-header {
                padding: 2rem 0;
            }

            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1rem;
            }

            .filter-section .row {
                row-gap: 1rem;
            }
        }

        @media (max-width: 480px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Product Action Buttons */
        .product-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .btn-add-cart {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-add-cart:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-add-cart:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .btn-wishlist {
            background: white;
            color: #ef4444;
            border: 2px solid #fecaca;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .btn-wishlist:hover {
            background: #fef2f2;
            border-color: #ef4444;
            transform: scale(1.1);
        }

        .btn-wishlist.active {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
        }

        .btn-wishlist.active i {
            font-weight: 900;
        }

        /* Toast Notification */
        .toast-notification {
            position: fixed;
            top: 100px;
            right: 20px;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: slideIn 0.3s ease;
            min-width: 300px;
        }

        .toast-notification.success {
            border-left: 4px solid #10b981;
        }

        .toast-notification.error {
            border-left: 4px solid #ef4444;
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
    </style>
</head>
<body>
    <!-- Include Sidebar (only if user is logged in as customer) -->
    <?php if (isLoggedIn() && isRegularCustomer()): ?>
        <?php include('components/sidebar_customer.php'); ?>
    <?php endif; ?>

    <!-- Main Content Wrapper -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <h1><i class="fas fa-store"></i> Browse Products</h1>
                <p class="mb-0">Discover healthcare products from verified pharmacies across Ghana</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="product.php" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="filter-label">Search</label>
                    <input type="text" class="form-control" name="search"
                           placeholder="Search products..."
                           value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
                <div class="col-md-3">
                    <label class="filter-label">Category</label>
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
                <div class="col-md-3">
                    <label class="filter-label">Brand</label>
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
                <div class="col-md-3">
                    <button type="submit" class="btn btn-filter w-100">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>
            </form>
            <?php if ($filter_category > 0 || $filter_brand > 0 || !empty($search_query)): ?>
                <div class="mt-2">
                    <a href="product.php" class="btn btn-clear btn-sm">
                        <i class="fas fa-times me-1"></i>Clear Filters
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Products Count -->
        <div class="mb-3">
            <p class="text-muted">
                <strong><?php echo count($filtered_products); ?></strong>
                <?php echo count($filtered_products) == 1 ? 'product' : 'products'; ?> found
            </p>
        </div>

        <!-- Product Grid -->
        <?php if (count($filtered_products) > 0): ?>
            <div class="product-grid">
                <?php foreach ($filtered_products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['product_image'])): ?>
                                <img src="../<?php echo htmlspecialchars($product['product_image']); ?>"
                                     alt="<?php echo htmlspecialchars($product['product_title']); ?>">
                            <?php else: ?>
                                <i class="fas fa-pills product-image-placeholder"></i>
                            <?php endif; ?>
                        </div>
                        <div class="product-body">
                            <div class="product-category">
                                <i class="fas fa-tag me-1"></i>
                                <?php echo htmlspecialchars($product['cat_name'] ?? 'Uncategorized'); ?>
                            </div>
                            <h3 class="product-title">
                                <?php echo htmlspecialchars($product['product_title']); ?>
                            </h3>
                            <div class="product-brand">
                                <i class="fas fa-certificate me-1"></i>
                                <?php echo htmlspecialchars($product['brand_name'] ?? 'Generic'); ?>
                            </div>
                            <?php if (!empty($product['product_desc'])): ?>
                                <p class="product-description">
                                    <?php echo htmlspecialchars($product['product_desc']); ?>
                                </p>
                            <?php endif; ?>
                            <div class="product-footer">
                                <div>
                                    <div class="product-price">
                                        GH₵ <?php echo number_format($product['product_price'], 2); ?>
                                    </div>
                                    <div class="product-stock">
                                        <?php
                                        $stock = intval($product['product_stock'] ?? 0);
                                        if ($stock > 10):
                                        ?>
                                            <span class="stock-badge stock-in">
                                                <i class="fas fa-check-circle me-1"></i>In Stock
                                            </span>
                                        <?php elseif ($stock > 0): ?>
                                            <span class="stock-badge stock-low">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Low Stock
                                            </span>
                                        <?php else: ?>
                                            <span class="stock-badge stock-out">
                                                <i class="fas fa-times-circle me-1"></i>Out of Stock
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <?php if (isLoggedIn() && isRegularCustomer()): ?>
                                <div class="product-actions">
                                    <button class="btn-add-cart"
                                            data-product-id="<?php echo $product['product_id']; ?>"
                                            onclick="addToCart(<?php echo $product['product_id']; ?>)"
                                            <?php echo ($stock <= 0) ? 'disabled' : ''; ?>>
                                        <i class="fas fa-shopping-cart me-1"></i>
                                        <span>Add to Cart</span>
                                    </button>
                                    <button class="btn-wishlist"
                                            onclick="toggleWishlist(<?php echo $product['product_id']; ?>, this)"
                                            data-product-id="<?php echo $product['product_id']; ?>">
                                        <i class="far fa-heart"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-products">
                <i class="fas fa-search"></i>
                <h3>No Products Found</h3>
                <p>Try adjusting your filters or search criteria</p>
                <?php if ($filter_category > 0 || $filter_brand > 0 || !empty($search_query)): ?>
                    <a href="product.php" class="btn btn-filter mt-3">
                        View All Products
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        </div>

        <!-- Footer Spacing -->
        <div style="height: 3rem;"></div>
    </div>

    <!-- Sidebar JS (only if user is logged in as customer) -->
    <?php if (isLoggedIn() && isRegularCustomer()): ?>
        <script src="../js/sidebar.js"></script>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <!-- Cart and Wishlist Functions -->
    <script src="../js/cart-wishlist.js"></script>
</body>
</html>
