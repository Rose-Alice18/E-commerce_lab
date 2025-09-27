<?php
// Start session
session_start();

// Include core functions
require_once(dirname(__FILE__) . '/settings/core.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Marketplace - Your Trusted Healthcare Platform</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        .navbar-brand {
            font-weight: 700;
        }
        .btn-custom {
            border-radius: 25px;
            padding: 10px 30px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-pills me-2"></i>PharmacyHub
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    
                    <?php if (!isLoggedIn()): ?>
                        <!-- Not logged in menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="login/register.php">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login/login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- Logged in menu -->
                        <?php if (hasAdminPrivileges()): ?>
                            <!-- Admin user menu -->
                            <li class="nav-item">
                                <a class="nav-link" href="admin/category.php">
                                    <i class="fas fa-tags me-1"></i>Categories
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?php echo isset($_SESSION['fname']) ? htmlspecialchars($_SESSION['fname']) : 'User'; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user-circle me-2"></i>Profile
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="login/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Welcome to PharmacyHub</h1>
            <p class="lead mb-5">Your trusted marketplace connecting customers with verified pharmacies and healthcare services</p>
            
            <?php if (!isLoggedIn()): ?>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="login/register.php" class="btn btn-light btn-custom">
                        <i class="fas fa-user-plus me-2"></i>Join as Pharmacy
                    </a>
                    <a href="#features" class="btn btn-outline-light btn-custom">
                        <i class="fas fa-search me-2"></i>Find Pharmacies
                    </a>
                </div>
            <?php else: ?>
                <div class="alert alert-light d-inline-block">
                    <h5 class="alert-heading">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Welcome back, <?php echo htmlspecialchars($_SESSION['fname']); ?>!
                    </h5>
                    <p class="mb-0">
                        <?php if (hasAdminPrivileges()): ?>
                            You have admin access. <a href="admin/category.php" class="alert-link">Manage categories</a>
                        <?php else: ?>
                            Explore our marketplace and discover trusted pharmacy services.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Why Choose PharmacyHub?</h2>
                <p class="lead text-muted">Connecting healthcare providers and customers with trust and convenience</p>
            </div>
            
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-shield-alt fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Verified Pharmacies</h5>
                            <p class="card-text">All pharmacies on our platform are verified and licensed, ensuring you receive authentic medications and professional service.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-search fa-3x text-success"></i>
                            </div>
                            <h5 class="card-title">Easy Search & Filter</h5>
                            <p class="card-text">Find exactly what you need with our advanced search and filtering system by categories, location, and services.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-clock fa-3x text-info"></i>
                            </div>
                            <h5 class="card-title">24/7 Availability</h5>
                            <p class="card-text">Access our platform anytime, anywhere. Find emergency pharmacies and essential medications when you need them most.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-pills fa-3x text-warning"></i>
                            </div>
                            <h5 class="card-title">Wide Range of Services</h5>
                            <p class="card-text">From prescription medications to health supplements, medical supplies to consultation services - find it all in one place.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-mobile-alt fa-3x text-danger"></i>
                            </div>
                            <h5 class="card-title">Mobile Friendly</h5>
                            <p class="card-text">Our responsive platform works seamlessly on all devices, making it easy to find pharmacies on the go.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-star fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Reviews & Ratings</h5>
                            <p class="card-text">Make informed decisions with genuine customer reviews and ratings for each pharmacy and service.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold mb-4">About PharmacyHub</h2>
                    <p class="lead">PharmacyHub is a comprehensive marketplace designed to bridge the gap between customers and trusted pharmacy services.</p>
                    <p>Our platform enables pharmacies to showcase their services while providing customers with easy access to essential healthcare products and services. Whether you're looking for prescription medications, over-the-counter drugs, medical supplies, or health consultations, PharmacyHub connects you with verified healthcare providers in your area.</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Licensed and verified pharmacies</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Secure and user-friendly platform</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Comprehensive service categories</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Customer support and assistance</li>
                    </ul>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-heartbeat" style="font-size: 200px; color: #667eea; opacity: 0.7;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-6 fw-bold mb-4">Ready to Get Started?</h2>
            <p class="lead mb-4">Join thousands of satisfied customers and pharmacy partners</p>
            
            <?php if (!isLoggedIn()): ?>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="login/register.php" class="btn btn-light btn-lg btn-custom">
                        <i class="fas fa-user-plus me-2"></i>Register Now
                    </a>
                    <a href="login/login.php" class="btn btn-outline-light btn-lg btn-custom">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                </div>
            <?php else: ?>
                <p class="lead">
                    <?php if (hasAdminPrivileges()): ?>
                        <a href="admin/category.php" class="btn btn-light btn-lg btn-custom">
                            <i class="fas fa-cogs me-2"></i>Manage Platform
                        </a>
                    <?php else: ?>
                        <a href="#features" class="btn btn-light btn-lg btn-custom">
                            <i class="fas fa-search me-2"></i>Explore Services
                        </a>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-pills me-2"></i>PharmacyHub</h5>
                    <p class="mb-0">Your trusted healthcare marketplace</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0">&copy; 2025 PharmacyHub. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>