<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core functions
require_once(dirname(__FILE__) . '/settings/core.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - PharmaVault | Your Trusted Healthcare Partner</title>
    <meta name="description" content="Learn about PharmaVault's mission to revolutionize healthcare access in Ghana by connecting customers with verified pharmacies.">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-bg: #1a1a2e;
            --light-bg: #f8f9fa;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        /* Modern Navbar */
        .modern-navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-link {
            font-weight: 500;
            color: #2d3748 !important;
            margin: 0 0.5rem;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }
        
        .btn-gradient-primary {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-gradient-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-outline-gradient {
            background: transparent;
            border: 2px solid #667eea;
            color: #667eea;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-gradient:hover {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        /* Page Header */
        .page-header {
            background: var(--primary-gradient);
            padding: 100px 0 60px;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -200px;
            right: -100px;
        }
        
        .page-header h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
        }
        
        .page-header p {
            font-size: 1.3rem;
            opacity: 0.95;
            position: relative;
            z-index: 2;
        }
        
        /* Content Sections */
        .content-section {
            padding: 80px 0;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #2d3748;
            margin-bottom: 1rem;
        }
        
        .section-subtitle {
            font-size: 1.2rem;
            color: #718096;
            margin-bottom: 3rem;
        }
        
        .content-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            height: 100%;
        }
        
        .icon-box {
            width: 70px;
            height: 70px;
            background: var(--primary-gradient);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .timeline {
            position: relative;
            padding: 3rem 0;
        }
        
        .timeline-item {
            padding: 2rem;
            background: white;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .timeline-item:hover {
            transform: translateX(10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }
        
        .timeline-year {
            font-size: 1.5rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .team-card {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }
        
        .team-avatar {
            width: 120px;
            height: 120px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 3rem;
            color: white;
        }
        
        .stats-box {
            text-align: center;
            padding: 2rem;
        }
        
        .stats-number {
            font-size: 3rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            font-size: 1.1rem;
            color: #718096;
        }
        
        /* Footer */
        .modern-footer {
            background: var(--dark-bg);
            color: white;
            padding: 60px 0 30px;
        }
        
        .footer-brand {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        
        .footer-description {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 1.5rem;
        }
        
        .footer-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.8rem;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: white;
            padding-left: 5px;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .social-link {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .social-link:hover {
            background: var(--primary-gradient);
            transform: translateY(-3px);
            color: white;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 3rem;
            padding-top: 2rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="modern-navbar navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="fas fa-pills me-2"></i>
                <span>PharmaVault</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#features">Features</a>
                    </li>
                    <!-- PDF Requirement: All Products Link -->
                    <li class="nav-item">
                        <a class="nav-link" href="view/all_product.php">
                            <i class="fas fa-pills me-1"></i>All Products
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="otherDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Other
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="otherDropdown">
                            <li>
                                <a class="dropdown-item" href="index.php#services">
                                    <i class="fas fa-concierge-bell me-2"></i>Services
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item active" href="about.php">
                                    <i class="fas fa-info-circle me-2"></i>About
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="index.php#contact">
                                    <i class="fas fa-envelope me-2"></i>Contact Us
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="index.php#developer">
                                    <i class="fas fa-code me-2"></i>About The App Developer
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>

                <!-- PDF Requirement: Search Box -->
                <form action="view/all_product.php" method="GET" class="d-flex me-3" style="min-width: 300px;">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search products..." aria-label="Search">
                        <button class="btn btn-gradient-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <div class="d-flex gap-2">
                    <?php if (!isLoggedIn()): ?>
                        <a href="login/login.php" class="btn btn-outline-gradient">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                        <!-- PDF Requirement: Register Link -->
                        <a href="login/register.php" class="btn btn-gradient-primary">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    <?php else: ?>
                        <a href="<?php
                            // Route to appropriate dashboard based on role
                            if (isSuperAdmin()) {
                                echo 'admin/dashboard.php';
                            } elseif (isPharmacyAdmin()) {
                                echo 'admin/pharmacy_dashboard.php';
                            } else {
                                echo 'admin/customer_dashboard.php';
                            }
                        ?>" class="btn btn-gradient-primary">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-gradient-primary dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item text-danger" href="login/logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1><i class="fas fa-heart me-3"></i>About PharmaVault</h1>
            <p>Revolutionizing Healthcare Access in Ghana</p>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="content-section bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="content-card">
                        <div class="icon-box">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3 class="mb-3">Our Mission</h3>
                        <p class="text-muted" style="line-height: 1.8;">
                            To bridge the healthcare access gap in Ghana by creating a trusted digital marketplace 
                            that connects customers with verified pharmacies, ensuring everyone has access to 
                            authentic medications and professional pharmaceutical services, regardless of their location.
                        </p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="content-card">
                        <div class="icon-box">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3 class="mb-3">Our Vision</h3>
                        <p class="text-muted" style="line-height: 1.8;">
                            To become Ghana's leading healthcare marketplace, where every Ghanaian can easily 
                            access quality medications and healthcare products through a network of trusted, 
                            verified pharmacies, contributing to a healthier nation.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Story -->
    <section class="content-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Our Story</h2>
                <p class="section-subtitle">How PharmaVault came to be</p>
            </div>
            
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4">
                    <i class="fas fa-hospital" style="font-size: 15rem; color: rgba(102, 126, 234, 0.1);"></i>
                </div>
                <div class="col-lg-6">
                    <p class="lead mb-4">
                        PharmaVault was born from a simple observation: accessing quality healthcare products 
                        in Ghana, especially in rural areas, remained a significant challenge.
                    </p>
                    <p class="text-muted" style="line-height: 1.8;">
                        Founded in 2024, we recognized that while Ghana has many licensed pharmacies, 
                        customers often struggled to find them, verify their authenticity, and access 
                        the products they needed. We set out to solve this problem by creating a digital 
                        platform that brings transparency, trust, and convenience to the pharmaceutical industry.
                    </p>
                    <p class="text-muted" style="line-height: 1.8;">
                        Today, PharmaVault serves as the bridge between customers seeking quality healthcare 
                        products and verified pharmacies ready to serve them. We're proud to be part of 
                        Ghana's digital healthcare transformation.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Values -->
    <section class="content-section bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Our Core Values</h2>
                <p class="section-subtitle">The principles that guide everything we do</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="content-card text-center">
                        <div class="icon-box mx-auto">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5 class="mb-3">Trust</h5>
                        <p class="text-muted">
                            We verify every pharmacy on our platform to ensure customers receive authentic products.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="content-card text-center">
                        <div class="icon-box mx-auto">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="mb-3">Accessibility</h5>
                        <p class="text-muted">
                            Making healthcare products available to everyone, everywhere in Ghana.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="content-card text-center">
                        <div class="icon-box mx-auto">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h5 class="mb-3">Innovation</h5>
                        <p class="text-muted">
                            Continuously improving our platform to better serve our users.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="content-card text-center">
                        <div class="icon-box mx-auto">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h5 class="mb-3">Care</h5>
                        <p class="text-muted">
                            Putting customer health and wellbeing at the center of everything we do.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Impact Stats -->
    <section class="content-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Our Impact</h2>
                <p class="section-subtitle">Making a difference in Ghana's healthcare landscape</p>
            </div>
            
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="stats-box">
                        <div class="stats-number">500+</div>
                        <div class="stats-label">Verified Pharmacies</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stats-box">
                        <div class="stats-number">10K+</div>
                        <div class="stats-label">Satisfied Customers</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stats-box">
                        <div class="stats-number">16</div>
                        <div class="stats-label">Regions Covered</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stats-box">
                        <div class="stats-number">24/7</div>
                        <div class="stats-label">Platform Availability</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="content-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Join Our Mission</h2>
            <p class="lead mb-4">
                Be part of Ghana's healthcare transformation
            </p>
            <?php if (!isLoggedIn()): ?>
                <a href="login/register.php" class="btn btn-light btn-lg" style="border-radius: 50px; padding: 1rem 3rem;">
                    <i class="fas fa-rocket me-2"></i>Get Started Today
                </a>
            <?php else: ?>
                <a href="index.php" class="btn btn-light btn-lg" style="border-radius: 50px; padding: 1rem 3rem;">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="modern-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-brand">
                        <i class="fas fa-pills me-2"></i>PharmaVault
                    </div>
                    <p class="footer-description">
                        Your trusted healthcare marketplace connecting customers with verified pharmacies across Ghana.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="index.php#features">Features</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-4 mb-4">
                    <h5 class="footer-title">For Customers</h5>
                    <ul class="footer-links">
                        <li><a href="#">Browse Pharmacies</a></li>
                        <li><a href="#">Search Products</a></li>
                        <li><a href="#">Track Orders</a></li>
                        <li><a href="#">Support Center</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-4 mb-4">
                    <h5 class="footer-title">For Pharmacies</h5>
                    <ul class="footer-links">
                        <li><a href="login/register.php">Join Platform</a></li>
                        <li><a href="#">List Products</a></li>
                        <li><a href="#">Manage Orders</a></li>
                        <li><a href="#">Partner Resources</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p class="mb-0">&copy; 2025 PharmaVault. All rights reserved. Built with <i class="fas fa-heart text-danger"></i> in Ghana</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>