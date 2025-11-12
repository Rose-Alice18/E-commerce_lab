<?php
/**
 * Revenue Analytics Page
 * Role: Pharmacy Admin (1)
 */
session_start();
require_once('../settings/core.php');

// Check if user is logged in as pharmacy admin
if (!isLoggedIn() || !isPharmacyAdmin()) {
    header("Location: ../login/login_view.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue Analytics - PharmaVault</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .analytics-container {
            background: white;
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .coming-soon-icon {
            font-size: 6rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
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
    <?php include('../view/components/sidebar_pharmacy_admin.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-dollar-sign me-2"></i>Revenue Analytics</h1>
            <p class="text-muted mb-0">Track your revenue and financial performance</p>
        </div>

        <div class="analytics-container">
            <i class="fas fa-dollar-sign coming-soon-icon"></i>
            <h3>Revenue Analytics Coming Soon!</h3>
            <p class="text-muted mb-4">You'll be able to view revenue trends, profit margins, and financial insights.</p>
            <a href="pharmacy_dashboard.php" class="btn btn-primary">
                <i class="fas fa-tachometer-alt me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <script src="../js/sidebar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
