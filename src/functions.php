<?php
/**
 * MEMBERPORTAL - Functions File
 * Contains all helper functions for the membership portal
 */

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
    // Swedish postal code to city mapping
    $postal_codes = [
        '10005' => ['city' => 'Stockholm', 'state' => 'Stockholm'],
        '10044' => ['city' => 'Stockholm', 'state' => 'Stockholm'],
        '11359' => ['city' => 'Stockholm', 'state' => 'Stockholm'],
        '11129' => ['city' => 'Stockholm', 'state' => 'Stockholm'],
        '21119' => ['city' => 'Malmö', 'state' => 'Skåne'],
        '41319' => ['city' => 'Göteborg', 'state' => 'Västra Götaland'],
        '58102' => ['city' => 'Linköping', 'state' => 'Östergötland'],
        '75229' => ['city' => 'Uppsala', 'state' => 'Uppsala'],
        '85178' => ['city' => 'Sundsvall', 'state' => 'Västernorrland'],
        '97234' => ['city' => 'Luleå', 'state' => 'Norrbotten'],
        '90325' => ['city' => 'Umeå', 'state' => 'Västerbotten'],
        '65225' => ['city' => 'Karlstad', 'state' => 'Värmland'],
        '79171' => ['city' => 'Falun', 'state' => 'Dalarna'],
        '55111' => ['city' => 'Jönköping', 'state' => 'Jönköping'],
        '39234' => ['city' => 'Kalmar', 'state' => 'Kalmar'],
        '37104' => ['city' => 'Karlskrona', 'state' => 'Blekinge'],
        '83145' => ['city' => 'Östersund', 'state' => 'Jämtland'],
        '96133' => ['city' => 'Boden', 'state' => 'Norrbotten'],
        '94185' => ['city' => 'Piteå', 'state' => 'Norrbotten'],
        '93134' => ['city' => 'Skellefteå', 'state' => 'Västerbotten']
    ];
    
    return isset($postal_codes[$postal_code]) ? $postal_codes[$postal_code] : null;
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if email already exists
 */
function emailExists($pdo, $email, $exclude_id = null) {
    $sql = "SELECT id FROM members WHERE email = ?";
    $params = [$email];
    
    if ($exclude_id) {
        $sql .= " AND id != ?";
        $params[] = $exclude_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() !== false;
}

/**
 * Check if personnummer already exists
 */
function personnummerExists($pdo, $personnummer, $exclude_id = null) {
    $sql = "SELECT id FROM members WHERE personnummer = ?";
    $params = [$personnummer];
    
    if ($exclude_id) {
        $sql .= " AND id != ?";
        $params[] = $exclude_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() !== false;
}

/**
 * Check if user has permission
 */
function hasPermission($userRole, $permission) {
    $permissions = [
        'super_admin' => [
            'view_members', 'edit_members', 'delete_members', 'manage_roles', 
            'view_reports', 'manage_settings', 'approve_members', 'suspend_members',
            'view_admin_dashboard', 'export_data', 'manage_payments', 'system_config'
        ],
        'admin' => [
            'view_members', 'edit_members', 'delete_members', 'view_reports', 
            'approve_members', 'suspend_members', 'view_admin_dashboard', 'export_data'
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
            'view_own_profile', 'edit_own_profile'
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
            $sql .= " AND is_active = 1 AND status = 'active'";
        } elseif ($filters['status'] === 'inactive') {
            $sql .= " AND is_active = 0";
        } elseif ($filters['status'] === 'pending') {
            $sql .= " AND status = 'pending'";
        }
    }

    if (isset($filters['search']) && !empty($filters['search'])) {
        $sql .= " AND (firstname LIKE ? OR lastname LIKE ? OR email LIKE ? OR personnummer LIKE ?)";
        $searchTerm = "%{$filters['search']}%";
        $params[] = $searchTerm;
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

/**
 * Get member statistics
 */
function getMemberStatistics($pdo) {
    $stats = [];
    
    // Total members
    $stats['total_members'] = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
    
    // Active members
    $stats['active_members'] = $pdo->query("SELECT COUNT(*) FROM members WHERE is_active = 1 AND status = 'active'")->fetchColumn();
    
    // Pending approval
    $stats['pending_members'] = $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'pending'")->fetchColumn();
    
    // Inactive members
    $stats['inactive_members'] = $pdo->query("SELECT COUNT(*) FROM members WHERE is_active = 0")->fetchColumn();
    
    // Members by role
    $roleStats = $pdo->query("SELECT role, COUNT(*) as count FROM members GROUP BY role")->fetchAll(PDO::FETCH_ASSOC);
    $stats['by_role'] = [];
    foreach ($roleStats as $roleStat) {
        $stats['by_role'][$roleStat['role']] = $roleStat['count'];
    }
    
    // New members this month
    $stats['new_this_month'] = $pdo->query("SELECT COUNT(*) FROM members WHERE registration_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
    
    return $stats;
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'Y-m-d') {
    if (!$date) return '';
    $timestamp = strtotime($date);
    return $timestamp ? date($format, $timestamp) : '';
}

/**
 * Format personnummer for display
 */
function formatPersonnummer($personnummer) {
    $clean = preg_replace('/[^\d]/', '', $personnummer);
    
    if (strlen($clean) === 12) {
        $clean = substr($clean, 2);
    }
    
    if (strlen($clean) === 10) {
        return substr($clean, 0, 6) . '-' . substr($clean, 6);
    }
    
    return $personnummer;
}

/**
 * Generate random password
 */
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Send welcome email
 */
function sendWelcomeEmail($email, $name, $login_url) {
    // In a real application, you would use PHPMailer or similar
    $subject = "Welcome to MEMBERPORTAL";
    $message = "
    <html>
    <head>
        <title>Welcome to MEMBERPORTAL</title>
    </head>
    <body>
        <h2>Welcome to MEMBERPORTAL, {$name}!</h2>
        <p>Your membership registration has been received and is pending approval.</p>
        <p>Once approved, you can login at: <a href='{$login_url}'>{$login_url}</a></p>
        <br>
        <p>Best regards,<br>MEMBERPORTAL Team</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@memberportal.se" . "\r\n";
    
    // In production, you would actually send the email
    // mail($email, $subject, $message, $headers);
    
    // For now, just log it
    error_log("Welcome email would be sent to: {$email}");
    return true;
}

/**
 * Log activity
 */
function logActivity($pdo, $user_id, $action, $details = null) {
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Activity logging failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user by ID
 */
function getUserById($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Update user profile
 */
function updateUserProfile($pdo, $user_id, $data) {
    $allowed_fields = ['firstname', 'lastname', 'telephone', 'email', 'address', 'postal_code', 'city', 'state', 'country'];
    $updates = [];
    $params = [];
    
    foreach ($data as $field => $value) {
        if (in_array($field, $allowed_fields)) {
            $updates[] = "{$field} = ?";
            $params[] = $value;
        }
    }
    
    if (empty($updates)) {
        return false;
    }
    
    $params[] = $user_id;
    $sql = "UPDATE members SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";
    
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Profile update failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if user can edit member
 */
function canEditMember($current_user_role, $target_member_role) {
    $hierarchy = [
        'super_admin' => 6,
        'admin' => 5,
        'board_member' => 4,
        'moderator' => 3,
        'staff' => 2,
        'member' => 1
    ];
    
    return ($hierarchy[$current_user_role] ?? 0) > ($hierarchy[$target_member_role] ?? 0);
}

/**
 * Get all roles for dropdown
 */
function getAllRoles() {
    return [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'board_member' => 'Board Member',
        'staff' => 'Staff',
        'moderator' => 'Moderator',
        'member' => 'Member'
    ];
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (basic international format)
 */
function isValidPhone($phone) {
    // Basic international phone validation
    return preg_match('/^\+?[\d\s\-\(\)]{10,}$/', $phone);
}

/**
 * Get pagination parameters
 */
function getPaginationParams($page, $per_page = 20) {
    $page = max(1, (int)$page);
    $offset = ($page - 1) * $per_page;
    return [
        'page' => $page,
        'per_page' => $per_page,
        'offset' => $offset,
        'limit' => $per_page
    ];
}

/**
 * Generate pagination links
 */
function generatePagination($total_items, $current_page, $per_page, $url_pattern) {
    $total_pages = ceil($total_items / $per_page);
    $pagination = [
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'total_items' => $total_items,
        'per_page' => $per_page,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages,
        'previous_page' => $current_page > 1 ? $current_page - 1 : null,
        'next_page' => $current_page < $total_pages ? $current_page + 1 : null,
        'pages' => []
    ];
    
    // Generate page numbers to show
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        $pagination['pages'][] = [
            'number' => $i,
            'url' => str_replace('{page}', $i, $url_pattern),
            'is_current' => $i == $current_page
        ];
    }
    
    return $pagination;
}

/**
 * Export members to CSV
 */
function exportMembersToCSV($pdo, $filters = []) {
    $members = getAllMembers($pdo, $filters);
    
    // Set headers for download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=members_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // CSV header
    fputcsv($output, [
        'ID', 'First Name', 'Last Name', 'Personnummer', 'Email', 'Telephone',
        'Address', 'Postal Code', 'City', 'State', 'Country', 'Role',
        'Status', 'Registration Date', 'Last Login'
    ]);
    
    // Data rows
    foreach ($members as $member) {
        fputcsv($output, [
            $member['id'],
            $member['firstname'],
            $member['lastname'],
            formatPersonnummer($member['personnummer']),
            $member['email'],
            $member['telephone'],
            $member['address'],
            $member['postal_code'],
            $member['city'],
            $member['state'],
            $member['country'],
            getRoleDisplayName($member['role']),
            $member['is_active'] ? 'Active' : 'Inactive',
            formatDate($member['registration_date']),
            formatDate($member['last_login'])
        ]);
    }
    
    fclose($output);
    exit;
}

/**
 * Get recent activity
 */
function getRecentActivity($pdo, $limit = 10) {
    // First, ensure the activity_logs table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'activity_logs'")->fetch();
    
    if (!$tableExists) {
        // Create activity_logs table if it doesn't exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS activity_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                action VARCHAR(255) NOT NULL,
                details TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES members(id) ON DELETE SET NULL
            )
        ");
        return [];
    }
    
    $stmt = $pdo->prepare("
        SELECT al.*, m.firstname, m.lastname, m.email 
        FROM activity_logs al 
        LEFT JOIN members m ON al.user_id = m.id 
        ORDER BY al.created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Clean old data (maintenance function)
 */
function cleanOldData($pdo, $days = 30) {
    try {
        // Delete activity logs older than specified days
        $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$days]);
        
        $deleted_count = $stmt->rowCount();
        error_log("Cleaned {$deleted_count} old activity logs");
        
        return $deleted_count;
    } catch (PDOException $e) {
        error_log("Data cleaning failed: " . $e->getMessage());
        return 0;
    }
}

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>