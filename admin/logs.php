<?php
/**
 * System Logs - Super Admin
 * View system activity logs
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

// Get log statistics
$stats_query = "SELECT
                    COUNT(*) as total_logs,
                    COUNT(CASE WHEN log_level = 'info' THEN 1 END) as info_count,
                    COUNT(CASE WHEN log_level = 'warning' THEN 1 END) as warning_count,
                    COUNT(CASE WHEN log_level = 'error' THEN 1 END) as error_count,
                    COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_count
                FROM system_logs";

$stats_result = $conn->query($stats_query);
if ($stats_result) {
    $stats = $stats_result->fetch_assoc();
} else {
    // Table doesn't exist - create dummy stats
    $stats = ['total_logs' => 0, 'info_count' => 0, 'warning_count' => 0, 'error_count' => 0, 'today_count' => 0];
}

// Get filter parameters
$filter_level = isset($_GET['level']) ? $_GET['level'] : 'all';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

// Build logs query
$logs_query = "SELECT l.*, u.customer_name as user_name
               FROM system_logs l
               LEFT JOIN customer u ON l.user_id = u.customer_id
               WHERE 1=1";

if ($filter_level != 'all') {
    $logs_query .= " AND l.log_level = '" . $conn->real_escape_string($filter_level) . "'";
}

if ($filter_date) {
    $logs_query .= " AND DATE(l.created_at) = '" . $conn->real_escape_string($filter_date) . "'";
}

$logs_query .= " ORDER BY l.created_at DESC LIMIT 100";

$logs_result = $conn->query($logs_query);
$logs = [];
if ($logs_result) {
    while ($row = $logs_result->fetch_assoc()) {
        $logs[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - PharmaVault Admin</title>

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

        .filters-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .logs-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .log-entry {
            border-left: 4px solid #e2e8f0;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 0 8px 8px 0;
            background: #f9fafb;
        }

        .log-entry.level-error {
            border-left-color: var(--danger);
            background: #fef2f2;
        }

        .log-entry.level-warning {
            border-left-color: var(--warning);
            background: #fffbeb;
        }

        .log-entry.level-info {
            border-left-color: #3b82f6;
            background: #eff6ff;
        }

        .log-time {
            color: #64748b;
            font-size: 0.9rem;
        }

        .log-message {
            margin: 0.5rem 0;
            color: #1e293b;
        }

        .log-details {
            font-size: 0.85rem;
            color: #64748b;
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
            <h1><i class="fas fa-clipboard-list me-3"></i>System Logs</h1>
            <p class="mb-0">View system activity and error logs</p>
        </div>

        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-number text-primary"><?php echo $stats['total_logs']; ?></div>
                <div class="stat-label">Total Logs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-info"><?php echo $stats['info_count']; ?></div>
                <div class="stat-label">Info</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-warning"><?php echo $stats['warning_count']; ?></div>
                <div class="stat-label">Warnings</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-danger"><?php echo $stats['error_count']; ?></div>
                <div class="stat-label">Errors</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-success"><?php echo $stats['today_count']; ?></div>
                <div class="stat-label">Today's Logs</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Log Level</label>
                    <select class="form-select" name="level">
                        <option value="all" <?php echo $filter_level == 'all' ? 'selected' : ''; ?>>All Levels</option>
                        <option value="info" <?php echo $filter_level == 'info' ? 'selected' : ''; ?>>Info</option>
                        <option value="warning" <?php echo $filter_level == 'warning' ? 'selected' : ''; ?>>Warning</option>
                        <option value="error" <?php echo $filter_level == 'error' ? 'selected' : ''; ?>>Error</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" name="date" value="<?php echo htmlspecialchars($filter_date); ?>">
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                    <a href="logs.php" class="btn btn-secondary">
                        <i class="fas fa-redo me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Logs List -->
        <div class="logs-container">
            <h3 class="mb-3">Recent Activity Logs</h3>

            <?php if (count($logs) > 0): ?>
                <?php foreach ($logs as $log): ?>
                    <div class="log-entry level-<?php echo $log['log_level']; ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <span class="badge bg-<?php echo $log['log_level'] == 'error' ? 'danger' : ($log['log_level'] == 'warning' ? 'warning' : 'info'); ?>">
                                    <?php echo strtoupper($log['log_level']); ?>
                                </span>
                                <span class="log-time ms-2">
                                    <i class="fas fa-clock me-1"></i><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                        <p class="log-message"><?php echo htmlspecialchars($log['message']); ?></p>
                        <div class="log-details">
                            <?php if ($log['user_name']): ?>
                                <i class="fas fa-user me-1"></i>User: <?php echo htmlspecialchars($log['user_name']); ?>
                            <?php endif; ?>
                            <?php if ($log['ip_address']): ?>
                                <i class="fas fa-network-wired ms-3 me-1"></i>IP: <?php echo htmlspecialchars($log['ip_address']); ?>
                            <?php endif; ?>
                            <?php if ($log['action']): ?>
                                <i class="fas fa-cog ms-3 me-1"></i>Action: <?php echo htmlspecialchars($log['action']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                    <h4>No System Logs</h4>
                    <p class="text-muted">No system logs have been recorded yet</p>
                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        The system_logs table needs to be created in the database.
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
