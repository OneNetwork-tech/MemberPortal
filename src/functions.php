<?php
// functions.php

/**
 * Validate Swedish personnummer
 */
function validatePersonnummer($personnummer) {
    // Remove any dashes or spaces
    $personnummer = preg_replace('/[-\s]/', '', $personnummer);
    
    // Check if it's 10 or 12 digits
    if (!preg_match('/^(\d{10}|\d{12})$/', $personnummer)) {
        return false;
    }
    
    // If 12 digits, remove first two (century)
    if (strlen($personnummer) == 12) {
        $personnummer = substr($personnummer, 2);
    }
    
    // Luhn algorithm validation
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $digit = intval($personnummer[$i]);
        if ($i % 2 == 0) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        $sum += $digit;
    }
    
    $checksum = (10 - ($sum % 10)) % 10;
    return $checksum == intval($personnummer[9]);
}

/**
 * Get address information from Swedish postal code
 */
function getAddressFromPostalCode($postal_code) {
    // Use Postnord or similar API - this is a simplified version
    $postal_codes = [
        '11359' => ['city' => 'Stockholm', 'state' => 'Stockholm'],
        '11129' => ['city' => 'Stockholm', 'state' => 'Stockholm'],
        '21119' => ['city' => 'Malmö', 'state' => 'Skåne'],
        '41319' => ['city' => 'Göteborg', 'state' => 'Västra Götaland'],
        // Add more postal codes as needed
    ];
    
    return isset($postal_codes[$postal_code]) ? $postal_codes[$postal_code] : null;
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Check if email already exists
 */
function emailExists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch() !== false;
}

/**
 * Check if personnummer already exists
 */
function personnummerExists($pdo, $personnummer) {
    $stmt = $pdo->prepare("SELECT id FROM members WHERE personnummer = ?");
    $stmt->execute([$personnummer]);
    return $stmt->fetch() !== false;
}
?>

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