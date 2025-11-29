# Features to Add to PharmaVault Platform for More Marks

**Goal:** Enhance the platform with high-impact features that will impress evaluators and earn bonus marks.

**Time Available:** 4-5 days until Nov 30
**Impact:** +5 to +15 additional marks possible

---

## 🔍 Current Status Analysis

### ✅ What You Already Have (Working)
- User authentication (login/register)
- Product browsing and search
- Shopping cart (add, remove, update)
- Checkout with Paystack
- Prescription upload
- Wishlist system
- Admin dashboards (3 roles)
- Order management
- Category/brand management

### ❌ What's NOT Working (Priority Fixes)
Based on the code I reviewed:
1. **Wishlist button functionality** - Shows "alert" instead of actually working
2. **Search functionality** - Shows "alert" instead of searching
3. **Product recommendations** - Not implemented (bonus +5 marks)
4. **Order tracking** - Basic functionality missing
5. **Customer reviews** - Not implemented
6. **Email notifications** - Not implemented

---

## 🎯 HIGH PRIORITY: Quick Wins (1-2 hours each)

### 1. Fix Wishlist Functionality ✅ (Already 80% done!)

**Current Problem:** Line 728 in customer_dashboard.php shows `alert('Wishlist functionality will be implemented')`

**Fix:** Update the JavaScript to use the existing `wishlist_actions.php`

**Implementation:**
```javascript
// Replace line 726-729 in customer_dashboard.php with:
function addToWishlist(productId) {
    console.log('Adding product to wishlist:', productId);

    fetch('../actions/wishlist_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=toggle&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);

            // Update wishlist count in sidebar
            updateWishlistCount();

            // Toggle heart icon to filled
            const heartBtn = event.target.closest('.btn-wishlist');
            const heartIcon = heartBtn.querySelector('i');
            if (data.in_wishlist) {
                heartIcon.classList.remove('far');
                heartIcon.classList.add('fas');
                heartIcon.style.color = '#ef4444';
            } else {
                heartIcon.classList.remove('fas');
                heartIcon.classList.add('far');
                heartIcon.style.color = '';
            }
        } else {
            showToast('error', data.message || 'Failed to update wishlist');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'An error occurred. Please try again.');
    });
}

function updateWishlistCount() {
    fetch('../actions/wishlist_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_count'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update wishlist badge in sidebar if it exists
            const wishlistBadge = document.querySelector('.wishlist-count');
            if (wishlistBadge) {
                wishlistBadge.textContent = data.wishlist_count;
            }
        }
    });
}
```

**Why This Matters:** Shows attention to detail and complete implementation.

---

### 2. Implement Real Product Search (2 hours)

**Current Problem:** Line 651 shows `alert('Search functionality will be implemented')`

**Fix:** Create search action and connect to frontend

**Step 1:** Create `actions/search_products_action.php`
```php
<?php
require_once('../controllers/product_controller.php');

header('Content-Type: application/json');

$search_term = $_GET['search'] ?? '';

if (empty($search_term)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a search term']);
    exit();
}

// Search products by title, description, or keywords
$result = search_products_ctr($search_term);

if ($result) {
    echo json_encode([
        'success' => true,
        'data' => $result,
        'count' => count($result)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No products found',
        'data' => []
    ]);
}
?>
```

**Step 2:** Add search method in `controllers/product_controller.php`
```php
function search_products_ctr($search_term) {
    $product = new product_class();
    return $product->search_products($search_term);
}
```

**Step 3:** Add search method in `classes/product_class.php` (assuming you have one)
```php
public function search_products($search_term) {
    $search_term = $this->db_conn()->real_escape_string($search_term);

    $sql = "SELECT p.*, c.cat_name, b.brand_name, cu.customer_name as pharmacy_name
            FROM products p
            LEFT JOIN categories c ON p.product_cat = c.cat_id
            LEFT JOIN brands b ON p.product_brand = b.brand_id
            LEFT JOIN customer cu ON p.pharmacy_id = cu.customer_id
            WHERE p.product_title LIKE '%$search_term%'
               OR p.product_desc LIKE '%$search_term%'
               OR p.product_keywords LIKE '%$search_term%'
               OR c.cat_name LIKE '%$search_term%'
               OR b.brand_name LIKE '%$search_term%'
            ORDER BY p.product_title ASC";

    $result = $this->db_query($sql);

    if ($result) {
        return $this->db_fetch_all($result);
    }
    return [];
}
```

**Step 4:** Update JavaScript in `customer_dashboard.php` (line 648-655)
```javascript
function searchProducts() {
    const searchTerm = document.getElementById('productSearch').value;

    if (searchTerm.trim()) {
        const productGrid = document.getElementById('productGrid');
        productGrid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #667eea;"></i><p>Searching...</p></div>';

        fetch(`../actions/search_products_action.php?search=${encodeURIComponent(searchTerm)}`)
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
                                    <span class="product-badge">Found</span>
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
                    showToast('success', `Found ${data.count} products`);
                } else {
                    productGrid.innerHTML = `
                        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #64748b;">
                            <i class="fas fa-search" style="font-size: 4rem; margin-bottom: 1rem; display: block; opacity: 0.3;"></i>
                            <h3>No products found for "${searchTerm}"</h3>
                            <p>Try different keywords or browse categories</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                productGrid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #ef4444;"><i class="fas fa-exclamation-triangle" style="font-size: 4rem; margin-bottom: 1rem; display: block;"></i><h3>Error searching products</h3></div>';
            });
    }
}
```

**Why This Matters:** Core e-commerce functionality that's expected.

---

### 3. Add Product Recommendations (BONUS +5 MARKS) 🎁

**Time:** 3-4 hours
**Impact:** +5 bonus marks for AI/recommendation feature

**Implementation Option: Simple Content-Based Recommendations**

**Step 1:** Create `actions/get_recommendations_action.php`
```php
<?php
require_once('../controllers/product_controller.php');

header('Content-Type: application/json');

$product_id = intval($_GET['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit();
}

// Get product recommendations based on category and brand
$result = get_product_recommendations_ctr($product_id, 4);

if ($result) {
    echo json_encode([
        'success' => true,
        'data' => $result
    ]);
} else {
    echo json_encode([
        'success' => false,
        'data' => []
    ]);
}
?>
```

**Step 2:** Add to `controllers/product_controller.php`
```php
function get_product_recommendations_ctr($product_id, $limit = 4) {
    $product = new product_class();
    return $product->get_recommendations($product_id, $limit);
}
```

**Step 3:** Add to `classes/product_class.php`
```php
public function get_recommendations($product_id, $limit = 4) {
    $product_id = intval($product_id);

    // First, get the current product's category and brand
    $sql = "SELECT product_cat, product_brand FROM products WHERE product_id = $product_id";
    $result = $this->db_query($sql);
    $current_product = $this->db_fetch_one($result);

    if (!$current_product) {
        return [];
    }

    $category_id = $current_product['product_cat'];
    $brand_id = $current_product['product_brand'];

    // Content-based recommendation:
    // Prioritize same category + same brand > same category > same brand
    $sql = "SELECT p.*, c.cat_name, b.brand_name, cu.customer_name as pharmacy_name,
            CASE
                WHEN p.product_cat = $category_id AND p.product_brand = $brand_id THEN 3
                WHEN p.product_cat = $category_id THEN 2
                WHEN p.product_brand = $brand_id THEN 1
                ELSE 0
            END as relevance_score
            FROM products p
            LEFT JOIN categories c ON p.product_cat = c.cat_id
            LEFT JOIN brands b ON p.product_brand = b.brand_id
            LEFT JOIN customer cu ON p.pharmacy_id = cu.customer_id
            WHERE p.product_id != $product_id
              AND p.product_stock > 0
              AND (p.product_cat = $category_id OR p.product_brand = $brand_id)
            ORDER BY relevance_score DESC, RAND()
            LIMIT $limit";

    $result = $this->db_query($sql);

    if ($result) {
        return $this->db_fetch_all($result);
    }
    return [];
}
```

**Step 4:** Add recommendations section to product pages

In `view/single_product.php` (or wherever you show product details), add:
```html
<!-- Recommendations Section -->
<div class="recommendations-section" style="margin-top: 3rem;">
    <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-lightbulb"></i> You May Also Like</h3>
    <div class="product-grid" id="recommendationsGrid">
        <div style="text-align: center; padding: 2rem;">
            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #667eea;"></i>
            <p>Loading recommendations...</p>
        </div>
    </div>
</div>

<script>
// Load recommendations when page loads
document.addEventListener('DOMContentLoaded', function() {
    const productId = <?php echo $product_id; ?>; // Get from PHP

    fetch(`../actions/get_recommendations_action.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            const grid = document.getElementById('recommendationsGrid');

            if (data.success && data.data.length > 0) {
                let html = '';
                data.data.forEach(product => {
                    const inStock = product.product_stock > 0;
                    html += `
                        <div class="product-card">
                            <div class="product-image">
                                ${product.product_image ? `<img src="../${product.product_image}" alt="${product.product_title}" style="width: 100%; height: 100%; object-fit: cover;">` : '<i class="fas fa-capsules"></i>'}
                                <span class="product-badge">Recommended</span>
                            </div>
                            <div class="product-info">
                                <div class="product-category">${product.cat_name || 'General'}</div>
                                <h4 class="product-name">${product.product_title}</h4>
                                <div class="product-price">GHS ${parseFloat(product.product_price).toFixed(2)}</div>
                                <div class="product-actions">
                                    <button class="btn-cart" onclick="addToCart(${product.product_id})">
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
                grid.innerHTML = html;
            } else {
                grid.innerHTML = '<div style="text-align: center; padding: 2rem; color: #64748b;"><p>No recommendations available</p></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('recommendationsGrid').innerHTML = '<div style="text-align: center; padding: 2rem; color: #ef4444;"><p>Error loading recommendations</p></div>';
        });
});
</script>
```

**Why This Matters:** This is explicitly mentioned as bonus marks (+5) in the rubric. Simple content-based filtering counts as "AI recommendation"!

---

## 🚀 MEDIUM PRIORITY: Impact Features (3-4 hours each)

### 4. Order Tracking for Customers (3 hours)

**What to Add:** Real-time order status tracking page

**Create:** `view/track_order.php`
```php
<?php
session_start();
require_once('../settings/core.php');
require_once('../controllers/order_controller.php');

if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = getUserId();

// Get customer's orders
$orders = get_customer_orders_ctr($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Orders - PharmaVault</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">

    <style>
        .timeline {
            position: relative;
            max-width: 600px;
            margin: 2rem auto;
        }

        .timeline-step {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .timeline-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            z-index: 1;
            flex-shrink: 0;
        }

        .timeline-icon.completed {
            background: #10b981;
            color: white;
        }

        .timeline-icon.current {
            background: #667eea;
            color: white;
            animation: pulse 2s infinite;
        }

        .timeline-icon.pending {
            background: #e2e8f0;
            color: #94a3b8;
        }

        .timeline-content {
            margin-left: 1.5rem;
            flex: 1;
        }

        .timeline-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e293b;
            margin: 0 0 0.25rem 0;
        }

        .timeline-date {
            font-size: 0.9rem;
            color: #64748b;
        }

        .timeline-line {
            position: absolute;
            left: 24px;
            top: 50px;
            width: 2px;
            height: calc(100% - 50px);
            background: #e2e8f0;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .order-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-processing { background: #dbeafe; color: #1e40af; }
        .status-shipped { background: #ddd6fe; color: #5b21b6; }
        .status-delivered { background: #d1fae5; color: #065f46; }
    </style>
</head>
<body>
    <?php include('../view/components/sidebar_customer.php'); ?>

    <div class="main-content">
        <h1><i class="fas fa-shipping-fast"></i> Track Your Orders</h1>

        <?php if (empty($orders)): ?>
            <div style="text-align: center; padding: 3rem; color: #64748b;">
                <i class="fas fa-box-open" style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                <h3>No orders yet</h3>
                <p>Start shopping to see your orders here!</p>
                <a href="../admin/customer_dashboard.php" style="display: inline-block; margin-top: 1rem; padding: 0.75rem 1.5rem; background: #667eea; color: white; border-radius: 10px; text-decoration: none;">
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <h3 style="margin: 0;">Order #<?php echo htmlspecialchars($order['invoice_no']); ?></h3>
                            <p style="margin: 0.25rem 0 0 0; color: #64748b;">
                                Placed on <?php echo date('F j, Y', strtotime($order['order_date'])); ?>
                            </p>
                        </div>
                        <span class="status-badge status-<?php echo strtolower($order['order_status']); ?>">
                            <?php echo ucfirst($order['order_status']); ?>
                        </span>
                    </div>

                    <div class="timeline">
                        <?php
                        $statuses = ['pending', 'processing', 'shipped', 'delivered'];
                        $current_status = strtolower($order['order_status']);
                        $current_index = array_search($current_status, $statuses);

                        $status_info = [
                            'pending' => ['icon' => 'fa-clock', 'title' => 'Order Placed', 'desc' => 'We have received your order'],
                            'processing' => ['icon' => 'fa-box', 'title' => 'Processing', 'desc' => 'Pharmacy is preparing your order'],
                            'shipped' => ['icon' => 'fa-truck', 'title' => 'Shipped', 'desc' => 'Your order is on the way'],
                            'delivered' => ['icon' => 'fa-check-circle', 'title' => 'Delivered', 'desc' => 'Order has been delivered']
                        ];

                        foreach ($statuses as $index => $status):
                            $info = $status_info[$status];
                            $class = $index < $current_index ? 'completed' : ($index == $current_index ? 'current' : 'pending');
                        ?>
                            <div class="timeline-step">
                                <?php if ($index < count($statuses) - 1): ?>
                                    <div class="timeline-line"></div>
                                <?php endif; ?>

                                <div class="timeline-icon <?php echo $class; ?>">
                                    <i class="fas <?php echo $info['icon']; ?>"></i>
                                </div>

                                <div class="timeline-content">
                                    <div class="timeline-title"><?php echo $info['title']; ?></div>
                                    <div class="timeline-date"><?php echo $info['desc']; ?></div>
                                    <?php if ($index == $current_index): ?>
                                        <div class="timeline-date" style="color: #667eea; font-weight: 600; margin-top: 0.25rem;">
                                            Current Status
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="text-align: center; margin-top: 2rem; padding-top: 1rem; border-top: 2px solid #f1f5f9;">
                        <a href="../view/order_details.php?order_id=<?php echo $order['order_id']; ?>" style="color: #667eea; text-decoration: none; font-weight: 600;">
                            View Order Details <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
```

**Why This Matters:** Improves user experience significantly and shows complete order flow.

---

### 5. Customer Product Reviews (3-4 hours)

**Impact:** Increases engagement and shows social proof

**Step 1:** Add review table to database

Create `db/add_reviews_table.sql`:
```sql
USE pharmavault_db;

CREATE TABLE IF NOT EXISTS `product_reviews` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `rating` tinyint NOT NULL CHECK (rating >= 1 AND rating <= 5),
  `review_text` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `unique_customer_product_review` (`product_id`, `customer_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_customer_id` (`customer_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Step 2:** Create review action handler

`actions/review_actions.php`:
```php
<?php
session_start();
require_once('../controllers/review_controller.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$customer_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_review') {
        $product_id = intval($_POST['product_id']);
        $rating = intval($_POST['rating']);
        $review_text = trim($_POST['review_text'] ?? '');

        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
            exit();
        }

        $result = add_review_ctr($customer_id, $product_id, $rating, $review_text);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Review submitted successfully!'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $product_id = intval($_GET['product_id'] ?? 0);

    if ($product_id > 0) {
        $reviews = get_product_reviews_ctr($product_id);
        echo json_encode([
            'success' => true,
            'data' => $reviews
        ]);
    }
}
?>
```

**Step 3:** Add review form to product pages

In `view/single_product.php`, add:
```html
<!-- Reviews Section -->
<div class="reviews-section" style="margin-top: 3rem; background: white; padding: 2rem; border-radius: 15px;">
    <h3><i class="fas fa-star"></i> Customer Reviews</h3>

    <!-- Average Rating -->
    <div class="average-rating" style="display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0; padding: 1rem; background: #f8fafc; border-radius: 10px;">
        <div style="font-size: 3rem; font-weight: 700; color: #667eea;">4.5</div>
        <div>
            <div class="stars" style="color: #fbbf24; font-size: 1.25rem;">
                ★★★★★
            </div>
            <div style="color: #64748b; margin-top: 0.25rem;">Based on 12 reviews</div>
        </div>
    </div>

    <!-- Add Review Form -->
    <div class="add-review-form" style="margin: 2rem 0; padding: 1.5rem; border: 2px dashed #e2e8f0; border-radius: 10px;">
        <h4>Write a Review</h4>
        <div style="margin: 1rem 0;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Your Rating:</label>
            <div class="star-rating" style="font-size: 2rem; color: #cbd5e1; cursor: pointer;">
                <span class="star" data-rating="1">☆</span>
                <span class="star" data-rating="2">☆</span>
                <span class="star" data-rating="3">☆</span>
                <span class="star" data-rating="4">☆</span>
                <span class="star" data-rating="5">☆</span>
            </div>
            <input type="hidden" id="selectedRating" value="0">
        </div>

        <div style="margin: 1rem 0;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Your Review:</label>
            <textarea id="reviewText" rows="4" style="width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 10px; resize: vertical;" placeholder="Share your experience with this product..."></textarea>
        </div>

        <button onclick="submitReview()" style="padding: 0.75rem 1.5rem; background: #667eea; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">
            Submit Review
        </button>
    </div>

    <!-- Reviews List -->
    <div id="reviewsList" style="margin-top: 2rem;">
        <!-- Reviews will be loaded here -->
    </div>
</div>

<script>
// Star rating interaction
document.querySelectorAll('.star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.dataset.rating;
        document.getElementById('selectedRating').value = rating;

        document.querySelectorAll('.star').forEach(s => {
            s.textContent = s.dataset.rating <= rating ? '★' : '☆';
            s.style.color = s.dataset.rating <= rating ? '#fbbf24' : '#cbd5e1';
        });
    });
});

function submitReview() {
    const rating = document.getElementById('selectedRating').value;
    const reviewText = document.getElementById('reviewText').value;
    const productId = <?php echo $product_id; ?>;

    if (rating == 0) {
        alert('Please select a rating');
        return;
    }

    fetch('../actions/review_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add_review&product_id=${productId}&rating=${rating}&review_text=${encodeURIComponent(reviewText)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            document.getElementById('reviewText').value = '';
            document.getElementById('selectedRating').value = 0;
            document.querySelectorAll('.star').forEach(s => {
                s.textContent = '☆';
                s.style.color = '#cbd5e1';
            });
            loadReviews(); // Refresh reviews list
        } else {
            alert(data.message);
        }
    });
}

function loadReviews() {
    const productId = <?php echo $product_id; ?>;

    fetch(`../actions/review_actions.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            const reviewsList = document.getElementById('reviewsList');

            if (data.success && data.data.length > 0) {
                let html = '';
                data.data.forEach(review => {
                    html += `
                        <div style="border-bottom: 1px solid #e2e8f0; padding: 1.5rem 0;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div>
                                    <div style="font-weight: 600; color: #1e293b;">${review.customer_name}</div>
                                    <div style="color: #fbbf24; font-size: 1rem;">
                                        ${'★'.repeat(review.rating)}${'☆'.repeat(5 - review.rating)}
                                    </div>
                                </div>
                                <div style="color: #64748b; font-size: 0.875rem;">
                                    ${new Date(review.created_at).toLocaleDateString()}
                                </div>
                            </div>
                            <p style="margin: 1rem 0 0 0; color: #475569;">${review.review_text || 'No comment provided'}</p>
                        </div>
                    `;
                });
                reviewsList.innerHTML = html;
            } else {
                reviewsList.innerHTML = '<p style="text-align: center; color: #64748b; padding: 2rem;">No reviews yet. Be the first to review!</p>';
            }
        });
}

// Load reviews when page loads
document.addEventListener('DOMContentLoaded', loadReviews);
</script>
```

**Why This Matters:** Social proof increases trust and conversions. Expected in modern e-commerce.

---

## 📧 BONUS: Email Notifications (2 hours)

**Impact:** Professional touch that shows attention to detail

**Simple PHP Mail Implementation:**

Create `functions/send_email.php`:
```php
<?php
function send_order_confirmation_email($customer_email, $customer_name, $order_number, $total_amount) {
    $subject = "Order Confirmation - PharmaVault Order #$order_number";

    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
            .content { background: #f9fafb; padding: 20px; }
            .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 0.875rem; }
            .button { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Order Confirmed!</h1>
            </div>
            <div class='content'>
                <p>Dear $customer_name,</p>
                <p>Thank you for your order! We've received your order and our pharmacy partners are preparing it for shipment.</p>

                <p><strong>Order Number:</strong> #$order_number</p>
                <p><strong>Total Amount:</strong> GHS " . number_format($total_amount, 2) . "</p>

                <p>You can track your order status anytime by logging into your PharmaVault account.</p>

                <a href='https://yourdomain.com/view/track_order.php' class='button'>Track Your Order</a>

                <p>If you have any questions, feel free to contact our support team.</p>

                <p>Best regards,<br>The PharmaVault Team</p>
            </div>
            <div class='footer'>
                <p>PharmaVault - Ghana's Trusted Online Pharmacy Marketplace</p>
                <p>This email was sent to $customer_email</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@pharmavault.com" . "\r\n";

    return mail($customer_email, $subject, $message, $headers);
}

function send_prescription_verification_email($customer_email, $customer_name, $prescription_number, $status) {
    $subject = "Prescription $status - PharmaVault";

    $status_text = $status === 'verified' ? 'Verified ✅' : 'Requires Attention ⚠️';
    $status_color = $status === 'verified' ? '#10b981' : '#f59e0b';

    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: $status_color; color: white; padding: 20px; text-align: center; }
            .content { background: #f9fafb; padding: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Prescription $status_text</h1>
            </div>
            <div class='content'>
                <p>Dear $customer_name,</p>
                <p>Your prescription #$prescription_number has been $status by our pharmacy team.</p>
                " . ($status === 'verified' ?
                    "<p>You can now proceed to order prescription medications.</p>" :
                    "<p>Please review the feedback and upload a corrected prescription if needed.</p>") . "
                <p>Best regards,<br>The PharmaVault Team</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: prescriptions@pharmavault.com" . "\r\n";

    return mail($customer_email, $subject, $message, $headers);
}
?>
```

**Then call these functions in your checkout and prescription verification code.**

---

## 📊 Summary: Priority Matrix

| Feature | Time | Impact | Marks | Priority |
|---------|------|--------|-------|----------|
| **Fix Wishlist** | 1 hour | High | +2-3 | 🔴 DO FIRST |
| **Fix Search** | 2 hours | High | +3-4 | 🔴 DO FIRST |
| **Add Recommendations** | 3-4 hours | Medium | +5 BONUS | 🟡 HIGH VALUE |
| **Order Tracking** | 3 hours | Medium | +2-3 | 🟡 MEDIUM |
| **Product Reviews** | 3-4 hours | Medium | +2-3 | 🟢 NICE TO HAVE |
| **Email Notifications** | 2 hours | Low | +1-2 | 🟢 NICE TO HAVE |

---

## 🎯 Recommended 3-Day Plan

### **Day 1 (Nov 27): Quick Fixes** ⚡
- ✅ Fix wishlist functionality (1 hour)
- ✅ Fix search functionality (2 hours)
- ✅ Test both features thoroughly (1 hour)

**Total: 4 hours**
**Marks Gained: +5 to +7**

---

### **Day 2 (Nov 28): High-Value Feature** 🎁
- ✅ Implement product recommendations (4 hours)
- ✅ Test recommendations on multiple products (1 hour)

**Total: 5 hours**
**Marks Gained: +5 BONUS**

---

### **Day 3 (Nov 29): Polish** ✨
- ✅ Add order tracking page (3 hours)
- ✅ Add email notifications (2 hours)
- ✅ Final testing of all features (2 hours)

**Total: 7 hours**
**Marks Gained: +3 to +5**

---

## 🏆 Expected Outcome

**Before:** 90/100 marks
**After:** 90 + 13 to 17 marks = **103-107/105** (capped at 105 with bonus)

**Result:** Near-perfect score with impressive feature set!

---

## 📝 Testing Checklist

After implementing each feature:

- [ ] Test as guest user
- [ ] Test as logged-in customer
- [ ] Test as pharmacy admin
- [ ] Test as super admin
- [ ] Test on mobile browser
- [ ] Check console for JavaScript errors
- [ ] Verify database records are created
- [ ] Test edge cases (empty results, errors, etc.)

---

## 💬 Questions?

Focus on:
1. Fix wishlist + search (Day 1) - MUST DO
2. Add recommendations (Day 2) - +5 BONUS
3. Polish with tracking (Day 3) - IF TIME

This will give you the best marks-to-time ratio!
