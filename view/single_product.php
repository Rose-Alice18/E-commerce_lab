<?php
/**
 * Single Product View - Week 7 Activity
 * PDF Requirement: Full view of a single product with all details
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core functions
require_once('../settings/core.php');

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header("Location: all_product.php");
    exit();
}

// Get product details using PDF-required method
require_once('../classes/product_class.php');
$product_obj = new Product();
$product = $product_obj->view_single_product($product_id); // PDF requirement: view_single_product($id)

if (!$product) {
    header("Location: all_product.php");
    exit();
}

// Get product images
$product_images = $product_obj->get_product_images($product_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_title']); ?> - PharmaVault</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Sidebar CSS -->
    <?php if (isLoggedIn() && isRegularCustomer()): ?>
        <link rel="stylesheet" href="../css/sidebar.css?v=2.2">
    <?php endif; ?>

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
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
            padding: 2rem;
        }

        .product-container {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .product-images {
            padding: 2rem;
            background: #f8fafc;
        }

        .main-image {
            width: 100%;
            height: 400px;
            object-fit: contain;
            border-radius: 15px;
            background: white;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .image-thumbnails {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .thumbnail:hover,
        .thumbnail.active {
            border-color: var(--primary-color);
            transform: scale(1.05);
        }

        .product-details {
            padding: 2rem;
        }

        .product-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .product-title {
            font-size: 2rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 1rem;
        }

        .product-meta {
            display: flex;
            gap: 2rem;
            margin-bottom: 1.5rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            font-size: 0.9rem;
        }

        .meta-item i {
            color: var(--primary-color);
        }

        .product-price {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--success-color);
            margin: 1.5rem 0;
        }

        .product-description {
            color: #475569;
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .product-keywords {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .keyword-tag {
            padding: 0.5rem 1rem;
            background: #f1f5f9;
            border-radius: 20px;
            font-size: 0.875rem;
            color: #64748b;
        }

        .stock-status {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            margin-bottom: 2rem;
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

        .btn-add-cart {
            padding: 1rem 3rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.125rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-add-cart:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 70px !important;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
                padding: 1rem;
            }
            .product-title {
                font-size: 1.5rem;
            }
            .product-price {
                font-size: 2rem;
            }
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
        <!-- Back Link -->
        <div class="mb-3">
            <a href="all_product.php" style="color: #667eea; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-arrow-left"></i> Back to All Products
            </a>
        </div>

        <div class="product-container">
            <div class="row g-0">
                <!-- Product Images Column -->
                <div class="col-lg-6">
                    <div class="product-images">
                        <!-- Main Product Image (PDF Requirement) -->
                        <img src="../<?php echo !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : 'uploads/products/placeholder.png'; ?>"
                             alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                             class="main-image"
                             id="mainImage">

                        <!-- Additional Images Thumbnails -->
                        <?php if (!empty($product_images)): ?>
                            <div class="image-thumbnails">
                                <?php foreach ($product_images as $img): ?>
                                    <img src="../<?php echo htmlspecialchars($img['image_path']); ?>"
                                         alt="Product image"
                                         class="thumbnail <?php echo $img['is_primary'] ? 'active' : ''; ?>"
                                         onclick="changeMainImage(this.src)">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Details Column -->
                <div class="col-lg-6">
                    <div class="product-details">
                        <!-- Product Category (PDF Requirement) -->
                        <div class="product-badge">
                            <i class="fas fa-tag me-2"></i>
                            <?php echo htmlspecialchars($product['cat_name'] ?? 'Uncategorized'); ?>
                        </div>

                        <!-- Product Title (PDF Requirement) -->
                        <h1 class="product-title">
                            <?php echo htmlspecialchars($product['product_title']); ?>
                        </h1>

                        <!-- Product Meta Information -->
                        <div class="product-meta">
                            <!-- Product Brand (PDF Requirement) -->
                            <div class="meta-item">
                                <i class="fas fa-certificate"></i>
                                <span><strong>Brand:</strong> <?php echo htmlspecialchars($product['brand_name'] ?? 'Generic'); ?></span>
                            </div>

                            <!-- Product ID (PDF Requirement: used behind the scenes) -->
                            <div class="meta-item">
                                <i class="fas fa-barcode"></i>
                                <span><strong>ID:</strong> #<?php echo $product['product_id']; ?></span>
                            </div>
                        </div>

                        <!-- Product Price (PDF Requirement) -->
                        <div class="product-price">
                            GH₵ <?php echo number_format($product['product_price'], 2); ?>
                        </div>

                        <!-- Stock Status -->
                        <?php
                        $stock = intval($product['product_stock'] ?? 0);
                        if ($stock > 10):
                        ?>
                            <div class="stock-status stock-in">
                                <i class="fas fa-check-circle me-2"></i>
                                In Stock (<?php echo $stock; ?> available)
                            </div>
                        <?php elseif ($stock > 0): ?>
                            <div class="stock-status stock-low">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Low Stock (Only <?php echo $stock; ?> left)
                            </div>
                        <?php else: ?>
                            <div class="stock-status stock-out">
                                <i class="fas fa-times-circle me-2"></i>
                                Out of Stock
                            </div>
                        <?php endif; ?>

                        <!-- Product Description (PDF Requirement) -->
                        <?php if (!empty($product['product_desc'])): ?>
                            <div class="mb-4">
                                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: #1e293b;">
                                    <i class="fas fa-info-circle me-2" style="color: var(--primary-color);"></i>
                                    Description
                                </h3>
                                <p class="product-description">
                                    <?php echo nl2br(htmlspecialchars($product['product_desc'])); ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Product Keywords (PDF Requirement) -->
                        <?php if (!empty($product['product_keywords'])): ?>
                            <div class="mb-4">
                                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: #1e293b;">
                                    <i class="fas fa-tags me-2" style="color: var(--primary-color);"></i>
                                    Tags
                                </h3>
                                <div class="product-keywords">
                                    <?php
                                    $keywords = explode(',', $product['product_keywords']);
                                    foreach ($keywords as $keyword):
                                        $keyword = trim($keyword);
                                        if (!empty($keyword)):
                                    ?>
                                        <span class="keyword-tag">
                                            <i class="fas fa-hashtag me-1"></i>
                                            <?php echo htmlspecialchars($keyword); ?>
                                        </span>
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Add to Cart Button (PDF Requirement: placeholder for now) -->
                        <button class="btn-add-cart"
                                <?php echo $stock <= 0 ? 'disabled' : ''; ?>
                                onclick="addToCart(<?php echo $product['product_id']; ?>)">
                            <i class="fas fa-cart-plus me-2"></i>
                            <?php echo $stock > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div style="height: 3rem;"></div>
    </div>

    <!-- Sidebar JS -->
    <?php if (isLoggedIn() && isRegularCustomer()): ?>
        <script src="../js/sidebar.js"></script>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        // Change main image when thumbnail is clicked
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;

            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        // Add to cart placeholder function
        function addToCart(productId) {
            alert('Cart functionality will be implemented in future labs.\nProduct ID: ' + productId);
            // This will be replaced with actual cart functionality later
        }
    </script>
</body>
</html>
