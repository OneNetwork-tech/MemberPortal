<?php
require_once 'config.php';
requirePermission('view_admin_dashboard');

// Get statistics
$stats = getMemberStatistics($pdo);
$recentActivity = getRecentActivity($pdo, 5);
$recentMembers = getAllMembers($pdo, ['limit' => 5]);
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: var(--white);
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        
        .dashboard-welcome {
            text-align: center;
        }
        
        .dashboard-welcome h1 {
            color: var(--white);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .dashboard-welcome p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.2rem;
            margin-bottom: 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary-color);
            transition: var(--transition);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-card.warning {
            border-left-color: #f59e0b;
        }
        
        .stat-card.success {
            border-left-color: #10b981;
        }
        
        .stat-card.danger {
            border-left-color: #ef4444;
        }
        
        .stat-card.info {
            border-left-color: #3b82f6;
        }
        
        .stat-header {
            display: flex;
            align-items: center;
            justify-content: between;
            margin-bottom: 1rem;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-card .stat-icon {
            background: rgba(0, 94, 162, 0.1);
            color: var(--primary-color);
        }
        
        .stat-card.warning .stat-icon {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        
        .stat-card.success .stat-icon {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        
        .stat-card.danger .stat-icon {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        
        .stat-card.info .stat-icon {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 0.5rem;
            color: var(--gray-900);
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        .stat-trend {
            font-size: 0.875rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        
        .trend-up {
            color: #10b981;
        }
        
        .trend-down {
            color: #ef4444;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .dashboard-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow);
            padding: 1.5rem;
        }
        
        .dashboard-card h3 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            color: var(--gray-800);
        }
        
        .dashboard-card h3 i {
            color: var(--primary-color);
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .action-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            border: 1px solid var(--gray-200);
        }
        
        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }
        
        .action-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: var(--white);
            font-size: 1.5rem;
        }
        
        .action-card h4 {
            margin-bottom: 0.5rem;
            color: var(--gray-800);
        }
        
        .action-card p {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 1rem;
        }
        
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--primary-color);
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.25rem;
        }
        
        .activity-details {
            font-size: 0.875rem;
            color: var(--gray-600);
        }
        
        .activity-time {
            font-size: 0.75rem;
            color: var(--gray-500);
        }
        
        .member-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .member-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .member-item:last-child {
            border-bottom: none;
        }
        
        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
            flex-shrink: 0;
        }
        
        .member-info {
            flex: 1;
        }
        
        .member-name {
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.25rem;
        }
        
        .member-email {
            font-size: 0.875rem;
            color: var(--gray-600);
        }
        
        .member-role {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            background: var(--gray-100);
            color: var(--gray-700);
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--gray-500);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
    </style>
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
                <a href="admin_dashboard.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <?php if (hasPermission($_SESSION['user_role'], 'view_members')): ?>
                    <a href="members_list.php" class="nav-link"><i class="fas fa-users"></i> Members</a>
                <?php endif; ?>
                <?php if (hasPermission($_SESSION['user_role'], 'view_reports')): ?>
                    <a href="reports.php" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a>
                <?php endif; ?>
                <?php if (hasPermission($_SESSION['user_role'], 'manage_settings')): ?>
                    <a href="settings.php" class="nav-link"><i class="fas fa-cog"></i> Settings</a>
                <?php endif; ?>
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

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <div class="dashboard-welcome">
                <h1>Admin Dashboard</h1>
                <p>Welcome back, <?php echo $_SESSION['user_name']; ?>! Here's what's happening with your membership portal.</p>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo $stats['total_members']; ?></div>
                        <div class="stat-label">Total Members</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-trend trend-up">
                    <i class="fas fa-arrow-up"></i> <?php echo $stats['new_this_month']; ?> new this month
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo $stats['active_members']; ?></div>
                        <div class="stat-label">Active Members</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
                <div class="stat-trend trend-up">
                    <i class="fas fa-check-circle"></i> <?php echo round(($stats['active_members'] / max(1, $stats['total_members'])) * 100); ?>% active rate
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo $stats['pending_members']; ?></div>
                        <div class="stat-label">Pending Approval</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-trend trend-down">
                    <i class="fas fa-exclamation-circle"></i> Needs attention
                </div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo count($stats['by_role']); ?></div>
                        <div class="stat-label">Member Roles</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-user-tag"></i>
                    </div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-info-circle"></i> Role-based access
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <?php if (hasPermission($_SESSION['user_role'], 'view_members')): ?>
            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h4>Manage Members</h4>
                <p>View and manage all member accounts</p>
                <a href="members_list.php" class="btn btn-primary btn-small">View Members</a>
            </div>
            <?php endif; ?>
            
            <?php if (hasPermission($_SESSION['user_role'], 'approve_members')): ?>
            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <h4>Approve Members</h4>
                <p>Review pending membership applications</p>
                <a href="members_list.php?filter=pending" class="btn btn-primary btn-small">Review Applications</a>
            </div>
            <?php endif; ?>
            
            <?php if (hasPermission($_SESSION['user_role'], 'view_reports')): ?>
            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h4>View Reports</h4>
                <p>Analytics and membership statistics</p>
                <a href="reports.php" class="btn btn-primary btn-small">View Reports</a>
            </div>
            <?php endif; ?>
            
            <?php if (hasPermission($_SESSION['user_role'], 'manage_settings')): ?>
            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <h4>Settings</h4>
                <p>Configure portal settings and preferences</p>
                <a href="settings.php" class="btn btn-primary btn-small">Manage Settings</a>
            </div>
            <?php endif; ?>
            
            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h4>Add Member</h4>
                <p>Manually register a new member</p>
                <a href="register.php" class="btn btn-primary btn-small">Add Member</a>
            </div>
            
            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-download"></i>
                </div>
                <h4>Export Data</h4>
                <p>Export member data to CSV</p>
                <a href="export.php" class="btn btn-primary btn-small">Export</a>
            </div>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Recent Activity -->
            <div class="dashboard-card">
                <h3><i class="fas fa-history"></i> Recent Activity</h3>
                <?php if (!empty($recentActivity)): ?>
                    <ul class="activity-list">
                        <?php foreach ($recentActivity as $activity): ?>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-<?php echo getActivityIcon($activity['action']); ?>"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <?php echo htmlspecialchars($activity['firstname'] ?? 'System'); ?> <?php echo htmlspecialchars($activity['lastname'] ?? ''); ?>
                                </div>
                                <div class="activity-details">
                                    <?php echo htmlspecialchars($activity['action']); ?>
                                    <?php if ($activity['details']): ?>
                                        - <?php echo htmlspecialchars($activity['details']); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-time">
                                    <i class="fas fa-clock"></i> 
                                    <?php echo time_elapsed_string($activity['created_at']); ?>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <p>No recent activity</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Members -->
            <div class="dashboard-card">
                <h3><i class="fas fa-user-plus"></i> Recent Members</h3>
                <?php if (!empty($recentMembers)): ?>
                    <ul class="member-list">
                        <?php foreach ($recentMembers as $member): ?>
                        <li class="member-item">
                            <div class="member-avatar">
                                <?php echo strtoupper(substr($member['firstname'], 0, 1) . substr($member['lastname'], 0, 1)); ?>
                            </div>
                            <div class="member-info">
                                <div class="member-name">
                                    <?php echo htmlspecialchars($member['firstname'] . ' ' . $member['lastname']); ?>
                                </div>
                                <div class="member-email">
                                    <?php echo htmlspecialchars($member['email']); ?>
                                </div>
                            </div>
                            <div class="member-role">
                                <?php echo getRoleDisplayName($member['role']); ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <p>No members found</p>
                    </div>
                <?php endif; ?>
                <div style="margin-top: 1rem; text-align: center;">
                    <a href="members_list.php" class="btn btn-outline btn-small">View All Members</a>
                </div>
            </div>
        </div>

        <!-- Role Statistics -->
        <div class="dashboard-card">
            <h3><i class="fas fa-chart-pie"></i> Member Distribution by Role</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                <?php foreach ($stats['by_role'] as $role => $count): ?>
                <div class="stat-card" style="text-align: center; padding: 1rem;">
                    <div class="stat-number"><?php echo $count; ?></div>
                    <div class="stat-label"><?php echo getRoleDisplayName($role); ?></div>
                    <div style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.5rem;">
                        <?php echo round(($count / max(1, $stats['total_members'])) * 100); ?>%
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>

<?php
// Helper function to get activity icons
function getActivityIcon($action) {
    $icons = [
        'user_login' => 'sign-in-alt',
        'user_logout' => 'sign-out-alt',
        'member_registered' => 'user-plus',
        'profile_updated' => 'user-edit',
        'member_approved' => 'user-check',
        'member_suspended' => 'user-slash',
        'member_deleted' => 'user-times',
        'login_failed' => 'exclamation-triangle'
    ];
    
    return $icons[$action] ?? 'circle';
}

// Helper function to show time elapsed
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>