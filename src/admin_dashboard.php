<?php
require_once 'config.php';
requirePermission('view_admin_dashboard');
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #005ea2;
        }
        .admin-nav {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .admin-nav a {
            margin-right: 1rem;
            text-decoration: none;
            color: #005ea2;
            padding: 0.5rem 1rem;
            border-radius: 4px;
        }
        .admin-nav a:hover {
            background: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo APP_NAME; ?> - Admin Dashboard</h1>
        <div style="color: white;">
            Welcome, <?php echo $_SESSION['user_name']; ?> 
            (<?php echo getRoleDisplayName($_SESSION['user_role']); ?>)
            | <a href="logout.php" style="color: white;">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="admin-nav">
            <?php if (hasPermission($_SESSION['user_role'], 'view_members')): ?>
                <a href="members_list.php">View Members</a>
            <?php endif; ?>
            <?php if (hasPermission($_SESSION['user_role'], 'view_reports')): ?>
                <a href="reports.php">Reports</a>
            <?php endif; ?>
            <?php if (hasPermission($_SESSION['user_role'], 'manage_settings')): ?>
                <a href="settings.php">Settings</a>
            <?php endif; ?>
        </div>

        <div class="dashboard-stats">
            <?php
            // Get statistics
            $totalMembers = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
            $activeMembers = $pdo->query("SELECT COUNT(*) FROM members WHERE is_active = 1")->fetchColumn();
            $pendingMembers = $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'pending'")->fetchColumn();
            ?>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalMembers; ?></div>
                <div>Total Members</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $activeMembers; ?></div>
                <div>Active Members</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pendingMembers; ?></div>
                <div>Pending Approval</div>
            </div>
        </div>

        <div class="form-container">
            <h2>Quick Actions</h2>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <?php if (hasPermission($_SESSION['user_role'], 'view_members')): ?>
                    <a href="members_list.php" class="btn">View All Members</a>
                <?php endif; ?>
                <?php if (hasPermission($_SESSION['user_role'], 'approve_members')): ?>
                    <a href="members_list.php?filter=pending" class="btn">Review Pending Members</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>