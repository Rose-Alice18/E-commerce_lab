<?php
/**
 * Super Admin Sidebar Component
 * Role: 0 (Super Admin)
 */
if (!isset($_SESSION)) {
    session_start();
}
require_once(__DIR__ . '/../../settings/core.php');

// Ensure only super admins can access
if (!isSuperAdmin()) {
    header("Location: ../../login/login_view.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Modern Hamburger Menu -->
<div class="hamburger-menu super-admin" id="hamburgerMenu">
    <div class="bar"></div>
    <div class="bar"></div>
    <div class="bar"></div>
</div>

<!-- Sidebar Backdrop -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- Modern Sidebar for Super Admin -->
<div class="sidebar super-admin-theme" id="sidebar">

    <div class="sidebar-header">
        <div class="logo-container">
            <img src="../../Piharma1.png" alt="Piharma Logo" style="max-width: 100%; height: auto; max-height: 50px; object-fit: contain;">
        </div>
        <span class="role-badge super-admin">Super Admin</span>
    </div>

    <div class="user-info">
        <div class="user-avatar">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="user-details">
            <h6><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></h6>
            <p><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></p>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-section-title">
                <span>MANAGEMENT</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
                <a href="categories.php">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == '.../admin/brand.php' ? 'active' : ''; ?>">
                <a href="brand.php">
                    <i class="fas fa-copyright"></i>
                    <span>Brands</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                <a href="products.php">
                    <i class="fas fa-pills"></i>
                    <span>Products</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'suggestions_review.php' ? 'active' : ''; ?>">
                <a href="suggestions_review.php">
                    <i class="fas fa-lightbulb"></i>
                    <span>Review Suggestions</span>
                    <span class="badge" id="pendingSuggestionCount">0</span>
                </a>
            </li>

            <li class="nav-section-title">
                <span>USER MANAGEMENT</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'pharmacies.php' ? 'active' : ''; ?>">
                <a href="pharmacies.php">
                    <i class="fas fa-clinic-medical"></i>
                    <span>Pharmacies</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'customers.php' ? 'active' : ''; ?>">
                <a href="customers.php">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
            </li>

            <li class="nav-section-title">
                <span>REPORTS</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'analytics.php' ? 'active' : ''; ?>">
                <a href="analytics.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
                <a href="reports.php">
                    <i class="fas fa-file-alt"></i>
                    <span>Reports</span>
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

            <li class="nav-section-title">
                <span>SETTINGS</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                <a href="settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
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
