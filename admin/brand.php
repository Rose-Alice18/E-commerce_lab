<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core functions and controllers
require_once('../settings/core.php');
require_once('../controllers/brand_controller.php');
require_once('../controllers/category_controller.php');
require_once('../controllers/suggestion_controller.php');
require_once('../controllers/customer_controller.php');

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit();
}

// Check if user is admin (only admins manage platform-wide brands)
if (!hasAdminPrivileges()) {
    header("Location: ../login/login.php");
    exit();
}

$user_name = $_SESSION['user_name'];
$user_id = getUserId();

// Get all platform brands (both super admin and pharmacy admin see all platform brands)
$brands_result = get_all_brands_ctr(null); // null = fetch all platform brands
$brands = $brands_result['data'] ?? [];

// Get all platform categories (both super admin and pharmacy admin see all platform categories)
$categories_result = fetch_all_categories_ctr();
$categories = $categories_result['data'] ?? [];

// Get user details for pharmacy name (if pharmacy admin)
$pharmacy_name = '';
if (isPharmacyAdmin()) {
    $user_result = get_customer_by_id_ctr($user_id);
    $user = $user_result['data'] ?? null;
    $pharmacy_name = $user['customer_name'] ?? '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brand Management - PharmaVault</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebar.css">
    
    <style>
        :root {
            <?php if (isSuperAdmin()): ?>
            /* Super Admin - Purple/Blue Theme */
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            --primary-color: #667eea;
            --primary-hover: #764ba2;
            --sidebar-width: 280px;
            <?php else: ?>
            /* Pharmacy Admin - Green Theme */
            --primary-gradient: linear-gradient(135deg, #059669 0%, #047857 100%);
            --secondary-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            --primary-color: #059669;
            --primary-hover: #047857;
            --sidebar-width: 280px;
            <?php endif; ?>
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            overflow-x: hidden;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            animation: fadeInDown 0.6s ease;
        }

        .page-header h1 {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .content-wrapper {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 2rem;
            animation: fadeInUp 0.6s ease;
        }

        /* Add Brand Form */
        .add-brand-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .add-brand-card h3 {
            color: #2d3748;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .add-brand-card h3 i {
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
            outline: none;
        }

        .btn-add-brand {
            width: 100%;
            padding: 0.875rem;
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-add-brand:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(5, 150, 105, 0.3);
        }

        .btn-add-brand:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Brands Display */
        .brands-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .brands-section h3 {
            color: #2d3748;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brands-section h3 i {
            color: var(--primary-color);
        }

        .category-group {
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary-color);
            padding-left: 1rem;
        }

        .category-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .category-title i {
            color: var(--primary-color);
        }

        .brands-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }

        .brand-card {
            background: linear-gradient(135deg, #f6f8fb 0%, #ffffff 100%);
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 1.25rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .brand-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }

        .brand-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .brand-icon {
            width: 45px;
            height: 45px;
            background: var(--primary-gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }

        .brand-actions {
            display: flex;
            gap: 0.5rem;
        }

        .brand-actions button {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: #4299e1;
            color: white;
        }

        .btn-edit:hover {
            background: #3182ce;
            transform: scale(1.1);
        }

        .btn-delete {
            background: #fc8181;
            color: white;
        }

        .btn-delete:hover {
            background: #f56565;
            transform: scale(1.1);
        }

        .brand-name {
            font-weight: 600;
            color: #2d3748;
            margin: 0;
            font-size: 1.1rem;
        }

        .brand-meta {
            color: #718096;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #718096;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 20px;
            border: none;
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 20px 20px 0 0;
            border-bottom: none;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            border-top: none;
            padding: 1rem 2rem 2rem;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 70px !important; /* Collapsed sidebar on tablet */
            }

            .content-wrapper {
                grid-template-columns: 1fr;
            }

            .brands-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }

        /* Suggestion Styles */
        .suggestion-item {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            border-left: 4px solid #667eea;
        }

        .suggestion-item.pending {
            border-left-color: #f59e0b;
        }

        .suggestion-item.approved {
            border-left-color: #10b981;
        }

        .suggestion-item.rejected {
            border-left-color: #ef4444;
        }

        @media (max-width: 992px) {
            .content-wrapper {
                grid-template-columns: 1fr;
            }

            .add-brand-card {
                position: static;
                margin-bottom: 2rem;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
                padding: 1rem !important;
            }

            .page-header {
                padding: 1.5rem !important;
                margin-bottom: 1.5rem !important;
            }

            .page-header h1 {
                font-size: 1.75rem !important;
            }

            .brands-grid {
                grid-template-columns: 1fr !important;
                gap: 0.75rem;
            }

            .brand-card {
                padding: 1rem !important;
            }

            .brand-actions {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn-edit,
            .btn-delete {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1.5rem !important;
            }

            .form-control,
            .form-select {
                font-size: 14px;
            }

            .btn-add-brand {
                padding: 0.75rem;
                font-size: 0.875rem;
            }

            .category-title {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Include Sidebar (role-based) -->
    <?php
    if (isSuperAdmin()) {
        include('../view/components/sidebar_super_admin.php');
    } elseif (isPharmacyAdmin()) {
        include('../view/components/sidebar_pharmacy_admin.php');
    }
    ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1><i class="fas fa-copyright" style="margin-right: 0.75rem;"></i>Brand Management</h1>
                    <p style="color: #64748b; margin: 0.5rem 0 0 0;">Manage product brands available on the platform (e.g., Panadol, Centrum, Johnson's Baby)</p>
                </div>
                <div style="text-align: right;">
                    <p style="margin: 0; color: #64748b;">Welcome,</p>
                    <p style="margin: 0.25rem 0 0 0; font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($user_name); ?></p>
                    <small style="color: #3b82f6; font-weight: 600;">Platform Administrator</small>
                </div>
            </div>
        </div>
        <div class="content-wrapper">
            <?php if (isSuperAdmin()): ?>
            <!-- Add Brand Form (Super Admin Only) -->
            <div class="add-brand-card">
                <h3><i class="fas fa-plus-circle"></i> Add New Brand</h3>

                <form id="addBrandForm">
                    <div class="form-group">
                        <label class="form-label">Brand Name</label>
                        <input
                            type="text"
                            class="form-control"
                            id="brandName"
                            name="brand_name"
                            placeholder="e.g., Panadol, Centrum, Dettol"
                            required
                        >
                        <small class="text-muted">Enter the recognized product brand name</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select class="form-select" id="brandCategory" name="cat_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['cat_id']; ?>">
                                    <?php echo htmlspecialchars($category['cat_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($categories)): ?>
                        <small class="text-danger">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Please create categories first
                        </small>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn-add-brand" <?php echo empty($categories) ? 'disabled' : ''; ?>>
                        <i class="fas fa-plus me-2"></i>Add Brand
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Brands Display -->
            <div class="brands-section">
                <h3>
                    <span><i class="fas fa-list"></i> Platform Brands</span>
                    <span class="badge bg-primary" id="brandCount"><?php echo count($brands); ?></span>
                </h3>

                <div id="brandsContainer" data-can-edit="<?php echo isSuperAdmin() ? 'true' : 'false'; ?>">
                    <?php if (empty($brands)): ?>
                        <div class="empty-state">
                            <i class="fas fa-award"></i>
                            <h4>No Brands Yet</h4>
                            <p>No product brands have been created yet</p>
                        </div>
                    <?php else: 
                        // Group brands by category
                        $grouped_brands = [];
                        foreach ($brands as $brand) {
                            $cat_name = $brand['cat_name'];
                            if (!isset($grouped_brands[$cat_name])) {
                                $grouped_brands[$cat_name] = [];
                            }
                            $grouped_brands[$cat_name][] = $brand;
                        }

                        // Display grouped brands
                        foreach ($grouped_brands as $cat_name => $cat_brands):
                    ?>
                        <div class="category-group">
                            <div class="category-title">
                                <i class="fas fa-folder"></i>
                                <?php echo htmlspecialchars($cat_name); ?>
                                <span class="badge bg-secondary ms-2"><?php echo count($cat_brands); ?></span>
                            </div>
                            <div class="brands-grid">
                                <?php foreach ($cat_brands as $brand): ?>
                                    <div class="brand-card">
                                        <div class="brand-card-header">
                                            <div class="brand-icon">
                                                <i class="fas fa-award"></i>
                                            </div>
                                            <?php if (isSuperAdmin()): ?>
                                            <div class="brand-actions">
                                                <button
                                                    class="btn-edit"
                                                    onclick="editBrand(<?php echo $brand['brand_id']; ?>, '<?php echo htmlspecialchars($brand['brand_name'], ENT_QUOTES); ?>', <?php echo $brand['cat_id']; ?>)"
                                                    title="Edit Brand"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button
                                                    class="btn-delete"
                                                    onclick="deleteBrand(<?php echo $brand['brand_id']; ?>, '<?php echo htmlspecialchars($brand['brand_name'], ENT_QUOTES); ?>')"
                                                    title="Delete Brand"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <h5 class="brand-name"><?php echo htmlspecialchars($brand['brand_name']); ?></h5>
                                        <div class="brand-meta">
                                            <i class="fas fa-clock me-1"></i>
                                            Added <?php echo date('M d, Y', strtotime($brand['created_at'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php 
                        endforeach;
                    endif; 
                    ?>
                </div>
            </div>

            <?php if (isPharmacyAdmin()): ?>
            <!-- Suggest Brand Section (Pharmacy Admin Only) -->
            <div class="brands-section mt-4">
                <h3>
                    <i class="fas fa-lightbulb me-2"></i>Suggest New Brand
                </h3>
                <p class="text-muted mb-3">Suggest new brands for super admin approval</p>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> As a pharmacy admin, you can suggest new brands. Your suggestions will be reviewed by the super administrator before being added to the system.
                </div>

                <div class="row g-4">
                    <!-- Suggest Form -->
                    <div class="col-lg-6">
                        <div class="add-brand-card">
                            <h4><i class="fas fa-paper-plane me-2"></i>Submit Suggestion</h4>
                            <form id="suggestBrandForm">
                                <div class="form-group">
                                    <label class="form-label fw-bold">Brand Name</label>
                                    <input type="text" class="form-control" name="brand_name" required
                                           placeholder="e.g., Pfizer, Johnson & Johnson, etc.">
                                </div>
                                <div class="form-group">
                                    <label class="form-label fw-bold">Reason for Suggestion</label>
                                    <textarea class="form-control" name="brand_reason" rows="3"
                                              placeholder="Explain why this brand is needed..."></textarea>
                                </div>
                                <button type="submit" class="btn-add-brand">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Suggestion
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- My Suggestions -->
                    <div class="col-lg-6">
                        <div class="add-brand-card">
                            <h4><i class="fas fa-history me-2"></i>My Brand Suggestions</h4>
                            <div id="myBrandSuggestions" style="max-height: 400px; overflow-y: auto;">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Brand Modal -->
    <div class="modal fade" id="editBrandModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Brand
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editBrandForm">
                    <div class="modal-body">
                        <input type="hidden" id="editBrandId" name="brand_id">
                        
                        <div class="form-group">
                            <label class="form-label">Brand Name</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="editBrandName" 
                                name="brand_name"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select class="form-select" id="editBrandCategory" name="cat_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['cat_id']; ?>">
                                        <?php echo htmlspecialchars($category['cat_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js"></script>

    <!-- Custom JS -->
    <script src="../js/brand.js"></script>

    <?php if (isPharmacyAdmin()): ?>
    <!-- Suggestion Functionality -->
    <script>
        // Load pharmacy's brand suggestions on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadMyBrandSuggestions();
        });

        // Suggest Brand
        document.getElementById('suggestBrandForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('action', 'suggest_brand');
            formData.append('suggested_name', this.brand_name.value);
            formData.append('pharmacy_name', '<?php echo addslashes($pharmacy_name); ?>');
            formData.append('reason', this.brand_reason.value);

            fetch('../actions/suggestion_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonColor: '<?php echo isPharmacyAdmin() ? "#059669" : "#667eea"; ?>'
                    }).then(() => {
                        this.reset();
                        loadMyBrandSuggestions();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    confirmButtonColor: '#ef4444'
                });
            });
        });

        // Load My Brand Suggestions
        function loadMyBrandSuggestions() {
            fetch('../actions/get_suggestions.php?type=brand&pharmacy_id=<?php echo $user_id; ?>')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('myBrandSuggestions');

                    if (data.success && data.suggestions && data.suggestions.length > 0) {
                        container.innerHTML = data.suggestions.map(suggestion => `
                            <div class="suggestion-item ${suggestion.status}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">${escapeHtml(suggestion.suggested_name)}</h6>
                                        <small class="text-muted">
                                            Suggested ${new Date(suggestion.created_at).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}
                                        </small>
                                        ${suggestion.review_comment ? `
                                            <div class="alert alert-info mt-2 mb-0">
                                                <strong>Admin Response:</strong> ${escapeHtml(suggestion.review_comment)}
                                            </div>
                                        ` : ''}
                                    </div>
                                    <span class="badge ${
                                        suggestion.status === 'pending' ? 'bg-warning' :
                                        suggestion.status === 'approved' ? 'bg-success' : 'bg-danger'
                                    }">
                                        ${suggestion.status.charAt(0).toUpperCase() + suggestion.status.slice(1)}
                                    </span>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        container.innerHTML = `
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                                <p class="mb-0 mt-2">No brand suggestions yet</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    document.getElementById('myBrandSuggestions').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading suggestions
                        </div>
                    `;
                });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
    <?php endif; ?>
</body>
</html>