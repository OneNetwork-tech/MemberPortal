-- Add role to members table
ALTER TABLE members 
ADD COLUMN role ENUM('super_admin', 'admin', 'board_member', 'staff', 'moderator', 'member') DEFAULT 'member',
ADD COLUMN is_active BOOLEAN DEFAULT TRUE,
ADD COLUMN last_login TIMESTAMP NULL,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Create permissions table
CREATE TABLE member_roles_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(50) NOT NULL UNIQUE,
    permissions JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default permissions
INSERT INTO member_roles_permissions (role, permissions) VALUES
('super_admin', '["view_members", "edit_members", "delete_members", "manage_roles", "view_reports", "manage_settings", "approve_members", "suspend_members"]'),
('admin', '["view_members", "edit_members", "delete_members", "view_reports", "approve_members", "suspend_members"]'),
('board_member', '["view_members", "view_reports", "approve_members"]'),
('staff', '["view_members", "edit_members"]'),
('moderator', '["view_members", "edit_members", "approve_members", "suspend_members"]'),
('member', '["view_own_profile"]');