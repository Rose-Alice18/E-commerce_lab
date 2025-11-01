<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include core functions (this will start the session)
require_once(dirname(__FILE__) . '/../settings/core.php');

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit();
}

// Check if user is admin
if (!hasAdminPrivileges()) {
    header("Location: ../login/login.php");
    exit();
}

// Get user ID from session
$user_id = getUserId();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management - PharmaVault</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

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
            --dark-bg: #1a1a2e;
            --light-bg: #f8f9fa;
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
            --dark-bg: #1a1a2e;
            --light-bg: #f8f9fa;
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
            overflow-x: hidden;
            background: var(--light-bg);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        
        /* Modern Navbar */
        .modern-navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-link {
            font-weight: 500;
            color: #2d3748 !important;
            margin: 0 0.5rem;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background: rgba(5, 150, 105, 0.1);
            transform: translateY(-2px);
        }

        .btn-gradient-primary {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
        }

        .btn-gradient-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
            color: white;
        }

        .btn-outline-gradient {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            background: transparent;
            transition: all 0.3s ease;
        }

        .btn-outline-gradient:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateY(-2px);
        }
        
        /* Page Header */
        .page-header {
            background: var(--primary-gradient);
            padding: 80px 0 60px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -200px;
            right: -100px;
        }
        
        .page-header-content {
            position: relative;
            z-index: 2;
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.95;
        }
        
        /* Main Content */
        .content-section {
            padding: 40px 0;
        }
        
        /* Modern Cards */
        .modern-card {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .modern-card:hover {
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12);
        }
        
        .card-header-gradient {
            background: var(--primary-gradient);
            color: white;
            padding: 1.5rem;
            border: none;
        }
        
        .card-header-gradient h5 {
            margin: 0;
            font-weight: 700;
        }
        
        .card-body-modern {
            padding: 2rem;
        }
        
        /* Form Styling */
        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }
        
        .form-text {
            color: #718096;
            font-size: 0.875rem;
        }
        
        /* Loading Spinner */
        .loading {
            display: none;
        }
        
        /* Example Categories */
        .example-item {
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, rgba(5, 150, 105, 0.05) 0%, rgba(4, 120, 87, 0.05) 100%);
            border-radius: 12px;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .example-item:hover {
            background: linear-gradient(135deg, rgba(5, 150, 105, 0.1) 0%, rgba(4, 120, 87, 0.1) 100%);
            border-left-color: var(--primary-color);
            transform: translateX(5px);
        }
        
        .example-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        /* Categories Display */
        .category-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-color: var(--primary-color);
        }
        
        .category-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .category-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .category-date {
            font-size: 0.875rem;
            color: #718096;
            margin-bottom: 1rem;
        }
        
        .category-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-action {
            flex: 1;
            padding: 0.5rem;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-edit {
            background: rgba(5, 150, 105, 0.1);
            color: var(--primary-color);
            border: none;
        }
        
        .btn-edit:hover {
            background: var(--primary-gradient);
            color: white;
        }
        
        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: none;
        }
        
        .btn-delete:hover {
            background: #ef4444;
            color: white;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .empty-icon {
            font-size: 5rem;
            color: #cbd5e0;
            margin-bottom: 1.5rem;
        }
        
        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .empty-text {
            color: #718096;
        }
        
        /* Alert Styling */
        .alert-fixed {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 350px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            border: none;
        }
        
        /* Modal Styling */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem;
        }
        
        .modal-title {
            font-weight: 700;
            color: #2d3748;
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .modal-footer {
            border-top: 1px solid #e2e8f0;
            padding: 1.5rem;
        }

        /* Category Grid Layout */
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        /* Modern Card Styles */
        .modern-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .card-header-gradient {
            background: var(--primary-gradient);
            color: white;
            padding: 1.25rem 1.5rem;
        }

        .card-header-gradient h5 {
            margin: 0;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .card-body-modern {
            padding: 1.5rem;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 70px !important; /* Collapsed sidebar on tablet */
            }
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.75rem !important;
            }

            .page-header {
                padding: 60px 0 40px !important;
            }

            .category-actions {
                flex-direction: column;
            }

            .btn-action {
                font-size: 0.8rem;
            }

            .category-grid {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }

            .form-grid {
                grid-template-columns: 1fr !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding: 1rem !important;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.5rem !important;
            }

            .stat-value {
                font-size: 1.75rem !important;
            }

            .category-card {
                padding: 1rem !important;
            }

            .btn-gradient-primary,
            .btn-outline-gradient {
                padding: 0.5rem 1rem !important;
                font-size: 0.875rem !important;
            }
        }
        
        /* Animations */
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
        
        .animate-fade-in {
            animation: fadeInUp 0.5s ease-out forwards;
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
        <div style="background: white; border-radius: 15px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin: 0; font-size: 2rem; font-weight: 700;">
                        <i class="fas fa-tags" style="margin-right: 0.75rem;"></i>Category Management
                    </h1>
                    <p style="color: #64748b; margin: 0.5rem 0 0 0;">Organize your pharmaceutical products into categories for better customer experience</p>
                </div>
                <div style="text-align: right;">
                    <p style="margin: 0; color: #64748b;">Welcome,</p>
                    <p style="margin: 0.25rem 0 0 0; font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></p>
                    <small style="color: #3b82f6; font-weight: 600;">Platform Administrator</small>
                </div>
            </div>
        </div>

        <div style="max-width: 1400px; margin: 0 auto;">
            <!-- Add Category Section -->
            <div class="row g-4 mb-4">
                <!-- Add Category Form -->
                <div class="col-lg-6">
                    <div class="modern-card animate-fade-in">
                        <div class="card-header-gradient">
                            <h5>
                                <i class="fas fa-plus-circle me-2"></i>Add New Category
                            </h5>
                        </div>
                        <div class="card-body-modern">
                            <form id="addCategoryForm">
                                <div class="mb-4">
                                    <label for="categoryName" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Category Name
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="categoryName" 
                                           name="category_name" 
                                           placeholder="e.g., Pain Relief Medications"
                                           required
                                           maxlength="100">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Enter a descriptive name for your product category
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-gradient-primary w-100">
                                    <i class="fas fa-plus me-2"></i>Add Category
                                    <span class="loading spinner-border spinner-border-sm ms-2" role="status"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Category Examples -->
                <div class="col-lg-6">
                    <div class="modern-card animate-fade-in" style="animation-delay: 0.1s;">
                        <div class="card-header-gradient">
                            <h5>
                                <i class="fas fa-lightbulb me-2"></i>Category Examples & Suggestions - *Scroll for more*
                            </h5>
                        </div>
                        <div class="card-body-modern" style="max-height: 450px; overflow-y: auto;">
                            <div class="example-item">
                                <span class="example-icon" style="background: var(--primary-gradient);">
                                    <i class="fas fa-pills text-white"></i>
                                </span>
                                <div>
                                    <strong>Prescription Medications</strong>
                                    <p class="mb-0 small text-muted">Antibiotics, antihypertensives, insulin</p>
                                </div>
                            </div>
                            
                            <div class="example-item">
                                <span class="example-icon" style="background: var(--secondary-gradient);">
                                    <i class="fas fa-capsules text-white"></i>
                                </span>
                                <div>
                                    <strong>Over-the-Counter (OTC)</strong>
                                    <p class="mb-0 small text-muted">Pain relievers, cough syrups, antacids</p>
                                </div>
                            </div>
                            
                            <div class="example-item">
                                <span class="example-icon" style="background: var(--success-gradient);">
                                    <i class="fas fa-heartbeat text-white"></i>
                                </span>
                                <div>
                                    <strong>Vitamins & Supplements</strong>
                                    <p class="mb-0 small text-muted">Multivitamins, calcium, omega-3</p>
                                </div>
                            </div>
                            
                            <div class="example-item">
                                <span class="example-icon" style="background: var(--primary-gradient);">
                                    <i class="fas fa-baby text-white"></i>
                                </span>
                                <div>
                                    <strong>Mother & Baby Care</strong>
                                    <p class="mb-0 small text-muted">Baby formula, diapers, prenatal vitamins</p>
                                </div>
                            </div>
                            
                            <div class="example-item">
                                <span class="example-icon" style="background: var(--secondary-gradient);">
                                    <i class="fas fa-spa text-dark"></i>
                                </span>
                                <div>
                                    <strong>Personal Care</strong>
                                    <p class="mb-0 small text-muted">Skincare, oral care, hygiene products</p>
                                </div>
                            </div>
                            
                            <div class="example-item">
                                <span class="example-icon" style="background: var(--success-gradient);">
                                    <i class="fas fa-first-aid text-white"></i>
                                </span>
                                <div>
                                    <strong>Medical Supplies</strong>
                                    <p class="mb-0 small text-muted">Bandages, thermometers, masks</p>
                                </div>
                            </div>
                            
                            <div class="example-item">
                                <span class="example-icon" style="background: var(--primary-gradient);">
                                    <i class="fas fa-laptop-medical text-white"></i>
                                </span>
                                <div>
                                    <strong>Medical Equipment</strong>
                                    <p class="mb-0 small text-muted">Blood pressure monitors, glucometers</p>
                                </div>
                            </div>
                            
                            <div class="example-item">
                                <span class="example-icon" style="background: var(--secondary-gradient);">
                                    <i class="fas fa-leaf text-success"></i>
                                </span>
                                <div>
                                    <strong>Herbal & Natural</strong>
                                    <p class="mb-0 small text-muted">Herbal supplements, natural remedies</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Display -->
            <div class="row">
                <div class="col-12">
                    <div class="modern-card animate-fade-in" style="animation-delay: 0.2s;">
                        <div class="card-header-gradient d-flex justify-content-between align-items-center">
                            <h5>
                                <i class="fas fa-th-large me-2"></i>Your Categories
                            </h5>
                            <button class="btn btn-light btn-sm" onclick="loadCategories()">
                                <i class="fas fa-sync-alt me-1"></i>Refresh
                            </button>
                        </div>
                        <div class="card-body-modern" style="min-height: 400px;">
                            <div id="categoriesContainer">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading categories...</span>
                                    </div>
                                    <p class="mt-3 text-muted">Loading your categories...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editCategoryForm">
                    <div class="modal-body">
                        <input type="hidden" id="editCategoryId" name="cat_id">
                        <div class="mb-3">
                            <label for="editCategoryName" class="form-label">Category Name</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="editCategoryName" 
                                   name="category_name" 
                                   required
                                   maxlength="100">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-gradient-primary">
                            <i class="fas fa-save me-1"></i>Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Are you sure you want to delete the category:</p>
                    <p class="fs-5 fw-bold text-center p-3 bg-light rounded">
                        "<span id="deleteCategoryName"></span>"
                    </p>
                    <p class="text-muted small mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        This action cannot be undone.
                    </p>
                    <input type="hidden" id="deleteCategoryId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash me-1"></i>Delete Category
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js"></script>
    <!-- Category JS -->
    <script src="../js/category.js"></script>
</body>
</html>