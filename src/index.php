<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Swedish Membership Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<!-- Navigation -->
<nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            <i class="fas fa-users"></i>
            <span><?php echo t('app_name'); ?></span>
        </div>
        <div class="nav-links">
            <a href="index.php" class="nav-link"><?php echo t('home'); ?></a>
            <a href="index.php#features" class="nav-link"><?php echo t('features'); ?></a>
            <a href="index.php#about" class="nav-link"><?php echo t('about'); ?></a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="admin_dashboard.php" class="nav-link"><?php echo t('dashboard'); ?></a>
                <?php if (hasPermission($_SESSION['user_role'], 'view_members')): ?>
                    <a href="members_list.php" class="nav-link"><?php echo t('members'); ?></a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="nav-actions">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn btn-outline"><?php echo t('login'); ?></a>
                <a href="register.php" class="btn btn-primary"><?php echo t('register'); ?></a>
            <?php else: ?>
                <div class="user-menu">
                    <span class="user-greeting"><?php echo t('welcome'); ?>, <?php echo $_SESSION['user_name']; ?></span>
                    <div class="user-dropdown">
                        <div class="user-info">
                            <strong><?php echo $_SESSION['user_name']; ?></strong>
                            <span><?php echo t($_SESSION['user_role']); ?></span>
                        </div>
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i> <?php echo t('profile'); ?>
                        </a>
                        <a href="logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i> <?php echo t('logout'); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Language Switcher -->
            <?php include 'includes/language_switcher.php'; ?>
        </div>
        <div class="nav-toggle">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</nav>
    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">Welcome to Swedish Membership Portal</h1>
                <p class="hero-description">
                    Join our community and become part of Sweden's premier membership network. 
                    Register today to access exclusive benefits and connect with fellow members.
                </p>
                <div class="hero-actions">
                    <a href="register.php" class="btn btn-primary btn-large">
                        <i class="fas fa-user-plus"></i>
                        Get Started
                    </a>
                    <a href="#features" class="btn btn-outline btn-large">
                        <i class="fas fa-info-circle"></i>
                        Learn More
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <div class="stat-number" id="memberCount">1000+</div>
                        <div class="stat-label">Active Members</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number" id="yearsExperience">5+</div>
                        <div class="stat-label">Years Experience</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number" id="cities">25+</div>
                        <div class="stat-label">Cities in Sweden</div>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <div class="floating-card card-1">
                    <i class="fas fa-shield-alt"></i>
                    <h4>Secure</h4>
                    <p>Bank-level security</p>
                </div>
                <div class="floating-card card-2">
                    <i class="fas fa-bolt"></i>
                    <h4>Fast</h4>
                    <p>Quick registration</p>
                </div>
                <div class="floating-card card-3">
                    <i class="fas fa-mobile-alt"></i>
                    <h4>Responsive</h4>
                    <p>Works on all devices</p>
                </div>
                <div class="main-visual">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="hero-wave">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" class="shape-fill"></path>
                <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" class="shape-fill"></path>
                <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" class="shape-fill"></path>
            </svg>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose Our Portal?</h2>
                <p>Experience the best membership management system designed for Swedish organizations</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h3>Personnummer Verification</h3>
                    <p>Secure Swedish personnummer validation system for authentic membership registration</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Auto Address Lookup</h3>
                    <p>Automatic address completion using Swedish postal codes and location data</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Role-Based Access</h3>
                    <p>Advanced permission system with Super Admin, Board Members, Staff, and Member roles</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile Friendly</h3>
                    <p>Fully responsive design that works perfectly on all devices and screen sizes</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3>Secure Database</h3>
                    <p>Your data is protected with enterprise-level security and regular backups</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Dedicated support team to help you with any questions or issues</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Join Our Community?</h2>
                <p>Register now and become part of Sweden's fastest growing membership network</p>
                <div class="cta-actions">
                    <a href="register.php" class="btn btn-primary btn-large">
                        <i class="fas fa-user-plus"></i>
                        Register Now
                    </a>
                    <a href="login.php" class="btn btn-outline btn-large">
                        <i class="fas fa-sign-in-alt"></i>
                        Member Login
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-users"></i>
                        <span><?php echo APP_NAME; ?></span>
                    </div>
                    <p>Swedish Membership Portal - Connecting communities across Sweden</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <a href="#home">Home</a>
                    <a href="#about">About</a>
                    <a href="#features">Features</a>
                    <a href="register.php">Register</a>
                    <a href="login.php">Login</a>
                </div>
                <div class="footer-section">
                    <h4>Support</h4>
                    <a href="#">Help Center</a>
                    <a href="#">Contact Us</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-map-marker-alt"></i> Stockholm, Sweden</p>
                    <p><i class="fas fa-phone"></i> +46 123 456 789</p>
                    <p><i class="fas fa-envelope"></i> info@memberportal.se</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 <?php echo APP_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>