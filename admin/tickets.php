<?php
/**
 * Support Tickets - Super Admin
 * Manage customer support tickets
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

// Get ticket statistics
$stats_query = "SELECT
                    COUNT(*) as total_tickets,
                    COUNT(CASE WHEN status = 'open' THEN 1 END) as open_count,
                    COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_count,
                    COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_count,
                    COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed_count
                FROM support_tickets";

$stats_result = $conn->query($stats_query);
if ($stats_result) {
    $stats = $stats_result->fetch_assoc();
} else {
    // Table doesn't exist yet - create dummy stats
    $stats = ['total_tickets' => 0, 'open_count' => 0, 'in_progress_count' => 0, 'resolved_count' => 0, 'closed_count' => 0];
}

// Get all tickets
$tickets_query = "SELECT t.*, c.customer_name, c.customer_email
                  FROM support_tickets t
                  INNER JOIN customer c ON t.customer_id = c.customer_id
                  ORDER BY t.created_at DESC";

$tickets_result = $conn->query($tickets_query);
$tickets = [];
if ($tickets_result) {
    while ($row = $tickets_result->fetch_assoc()) {
        $tickets[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets - PharmaVault Admin</title>

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

        .tickets-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .ticket-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
            cursor: pointer;
        }

        .ticket-card:hover {
            border-color: var(--primary);
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.1);
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-open {
            background: #fef3c7;
            color: #92400e;
        }

        .status-in_progress {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-resolved {
            background: #d1fae5;
            color: #065f46;
        }

        .status-closed {
            background: #e5e7eb;
            color: #374151;
        }

        .priority-high {
            background: #fee2e2;
            color: #991b1b;
        }

        .priority-medium {
            background: #fef3c7;
            color: #92400e;
        }

        .priority-low {
            background: #dbeafe;
            color: #1e40af;
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
            <h1><i class="fas fa-ticket-alt me-3"></i>Support Tickets</h1>
            <p class="mb-0">Manage customer support requests and issues</p>
        </div>

        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-number text-primary"><?php echo $stats['total_tickets']; ?></div>
                <div class="stat-label">Total Tickets</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-warning"><?php echo $stats['open_count']; ?></div>
                <div class="stat-label">Open</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-info"><?php echo $stats['in_progress_count']; ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-success"><?php echo $stats['resolved_count']; ?></div>
                <div class="stat-label">Resolved</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-secondary"><?php echo $stats['closed_count']; ?></div>
                <div class="stat-label">Closed</div>
            </div>
        </div>

        <!-- Tickets List -->
        <div class="tickets-container">
            <h3 class="mb-3">Support Tickets</h3>

            <?php if (count($tickets) > 0): ?>
                <?php foreach ($tickets as $ticket): ?>
                    <div class="ticket-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($ticket['subject']); ?></h5>
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($ticket['customer_name']); ?>
                                    <i class="fas fa-calendar ms-3 me-1"></i><?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="status-badge status-<?php echo $ticket['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                </span>
                                <br>
                                <span class="status-badge priority-<?php echo $ticket['priority']; ?> mt-2">
                                    <?php echo ucfirst($ticket['priority']); ?> Priority
                                </span>
                            </div>
                        </div>
                        <p class="mb-2"><?php echo htmlspecialchars(substr($ticket['description'], 0, 200)) . (strlen($ticket['description']) > 200 ? '...' : ''); ?></p>
                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-eye me-1"></i>View Details
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-ticket-alt fa-4x text-muted mb-3"></i>
                    <h4>No Support Tickets</h4>
                    <p class="text-muted">No support tickets have been submitted yet</p>
                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        The support_tickets table needs to be created in the database.
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
