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

        /* Reviews Section Styles */
        .reviews-section {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .rating-summary {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            display: flex;
            gap: 3rem;
            align-items: center;
        }

        .rating-overview {
            text-align: center;
        }

        .average-rating {
            font-size: 3.5rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1;
        }

        .stars {
            color: #f59e0b;
            font-size: 1.5rem;
            margin: 0.5rem 0;
        }

        .total-reviews {
            color: #64748b;
            font-size: 0.9rem;
        }

        .rating-breakdown {
            flex: 1;
        }

        .rating-bar-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }

        .rating-bar-label {
            min-width: 60px;
            color: #64748b;
            font-size: 0.9rem;
        }

        .rating-bar {
            flex: 1;
            height: 8px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .rating-bar-fill {
            height: 100%;
            background: #f59e0b;
            transition: width 0.3s ease;
        }

        .rating-bar-count {
            min-width: 40px;
            text-align: right;
            color: #64748b;
            font-size: 0.9rem;
        }

        .write-review-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .write-review-section h4 {
            margin-bottom: 1.5rem;
            color: #1e293b;
        }

        .stars-input {
            display: flex;
            gap: 0.5rem;
            font-size: 2rem;
        }

        .stars-input i {
            cursor: pointer;
            color: #cbd5e1;
            transition: all 0.2s;
        }

        .stars-input i:hover,
        .stars-input i.active {
            color: #f59e0b;
            transform: scale(1.1);
        }

        .review-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .review-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .reviewer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .reviewer-details h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
        }

        .review-date {
            font-size: 0.85rem;
            color: #64748b;
        }

        .review-rating {
            color: #f59e0b;
        }

        .verified-badge {
            background: #d1fae5;
            color: #065f46;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .review-content h6 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .review-text {
            color: #475569;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .review-actions {
            display: flex;
            gap: 1rem;
        }

        .review-action-btn {
            background: none;
            border: none;
            color: #64748b;
            font-size: 0.9rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.2s;
        }

        .review-action-btn:hover {
            color: var(--primary-color);
        }

        /* Recommendations Section Styles */
        .recommendations-section {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .recommendation-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .recommendation-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            border-color: var(--primary-color);
        }

        .recommendation-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        }

        .recommendation-body {
            padding: 1rem;
        }

        .recommendation-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .recommendation-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--success-color);
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

        <!-- Product Reviews Section (+3 MARKS) -->
        <div class="container mt-5">
            <div class="reviews-section">
                <h2 class="section-title">
                    <i class="fas fa-star"></i> Customer Reviews
                </h2>

                <!-- Rating Summary -->
                <div class="rating-summary" id="ratingSummary">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading ratings...</span>
                    </div>
                </div>

                <!-- Write Review Form (if logged in and can review) -->
                <?php if (isLoggedIn() && isRegularCustomer()): ?>
                <div class="write-review-section" id="writeReviewSection" style="display: none;">
                    <h4><i class="fas fa-pencil-alt"></i> Write a Review</h4>
                    <form id="reviewForm">
                        <div class="star-rating-input mb-3">
                            <label class="form-label">Your Rating *</label>
                            <div class="stars-input">
                                <i class="far fa-star" data-rating="1"></i>
                                <i class="far fa-star" data-rating="2"></i>
                                <i class="far fa-star" data-rating="3"></i>
                                <i class="far fa-star" data-rating="4"></i>
                                <i class="far fa-star" data-rating="5"></i>
                            </div>
                            <input type="hidden" id="ratingInput" name="rating" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Review Title</label>
                            <input type="text" class="form-control" id="reviewTitle" placeholder="Sum up your experience">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Your Review</label>
                            <textarea class="form-control" id="reviewText" rows="4" placeholder="Share your thoughts about this product..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Review
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Please <a href="../login/login_view.php">login</a> to leave a review.
                </div>
                <?php endif; ?>

                <!-- Reviews List -->
                <div class="reviews-list" id="reviewsList">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading reviews...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Recommendations Section - BONUS FEATURE (+5 MARKS) -->
        <div class="container mt-5">
            <div class="recommendations-section">
                <h2 class="section-title">
                    <i class="fas fa-magic"></i> You May Also Like
                </h2>
                <p class="text-muted mb-4">Based on your viewing history and similar products</p>

                <div id="recommendations-grid" class="product-grid">
                    <!-- Loading state -->
                    <div class="col-12 text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading recommendations...</span>
                        </div>
                        <p class="text-muted mt-2">Finding similar products...</p>
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

        // Load Product Recommendations (BONUS FEATURE - +5 MARKS)
        document.addEventListener('DOMContentLoaded', function() {
            loadRecommendations();
        });

        function loadRecommendations() {
            const productId = <?php echo $product_id; ?>;
            const grid = document.getElementById('recommendations-grid');

            fetch(`../actions/get_recommendations_action.php?product_id=${productId}&limit=4`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        displayRecommendations(data.data);
                    } else {
                        grid.innerHTML = `
                            <div class="col-12 text-center py-4">
                                <p class="text-muted">No similar products found</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading recommendations:', error);
                    grid.innerHTML = `
                        <div class="col-12 text-center py-4">
                            <p class="text-muted">Unable to load recommendations</p>
                        </div>
                    `;
                });
        }

        function displayRecommendations(products) {
            const grid = document.getElementById('recommendations-grid');
            grid.innerHTML = products.map(product => `
                <div class="recommendation-card" onclick="window.location.href='single_product.php?id=${product.product_id}'">
                    <img src="../${product.product_image || 'uploads/products/placeholder.png'}"
                         alt="${escapeHtml(product.product_title)}"
                         class="recommendation-image"
                         onerror="this.src='../uploads/products/placeholder.png'">
                    <div class="recommendation-body">
                        <div class="recommendation-title">${escapeHtml(product.product_title)}</div>
                        <div class="recommendation-price">GH₵ ${parseFloat(product.product_price).toFixed(2)}</div>
                    </div>
                </div>
            `).join('');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Load Reviews (+3 MARKS)
        loadProductReviews();

        function loadProductReviews() {
            const productId = <?php echo $product_id; ?>;

            // Load rating summary
            fetch(`../actions/review_actions.php?action=get_rating&product_id=${productId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        displayRatingSummary(data.data);
                    }
                });

            // Load reviews list
            fetch(`../actions/review_actions.php?action=get_reviews&product_id=${productId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        displayReviews(data.data);
                    }
                });

            // Check if user can review
            <?php if (isLoggedIn() && isRegularCustomer()): ?>
            fetch(`../actions/review_actions.php?action=can_review&product_id=${productId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.can_review) {
                        document.getElementById('writeReviewSection').style.display = 'block';
                        initReviewForm();
                    }
                });
            <?php endif; ?>
        }

        function displayRatingSummary(data) {
            const summary = document.getElementById('ratingSummary');
            const dist = data.distribution || {};
            const total = data.count || 0;

            if (total === 0) {
                summary.innerHTML = `
                    <div class="text-center py-4 w-100">
                        <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                    </div>
                `;
                return;
            }

            summary.innerHTML = `
                <div class="rating-overview">
                    <div class="average-rating">${data.average.toFixed(1)}</div>
                    <div class="stars">${getStarsHTML(data.average)}</div>
                    <div class="total-reviews">${total} review${total !== 1 ? 's' : ''}</div>
                </div>
                <div class="rating-breakdown">
                    ${[5,4,3,2,1].map(rating => {
                        const count = dist[rating] || 0;
                        const percentage = total > 0 ? (count / total * 100) : 0;
                        return `
                            <div class="rating-bar-row">
                                <div class="rating-bar-label">${rating} stars</div>
                                <div class="rating-bar">
                                    <div class="rating-bar-fill" style="width: ${percentage}%"></div>
                                </div>
                                <div class="rating-bar-count">${count}</div>
                            </div>
                        `;
                    }).join('')}
                </div>
            `;
        }

        function displayReviews(reviews) {
            const list = document.getElementById('reviewsList');

            if (reviews.length === 0) {
                list.innerHTML = '<p class="text-muted text-center py-4">No reviews yet.</p>';
                return;
            }

            list.innerHTML = reviews.map(review => `
                <div class="review-card">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">${getInitials(review.customer_name)}</div>
                            <div class="reviewer-details">
                                <h5>${escapeHtml(review.customer_name)}</h5>
                                <div class="review-date">${formatDate(review.created_at)}</div>
                            </div>
                        </div>
                        <div>
                            <div class="review-rating">${getStarsHTML(review.rating)}</div>
                            ${review.is_verified_purchase ? '<span class="verified-badge mt-2"><i class="fas fa-check-circle"></i> Verified Purchase</span>' : ''}
                        </div>
                    </div>
                    <div class="review-content">
                        ${review.review_title ? `<h6>${escapeHtml(review.review_title)}</h6>` : ''}
                        <div class="review-text">${escapeHtml(review.review_text)}</div>
                    </div>
                    <div class="review-actions">
                        <button class="review-action-btn" onclick="markHelpful(${review.review_id})">
                            <i class="far fa-thumbs-up"></i> Helpful ${review.helpful_count > 0 ? '(' + review.helpful_count + ')' : ''}
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function getStarsHTML(rating) {
            let html = '';
            for (let i = 1; i <= 5; i++) {
                html += `<i class="fas fa-star" style="color: ${i <= rating ? '#f59e0b' : '#cbd5e1'}"></i>`;
            }
            return html;
        }

        function getInitials(name) {
            return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }

        <?php if (isLoggedIn() && isRegularCustomer()): ?>
        function initReviewForm() {
            const stars = document.querySelectorAll('.stars-input i');
            const ratingInput = document.getElementById('ratingInput');

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.dataset.rating;
                    ratingInput.value = rating;

                    stars.forEach((s, index) => {
                        if (index < rating) {
                            s.classList.remove('far');
                            s.classList.add('fas', 'active');
                        } else {
                            s.classList.remove('fas', 'active');
                            s.classList.add('far');
                        }
                    });
                });
            });

            document.getElementById('reviewForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const rating = parseInt(ratingInput.value);
                if (rating === 0) {
                    showToast('Please select a rating', 'error');
                    return;
                }

                const formData = new FormData();
                formData.append('action', 'add_review');
                formData.append('product_id', <?php echo $product_id; ?>);
                formData.append('rating', rating);
                formData.append('review_title', document.getElementById('reviewTitle').value);
                formData.append('review_text', document.getElementById('reviewText').value);

                fetch('../actions/review_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        document.getElementById('writeReviewSection').style.display = 'none';
                        loadProductReviews(); // Reload reviews
                    } else {
                        showToast(data.message, 'error');
                    }
                });
            });
        }

        function markHelpful(reviewId) {
            const formData = new FormData();
            formData.append('action', 'mark_helpful');
            formData.append('review_id', reviewId);

            fetch('../actions/review_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    loadProductReviews();
                } else {
                    showToast(data.message || 'Error', 'error');
                }
            });
        }
        <?php endif; ?>
    </script>

    <!-- Cart & Wishlist JS -->
    <script src="../js/cart-wishlist.js"></script>
</body>
</html>
