<?php
/**
 * Customer Sidebar Component
 * Role: 2 (Regular Customer)
 */
if (!isset($_SESSION)) {
    session_start();
}
require_once(__DIR__ . '/../../settings/core.php');

// Ensure only customers can access
if (!isRegularCustomer()) {
    header("Location: ../../login/login_view.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Modern Sidebar for Customer -->
<div class="sidebar customer-theme" id="sidebar">
    <!-- Collapse/Expand Toggle Button -->
    <div class="sidebar-collapse-toggle" id="sidebarCollapseToggle" title="Collapse/Expand Sidebar">
        <i class="fas fa-chevron-left"></i>
    </div>

    <div class="sidebar-header">
        <div class="logo-container">
            <i class="fas fa-hospital-symbol"></i>
            <h4>PharmaVault</h4>
        </div>
        <span class="role-badge customer">Customer</span>
    </div>

    <div class="user-info">
        <div class="user-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="user-details">
            <h6><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Customer'); ?></h6>
            <p><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></p>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item <?php echo $current_page == 'customer_dashboard.php' ? 'active' : ''; ?>">
                <a href="customer_dashboard.php" title="Home">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
            </li>

            <li class="nav-section-title">
                <span>SHOPPING</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'browse_products.php' ? 'active' : ''; ?>">
                <a href="browse_products.php" title="Browse Products">
                    <i class="fas fa-pills"></i>
                    <span>Browse Products</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'cart.php' ? 'active' : ''; ?>">
                <a href="cart.php" title="My Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span>My Cart</span>
                    <span class="badge" id="cartCount">0</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'wishlist.php' ? 'active' : ''; ?>">
                <a href="wishlist.php" title="Wishlist">
                    <i class="fas fa-heart"></i>
                    <span>Wishlist</span>
                </a>
            </li>

            <li class="nav-section-title">
                <span>ORDERS</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'my_orders.php' ? 'active' : ''; ?>">
                <a href="my_orders.php" title="My Orders">
                    <i class="fas fa-box"></i>
                    <span>My Orders</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'track_order.php' ? 'active' : ''; ?>">
                <a href="track_order.php" title="Track Order">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Track Order</span>
                </a>
            </li>

            <li class="nav-section-title">
                <span>ACCOUNT</span>
            </li>

            <li class="nav-item <?php echo ($current_page == 'profile.php' || $current_page == 'edit_profile.php' || $current_page == 'change_password.php') ? 'active' : ''; ?>">
                <a href="profile.php" title="My Profile">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                </a>
            </li>

            <li class="nav-section-title">
                <span>SUPPORT</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'help.php' ? 'active' : ''; ?>">
                <a href="help.php" title="Help Center">
                    <i class="fas fa-question-circle"></i>
                    <span>Help Center</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="../login/logout.php" class="logout-btn" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<!-- Sidebar Toggle Button for Mobile -->
<button class="sidebar-toggle" id="sidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
