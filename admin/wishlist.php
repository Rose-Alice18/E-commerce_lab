<?php
/**
 * Wishlist Page
 * Role: Customer (2)
 */
session_start();
require_once('../settings/core.php');
require_once('../controllers/wishlist_controller.php');

// Check if user is logged in as customer
if (!isLoggedIn() || !isRegularCustomer()) {
    header("Location: ../login/login_view.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

// Handle wishlist actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    switch ($action) {
        case 'remove':
            if ($product_id > 0) {
                remove_from_wishlist_ctr($customer_id, $product_id);
                header("Location: wishlist.php?msg=removed");
                exit();
            }
            break;

        case 'clear':
            clear_wishlist_ctr($customer_id);
            header("Location: wishlist.php?msg=cleared");
            exit();
            break;
    }
}

// Get wishlist items
$wishlist_items = get_wishlist_items_ctr($customer_id);
$wishlist_count = count($wishlist_items);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - PharmaVault</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebar.css?v=2.5">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --sidebar-width: 280px;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .page-header h1 {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
            font-weight: 700;
        }

        .wishlist-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .wishlist-item {
            border-bottom: 1px solid #e9ecef;
            padding: 1.5rem 0;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .wishlist-item:last-child {
            border-bottom: none;
        }

        .wishlist-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            background: #f8f9fa;
        }

        .wishlist-item-details {
            flex: 1;
        }

        .wishlist-item-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .wishlist-item-meta {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .wishlist-item-price {
            font-size: 1.25rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .wishlist-actions {
            display: flex;
            gap: 0.5rem;
            flex-direction: column;
        }

        .btn-add-cart {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .btn-remove {
            background: transparent;
            border: 1px solid #ef4444;
            color: #ef4444;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-remove:hover {
            background: #ef4444;
            color: white;
        }

        .empty-wishlist {
            text-align: center;
            padding: 3rem;
        }

        .empty-icon {
            font-size: 5rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }

        .stock-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .in-stock {
            background: #dcfce7;
            color: #16a34a;
        }

        .out-of-stock {
            background: #fee2e2;
            color: #dc2626;
        }

        .wishlist-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
        }

        .wishlist-count {
            color: #64748b;
            font-size: 0.95rem;
        }

        @media (max-width: 1024px) {
            .main-content { margin-left: 70px; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; }
            .wishlist-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .wishlist-actions {
                width: 100%;
                flex-direction: row;
            }
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include('../view/components/sidebar_customer.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-heart me-2"></i>My Wishlist</h1>
            <p class="text-muted mb-0">Save and manage your favorite products</p>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                    if ($_GET['msg'] == 'removed') echo 'Product removed from wishlist';
                    if ($_GET['msg'] == 'cleared') echo 'Wishlist cleared successfully';
                    if ($_GET['msg'] == 'added_to_cart') echo 'Product added to cart';
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="wishlist-container">
            <?php if ($wishlist_count > 0): ?>
                <div class="wishlist-header">
                    <div>
                        <h5 class="mb-0">Your Wishlist</h5>
                        <p class="wishlist-count mb-0"><?php echo $wishlist_count; ?> <?php echo $wishlist_count == 1 ? 'item' : 'items'; ?></p>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="clear-wishlist-btn">
                        <i class="fas fa-trash me-1"></i>Clear All
                    </button>
                </div>

                <?php foreach ($wishlist_items as $item): ?>
                    <div class="wishlist-item">
                        <img src="../<?php echo !empty($item['product_image']) ? htmlspecialchars($item['product_image']) : 'images/products/default.png'; ?>"
                             alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                             class="wishlist-item-image">

                        <div class="wishlist-item-details">
                            <h3 class="wishlist-item-title"><?php echo htmlspecialchars($item['product_title']); ?></h3>
                            <p class="wishlist-item-meta">
                                <?php if (!empty($item['cat_name'])): ?>
                                    <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($item['cat_name']); ?>
                                <?php endif; ?>
                                <?php if (!empty($item['brand_name'])): ?>
                                    <span class="ms-3"><i class="fas fa-certificate me-1"></i><?php echo htmlspecialchars($item['brand_name']); ?></span>
                                <?php endif; ?>
                            </p>
                            <div class="d-flex align-items-center gap-3">
                                <div class="wishlist-item-price">GHS <?php echo number_format($item['product_price'], 2); ?></div>
                                <span class="stock-badge <?php echo $item['product_stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                    <?php echo $item['product_stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                </span>
                            </div>
                        </div>

                        <div class="wishlist-actions">
                            <?php if ($item['product_stock'] > 0): ?>
                                <button type="button"
                                        class="btn btn-add-cart add-to-cart-btn"
                                        data-product-id="<?php echo $item['product_id']; ?>">
                                    <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                                </button>
                            <?php else: ?>
                                <button class="btn btn-add-cart" disabled>
                                    <i class="fas fa-times-circle me-1"></i>Out of Stock
                                </button>
                            <?php endif; ?>

                            <button type="button"
                                    class="btn btn-remove remove-from-wishlist-btn"
                                    data-product-id="<?php echo $item['product_id']; ?>">
                                <i class="fas fa-trash me-1"></i>Remove
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-wishlist">
                    <i class="fas fa-heart-broken empty-icon"></i>
                    <h3>Your Wishlist is Empty</h3>
                    <p class="text-muted mb-4">Start adding products you love to your wishlist!</p>
                    <a href="../view/product.php" class="btn btn-add-cart">
                        <i class="fas fa-pills me-2"></i>Browse Products
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../js/sidebar.js?v=2.5"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Cart and Wishlist JS (for sidebar counts) -->
    <script src="../js/cart-wishlist.js"></script>

    <script>
    // Clear All Wishlist Items
    const clearWishlistBtn = document.getElementById('clear-wishlist-btn');
    if (clearWishlistBtn) {
        clearWishlistBtn.addEventListener('click', function() {
            if (!confirm('Are you sure you want to clear all wishlist items?')) return;

            const btnIcon = this.querySelector('i');
            this.disabled = true;
            btnIcon.className = 'fas fa-spinner fa-spin me-1';

            fetch('../actions/wishlist_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=clear'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Wishlist cleared successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message || 'Failed to clear wishlist', 'error');
                    btnIcon.className = 'fas fa-trash me-1';
                    this.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error clearing wishlist', 'error');
                btnIcon.className = 'fas fa-trash me-1';
                this.disabled = false;
            });
        });
    }

    // Add to Cart from Wishlist with AJAX
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const btnIcon = this.querySelector('i');
            const btnText = this;

            // Show loading state
            btnText.disabled = true;
            btnIcon.className = 'fas fa-spinner fa-spin me-1';

            fetch('../actions/add_to_cart_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success toast
                    showToast(data.message, 'success');

                    // Update cart badge
                    const cartBadge = document.querySelector('.sidebar-nav a[href*="cart.php"] .badge');
                    if (cartBadge && data.cart_count) {
                        cartBadge.textContent = data.cart_count;
                    }

                    // Reset button
                    btnIcon.className = 'fas fa-check me-1';
                    setTimeout(() => {
                        btnIcon.className = 'fas fa-shopping-cart me-1';
                        btnText.disabled = false;
                    }, 2000);
                } else {
                    showToast(data.message || 'Failed to add to cart', 'error');
                    btnIcon.className = 'fas fa-shopping-cart me-1';
                    btnText.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error adding to cart', 'error');
                btnIcon.className = 'fas fa-shopping-cart me-1';
                btnText.disabled = false;
            });
        });
    });

    // Remove from Wishlist with AJAX
    document.querySelectorAll('.remove-from-wishlist-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Remove this item from your wishlist?')) return;

            const productId = this.dataset.productId;
            const wishlistItem = this.closest('.wishlist-item');

            fetch('../actions/wishlist_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=remove&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Animate and remove item
                    wishlistItem.style.transition = 'opacity 0.3s, transform 0.3s';
                    wishlistItem.style.opacity = '0';
                    wishlistItem.style.transform = 'translateX(-20px)';

                    setTimeout(() => {
                        wishlistItem.remove();

                        // Update wishlist badge
                        const wishlistBadge = document.querySelector('.sidebar-nav a[href*="wishlist.php"] .badge');
                        if (wishlistBadge && data.wishlist_count !== undefined) {
                            wishlistBadge.textContent = data.wishlist_count;
                        }

                        // Check if wishlist is now empty
                        const remainingItems = document.querySelectorAll('.wishlist-item');
                        if (remainingItems.length === 0) {
                            location.reload();
                        }
                    }, 300);

                    showToast('Item removed from wishlist', 'success');
                } else {
                    showToast(data.message || 'Failed to remove item', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error removing item', 'error');
            });
        });
    });

    // Toast notification function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            z-index: 10000;
            animation: slideIn 0.3s ease;
            max-width: 350px;
            font-weight: 500;
        `;
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
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
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>
