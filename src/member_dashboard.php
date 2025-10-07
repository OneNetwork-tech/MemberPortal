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
                <span><?php echo APP_NAME; ?></span>
            </div>
            <div class="nav-links">
                <a href="member_dashboard.php" class="nav-link active">Dashboard</a>
                <a href="profile.php" class="nav-link">My Profile</a>
            </div>
            <div class="nav-actions">
                <div class="user-menu">
                    <span class="user-greeting">Welcome, <?php echo $_SESSION['user_name']; ?></span>
                    <div class="user-dropdown">
                        <div class="user-info">
                            <strong><?php echo $_SESSION['user_name']; ?></strong>
                            <span><?php echo getRoleDisplayName($_SESSION['user_role']); ?></span>
                        </div>
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <a href="logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
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