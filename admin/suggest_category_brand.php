<?php
/**
 * Suggest Category/Brand Page - For Pharmacy Admin
 * Pharmacy admins can suggest new categories and brands
 */
session_start();
require_once('../settings/core.php');
require_once('../controllers/suggestion_controller.php');
require_once('../controllers/customer_controller.php');

// Only pharmacy admins can access
if (!isLoggedIn() || !isPharmacyAdmin()) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = getUserId();

// Get user details for pharmacy name
$user_result = get_customer_by_id_ctr($user_id);
$user = $user_result['data'] ?? null;
$pharmacy_name = $user['customer_name'] ?? 'Unknown Pharmacy';

// Get pharmacy's suggestions
$my_category_suggestions = get_pharmacy_category_suggestions_ctr($user_id);
$my_brand_suggestions = get_pharmacy_brand_suggestions_ctr($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suggest Category/Brand - PharmaVault</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css?v=2.5">

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

        .suggestion-form-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .suggestion-item {
            background: white;
            border-radius: 10px;
            padding: 1.25rem;
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

        .status-badge {
            padding: 0.35rem 0.9rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-suggest {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-suggest:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <?php include('../view/components/sidebar_pharmacy_admin.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-lightbulb me-2"></i>Suggest Category or Brand</h1>
            <p class="text-muted mb-0">Suggest new categories or brands for super admin approval</p>
        </div>

        <div class="info-box">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Note:</strong> As a pharmacy admin, you can suggest new categories and brands.
            Your suggestions will be reviewed by the super administrator before being added to the system.
        </div>

        <div class="row">
            <!-- Suggest Category Form -->
            <div class="col-md-6">
                <div class="suggestion-form-card">
                    <h4 class="mb-4"><i class="fas fa-tag me-2"></i>Suggest Category</h4>
                    <form id="suggestCategoryForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Category Name</label>
                            <input type="text" class="form-control" name="category_name" required
                                   placeholder="e.g., Vitamins, Pain Relief, etc.">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Reason for Suggestion</label>
                            <textarea class="form-control" name="category_reason" rows="3"
                                      placeholder="Explain why this category is needed..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-suggest w-100">
                            <i class="fas fa-paper-plane me-2"></i>Submit Suggestion
                        </button>
                    </form>
                </div>
            </div>

            <!-- Suggest Brand Form -->
            <div class="col-md-6">
                <div class="suggestion-form-card">
                    <h4 class="mb-4"><i class="fas fa-certificate me-2"></i>Suggest Brand</h4>
                    <form id="suggestBrandForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Brand Name</label>
                            <input type="text" class="form-control" name="brand_name" required
                                   placeholder="e.g., Pfizer, Johnson & Johnson, etc.">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Reason for Suggestion</label>
                            <textarea class="form-control" name="brand_reason" rows="3"
                                      placeholder="Explain why this brand is needed..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-suggest w-100">
                            <i class="fas fa-paper-plane me-2"></i>Submit Suggestion
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- My Category Suggestions -->
        <div class="suggestion-form-card mt-4">
            <h4 class="mb-3"><i class="fas fa-history me-2"></i>My Category Suggestions</h4>
            <?php if (count($my_category_suggestions) > 0): ?>
                <?php foreach ($my_category_suggestions as $suggestion): ?>
                    <div class="suggestion-item <?php echo $suggestion['status']; ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($suggestion['suggested_name']); ?></h6>
                                <small class="text-muted">
                                    Suggested <?php echo date('M d, Y', strtotime($suggestion['created_at'])); ?>
                                </small>
                                <?php if (!empty($suggestion['review_comment'])): ?>
                                    <div class="alert alert-info mt-2 mb-0">
                                        <strong>Admin Response:</strong> <?php echo htmlspecialchars($suggestion['review_comment']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <span class="status-badge status-<?php echo $suggestion['status']; ?>">
                                <?php echo ucfirst($suggestion['status']); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                    <p class="mb-0">No category suggestions yet</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- My Brand Suggestions -->
        <div class="suggestion-form-card mt-4">
            <h4 class="mb-3"><i class="fas fa-history me-2"></i>My Brand Suggestions</h4>
            <?php if (count($my_brand_suggestions) > 0): ?>
                <?php foreach ($my_brand_suggestions as $suggestion): ?>
                    <div class="suggestion-item <?php echo $suggestion['status']; ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($suggestion['suggested_name']); ?></h6>
                                <small class="text-muted">
                                    Suggested <?php echo date('M d, Y', strtotime($suggestion['created_at'])); ?>
                                </small>
                                <?php if (!empty($suggestion['review_comment'])): ?>
                                    <div class="alert alert-info mt-2 mb-0">
                                        <strong>Admin Response:</strong> <?php echo htmlspecialchars($suggestion['review_comment']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <span class="status-badge status-<?php echo $suggestion['status']; ?>">
                                <?php echo ucfirst($suggestion['status']); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                    <p class="mb-0">No brand suggestions yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../js/sidebar.js?v=2.5"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Suggest Category
        document.getElementById('suggestCategoryForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('action', 'suggest_category');
            formData.append('suggested_name', this.category_name.value);
            formData.append('pharmacy_name', '<?php echo addslashes($pharmacy_name); ?>');
            formData.append('reason', this.category_reason.value);

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
                        confirmButtonColor: '#667eea'
                    }).then(() => {
                        location.reload();
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
                        confirmButtonColor: '#667eea'
                    }).then(() => {
                        location.reload();
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
    </script>
</body>
</html>
