<?php
require_once 'config.php';
require_once 'language_handler.php';
requirePermission('view_members');

// Handle filters
$filters = [];
if (isset($_GET['role']) && $_GET['role'] !== '') {
    $filters['role'] = $_GET['role'];
}
if (isset($_GET['status']) && $_GET['status'] !== '') {
    $filters['status'] = $_GET['status'];
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

// Get members based on filters
$members = getAllMembers($pdo, $filters);

// Handle member actions (approve, suspend, etc.)
if (isset($_POST['action']) && isset($_POST['member_id'])) {
    $memberId = $_POST['member_id'];
    $action = $_POST['action'];
    
    switch ($action) {
        case 'approve':
            if (hasPermission($_SESSION['user_role'], 'approve_members')) {
                $stmt = $pdo->prepare("UPDATE members SET status = 'active', is_active = 1 WHERE id = ?");
                $stmt->execute([$memberId]);
                logActivity($pdo, $_SESSION['user_id'], 'member_approved', 'Approved member ID: ' . $memberId);
            }
            break;
            
        case 'suspend':
            if (hasPermission($_SESSION['user_role'], 'suspend_members')) {
                $stmt = $pdo->prepare("UPDATE members SET is_active = 0 WHERE id = ?");
                $stmt->execute([$memberId]);
                logActivity($pdo, $_SESSION['user_id'], 'member_suspended', 'Suspended member ID: ' . $memberId);
            }
            break;
            
        case 'activate':
            if (hasPermission($_SESSION['user_role'], 'suspend_members')) {
                $stmt = $pdo->prepare("UPDATE members SET is_active = 1 WHERE id = ?");
                $stmt->execute([$memberId]);
                logActivity($pdo, $_SESSION['user_id'], 'member_activated', 'Activated member ID: ' . $memberId);
            }
            break;
            
        case 'delete':
            if (hasPermission($_SESSION['user_role'], 'delete_members')) {
                // Log before deletion
                $member = getUserById($pdo, $memberId);
                if ($member) {
                    logActivity($pdo, $_SESSION['user_id'], 'member_deleted', 'Deleted member: ' . $member['email']);
                }
                $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
                $stmt->execute([$memberId]);
            }
            break;
            
        case 'change_role':
            if (hasPermission($_SESSION['user_role'], 'manage_roles') && isset($_POST['new_role'])) {
                $newRole = $_POST['new_role'];
                $stmt = $pdo->prepare("UPDATE members SET role = ? WHERE id = ?");
                $stmt->execute([$newRole, $memberId]);
                logActivity($pdo, $_SESSION['user_id'], 'role_changed', 'Changed role for member ID: ' . $memberId . ' to ' . $newRole);
            }
            break;
    }
    
    // Redirect to avoid form resubmission
    header('Location: members_list.php' . (isset($_GET['role']) ? '?role=' . $_GET['role'] : ''));
    exit;
}
?>

<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('members'); ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: var(--white);
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .page-title {
            text-align: center;
        }
        
        .page-title h1 {
            color: var(--white);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .page-title p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            margin-bottom: 0;
        }
        
        .filters {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            border: 1px solid var(--gray-200);
        }
        
        .filter-row {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .members-table {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }
        
        th {
            background: var(--gray-50);
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-active { 
            color: #10b981;
            font-weight: 600;
        }
        
        .status-inactive { 
            color: #ef4444;
            font-weight: 600;
        }
        
        .status-pending { 
            color: #f59e0b;
            font-weight: 600;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .btn-small {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            border-radius: var(--border-radius);
        }
        
        .role-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .role-super_admin { 
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        
        .role-admin { 
            background: linear-gradient(135deg, #fd7e14, #e55a00);
            color: white;
        }
        
        .role-board_member { 
            background: linear-gradient(135deg, #6f42c1, #5a2d9e);
            color: white;
        }
        
        .role-staff { 
            background: linear-gradient(135deg, #20c997, #199d75);
            color: white;
        }
        
        .role-moderator { 
            background: linear-gradient(135deg, #6610f2, #520dc2);
            color: white;
        }
        
        .role-member { 
            background: linear-gradient(135deg, #6c757d, #545b62);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--gray-500);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        .member-actions-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .export-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--success-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            transition: var(--transition);
        }
        
        .export-btn:hover {
            background: #0da65c;
            transform: translateY(-1px);
        }
        
        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
                gap: 1rem;
            }
            
            .filter-group {
                min-width: 100%;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            th, td {
                padding: 0.75rem 0.5rem;
                font-size: 0.875rem;
            }
            
            .member-actions-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
        
        .role-select {
            padding: 0.25rem 0.5rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 0.75rem;
            background: var(--white);
        }
    </style>
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
                <a href="admin_dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> <?php echo t('dashboard'); ?></a>
                <a href="members_list.php" class="nav-link active"><i class="fas fa-users"></i> <?php echo t('members'); ?></a>
                <?php if (hasPermission($_SESSION['user_role'], 'view_reports')): ?>
                    <a href="reports.php" class="nav-link"><i class="fas fa-chart-bar"></i> <?php echo t('reports'); ?></a>
                <?php endif; ?>
                <?php if (hasPermission($_SESSION['user_role'], 'manage_settings')): ?>
                    <a href="settings.php" class="nav-link"><i class="fas fa-cog"></i> <?php echo t('settings'); ?></a>
                <?php endif; ?>
            </div>
            <div class="nav-actions">
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
                <?php include 'includes/language_switcher.php'; ?>
            </div>
            <div class="nav-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="page-title">
                <h1><?php echo t('members'); ?> <?php echo t('management'); ?></h1>
                <p><?php echo t('manage_members_description'); ?></p>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Overview -->
        <?php
        $stats = getMemberStatistics($pdo);
        $totalMembers = $stats['total_members'];
        $activeMembers = $stats['active_members'];
        $pendingMembers = $stats['pending_members'];
        ?>
        
        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalMembers; ?></div>
                <div class="stat-label"><?php echo t('total_members'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $activeMembers; ?></div>
                <div class="stat-label"><?php echo t('active_members'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pendingMembers; ?></div>
                <div class="stat-label"><?php echo t('pending_approval'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($members); ?></div>
                <div class="stat-label"><?php echo t('filtered_results'); ?></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="search"><?php echo t('search'); ?></label>
                        <div class="input-with-icon">
                            <i class="fas fa-search"></i>
                            <input type="text" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" 
                                   placeholder="<?php echo t('search_placeholder'); ?>">
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label for="role"><?php echo t('role'); ?></label>
                        <select id="role" name="role">
                            <option value=""><?php echo t('all_roles'); ?></option>
                            <option value="super_admin" <?php echo ($_GET['role'] ?? '') === 'super_admin' ? 'selected' : ''; ?>><?php echo t('super_admin'); ?></option>
                            <option value="admin" <?php echo ($_GET['role'] ?? '') === 'admin' ? 'selected' : ''; ?>><?php echo t('admin'); ?></option>
                            <option value="board_member" <?php echo ($_GET['role'] ?? '') === 'board_member' ? 'selected' : ''; ?>><?php echo t('board_member'); ?></option>
                            <option value="staff" <?php echo ($_GET['role'] ?? '') === 'staff' ? 'selected' : ''; ?>><?php echo t('staff'); ?></option>
                            <option value="moderator" <?php echo ($_GET['role'] ?? '') === 'moderator' ? 'selected' : ''; ?>><?php echo t('moderator'); ?></option>
                            <option value="member" <?php echo ($_GET['role'] ?? '') === 'member' ? 'selected' : ''; ?>><?php echo t('member'); ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="status"><?php echo t('status'); ?></label>
                        <select id="status" name="status">
                            <option value=""><?php echo t('all_status'); ?></option>
                            <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>><?php echo t('active'); ?></option>
                            <option value="inactive" <?php echo ($_GET['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>><?php echo t('inactive'); ?></option>
                            <option value="pending" <?php echo ($_GET['status'] ?? '') === 'pending' ? 'selected' : ''; ?>><?php echo t('pending'); ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i>
                            <?php echo t('apply_filters'); ?>
                        </button>
                        <a href="members_list.php" class="btn btn-outline">
                            <i class="fas fa-times"></i>
                            <?php echo t('clear'); ?>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Members Table -->
        <div class="members-table">
            <div class="member-actions-header">
                <h3 style="margin: 0;">
                    <i class="fas fa-users"></i>
                    <?php echo t('members_list'); ?> 
                    <span style="font-size: 0.875rem; color: var(--gray-600); font-weight: normal;">
                        (<?php echo count($members); ?> <?php echo t('members_found'); ?>)
                    </span>
                </h3>
                
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <?php if (hasPermission($_SESSION['user_role'], 'export_data')): ?>
                        <a href="export_members.php?<?php echo http_build_query($_GET); ?>" class="export-btn">
                            <i class="fas fa-download"></i>
                            <?php echo t('export_csv'); ?>
                        </a>
                    <?php endif; ?>
                    
                    <a href="register.php" class="btn btn-primary btn-small">
                        <i class="fas fa-user-plus"></i>
                        <?php echo t('add_member'); ?>
                    </a>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th><?php echo t('name'); ?></th>
                        <th><?php echo t('email'); ?></th>
                        <th><?php echo t('personnummer'); ?></th>
                        <th><?php echo t('role'); ?></th>
                        <th><?php echo t('status'); ?></th>
                        <th><?php echo t('registration_date'); ?></th>
                        <th><?php echo t('last_login'); ?></th>
                        <th><?php echo t('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($member['firstname'] . ' ' . $member['lastname']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                        <td><?php echo htmlspecialchars(formatPersonnummer($member['personnummer'])); ?></td>
                        <td>
                            <?php if (hasPermission($_SESSION['user_role'], 'manage_roles') && $member['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                    <input type="hidden" name="action" value="change_role">
                                    <select name="new_role" class="role-select" onchange="this.form.submit()">
                                        <?php foreach (getAllRoles() as $roleValue => $roleName): ?>
                                            <option value="<?php echo $roleValue; ?>" <?php echo $member['role'] === $roleValue ? 'selected' : ''; ?>>
                                                <?php echo t($roleValue); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            <?php else: ?>
                                <span class="role-badge role-<?php echo $member['role']; ?>">
                                    <?php echo t($member['role']); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($member['status'] === 'pending'): ?>
                                <span class="status-pending">
                                    <i class="fas fa-clock"></i> <?php echo t('pending'); ?>
                                </span>
                            <?php elseif ($member['is_active']): ?>
                                <span class="status-active">
                                    <i class="fas fa-check-circle"></i> <?php echo t('active'); ?>
                                </span>
                            <?php else: ?>
                                <span class="status-inactive">
                                    <i class="fas fa-times-circle"></i> <?php echo t('inactive'); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatDate($member['registration_date']); ?></td>
                        <td>
                            <?php if ($member['last_login']): ?>
                                <?php echo formatDate($member['last_login'], 'Y-m-d H:i'); ?>
                            <?php else: ?>
                                <span style="color: var(--gray-500);"><?php echo t('never'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($member['status'] === 'pending' && hasPermission($_SESSION['user_role'], 'approve_members')): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-small btn-success" onclick="return confirm('<?php echo t('confirm_approve'); ?>')">
                                            <i class="fas fa-check"></i> <?php echo t('approve'); ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($member['is_active'] && hasPermission($_SESSION['user_role'], 'suspend_members') && $member['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <input type="hidden" name="action" value="suspend">
                                        <button type="submit" class="btn btn-small btn-warning" onclick="return confirm('<?php echo t('confirm_suspend'); ?>')">
                                            <i class="fas fa-pause"></i> <?php echo t('suspend'); ?>
                                        </button>
                                    </form>
                                <?php elseif (!$member['is_active'] && hasPermission($_SESSION['user_role'], 'suspend_members') && $member['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit" class="btn btn-small btn-success">
                                            <i class="fas fa-play"></i> <?php echo t('activate'); ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if (hasPermission($_SESSION['user_role'], 'delete_members') && $member['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('<?php echo t('confirm_delete'); ?>')">
                                            <i class="fas fa-trash"></i> <?php echo t('delete'); ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <a href="member_profile.php?id=<?php echo $member['id']; ?>" class="btn btn-small btn-outline">
                                    <i class="fas fa-eye"></i> <?php echo t('view'); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (empty($members)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3><?php echo t('no_members_found'); ?></h3>
                    <p><?php echo t('no_members_match'); ?></p>
                    <a href="members_list.php" class="btn btn-primary"><?php echo t('clear_filters'); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>