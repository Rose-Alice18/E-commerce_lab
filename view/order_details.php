<?php
/**
 * Order Details Page
 * Role: Customer (2)
 * Displays detailed information about a specific order
 */
session_start();
require_once('../settings/core.php');
require_once('../controllers/order_controller.php');

// Check if user is logged in as customer
if (!isLoggedIn() || !isRegularCustomer()) {
    header("Location: ../login/login_view.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

// Get order ID from URL
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("Location: ../admin/my_orders.php");
    exit();
}

$order_id = intval($_GET['order_id']);

// Get order details
$order = get_order_ctr($order_id);

// Verify order belongs to logged-in customer
if (!$order || $order['customer_id'] != $customer_id) {
    header("Location: ../admin/my_orders.php");
    exit();
}

// Get order items
$order_items = get_order_details_ctr($order_id);
$order_total = get_order_total_ctr($order_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - PharmaVault</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebar.css?v=2.2">

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

        .order-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        .order-info-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            font-weight: 600;
        }

        .info-value {
            color: #2c3e50;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #cfe2ff;
            color: #084298;
        }

        .status-shipped {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-delivered {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #842029;
        }

        .order-items-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .order-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }

        .item-details {
            flex: 1;
        }

        .item-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.3rem;
        }

        .item-meta {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .item-price {
            text-align: right;
        }

        .item-price-label {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .item-price-value {
            color: #667eea;
            font-weight: 600;
            font-size: 1.1rem;
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
        }

        .summary-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: #667eea;
        }

        .btn-back {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
            font-weight: 600;
        }

        .btn-back:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 70px;
            }

            .order-container {
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

            .info-grid {
                grid-template-columns: 1fr;
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
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-receipt me-2"></i>Order #<?php echo htmlspecialchars($order['order_id']); ?></h1>
                    <p class="text-muted mb-0">Invoice: <?php echo htmlspecialchars($order['invoice_no']); ?></p>
                </div>
                <a href="../admin/my_orders.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i>Back to Orders
                </a>
            </div>
        </div>

        <div class="order-container">
            <!-- Left Column: Order Info & Items -->
            <div>
                <!-- Order Information -->
                <div class="order-info-card">
                    <h4 class="mb-4"><i class="fas fa-info-circle me-2"></i>Order Information</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Order Date</span>
                            <span class="info-value">
                                <?php echo date('M d, Y', strtotime($order['order_date'])); ?>
                            </span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Order Status</span>
                            <span class="info-value">
                                <span class="status-badge status-<?php echo htmlspecialchars($order['order_status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($order['order_status'])); ?>
                                </span>
                            </span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Payment Date</span>
                            <span class="info-value">
                                <?php echo $order['payment_date'] ? date('M d, Y', strtotime($order['payment_date'])) : 'N/A'; ?>
                            </span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Payment Method</span>
                            <span class="info-value">Simulated Payment</span>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="order-info-card">
                    <h4 class="mb-4"><i class="fas fa-user me-2"></i>Customer Information</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Name</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Contact</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['customer_contact']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="order-items-card">
                    <h4 class="mb-4"><i class="fas fa-shopping-bag me-2"></i>Order Items (<?php echo count($order_items); ?>)</h4>

                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo !empty($item['product_image']) ? '../' . $item['product_image'] : '../uploads/placeholder.jpg'; ?>"
                                 alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                 class="item-image">

                            <div class="item-details">
                                <div class="item-title"><?php echo htmlspecialchars($item['product_title']); ?></div>
                                <div class="item-meta">
                                    <?php echo htmlspecialchars($item['cat_name'] ?? 'N/A'); ?> •
                                    <?php echo htmlspecialchars($item['brand_name'] ?? 'N/A'); ?><br>
                                    Quantity: <?php echo $item['qty']; ?> × GH₵ <?php echo number_format($item['product_price'], 2); ?>
                                </div>
                            </div>

                            <div class="item-price">
                                <div class="item-price-label">Subtotal</div>
                                <div class="item-price-value">
                                    GH₵ <?php echo number_format($item['product_price'] * $item['qty'], 2); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right Column: Order Summary -->
            <div class="order-summary">
                <h4 class="mb-4"><i class="fas fa-calculator me-2"></i>Order Summary</h4>

                <div class="summary-row">
                    <span>Subtotal (<?php echo count($order_items); ?> items)</span>
                    <span>GH₵ <?php echo number_format($order_total, 2); ?></span>
                </div>

                <div class="summary-row">
                    <span>Delivery Fee</span>
                    <span>GH₵ 10.00</span>
                </div>

                <div class="summary-row">
                    <span>Total Paid</span>
                    <span class="summary-total">
                        <?php echo htmlspecialchars($order['currency']); ?> <?php echo number_format($order['payment_amount'], 2); ?>
                    </span>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> This order was completed via simulated payment.
                </div>
            </div>
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
