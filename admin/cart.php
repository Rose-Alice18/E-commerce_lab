<?php
/**
 * Shopping Cart Page
 * Role: Customer (2)
 */
session_start();
require_once('../settings/core.php');
require_once('../controllers/cart_controller.php');
require_once('../controllers/product_controller.php');

// Check if user is logged in as customer
if (!isLoggedIn() || !isRegularCustomer()) {
    header("Location: ../login/login_view.php");
    exit();
}

$customer_id = $_SESSION['user_id'];
$ip_address = $_SERVER['REMOTE_ADDR'];

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update':
                $product_id = intval($_POST['product_id']);
                $quantity = intval($_POST['quantity']);
                update_cart_quantity_ctr($product_id, $customer_id, $ip_address, $quantity);
                header("Location: cart.php?msg=updated");
                exit();
                break;

            case 'remove':
                $product_id = intval($_POST['product_id']);
                remove_from_cart_ctr($product_id, $customer_id, $ip_address);
                header("Location: cart.php?msg=removed");
                exit();
                break;

            case 'clear':
                clear_cart_ctr($customer_id, $ip_address);
                header("Location: cart.php?msg=cleared");
                exit();
                break;
        }
    }
}

// Get cart items
$cart_items = get_cart_items_ctr($customer_id, $ip_address);
$cart_total = get_cart_total_ctr($customer_id, $ip_address);
$cart_count = count($cart_items);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - PharmaVault</title>

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

        .cart-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .cart-item {
            border-bottom: 1px solid #e9ecef;
            padding: 1.5rem 0;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .cart-item-meta {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .cart-item-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #667eea;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-control button {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .quantity-control button:hover {
            background: #667eea;
            color: white;
        }

        .quantity-control input {
            width: 60px;
            text-align: center;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.4rem;
        }

        .btn-remove {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-remove:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .cart-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .summary-row:last-child {
            border-bottom: none;
            font-size: 1.5rem;
            font-weight: 700;
            padding-top: 1rem;
        }

        .btn-checkout {
            background: white;
            color: #667eea;
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            margin-top: 1.5rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-cart i {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 70px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .cart-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .cart-item-image {
                width: 100%;
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include('../view/components/sidebar_customer.php'); ?>

    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-shopping-cart me-2"></i>My Shopping Cart</h1>
            <p class="text-muted mb-0"><?php echo $cart_count; ?> item(s) in your cart</p>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php
                switch ($_GET['msg']) {
                    case 'updated':
                        echo '<i class="fas fa-check-circle me-2"></i>Cart updated successfully!';
                        break;
                    case 'removed':
                        echo '<i class="fas fa-check-circle me-2"></i>Item removed from cart!';
                        break;
                    case 'cleared':
                        echo '<i class="fas fa-check-circle me-2"></i>Cart cleared successfully!';
                        break;
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="cart-container">
            <?php if ($cart_count > 0): ?>
                <!-- Cart Items -->
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <img src="<?php echo !empty($item['product_image']) ? '../' . $item['product_image'] : '../uploads/placeholder.jpg'; ?>"
                             alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                             class="cart-item-image">

                        <div class="cart-item-details">
                            <div class="cart-item-title"><?php echo htmlspecialchars($item['product_title']); ?></div>
                            <div class="cart-item-meta">
                                <?php echo htmlspecialchars($item['cat_name'] ?? 'N/A'); ?> •
                                <?php echo htmlspecialchars($item['brand_name'] ?? 'N/A'); ?>
                            </div>
                            <div class="cart-item-price mt-2">
                                GH₵ <?php echo number_format($item['product_price'], 2); ?>
                            </div>
                        </div>

                        <form method="POST" class="quantity-control">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="product_id" value="<?php echo $item['p_id']; ?>">
                            <button type="submit" name="quantity" value="<?php echo max(1, $item['qty'] - 1); ?>">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" name="quantity_display" value="<?php echo $item['qty']; ?>"
                                   min="1" max="<?php echo $item['product_stock']; ?>" readonly>
                            <button type="submit" name="quantity" value="<?php echo min($item['product_stock'], $item['qty'] + 1); ?>">
                                <i class="fas fa-plus"></i>
                            </button>
                        </form>

                        <form method="POST" style="margin-left: 1rem;">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?php echo $item['p_id']; ?>">
                            <button type="submit" class="btn-remove">
                                <i class="fas fa-trash me-1"></i>Remove
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h4 class="mb-3"><i class="fas fa-receipt me-2"></i>Order Summary</h4>
                    <div class="summary-row">
                        <span>Subtotal (<?php echo $cart_count; ?> items)</span>
                        <span>GH₵ <?php echo number_format($cart_total, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Fee</span>
                        <span>GH₵ 10.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Total</span>
                        <span>GH₵ <?php echo number_format($cart_total + 10, 2); ?></span>
                    </div>
                    <a href="../view/checkout.php" class="btn-checkout" style="text-decoration: none; text-align: center; display: block;">
                        <i class="fas fa-lock me-2"></i>Proceed to Checkout
                    </a>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-outline-light w-100"
                                onclick="return confirm('Are you sure you want to clear your cart?')">
                            <i class="fas fa-trash me-2"></i>Clear Cart
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Empty Cart -->
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your cart is empty</h3>
                    <p class="text-muted mb-4">Start adding products to your cart!</p>
                    <a href="../view/all_product.php" class="btn btn-primary">
                        <i class="fas fa-pills me-2"></i>Browse Products
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Cart and Wishlist JS (for sidebar counts) -->
    <script src="../js/cart-wishlist.js"></script>
</body>
</html>
