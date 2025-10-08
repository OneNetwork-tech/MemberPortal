<?php
require_once 'config.php';
requireAuth();
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - <?php echo APP_NAME; ?></title>
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
    <div class="container" style="margin-top: 2rem;">
        <div class="form-container">
            <h1>Member Dashboard</h1>
            <p>Welcome to your member dashboard, <?php echo $_SESSION['user_name']; ?>!</p>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-number">Member</div>
                    <div>Your Role</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">Active</div>
                    <div>Account Status</div>
                </div>
            </div>
            
            <div style="margin-top: 2rem;">
                <h3>Quick Actions</h3>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;">
                    <a href="profile.php" class="btn btn-primary">Update Profile</a>
                    <a href="logout.php" class="btn btn-outline">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>