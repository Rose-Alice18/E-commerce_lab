<?php
/**
 * Help Center Page
 * Role: Customer (2)
 */
session_start();
require_once('../settings/core.php');

// Check if user is logged in as customer
if (!isLoggedIn() || !isRegularCustomer()) {
    header("Location: ../login/login_view.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - PharmaVault</title>

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

        .help-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .help-card:hover {
            transform: translateY(-5px);
        }

        .help-icon {
            font-size: 2.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
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
    <?php include('../view/components/sidebar_customer.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-question-circle me-2"></i>Help Center</h1>
            <p class="text-muted mb-0">How can we assist you today?</p>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="help-card text-center">
                    <i class="fas fa-book help-icon"></i>
                    <h4>FAQs</h4>
                    <p class="text-muted">Find answers to common questions</p>
                    <button class="btn btn-outline-primary btn-sm">Coming Soon</button>
                </div>
            </div>

            <div class="col-md-4">
                <div class="help-card text-center">
                    <i class="fas fa-envelope help-icon"></i>
                    <h4>Contact Support</h4>
                    <p class="text-muted">Get in touch with our team</p>
                    <button class="btn btn-outline-primary btn-sm">Coming Soon</button>
                </div>
            </div>

            <div class="col-md-4">
                <div class="help-card text-center">
                    <i class="fas fa-phone help-icon"></i>
                    <h4>Call Us</h4>
                    <p class="text-muted">+233 24 453 5378</p>
                    <a href="tel:+233244535378" class="btn btn-primary btn-sm">
                        <i class="fas fa-phone me-1"></i>Call Now
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/sidebar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Cart and Wishlist JS (for sidebar counts) -->
    <script src="../js/cart-wishlist.js"></script>
</body>
</html>
