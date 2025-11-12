<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core functions
require_once('../settings/core.php');
require_once('../controllers/customer_controller.php');

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = getUserId();
$user_role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? 2;

// Get user details
$user_result = get_customer_by_id_ctr($user_id);
$user = $user_result['data'] ?? null;

if (!$user) {
    die("User not found");
}

// Determine which sidebar to include
$sidebar_file = '';
$page_title = 'My Profile';

// Handle both role 0 and role 7 as Super Admin
if ($user_role == 0 || $user_role == 7) {
    $sidebar_file = 'sidebar_super_admin.php';
    $page_title = 'Admin Profile';
} elseif ($user_role == 1) {
    $sidebar_file = 'sidebar_pharmacy_admin.php';
    $page_title = 'Pharmacy Profile';
} else {
    $sidebar_file = 'sidebar_customer.php';
    $page_title = 'My Profile';
}

// Get role-specific stats
$stats = [];
if ($user_role == 1) {
    require_once('../controllers/product_controller.php');
    $products_result = get_products_by_pharmacy_ctr($user_id);
    $products = $products_result['data'] ?? [];
    $stats = [
        'total_products' => count($products),
        'in_stock' => count(array_filter($products, fn($p) => ($p['product_stock'] ?? 0) > 0)),
        'low_stock' => count(array_filter($products, fn($p) => ($p['product_stock'] ?? 0) > 0 && ($p['product_stock'] ?? 0) <= 10))
    ];
} elseif ($user_role == 2) {
    require_once('../classes/product_class.php');
    $product_obj = new Product();
    $stats = [
        'cart_count' => $product_obj->get_cart_count($user_id),
        'orders_count' => $product_obj->get_customer_orders_count($user_id),
        'pending_deliveries' => $product_obj->get_pending_deliveries($user_id)
    ];
}

// Get all categories and brands for dropdowns
require_once('../classes/category_class.php');
require_once('../classes/brand_class.php');
$category_obj = new Category();
$categories = $category_obj->fetch_all_categories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - PharmaVault</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebar.css?v=2.5">

    <!-- Profile CSS -->
    <link rel="stylesheet" href="../css/profile.css">
</head>
<body>
    <!-- Include Appropriate Sidebar -->
    <?php include('../view/components/' . $sidebar_file); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="profile-container">
            <!-- Profile Header Card -->
            <div class="profile-header-card">
                <div class="profile-cover">
                    <div class="cover-gradient"></div>
                </div>

                <div class="profile-header-content">
                    <div class="profile-avatar-section">
                        <div class="profile-avatar" id="profileAvatar">
                            <?php if (!empty($user['customer_image'])): ?>
                                <img src="../<?php echo htmlspecialchars($user['customer_image']); ?>" alt="Profile">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <button class="avatar-edit-btn" onclick="document.getElementById('avatarUploadInput').click()">
                            <i class="fas fa-camera"></i>
                        </button>
                        <input type="file" id="avatarUploadInput" accept="image/*" style="display: none;">
                    </div>

                    <div class="profile-info">
                        <h1 class="profile-name"><?php echo htmlspecialchars($user['customer_name']); ?></h1>
                        <p class="profile-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['customer_email']); ?></p>
                        <div class="profile-badges">
                            <span class="role-badge role-<?php echo $user_role; ?>">
                                <i class="fas <?php
                                    if ($user_role == 0) echo 'fa-crown';
                                    elseif ($user_role == 1) echo 'fa-hospital';
                                    else echo 'fa-user';
                                ?>"></i>
                                <?php
                                if ($user_role == 0) echo "Super Administrator";
                                elseif ($user_role == 1) echo "Pharmacy Administrator";
                                else echo "Customer";
                                ?>
                            </span>
                            <span class="member-badge">
                                <i class="fas fa-calendar-check"></i>
                                Member since <?php echo isset($user['created_at']) ? date('M Y', strtotime($user['created_at'])) : 'N/A'; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <?php if (!empty($stats)): ?>
                    <div class="quick-stats">
                        <?php if ($user_role == 1): ?>
                            <div class="stat-item">
                                <div class="stat-icon"><i class="fas fa-boxes"></i></div>
                                <div class="stat-details">
                                    <span class="stat-value"><?php echo $stats['total_products']; ?></span>
                                    <span class="stat-label">Products</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                                <div class="stat-details">
                                    <span class="stat-value"><?php echo $stats['in_stock']; ?></span>
                                    <span class="stat-label">In Stock</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                                <div class="stat-details">
                                    <span class="stat-value"><?php echo $stats['low_stock']; ?></span>
                                    <span class="stat-label">Low Stock</span>
                                </div>
                            </div>
                        <?php elseif ($user_role == 2): ?>
                            <div class="stat-item">
                                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                                <div class="stat-details">
                                    <span class="stat-value"><?php echo $stats['cart_count']; ?></span>
                                    <span class="stat-label">Cart Items</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                                <div class="stat-details">
                                    <span class="stat-value"><?php echo $stats['orders_count']; ?></span>
                                    <span class="stat-label">Total Orders</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon"><i class="fas fa-truck"></i></div>
                                <div class="stat-details">
                                    <span class="stat-value"><?php echo $stats['pending_deliveries']; ?></span>
                                    <span class="stat-label">Pending</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="profile-tabs">
                <button class="tab-btn active" data-tab="overview">
                    <i class="fas fa-user"></i>
                    <span>Overview</span>
                </button>
                <button class="tab-btn" data-tab="edit-profile">
                    <i class="fas fa-edit"></i>
                    <span>Edit Profile</span>
                </button>
                <button class="tab-btn" data-tab="security">
                    <i class="fas fa-shield-alt"></i>
                    <span>Security</span>
                </button>
                <?php if ($user_role == 1): ?>
                <button class="tab-btn" data-tab="pharmacy-info">
                    <i class="fas fa-hospital"></i>
                    <span>Pharmacy Info</span>
                </button>
                <?php endif; ?>
            </div>

            <!-- Tab Content -->
            <div class="tabs-content">
                <!-- Overview Tab -->
                <div class="tab-content active" id="overview-tab">
                    <div class="content-grid">
                        <div class="info-card">
                            <div class="card-header">
                                <h3><i class="fas fa-user-circle"></i> Personal Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="info-row">
                                    <span class="info-label">Full Name</span>
                                    <span class="info-value"><?php echo htmlspecialchars($user['customer_name']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Email Address</span>
                                    <span class="info-value"><?php echo htmlspecialchars($user['customer_email']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Phone Number</span>
                                    <span class="info-value"><?php echo htmlspecialchars($user['customer_contact']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="card-header">
                                <h3><i class="fas fa-map-marker-alt"></i> Location Details</h3>
                            </div>
                            <div class="card-body">
                                <div class="info-row">
                                    <span class="info-label">Country</span>
                                    <span class="info-value"><?php echo htmlspecialchars($user['customer_country']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">City</span>
                                    <span class="info-value"><?php echo htmlspecialchars($user['customer_city']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="card-header">
                                <h3><i class="fas fa-clock"></i> Account Activity</h3>
                            </div>
                            <div class="card-body">
                                <div class="info-row">
                                    <span class="info-label">Member Since</span>
                                    <span class="info-value"><?php echo isset($user['created_at']) ? date('F d, Y', strtotime($user['created_at'])) : 'N/A'; ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Last Updated</span>
                                    <span class="info-value"><?php echo isset($user['updated_at']) ? date('F d, Y', strtotime($user['updated_at'])) : 'N/A'; ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Account Status</span>
                                    <span class="info-value">
                                        <span class="status-badge active">
                                            <i class="fas fa-check-circle"></i> Active
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Tab -->
                <div class="tab-content" id="edit-profile-tab">
                    <div class="form-card">
                        <div class="card-header">
                            <h3><i class="fas fa-user-edit"></i> Update Your Information</h3>
                            <p>Keep your profile information up to date</p>
                        </div>
                        <div class="card-body">
                            <form id="editProfileForm" enctype="multipart/form-data">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-user"></i> Full Name
                                        </label>
                                        <input type="text" class="form-control" name="customer_name"
                                               value="<?php echo htmlspecialchars($user['customer_name']); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-envelope"></i> Email Address
                                        </label>
                                        <input type="email" class="form-control" name="customer_email"
                                               value="<?php echo htmlspecialchars($user['customer_email']); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-phone"></i> Phone Number
                                        </label>
                                        <input type="tel" class="form-control" name="customer_contact"
                                               value="<?php echo htmlspecialchars($user['customer_contact']); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-globe"></i> Country
                                        </label>
                                        <input type="text" class="form-control" name="customer_country"
                                               value="<?php echo htmlspecialchars($user['customer_country']); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-city"></i> City
                                        </label>
                                        <input type="text" class="form-control" name="customer_city"
                                               value="<?php echo htmlspecialchars($user['customer_city']); ?>" required>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="switchTab('overview')">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div class="tab-content" id="security-tab">
                    <div class="form-card">
                        <div class="card-header">
                            <h3><i class="fas fa-lock"></i> Change Password</h3>
                            <p>Ensure your account is using a strong password</p>
                        </div>
                        <div class="card-body">
                            <form id="changePasswordForm">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-key"></i> Current Password
                                    </label>
                                    <div class="password-input-wrapper">
                                        <input type="password" class="form-control" id="currentPassword"
                                               name="current_password" required>
                                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('currentPassword', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-lock"></i> New Password
                                    </label>
                                    <div class="password-input-wrapper">
                                        <input type="password" class="form-control" id="newPassword"
                                               name="new_password" required minlength="8">
                                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('newPassword', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength" id="passwordStrength"></div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-check-circle"></i> Confirm New Password
                                    </label>
                                    <div class="password-input-wrapper">
                                        <input type="password" class="form-control" id="confirmPassword"
                                               name="confirm_password" required>
                                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirmPassword', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="password-requirements">
                                    <h4><i class="fas fa-info-circle"></i> Password Requirements</h4>
                                    <ul>
                                        <li id="req-length"><i class="fas fa-circle"></i> Minimum 8 characters</li>
                                        <li id="req-uppercase"><i class="fas fa-circle"></i> At least one uppercase letter</li>
                                        <li id="req-lowercase"><i class="fas fa-circle"></i> At least one lowercase letter</li>
                                        <li id="req-number"><i class="fas fa-circle"></i> At least one number</li>
                                        <li id="req-special"><i class="fas fa-circle"></i> At least one special character</li>
                                    </ul>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-shield-alt"></i> Update Password
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="switchTab('overview')">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Pharmacy Info Tab (Only for Pharmacy Admins) -->
                <?php if ($user_role == 1): ?>
                <div class="tab-content" id="pharmacy-info-tab">
                    <div class="info-card">
                        <div class="card-header">
                            <h3><i class="fas fa-hospital"></i> Pharmacy Statistics</h3>
                        </div>
                        <div class="card-body">
                            <div class="stats-grid">
                                <div class="stat-box">
                                    <div class="stat-icon purple">
                                        <i class="fas fa-boxes"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h4><?php echo $stats['total_products']; ?></h4>
                                        <p>Total Products</p>
                                    </div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-icon green">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h4><?php echo $stats['in_stock']; ?></h4>
                                        <p>In Stock</p>
                                    </div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-icon orange">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h4><?php echo $stats['low_stock']; ?></h4>
                                        <p>Low Stock</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Hidden user data for JavaScript -->
    <script>
        const USER_ID = <?php echo $user_id; ?>;
        const USER_ROLE = <?php echo $user_role; ?>;
    </script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js"></script>

    <!-- Cart and Wishlist JS -->
    <script src="../js/cart-wishlist.js"></script>

    <!-- Profile JS -->
    <script src="../js/profile.js"></script>
</body>
</html>
