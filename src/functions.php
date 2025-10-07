<?php
// ... existing functions ...

/**
 * Check if user has permission
 */
function hasPermission($userRole, $permission) {
    $permissions = [
        'super_admin' => [
            'view_members', 'edit_members', 'delete_members', 'manage_roles', 
            'view_reports', 'manage_settings', 'approve_members', 'suspend_members',
            'view_admin_dashboard'
        ],
        'admin' => [
            'view_members', 'edit_members', 'delete_members', 'view_reports', 
            'approve_members', 'suspend_members', 'view_admin_dashboard'
        ],
        'board_member' => [
            'view_members', 'view_reports', 'approve_members', 'view_admin_dashboard'
        ],
        'staff' => [
            'view_members', 'edit_members', 'view_admin_dashboard'
        ],
        'moderator' => [
            'view_members', 'edit_members', 'approve_members', 'suspend_members',
            'view_admin_dashboard'
        ],
        'member' => [
            'view_own_profile'
        ]
    ];

    return isset($permissions[$userRole]) && in_array($permission, $permissions[$userRole]);
}

/**
 * Redirect if not authenticated
 */
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Redirect if no permission
 */
function requirePermission($permission) {
    requireAuth();
    
    if (!isset($_SESSION['user_role']) || !hasPermission($_SESSION['user_role'], $permission)) {
        header('Location: unauthorized.php');
        exit;
    }
}

/**
 * Get all members with optional filters
 */
function getAllMembers($pdo, $filters = []) {
    $sql = "SELECT * FROM members WHERE 1=1";
    $params = [];

    if (isset($filters['role']) && $filters['role'] !== '') {
        $sql .= " AND role = ?";
        $params[] = $filters['role'];
    }

    if (isset($filters['status']) && $filters['status'] !== '') {
        if ($filters['status'] === 'active') {
            $sql .= " AND is_active = 1";
        } elseif ($filters['status'] === 'inactive') {
            $sql .= " AND is_active = 0";
        }
    }

    if (isset($filters['search']) && !empty($filters['search'])) {
        $sql .= " AND (firstname LIKE ? OR lastname LIKE ? OR email LIKE ?)";
        $searchTerm = "%{$filters['search']}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $sql .= " ORDER BY registration_date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get role display name
 */
function getRoleDisplayName($role) {
    $roleNames = [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'board_member' => 'Board Member',
        'staff' => 'Staff',
        'moderator' => 'Moderator',
        'member' => 'Member'
    ];

    return $roleNames[$role] ?? ucfirst(str_replace('_', ' ', $role));
}
?>