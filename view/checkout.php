<?php
/**
 * Checkout Page
 * Role: Customer (2)
 * Displays cart summary and handles simulated payment
 */
session_start();
require_once('../settings/core.php');
require_once('../controllers/cart_controller.php');

// Check if user is logged in as customer
if (!isLoggedIn() || !isRegularCustomer()) {
    header("Location: ../login/login_view.php");
    exit();
}

$customer_id = $_SESSION['user_id'];
$ip_address = $_SERVER['REMOTE_ADDR'];

// Get cart items
$cart_items = get_cart_items_ctr($customer_id, $ip_address);
$cart_total = get_cart_total_ctr($customer_id, $ip_address);
$cart_count = count($cart_items);

// Redirect if cart is empty
if ($cart_count === 0) {
    header("Location: ../admin/cart.php");
    exit();
}

// Calculate delivery fee and grand total
$delivery_fee = 10.00;
$grand_total = $cart_total + $delivery_fee;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - PharmaVault</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebar.css?v=2.2">
    <!-- Cart & Wishlist CSS -->
    <link rel="stylesheet" href="../css/cart-wishlist.css?v=1.0">

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

        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        .order-items {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .order-summary {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .checkout-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .checkout-item:last-child {
            border-bottom: none;
        }

        .checkout-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }

        .checkout-item-details {
            flex: 1;
        }

        .checkout-item-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.3rem;
        }

        .checkout-item-meta {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .checkout-item-price {
            text-align: right;
            color: #667eea;
            font-weight: 600;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .summary-row:last-child {
            border-bottom: none;
            padding-top: 1.5rem;
            margin-top: 1rem;
            border-top: 2px solid #667eea;
            font-size: 1.3rem;
            font-weight: 700;
            color: #667eea;
        }

        .btn-pay {
            background: var(--primary-gradient);
            color: white;
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

        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .payment-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .payment-modal.active {
            display: flex;
        }

        .payment-modal-content {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            text-align: center;
            max-width: 500px;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .payment-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 1.5rem;
        }

        .payment-amount {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 1rem 0;
        }

        .payment-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .payment-buttons button {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-confirm-payment {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-confirm-payment:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-cancel-payment {
            background: #dc3545;
            color: white;
        }

        .btn-cancel-payment:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 70px;
            }

            .checkout-container {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .payment-modal-content {
                margin: 1rem;
                padding: 2rem;
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
            <h1><i class="fas fa-credit-card me-2"></i>Checkout</h1>
            <p class="text-muted mb-0">Review your order and complete payment</p>
        </div>

        <div class="checkout-container">
            <!-- Order Items -->
            <div class="order-items">
                <h4 class="mb-4"><i class="fas fa-shopping-bag me-2"></i>Order Items (<?php echo $cart_count; ?>)</h4>

                <?php foreach ($cart_items as $item): ?>
                    <div class="checkout-item">
                        <img src="<?php echo !empty($item['product_image']) ? '../' . $item['product_image'] : '../uploads/placeholder.jpg'; ?>"
                             alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                             class="checkout-item-image">

                        <div class="checkout-item-details">
                            <div class="checkout-item-title"><?php echo htmlspecialchars($item['product_title']); ?></div>
                            <div class="checkout-item-meta">
                                <?php echo htmlspecialchars($item['cat_name'] ?? 'N/A'); ?> •
                                <?php echo htmlspecialchars($item['brand_name'] ?? 'N/A'); ?><br>
                                Quantity: <?php echo $item['qty']; ?>
                            </div>
                        </div>

                        <div class="checkout-item-price">
                            GH₵ <?php echo number_format($item['product_price'] * $item['qty'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <h4 class="mb-4"><i class="fas fa-receipt me-2"></i>Order Summary</h4>

                <div class="summary-row">
                    <span>Subtotal (<?php echo $cart_count; ?> items)</span>
                    <span>GH₵ <?php echo number_format($cart_total, 2); ?></span>
                </div>

                <div class="summary-row">
                    <span>Delivery Fee</span>
                    <span>GH₵ <?php echo number_format($delivery_fee, 2); ?></span>
                </div>

                <div class="summary-row">
                    <span>Total Amount</span>
                    <span id="grand-total">GH₵ <?php echo number_format($grand_total, 2); ?></span>
                </div>

                <button class="btn-pay" id="btn-simulate-payment">
                    <i class="fas fa-lock me-2"></i>Simulate Payment
                </button>

                <a href="../admin/cart.php" class="btn btn-outline-secondary w-100 mt-3">
                    <i class="fas fa-arrow-left me-2"></i>Back to Cart
                </a>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="payment-modal" id="payment-modal">
        <div class="payment-modal-content">
            <div class="payment-icon">
                <i class="fas fa-mobile-alt"></i>
            </div>
            <h3>Simulated Payment</h3>
            <p class="text-muted">Confirm your payment of:</p>
            <div class="payment-amount">GH₵ <?php echo number_format($grand_total, 2); ?></div>
            <p class="text-muted">This is a simulated payment. No real transaction will occur.</p>

            <div class="payment-buttons">
                <button class="btn-cancel-payment" id="btn-cancel">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button class="btn-confirm-payment" id="btn-confirm">
                    <i class="fas fa-check me-2"></i>Yes, I've Paid
                </button>
            </div>
        </div>
    </div>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Checkout JS -->
    <script src="../js/checkout.js"></script>
</body>
</html>
