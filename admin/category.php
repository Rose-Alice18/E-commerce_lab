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
    <title>Category Management - Pharmacy Marketplace</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .category-card {
            transition: transform 0.2s ease-in-out;
        }
        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .loading {
            display: none;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-tags me-2"></i>Category Management</h1>
                    <p class="mb-0">Manage your pharmacy service categories</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="../index.php" class="btn btn-light">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                    <a href="../login/logout.php" class="btn btn-outline-light ms-2">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Add Category Form -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-plus me-2"></i>Add New Category
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="addCategoryForm">
                            <div class="mb-3">
                                <label for="categoryName" class="form-label">Category Name</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="categoryName" 
                                       name="category_name" 
                                       placeholder="e.g., Prescription Medications, Medical Supplies"
                                       required
                                       maxlength="100">
                                <div class="form-text">
                                    Enter a unique name for your pharmacy service category
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Add Category
                                <span class="loading spinner-border spinner-border-sm ms-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Category Examples
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-pill text-primary me-2"></i>Prescription Medications</li>
                            <li><i class="fas fa-capsules text-success me-2"></i>Over-the-Counter Drugs</li>
                            <li><i class="fas fa-first-aid text-danger me-2"></i>Medical Supplies</li>
                            <li><i class="fas fa-leaf text-warning me-2"></i>Health Supplements</li>
                            <li><i class="fas fa-heartbeat text-info me-2"></i>Medical Equipment</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Display -->
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Your Categories
                </h5>
                <button class="btn btn-light btn-sm" onclick="loadCategories()">
                    <i class="fas fa-refresh me-1"></i>Refresh
                </button>
            </div>
            <div class="card-body">
                <div id="categoriesContainer">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading categories...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading your categories...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the category "<span id="deleteCategoryName"></span>"?</p>
                    <p class="text-muted">This action cannot be undone.</p>
                    <input type="hidden" id="deleteCategoryId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash me-1"></i>Delete Category
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <!-- Category JS -->
    <script src="../js/category.js"></script>
    
    <!--script>
        // Load categories when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadCategories();
        });
    </script-->
</body>
</html>