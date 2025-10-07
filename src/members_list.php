<?php
require_once 'config.php';
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
            }
            break;
            
        case 'suspend':
            if (hasPermission($_SESSION['user_role'], 'suspend_members')) {
                $stmt = $pdo->prepare("UPDATE members SET is_active = 0 WHERE id = ?");
                $stmt->execute([$memberId]);
            }
            break;
            
        case 'activate':
            if (hasPermission($_SESSION['user_role'], 'suspend_members')) {
                $stmt = $pdo->prepare("UPDATE members SET is_active = 1 WHERE id = ?");
                $stmt->execute([$memberId]);
            }
            break;
            
        case 'delete':
            if (hasPermission($_SESSION['user_role'], 'delete_members')) {
                $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
                $stmt->execute([$memberId]);
            }
            break;
    }
    
    // Redirect to avoid form resubmission
    header('Location: members_list.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members List - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
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
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .status-active { color: green; }
        .status-inactive { color: red; }
        .status-pending { color: orange; }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .role-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .role-super_admin { background: #dc3545; color: white; }
        .role-admin { background: #fd7e14; color: white; }
        .role-board_member { background: #6f42c1; color: white; }
        .role-staff { background: #20c997; color: white; }
        .role-moderator { background: #6610f2; color: white; }
        .role-member { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo APP_NAME; ?> - Members Management</h1>
        <div style="color: white;">
            <a href="admin_dashboard.php" style="color: white;">Dashboard</a> | 
            Logged in as: <?php echo $_SESSION['user_name']; ?> 
            (<?php echo getRoleDisplayName($_SESSION['user_role']); ?>)
            | <a href="logout.php" style="color: white;">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="filters">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" 
                               value="<?php echo $_GET['search'] ?? ''; ?>" 
                               placeholder="Search by name or email">
                    </div>
                    
                    <div class="filter-group">
                        <label for="role">Role</label>
                        <select id="role" name="role">
                            <option value="">All Roles</option>
                            <option value="super_admin" <?php echo ($_GET['role'] ?? '') === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                            <option value="admin" <?php echo ($_GET['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="board_member" <?php echo ($_GET['role'] ?? '') === 'board_member' ? 'selected' : ''; ?>>Board Member</option>
                            <option value="staff" <?php echo ($_GET['role'] ?? '') === 'staff' ? 'selected' : ''; ?>>Staff</option>
                            <option value="moderator" <?php echo ($_GET['role'] ?? '') === 'moderator' ? 'selected' : ''; ?>>Moderator</option>
                            <option value="member" <?php echo ($_GET['role'] ?? '') === 'member' ? 'selected' : ''; ?>>Member</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($_GET['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="pending" <?php echo ($_GET['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn">Apply Filters</button>
                        <a href="members_list.php" class="btn" style="background: #6c757d;">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="members-table">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Personnummer</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($member['firstname'] . ' ' . $member['lastname']); ?></td>
                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                        <td><?php echo htmlspecialchars($member['personnummer']); ?></td>
                        <td>
                            <span class="role-badge role-<?php echo $member['role']; ?>">
                                <?php echo getRoleDisplayName($member['role']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($member['status'] === 'pending'): ?>
                                <span class="status-pending">Pending</span>
                            <?php elseif ($member['is_active']): ?>
                                <span class="status-active">Active</span>
                            <?php else: ?>
                                <span class="status-inactive">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('Y-m-d', strtotime($member['registration_date'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($member['status'] === 'pending' && hasPermission($_SESSION['user_role'], 'approve_members')): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-small" onclick="return confirm('Approve this member?')">Approve</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($member['is_active'] && hasPermission($_SESSION['user_role'], 'suspend_members')): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <input type="hidden" name="action" value="suspend">
                                        <button type="submit" class="btn btn-small" style="background: #dc3545;" onclick="return confirm('Suspend this member?')">Suspend</button>
                                    </form>
                                <?php elseif (!$member['is_active'] && hasPermission($_SESSION['user_role'], 'suspend_members')): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit" class="btn btn-small" style="background: #20c997;">Activate</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if (hasPermission($_SESSION['user_role'], 'delete_members') && $member['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-small" style="background: #dc3545;" onclick="return confirm('Delete this member permanently?')">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (empty($members)): ?>
                <div style="padding: 2rem; text-align: center; color: #666;">
                    No members found matching your criteria.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>