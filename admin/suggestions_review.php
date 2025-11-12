<?php
/**
 * Suggestions Review Page - For Super Admin
 * Review and approve/reject category and brand suggestions from pharmacy admins
 */
session_start();
require_once('../settings/core.php');
require_once('../controllers/suggestion_controller.php');

// Only super admins can access
if (!isLoggedIn() || !isSuperAdmin()) {
    header("Location: ../login/login.php");
    exit();
}

// Get all suggestions
$pending_category_suggestions = get_pending_category_suggestions_ctr();
$pending_brand_suggestions = get_pending_brand_suggestions_ctr();
$all_category_suggestions = get_all_category_suggestions_ctr();
$all_brand_suggestions = get_all_brand_suggestions_ctr();

$total_pending = count($pending_category_suggestions) + count($pending_brand_suggestions);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Suggestions - PharmaVault</title>

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

        .suggestion-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #667eea;
        }

        .suggestion-card.pending {
            border-left-color: #f59e0b;
        }

        .suggestion-card.approved {
            border-left-color: #10b981;
        }

        .suggestion-card.rejected {
            border-left-color: #ef4444;
        }

        .suggestion-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .suggestion-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }

        .status-badge {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
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

        .pharmacy-info {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .pharmacy-info strong {
            color: #667eea;
        }

        .reason-box {
            background: #fef9f3;
            border-left: 3px solid #f59e0b;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .btn-approve {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-reject {
            background: transparent;
            border: 2px solid #ef4444;
            color: #ef4444;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-reject:hover {
            background: #ef4444;
            color: white;
        }

        .nav-tabs .nav-link {
            color: #64748b;
            border: none;
            padding: 1rem 1.5rem;
            font-weight: 500;
        }

        .nav-tabs .nav-link.active {
            color: #667eea;
            background: white;
            border-bottom: 3px solid #667eea;
        }

        .badge-count {
            background: #ef4444;
            color: white;
            padding: 0.25rem 0.6rem;
            border-radius: 12px;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #64748b;
        }

        .empty-icon {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include('../view/components/sidebar_super_admin.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-lightbulb me-2"></i>Review Suggestions</h1>
            <p class="text-muted mb-0">Review category and brand suggestions from pharmacy admins</p>
            <?php if ($total_pending > 0): ?>
                <span class="badge bg-warning text-dark mt-2">
                    <i class="fas fa-clock me-1"></i><?php echo $total_pending; ?> Pending Review
                </span>
            <?php endif; ?>
        </div>

        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#pending">
                    Pending
                    <?php if ($total_pending > 0): ?>
                        <span class="badge-count"><?php echo $total_pending; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#all">All Suggestions</a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Pending Tab -->
            <div class="tab-pane fade show active" id="pending">
                <h4 class="mb-3">Pending Category Suggestions</h4>
                <?php if (count($pending_category_suggestions) > 0): ?>
                    <?php foreach ($pending_category_suggestions as $suggestion): ?>
                        <div class="suggestion-card pending">
                            <div class="suggestion-header">
                                <div>
                                    <div class="suggestion-name">
                                        <i class="fas fa-tag me-2"></i><?php echo htmlspecialchars($suggestion['suggested_name']); ?>
                                    </div>
                                    <small class="text-muted">
                                        Suggested <?php echo date('M d, Y', strtotime($suggestion['created_at'])); ?>
                                    </small>
                                </div>
                                <span class="status-badge status-pending">Pending</span>
                            </div>

                            <div class="pharmacy-info">
                                <strong><i class="fas fa-hospital me-2"></i>From:</strong>
                                <?php echo htmlspecialchars($suggestion['pharmacy_name'] ?: $suggestion['customer_name']); ?>
                                <br>
                                <strong><i class="fas fa-user me-2"></i>Admin:</strong>
                                <?php echo htmlspecialchars($suggestion['customer_name']); ?>
                                (<?php echo htmlspecialchars($suggestion['customer_email']); ?>)
                            </div>

                            <?php if (!empty($suggestion['reason'])): ?>
                                <div class="reason-box">
                                    <strong><i class="fas fa-comment-dots me-2"></i>Reason:</strong>
                                    <p class="mb-0 mt-1"><?php echo nl2br(htmlspecialchars($suggestion['reason'])); ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="action-buttons">
                                <button class="btn btn-approve" onclick="reviewSuggestion('category', <?php echo $suggestion['suggestion_id']; ?>, 'approve')">
                                    <i class="fas fa-check me-2"></i>Approve & Create Category
                                </button>
                                <button class="btn btn-reject" onclick="reviewSuggestion('category', <?php echo $suggestion['suggestion_id']; ?>, 'reject')">
                                    <i class="fas fa-times me-2"></i>Reject
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle empty-icon"></i>
                        <p>No pending category suggestions</p>
                    </div>
                <?php endif; ?>

                <h4 class="mb-3 mt-5">Pending Brand Suggestions</h4>
                <?php if (count($pending_brand_suggestions) > 0): ?>
                    <?php foreach ($pending_brand_suggestions as $suggestion): ?>
                        <div class="suggestion-card pending">
                            <div class="suggestion-header">
                                <div>
                                    <div class="suggestion-name">
                                        <i class="fas fa-certificate me-2"></i><?php echo htmlspecialchars($suggestion['suggested_name']); ?>
                                    </div>
                                    <small class="text-muted">
                                        Suggested <?php echo date('M d, Y', strtotime($suggestion['created_at'])); ?>
                                    </small>
                                </div>
                                <span class="status-badge status-pending">Pending</span>
                            </div>

                            <div class="pharmacy-info">
                                <strong><i class="fas fa-hospital me-2"></i>From:</strong>
                                <?php echo htmlspecialchars($suggestion['pharmacy_name'] ?: $suggestion['customer_name']); ?>
                                <br>
                                <strong><i class="fas fa-user me-2"></i>Admin:</strong>
                                <?php echo htmlspecialchars($suggestion['customer_name']); ?>
                                (<?php echo htmlspecialchars($suggestion['customer_email']); ?>)
                            </div>

                            <?php if (!empty($suggestion['reason'])): ?>
                                <div class="reason-box">
                                    <strong><i class="fas fa-comment-dots me-2"></i>Reason:</strong>
                                    <p class="mb-0 mt-1"><?php echo nl2br(htmlspecialchars($suggestion['reason'])); ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="action-buttons">
                                <button class="btn btn-approve" onclick="reviewSuggestion('brand', <?php echo $suggestion['suggestion_id']; ?>, 'approve')">
                                    <i class="fas fa-check me-2"></i>Approve & Create Brand
                                </button>
                                <button class="btn btn-reject" onclick="reviewSuggestion('brand', <?php echo $suggestion['suggestion_id']; ?>, 'reject')">
                                    <i class="fas fa-times me-2"></i>Reject
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle empty-icon"></i>
                        <p>No pending brand suggestions</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- All Suggestions Tab -->
            <div class="tab-pane fade" id="all">
                <h4 class="mb-3">All Category Suggestions</h4>
                <?php foreach ($all_category_suggestions as $suggestion): ?>
                    <div class="suggestion-card <?php echo $suggestion['status']; ?>">
                        <div class="suggestion-header">
                            <div>
                                <div class="suggestion-name">
                                    <i class="fas fa-tag me-2"></i><?php echo htmlspecialchars($suggestion['suggested_name']); ?>
                                </div>
                                <small class="text-muted">
                                    Suggested <?php echo date('M d, Y', strtotime($suggestion['created_at'])); ?>
                                </small>
                            </div>
                            <span class="status-badge status-<?php echo $suggestion['status']; ?>">
                                <?php echo ucfirst($suggestion['status']); ?>
                            </span>
                        </div>

                        <div class="pharmacy-info">
                            <strong><i class="fas fa-hospital me-2"></i>From:</strong>
                            <?php echo htmlspecialchars($suggestion['pharmacy_name'] ?: $suggestion['customer_name']); ?>
                        </div>

                        <?php if ($suggestion['status'] != 'pending' && !empty($suggestion['review_comment'])): ?>
                            <div class="alert alert-info mt-2">
                                <strong>Review Comment:</strong> <?php echo htmlspecialchars($suggestion['review_comment']); ?>
                                <br><small>Reviewed by <?php echo htmlspecialchars($suggestion['reviewer_name'] ?? 'Admin'); ?>
                                on <?php echo date('M d, Y', strtotime($suggestion['reviewed_at'])); ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <h4 class="mb-3 mt-5">All Brand Suggestions</h4>
                <?php foreach ($all_brand_suggestions as $suggestion): ?>
                    <div class="suggestion-card <?php echo $suggestion['status']; ?>">
                        <div class="suggestion-header">
                            <div>
                                <div class="suggestion-name">
                                    <i class="fas fa-certificate me-2"></i><?php echo htmlspecialchars($suggestion['suggested_name']); ?>
                                </div>
                                <small class="text-muted">
                                    Suggested <?php echo date('M d, Y', strtotime($suggestion['created_at'])); ?>
                                </small>
                            </div>
                            <span class="status-badge status-<?php echo $suggestion['status']; ?>">
                                <?php echo ucfirst($suggestion['status']); ?>
                            </span>
                        </div>

                        <div class="pharmacy-info">
                            <strong><i class="fas fa-hospital me-2"></i>From:</strong>
                            <?php echo htmlspecialchars($suggestion['pharmacy_name'] ?: $suggestion['customer_name']); ?>
                        </div>

                        <?php if ($suggestion['status'] != 'pending' && !empty($suggestion['review_comment'])): ?>
                            <div class="alert alert-info mt-2">
                                <strong>Review Comment:</strong> <?php echo htmlspecialchars($suggestion['review_comment']); ?>
                                <br><small>Reviewed by <?php echo htmlspecialchars($suggestion['reviewer_name'] ?? 'Admin'); ?>
                                on <?php echo date('M d, Y', strtotime($suggestion['reviewed_at'])); ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="../js/sidebar.js?v=2.5"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function reviewSuggestion(type, suggestionId, action) {
            if (action === 'approve') {
                Swal.fire({
                    title: 'Approve Suggestion?',
                    text: `This will create the new ${type} and notify the pharmacy admin.`,
                    input: 'textarea',
                    inputLabel: 'Optional comment',
                    inputPlaceholder: 'Add a comment for the pharmacy admin...',
                    showCancelButton: true,
                    confirmButtonText: 'Approve',
                    confirmButtonColor: '#10b981',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitReview(type, suggestionId, 'approve', result.value || '');
                    }
                });
            } else {
                Swal.fire({
                    title: 'Reject Suggestion?',
                    text: 'Please provide a reason for rejection',
                    input: 'textarea',
                    inputLabel: 'Reason for rejection (required)',
                    inputPlaceholder: 'Explain why this suggestion was rejected...',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Please provide a reason for rejection';
                        }
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Reject',
                    confirmButtonColor: '#ef4444',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitReview(type, suggestionId, 'reject', result.value);
                    }
                });
            }
        }

        function submitReview(type, suggestionId, action, comment) {
            const formData = new FormData();
            formData.append('action', `${action}_${type}`);
            formData.append('suggestion_id', suggestionId);
            formData.append('review_comment', comment);

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
        }
    </script>
</body>
</html>
