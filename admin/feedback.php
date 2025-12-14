<?php
/**
 * Customer Feedback - Super Admin
 * View and manage customer feedback
 */
session_start();
require_once('../settings/core.php');
require_once('../settings/db_cred.php');

// Check if user is super admin
if (!isSuperAdmin()) {
    header("Location: ../login/login.php");
    exit();
}

$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

// Get feedback statistics
$stats_query = "SELECT
                    COUNT(*) as total_feedback,
                    COUNT(CASE WHEN status = 'new' THEN 1 END) as new_count,
                    COUNT(CASE WHEN status = 'reviewed' THEN 1 END) as reviewed_count,
                    COUNT(CASE WHEN type = 'complaint' THEN 1 END) as complaints_count,
                    COUNT(CASE WHEN type = 'suggestion' THEN 1 END) as suggestions_count
                FROM customer_feedback";

$stats_result = $conn->query($stats_query);
if ($stats_result) {
    $stats = $stats_result->fetch_assoc();
} else {
    // Table doesn't exist - create dummy stats
    $stats = ['total_feedback' => 0, 'new_count' => 0, 'reviewed_count' => 0, 'complaints_count' => 0, 'suggestions_count' => 0];
}

// Get all feedback
$feedback_query = "SELECT f.*, c.customer_name, c.customer_email
                   FROM customer_feedback f
                   INNER JOIN customer c ON f.customer_id = c.customer_id
                   ORDER BY f.created_at DESC LIMIT 50";

$feedback_result = $conn->query($feedback_query);
$feedbacks = [];
if ($feedback_result) {
    while ($row = $feedback_result->fetch_assoc()) {
        $feedbacks[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback - PharmaVault Admin</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css?v=2.2">

    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #764ba2;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --sidebar-width: 280px;
        }

        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .feedback-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .feedback-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .feedback-card:hover {
            border-color: var(--primary);
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.1);
        }

        .type-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .type-complaint {
            background: #fee2e2;
            color: #991b1b;
        }

        .type-suggestion {
            background: #dbeafe;
            color: #1e40af;
        }

        .type-praise {
            background: #d1fae5;
            color: #065f46;
        }

        .type-other {
            background: #e5e7eb;
            color: #374151;
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-new {
            background: #fef3c7;
            color: #92400e;
        }

        .status-reviewed {
            background: #d1fae5;
            color: #065f46;
        }

        @media (max-width: 1024px) {
            .main-content { margin-left: 70px; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; }
            .stats-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include('../view/components/sidebar_super_admin.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-comment-dots me-3"></i>Customer Feedback</h1>
            <p class="mb-0">View and manage customer feedback and suggestions</p>
        </div>

        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-number text-primary"><?php echo $stats['total_feedback']; ?></div>
                <div class="stat-label">Total Feedback</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-warning"><?php echo $stats['new_count']; ?></div>
                <div class="stat-label">New</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-success"><?php echo $stats['reviewed_count']; ?></div>
                <div class="stat-label">Reviewed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-danger"><?php echo $stats['complaints_count']; ?></div>
                <div class="stat-label">Complaints</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-info"><?php echo $stats['suggestions_count']; ?></div>
                <div class="stat-label">Suggestions</div>
            </div>
        </div>

        <!-- Feedback List -->
        <div class="feedback-container">
            <h3 class="mb-3">Customer Feedback</h3>

            <?php if (count($feedbacks) > 0): ?>
                <?php foreach ($feedbacks as $feedback): ?>
                    <div class="feedback-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($feedback['subject']); ?></h5>
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($feedback['customer_name']); ?>
                                    <i class="fas fa-envelope ms-3 me-1"></i><?php echo htmlspecialchars($feedback['customer_email']); ?>
                                    <i class="fas fa-calendar ms-3 me-1"></i><?php echo date('M d, Y H:i', strtotime($feedback['created_at'])); ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="type-badge type-<?php echo $feedback['type']; ?>">
                                    <?php echo ucfirst($feedback['type']); ?>
                                </span>
                                <br>
                                <span class="status-badge status-<?php echo $feedback['status']; ?> mt-2">
                                    <?php echo ucfirst($feedback['status']); ?>
                                </span>
                            </div>
                        </div>
                        <p class="mb-2"><?php echo nl2br(htmlspecialchars($feedback['message'])); ?></p>
                        <?php if ($feedback['status'] == 'new'): ?>
                            <button class="btn btn-sm btn-success">
                                <i class="fas fa-check me-1"></i>Mark as Reviewed
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-comment-dots fa-4x text-muted mb-3"></i>
                    <h4>No Customer Feedback</h4>
                    <p class="text-muted">No customer feedback has been submitted yet</p>
                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        The customer_feedback table needs to be created in the database.
                        <br>Run the SQL migration file to create this table.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../js/sidebar.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
