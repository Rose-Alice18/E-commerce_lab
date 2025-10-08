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
    <title>PharmaHub - Your Trusted Healthcare Marketplace in Ghana</title>
    <meta name="description" content="Connect with verified pharmacies across Ghana. Find prescription medications, health supplements, medical supplies, and professional healthcare services.">
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
        
        .modern-navbar.scrolled {
            padding: 0.5rem 0;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.12);
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
        }
        
        .btn-outline-gradient {
            border: 2px solid #667eea;
            color: #667eea;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            background: transparent;
            transition: all 0.3s ease;
        }
        
        .btn-outline-gradient:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        /* Hero Section */
        .hero-section {
            background: var(--primary-gradient);
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -250px;
            right: -100px;
        }
        
        .hero-section::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -200px;
            left: -100px;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 2.5rem;
            font-weight: 400;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .hero-button {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-hero-light {
            background: white;
            color: #667eea;
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .btn-hero-light:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2);
            color: #667eea;
        }
        
        .btn-hero-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-hero-outline:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
        }
        
        /* Welcome Card for Logged In Users */
        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .welcome-card h4 {
            color: #2d3748;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .welcome-card p {
            color: #4a5568;
            margin-bottom: 0;
        }
        
        .welcome-card .btn {
            margin-top: 1rem;
        }
        
        .admin-badge {
            display: inline-block;
            background: var(--success-gradient);
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        
        /* Feature Cards */
        .features-section {
            padding: 100px 0;
            background: var(--light-bg);
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
            margin-bottom: 4rem;
        }
        
        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            height: 100%;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            font-size: 2rem;
            background: var(--primary-gradient);
            color: white;
        }
        
        .feature-card h5 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1rem;
        }
        
        .feature-card p {
            color: #718096;
            line-height: 1.8;
            margin: 0;
        }
        
        /* Stats Section */
        .stats-section {
            background: var(--primary-gradient);
            padding: 80px 0;
            color: white;
        }
        
        .stat-item {
            text-align: center;
            padding: 2rem;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -300px;
            left: -200px;
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
        }
        
        .social-link:hover {
            background: var(--primary-gradient);
            transform: translateY(-3px);
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
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .hero-buttons {
                flex-direction: column;
            }
            
            .hero-button {
                width: 100%;
            }
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
    </style>
</head>
<body>
    <!-- Modern Navigation -->
    <nav class="modern-navbar navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="fas fa-pills me-2"></i>
                <span>PharmaHub</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    
                    <?php if (isLoggedIn() && hasAdminPrivileges()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/category.php">
                                <i class="fas fa-cog me-1"></i>Manage
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <div class="d-flex gap-2">
                    <?php if (!isLoggedIn()): ?>
                        <a href="login/login.php" class="btn btn-outline-gradient">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                        <a href="login/register.php" class="btn btn-gradient-primary">
                            <i class="fas fa-user-plus me-1"></i>Sign Up
                        </a>
                    <?php else: ?>
                        <div class="dropdown">
                            <button class="btn btn-gradient-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Account'; ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user me-2"></i>My Profile
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="login/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 hero-content">
                    <h1 class="hero-title animate-fade-in-up">
                        Your Trusted Healthcare Marketplace
                    </h1>
                    <p class="hero-subtitle animate-fade-in-up" style="animation-delay: 0.2s;">
                        Connect with verified pharmacies across Ghana. Access prescription medications, health supplements, and professional healthcare services.
                    </p>
                    
                    <?php if (!isLoggedIn()): ?>
                        <div class="hero-buttons animate-fade-in-up" style="animation-delay: 0.4s;">
                            <a href="login/register.php" class="btn btn-hero-light hero-button">
                                <i class="fas fa-rocket me-2"></i>Get Started
                            </a>
                            <a href="#features" class="btn btn-hero-outline hero-button">
                                <i class="fas fa-info-circle me-2"></i>Learn More
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="welcome-card animate-fade-in-up" style="animation-delay: 0.4s;">
                            <h4>
                                <i class="fas fa-hand-sparkles me-2"></i>
                                Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>!
                                <?php if (hasAdminPrivileges()): ?>
                                    <span class="admin-badge">
                                        <i class="fas fa-crown me-1"></i>Admin
                                    </span>
                                <?php endif; ?>
                            </h4>
                            <p>
                                <?php if (hasAdminPrivileges()): ?>
                                    You have full administrative access to manage the platform.
                                <?php else: ?>
                                    Explore our marketplace and discover trusted pharmacy services tailored for you.
                                <?php endif; ?>
                            </p>
                            <?php if (hasAdminPrivileges()): ?>
                                <a href="admin/category.php" class="btn btn-gradient-primary">
                                    <i class="fas fa-cog me-2"></i>Manage Categories
                                </a>
                            <?php else: ?>
                                <a href="#features" class="btn btn-gradient-primary">
                                    <i class="fas fa-compass me-2"></i>Explore Services
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-5 text-center d-none d-lg-block">
                    <i class="fas fa-clinic-medical" style="font-size: 20rem; color: rgba(255, 255, 255, 0.15);"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Verified Pharmacies</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number">10K+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number">50K+</div>
                        <div class="stat-label">Products Available</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Customer Support</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Why Choose PharmaHub?</h2>
                <p class="section-subtitle">Your health is our priority. Here's what makes us different.</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5>Verified Pharmacies</h5>
                        <p>All pharmacies are thoroughly verified and licensed, ensuring you receive authentic medications and professional service every time.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-search-location"></i>
                        </div>
                        <h5>Smart Search</h5>
                        <p>Find exactly what you need with our intelligent search system. Filter by categories, location, price, and availability.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h5>Fast Delivery</h5>
                        <p>Get your medications delivered quickly and safely. Track your order in real-time from pharmacy to your doorstep.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-pills"></i>
                        </div>
                        <h5>Wide Product Range</h5>
                        <p>From prescription medications to health supplements and medical supplies - find everything you need in one convenient platform.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h5>Mobile Friendly</h5>
                        <p>Access PharmaHub on any device. Our responsive platform works seamlessly on desktop, tablet, and mobile.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h5>24/7 Support</h5>
                        <p>Our dedicated support team is always available to help you with any questions or concerns about your health needs.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section text-white">
        <div class="container text-center" style="position: relative; z-index: 2;">
            <h2 class="display-4 fw-bold mb-4">Ready to Get Started?</h2>
            <p class="lead mb-5">Join thousands of satisfied customers and pharmacy partners</p>
            
            <?php if (!isLoggedIn()): ?>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="login/register.php" class="btn btn-hero-light hero-button">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </a>
                    <a href="login/login.php" class="btn btn-hero-outline hero-button">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </a>
                </div>
            <?php else: ?>
                <a href="#features" class="btn btn-hero-light hero-button">
                    <i class="fas fa-compass me-2"></i>Explore Platform
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
                        <i class="fas fa-pills me-2"></i>PharmaHub
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
                        <li><a href="#features">Features</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                        <li><a href="#">FAQs</a></li>
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
                <p class="mb-0">&copy; 2025 PharmaHub. All rights reserved. Built with <i class="fas fa-heart text-danger"></i> in Ghana</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scroll Animation Script -->
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.modern-navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>