<?php
/**
 * Customer Sidebar Component
 * Role: 2 (Regular Customer)
 * Last Updated: 2025-11-12 - Fixed SHOPPING section visibility
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

// Determine the base path based on current directory
$current_dir = dirname($_SERVER['PHP_SELF']);
$is_in_admin = (strpos($current_dir, '/admin') !== false);
$is_in_view = (strpos($current_dir, '/view') !== false);

// Set base path for links
if ($is_in_admin) {
    $base_path = '../admin/';
    $view_path = '../view/';
    $login_path = '../login/';
} elseif ($is_in_view) {
    $base_path = '../admin/';
    $view_path = './';
    $login_path = '../login/';
} else {
    $base_path = './admin/';
    $view_path = './view/';
    $login_path = './login/';
}
?>

<!-- Modern Hamburger Menu -->
<div class="hamburger-menu customer" id="hamburgerMenu">
    <div class="bar"></div>
    <div class="bar"></div>
    <div class="bar"></div>
</div>

<!-- Sidebar Backdrop -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- Modern Sidebar for Customer -->
<div class="sidebar customer-theme" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-container">
            <?php
            // Determine logo path based on current directory
            if ($is_in_admin) {
                $logo_path = '../Piharma1.png';
            } elseif ($is_in_view) {
                $logo_path = '../Piharma1.png';
            } else {
                $logo_path = './Piharma1.png';
            }
            ?>
            <img src="<?php echo $logo_path; ?>" alt="Piharma Logo" style="max-width: 100%; height: auto; max-height: 50px; object-fit: contain;">
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
            <li class="nav-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                <a href="<?php echo $is_in_admin ? '../index.php' : ($is_in_view ? '../index.php' : './index.php'); ?>" title="Home">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'customer_dashboard.php' ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>customer_dashboard.php" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'product.php' ? 'active' : ''; ?>">
                <a href="<?php echo $view_path; ?>product.php" title="Browse Products">
                    <i class="fas fa-pills"></i>
                    <span>Browse Products</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'medicine_availability.php' ? 'active' : ''; ?>">
                <a href="<?php echo $view_path; ?>medicine_availability.php" title="Medicine Availability">
                    <i class="fas fa-search-location"></i>
                    <span>Medicine Availability</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'pharmacies.php' || $current_page == 'pharmacy_view.php' ? 'active' : ''; ?>">
                <a href="<?php echo $view_path; ?>pharmacies.php" title="Pharmacies">
                    <i class="fas fa-hospital"></i>
                    <span>Our Pharmacies</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'upload_prescription.php' || $current_page == 'my_prescriptions.php' ? 'active' : ''; ?>">
                <a href="<?php echo $view_path; ?>upload_prescription.php" title="Prescriptions">
                    <i class="fas fa-file-medical"></i>
                    <span>Prescriptions</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'cart.php' ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>cart.php" title="My Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span>My Cart</span>
                    <span class="badge" id="cartCount">0</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'wishlist.php' ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>wishlist.php" title="Wishlist">
                    <i class="fas fa-heart"></i>
                    <span>Wishlist</span>
                    <span class="badge" id="wishlistCount">0</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'my_orders.php' ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>my_orders.php" title="My Orders">
                    <i class="fas fa-box"></i>
                    <span>My Orders</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'track_order.php' ? 'active' : ''; ?>">
                <a href="<?php echo $view_path; ?>track_order.php" title="Track Order">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Track Order</span>
                </a>
            </li>

            <li class="nav-item <?php echo ($current_page == 'profile.php' || $current_page == 'edit_profile.php' || $current_page == 'change_password.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>profile.php" title="My Profile">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'help.php' ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>help.php" title="Help Center">
                    <i class="fas fa-question-circle"></i>
                    <span>Help Center</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="<?php echo $login_path; ?>logout.php" class="logout-btn" title="Logout">
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

<!-- Update Cart and Wishlist Counts -->
<script>
// Function to update cart and wishlist badge counts
function updateSidebarCounts() {
    // Get cart count from localStorage
    const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
    const cartCount = cartItems.length;

    // Get wishlist count from localStorage
    const wishlistItems = JSON.parse(localStorage.getItem('wishlist')) || [];
    const wishlistCount = wishlistItems.length;

    // Update cart badge
    const cartBadge = document.getElementById('cartCount');
    if (cartBadge) {
        cartBadge.textContent = cartCount;
        cartBadge.style.display = cartCount > 0 ? 'inline-block' : 'none';
    }

    // Update wishlist badge
    const wishlistBadge = document.getElementById('wishlistCount');
    if (wishlistBadge) {
        wishlistBadge.textContent = wishlistCount;
        wishlistBadge.style.display = wishlistCount > 0 ? 'inline-block' : 'none';
    }
}

// Update counts on page load
document.addEventListener('DOMContentLoaded', updateSidebarCounts);

// Listen for storage changes (when cart/wishlist is updated in another tab)
window.addEventListener('storage', function(e) {
    if (e.key === 'cart' || e.key === 'wishlist') {
        updateSidebarCounts();
    }
});

// Listen for custom events (when cart/wishlist is updated in same tab)
window.addEventListener('cartUpdated', updateSidebarCounts);
window.addEventListener('wishlistUpdated', updateSidebarCounts);
</script>
