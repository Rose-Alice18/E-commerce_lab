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

// Determine the base path based on current directory
$current_dir = dirname($_SERVER['PHP_SELF']);
$is_in_admin = (strpos($current_dir, '/admin') !== false);
$is_in_view = (strpos($current_dir, '/view') !== false);
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
            <?php
            // Determine logo path based on current directory
            if ($is_in_admin) {
                $logo_path = '../Pharma1logo.png';
            } elseif ($is_in_view) {
                $logo_path = '../Pharma1logo.png';
            } else {
                $logo_path = './Pharma1logo.png';
            }
            ?>
            <img src="<?php echo $logo_path; ?>" alt="PharmaVault Logo" class="sidebar-logo">
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
                <span>USER MANAGEMENT</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                <a href="users.php">
                    <i class="fas fa-users-cog"></i>
                    <span>All Users</span>
                </a>
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
                <span>CATALOG MANAGEMENT</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
                <a href="categories.php">
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

            <li class="nav-item <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                <a href="products.php">
                    <i class="fas fa-pills"></i>
                    <span>All Products</span>
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
                <span>HEALTHCARE</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'prescriptions.php' ? 'active' : ''; ?>">
                <a href="prescriptions.php">
                    <i class="fas fa-prescription"></i>
                    <span>Prescriptions</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'pending_verifications.php' ? 'active' : ''; ?>">
                <a href="pending_verifications.php">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Pending Verifications</span>
                    <span class="badge badge-warning" id="pendingPrescriptionCount">0</span>
                </a>
            </li>

            <li class="nav-section-title">
                <span>ORDER MANAGEMENT</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'all_orders.php' ? 'active' : ''; ?>">
                <a href="all_orders.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>All Orders</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'all_orders.php' ? 'active' : ''; ?>">
                <a href="all_orders.php">
                    <i class="fas fa-history"></i>
                    <span>Order History</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'payments.php' ? 'active' : ''; ?>">
                <a href="payments.php">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </a>
            </li>

            <li class="nav-section-title">
                <span>DELIVERY MANAGEMENT</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'rider_management.php' ? 'active' : ''; ?>">
                <a href="rider_management.php">
                    <i class="fas fa-motorcycle"></i>
                    <span>Riders</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'deliveries.php' ? 'active' : ''; ?>">
                <a href="deliveries.php">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Deliveries</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'delivery_tracking.php' ? 'active' : ''; ?>">
                <a href="delivery_tracking.php">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Track Deliveries</span>
                </a>
            </li>

            <li class="nav-section-title">
                <span>COMMUNICATION</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'messages.php' ? 'active' : ''; ?>">
                <a href="messages.php">
                    <i class="fas fa-comments"></i>
                    <span>Messages</span>
                    <span class="badge badge-danger" id="unreadMessagesCount">0</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'tickets.php' ? 'active' : ''; ?>">
                <a href="tickets.php">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Support Tickets</span>
                    <span class="badge badge-warning" id="openTicketsCount">0</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'announcements.php' ? 'active' : ''; ?>">
                <a href="announcements.php">
                    <i class="fas fa-bullhorn"></i>
                    <span>Announcements</span>
                </a>
            </li>

            <li class="nav-section-title">
                <span>REPORTS & ANALYTICS</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'analytics.php' ? 'active' : ''; ?>">
                <a href="analytics.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'sales_report.php' ? 'active' : ''; ?>">
                <a href="sales_report.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Sales Reports</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'revenue_analytics.php' ? 'active' : ''; ?>">
                <a href="revenue_analytics.php">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Revenue</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
                <a href="reports.php">
                    <i class="fas fa-file-alt"></i>
                    <span>Custom Reports</span>
                </a>
            </li>

            <li class="nav-section-title">
                <span>REVIEWS & FEEDBACK</span>
            </li>

            <li class="nav-item <?php echo $current_page == 'reviews.php' ? 'active' : ''; ?>">
                <a href="reviews.php">
                    <i class="fas fa-star"></i>
                    <span>Product Reviews</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'feedback.php' ? 'active' : ''; ?>">
                <a href="feedback.php">
                    <i class="fas fa-comment-dots"></i>
                    <span>Customer Feedback</span>
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
                    <span>System Settings</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'logs.php' ? 'active' : ''; ?>">
                <a href="logs.php">
                    <i class="fas fa-clipboard-list"></i>
                    <span>System Logs</span>
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
