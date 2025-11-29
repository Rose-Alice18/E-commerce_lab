<?php
/**
 * Pharmacy Admin Sidebar Component
 * Role: 1 (Pharmacy Admin/Owner)
 */
if (!isset($_SESSION)) {
    session_start();
}
require_once(__DIR__ . '/../../settings/core.php');

// Ensure only pharmacy admins can access
if (!isPharmacyAdmin()) {
    header("Location: ../../login/login_view.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Modern Hamburger Menu -->
<div class="hamburger-menu pharmacy" id="hamburgerMenu">
    <div class="bar"></div>
    <div class="bar"></div>
    <div class="bar"></div>
</div>

<!-- Sidebar Backdrop -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- Modern Sidebar for Pharmacy Admin -->
<div class="sidebar pharmacy-admin-theme" id="sidebar">

    <div class="sidebar-header">
        <div class="logo-container">
            <img src="../../Piharma1.png" alt="Piharma Logo" style="max-width: 100%; height: auto; max-height: 50px; object-fit: contain;">
        </div>
        <span class="role-badge pharmacy-admin">Pharmacy Admin</span>
    </div>

    <div class="user-info">
        <div class="user-avatar">
            <i class="fas fa-user-md"></i>
        </div>
        <div class="user-details">
            <h6><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Pharmacy Admin'); ?></h6>
            <p><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></p>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item <?php echo $current_page == 'pharmacy_dashboard.php' ? 'active' : ''; ?>">
                <a href="pharmacy_dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-section-title">
                <span>INVENTORY</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'my_products.php' ? 'active' : ''; ?>">
                <a href="my_products.php">
                    <i class="fas fa-pills"></i>
                    <span>My Products</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'add_product.php' ? 'active' : ''; ?>">
                <a href="add_product.php">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Product</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'bulk_upload_products.php' ? 'active' : ''; ?>">
                <a href="bulk_upload_products.php">
                    <i class="fas fa-file-upload"></i>
                    <span>Bulk Upload</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'category.php' ? 'active' : ''; ?>">
                <a href="category.php">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'brand.php' ? 'active' : ''; ?>">
                <a href="brand.php">
                    <i class="fas fa-copyright"></i>
                    <span>Brands</span>
                </a>
            </li>

            <li class="nav-section-title">
                <span>ORDERS</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
                <a href="orders.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'order_history.php' ? 'active' : ''; ?>">
                <a href="order_history.php">
                    <i class="fas fa-history"></i>
                    <span>Order History</span>
                </a>
            </li>

            <li class="nav-section-title">
                <span>ANALYTICS</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'sales_report.php' ? 'active' : ''; ?>">
                <a href="sales_report.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Sales Report</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'revenue_analytics.php' ? 'active' : ''; ?>">
                <a href="revenue_analytics.php">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Revenue</span>
                </a>
            </li>

            <li class="nav-section-title">
                <span>ACCOUNT</span>
            </li>

            <li class="nav-item <?php echo ($current_page == 'profile.php' || $current_page == 'edit_profile.php' || $current_page == 'change_password.php') ? 'active' : ''; ?>">
                <a href="profile.php">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="../login/logout.php" class="logout-btn">
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
