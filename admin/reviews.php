<?php
/**
 * Product Reviews - Super Admin
 * Manage product reviews and ratings
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

// Get review statistics
$stats_query = "SELECT
                    COUNT(*) as total_reviews,
                    AVG(rating) as avg_rating,
                    COUNT(CASE WHEN is_verified_purchase = 1 THEN 1 END) as verified_count,
                    COUNT(CASE WHEN rating >= 4 THEN 1 END) as positive_count,
                    COUNT(CASE WHEN rating <= 2 THEN 1 END) as negative_count
                FROM product_reviews";

$stats_result = $conn->query($stats_query);
if ($stats_result) {
    $stats = $stats_result->fetch_assoc();
} else {
    // Table doesn't exist - create dummy stats
    $stats = ['total_reviews' => 0, 'avg_rating' => 0, 'verified_count' => 0, 'positive_count' => 0, 'negative_count' => 0];
}

// Get all reviews
$reviews_query = "SELECT r.*, p.product_title, p.product_image, c.customer_name, c.customer_email
                  FROM product_reviews r
                  INNER JOIN products p ON r.product_id = p.product_id
                  INNER JOIN customer c ON r.customer_id = c.customer_id
                  ORDER BY r.created_at DESC LIMIT 50";

$reviews_result = $conn->query($reviews_query);
$reviews = [];
if ($reviews_result) {
    while ($row = $reviews_result->fetch_assoc()) {
        $reviews[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Reviews - PharmaVault Admin</title>

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

        .reviews-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .review-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .review-card:hover {
            border-color: var(--primary);
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.1);
        }

        .rating-stars {
            color: #fbbf24;
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
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
            <h1><i class="fas fa-star me-3"></i>Product Reviews</h1>
            <p class="mb-0">Manage and moderate product reviews</p>
        </div>

        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-number text-primary"><?php echo $stats['total_reviews']; ?></div>
                <div class="stat-label">Total Reviews</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-warning"><?php echo number_format($stats['avg_rating'], 1); ?> <i class="fas fa-star" style="font-size: 1.5rem;"></i></div>
                <div class="stat-label">Average Rating</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-info"><?php echo $stats['verified_count']; ?></div>
                <div class="stat-label">Verified Purchases</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-success"><?php echo $stats['positive_count']; ?></div>
                <div class="stat-label">Positive (4-5★)</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-danger"><?php echo $stats['negative_count']; ?></div>
                <div class="stat-label">Negative (1-2★)</div>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="reviews-container">
            <h3 class="mb-3">Product Reviews</h3>

            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="row">
                            <div class="col-md-2">
                                <?php if ($review['product_image']): ?>
                                    <img src="../<?php echo htmlspecialchars($review['product_image']); ?>"
                                         alt="Product" class="img-fluid rounded">
                                <?php else: ?>
                                    <div class="bg-light rounded p-4 text-center">
                                        <i class="fas fa-pills fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-7">
                                <h5 class="mb-2">
                                    <?php echo htmlspecialchars($review['product_title']); ?>
                                    <?php if ($review['is_verified_purchase']): ?>
                                        <span class="badge bg-success ms-2" title="Verified Purchase">
                                            <i class="fas fa-check-circle"></i> Verified
                                        </span>
                                    <?php endif; ?>
                                </h5>
                                <div class="rating-stars mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                    <?php endfor; ?>
                                    <span class="ms-2 text-muted"><?php echo $review['rating']; ?>/5</span>
                                </div>
                                <?php if (!empty($review['review_title'])): ?>
                                    <h6 class="mb-2"><?php echo htmlspecialchars($review['review_title']); ?></h6>
                                <?php endif; ?>
                                <p class="mb-2"><?php echo htmlspecialchars($review['review_text']); ?></p>
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($review['customer_name']); ?>
                                    <i class="fas fa-calendar ms-3 me-1"></i><?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                    <?php if ($review['helpful_count'] > 0): ?>
                                        <i class="fas fa-thumbs-up ms-3 me-1"></i><?php echo $review['helpful_count']; ?> helpful
                                    <?php endif; ?>
                                </small>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="mb-2">
                                    <span class="badge <?php echo $review['rating'] >= 4 ? 'bg-success' : ($review['rating'] <= 2 ? 'bg-danger' : 'bg-warning'); ?> p-2">
                                        <?php
                                        if ($review['rating'] >= 4) {
                                            echo '<i class="fas fa-smile me-1"></i>Positive';
                                        } elseif ($review['rating'] <= 2) {
                                            echo '<i class="fas fa-frown me-1"></i>Negative';
                                        } else {
                                            echo '<i class="fas fa-meh me-1"></i>Neutral';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <button class="btn btn-sm btn-outline-primary w-100 mb-1" title="View Product">
                                    <i class="fas fa-eye me-1"></i>View Product
                                </button>
                                <button class="btn btn-sm btn-outline-secondary w-100" title="View Customer">
                                    <i class="fas fa-user me-1"></i>View Customer
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-star fa-4x text-muted mb-3"></i>
                    <h4>No Product Reviews</h4>
                    <p class="text-muted">No product reviews have been submitted yet</p>
                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        The product_reviews table needs to be created in the database.
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
