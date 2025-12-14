<?php
/**
 * Announcements Management - Super Admin
 * Create and manage system-wide announcements
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

// Get all announcements
$announcements_query = "SELECT * FROM announcements ORDER BY created_at DESC";
$announcements_result = $conn->query($announcements_query);
$announcements = [];
if ($announcements_result) {
    while ($row = $announcements_result->fetch_assoc()) {
        $announcements[] = $row;
    }
}

// Get statistics
$stats_query = "SELECT
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                    COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft,
                    COUNT(CASE WHEN status = 'archived' THEN 1 END) as archived
                FROM announcements";
$stats_result = $conn->query($stats_query);
$stats = $stats_result ? $stats_result->fetch_assoc() : ['total' => 0, 'active' => 0, 'draft' => 0, 'archived' => 0];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - PharmaVault Admin</title>

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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

        .announcements-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .announcement-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .announcement-card:hover {
            border-color: var(--primary);
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.1);
        }

        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .announcement-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .announcement-meta {
            color: #64748b;
            font-size: 0.9rem;
        }

        .announcement-content {
            color: #475569;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .announcement-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-draft {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-archived {
            background: #e5e7eb;
            color: #374151;
        }

        .priority-badge {
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 0.5rem;
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

        .btn-action {
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
            border: none;
        }

        .btn-edit {
            background: #3b82f6;
            color: white;
        }

        .btn-edit:hover {
            background: #2563eb;
        }

        .btn-delete {
            background: var(--danger);
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .btn-create {
            background: white;
            color: var(--primary);
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            font-weight: 700;
            transition: all 0.3s;
            border: 2px solid white;
        }

        .btn-create:hover {
            background: rgba(255,255,255,0.9);
            transform: translateY(-2px);
        }

        .filter-tabs {
            margin-bottom: 1.5rem;
        }

        .filter-tab {
            padding: 0.6rem 1.2rem;
            border: 2px solid #e2e8f0;
            background: white;
            color: #64748b;
            border-radius: 8px;
            margin-right: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-tab.active {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
        }

        @media (max-width: 1024px) {
            .main-content { margin-left: 70px; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; }
            .page-header { flex-direction: column; gap: 1rem; }
            .stats-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include('../view/components/sidebar_super_admin.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-bullhorn me-3"></i>Announcements</h1>
                <p class="mb-0">Create and manage system-wide announcements</p>
            </div>
            <button class="btn btn-create" onclick="showCreateModal()">
                <i class="fas fa-plus me-2"></i>New Announcement
            </button>
        </div>

        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-number text-primary"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Announcements</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-success"><?php echo $stats['active']; ?></div>
                <div class="stat-label">Active</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-danger"><?php echo $stats['draft']; ?></div>
                <div class="stat-label">Drafts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-secondary"><?php echo $stats['archived']; ?></div>
                <div class="stat-label">Archived</div>
            </div>
        </div>

        <!-- Announcements List -->
        <div class="announcements-container">
            <div class="filter-tabs">
                <button class="filter-tab active" onclick="filterAnnouncements('all')">All</button>
                <button class="filter-tab" onclick="filterAnnouncements('active')">Active</button>
                <button class="filter-tab" onclick="filterAnnouncements('draft')">Drafts</button>
                <button class="filter-tab" onclick="filterAnnouncements('archived')">Archived</button>
            </div>

            <div id="announcementsList">
                <?php if (count($announcements) > 0): ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="announcement-card" data-status="<?php echo $announcement['status']; ?>">
                            <div class="announcement-header">
                                <div>
                                    <h3 class="announcement-title">
                                        <?php echo htmlspecialchars($announcement['title']); ?>
                                        <span class="priority-badge priority-<?php echo $announcement['priority']; ?>">
                                            <?php echo ucfirst($announcement['priority']); ?> Priority
                                        </span>
                                    </h3>
                                    <div class="announcement-meta">
                                        <i class="fas fa-calendar me-2"></i>
                                        <?php echo date('M d, Y H:i', strtotime($announcement['created_at'])); ?>
                                        <?php if ($announcement['target_audience'] != 'all'): ?>
                                            <span class="ms-3">
                                                <i class="fas fa-users me-2"></i>
                                                <?php echo ucfirst($announcement['target_audience']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="status-badge status-<?php echo $announcement['status']; ?>">
                                    <?php echo ucfirst($announcement['status']); ?>
                                </span>
                            </div>
                            <div class="announcement-content">
                                <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                            </div>
                            <div class="announcement-footer">
                                <div>
                                    <?php if ($announcement['expires_at']): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            Expires: <?php echo date('M d, Y', strtotime($announcement['expires_at'])); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <button class="btn btn-action btn-edit me-2"
                                            onclick="editAnnouncement(<?php echo $announcement['announcement_id']; ?>)">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                    <button class="btn btn-action btn-delete"
                                            onclick="deleteAnnouncement(<?php echo $announcement['announcement_id']; ?>)">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-bullhorn fa-4x text-muted mb-3"></i>
                        <h4>No Announcements Yet</h4>
                        <p class="text-muted">Create your first announcement to notify users</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Create/Edit Announcement Modal -->
    <div class="modal fade" id="announcementModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Create Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="announcementForm">
                        <input type="hidden" id="announcement_id" name="announcement_id">

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Priority</label>
                                <select class="form-select" id="priority" name="priority" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="draft">Draft</option>
                                    <option value="active" selected>Active</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Target Audience</label>
                                <select class="form-select" id="target_audience" name="target_audience" required>
                                    <option value="all" selected>All Users</option>
                                    <option value="customers">Customers Only</option>
                                    <option value="pharmacies">Pharmacies Only</option>
                                    <option value="admins">Admins Only</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expires At (Optional)</label>
                                <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveAnnouncement()">Save Announcement</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/sidebar.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        function showCreateModal() {
            document.getElementById('modalTitle').textContent = 'Create Announcement';
            document.getElementById('announcementForm').reset();
            document.getElementById('announcement_id').value = '';
            const modal = new bootstrap.Modal(document.getElementById('announcementModal'));
            modal.show();
        }

        function editAnnouncement(id) {
            fetch(`../actions/get_announcement.php?announcement_id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const announcement = data.announcement;
                        document.getElementById('modalTitle').textContent = 'Edit Announcement';
                        document.getElementById('announcement_id').value = announcement.announcement_id;
                        document.getElementById('title').value = announcement.title;
                        document.getElementById('content').value = announcement.content;
                        document.getElementById('priority').value = announcement.priority;
                        document.getElementById('status').value = announcement.status;
                        document.getElementById('target_audience').value = announcement.target_audience;

                        if (announcement.expires_at) {
                            const date = new Date(announcement.expires_at);
                            document.getElementById('expires_at').value = date.toISOString().slice(0, 16);
                        }

                        const modal = new bootstrap.Modal(document.getElementById('announcementModal'));
                        modal.show();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load announcement details');
                });
        }

        function saveAnnouncement() {
            const form = document.getElementById('announcementForm');
            const formData = new FormData(form);

            fetch('../actions/save_announcement.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Announcement saved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to save announcement');
            });
        }

        function deleteAnnouncement(id) {
            if (!confirm('Are you sure you want to delete this announcement? This action cannot be undone.')) {
                return;
            }

            fetch('../actions/delete_announcement.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `announcement_id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Announcement deleted successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete announcement');
            });
        }

        function filterAnnouncements(status) {
            // Update active tab
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');

            // Filter announcements
            const cards = document.querySelectorAll('.announcement-card');
            cards.forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
