<?php
/**
 * Pharmacies Management Page
 * Role: Super Admin (0)
 */
session_start();
require_once('../settings/core.php');
require_once('../controllers/customer_controller.php');

// Check if user is logged in as super admin
if (!isLoggedIn() || !isSuperAdmin()) {
    header("Location: ../login/login_view.php");
    exit();
}

// Get all pharmacy admins
$pharmacies = get_all_users_by_role_ctr(1); // Role 1 = Pharmacy Admin
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacies - PharmaVault</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../css/sidebar.css?v=2.2">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --sidebar-width: 280px;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
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

        .pharmacies-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        @media (max-width: 1024px) {
            .main-content { margin-left: 70px; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; }
        }
    </style>
</head>
<body>
    <?php include('../view/components/sidebar_super_admin.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-clinic-medical me-2"></i>Pharmacies</h1>
            <p class="text-muted mb-0">Manage all registered pharmacies</p>
        </div>

        <div class="pharmacies-container">
            <table id="pharmaciesTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pharmacy Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>City</th>
                        <th>Country</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pharmacies as $pharmacy): ?>
                        <tr>
                            <td><?php echo $pharmacy['customer_id']; ?></td>
                            <td><?php echo htmlspecialchars($pharmacy['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($pharmacy['customer_email']); ?></td>
                            <td><?php echo htmlspecialchars($pharmacy['customer_contact']); ?></td>
                            <td><?php echo htmlspecialchars($pharmacy['customer_city']); ?></td>
                            <td><?php echo htmlspecialchars($pharmacy['customer_country']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script>
        $(document).ready(function() {
            $('#pharmaciesTable').DataTable({
                pageLength: 25
            });
        });
    </script>
</body>
</html>
