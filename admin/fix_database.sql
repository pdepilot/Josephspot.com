-- Database Fixes for Admin Authentication System
-- Run this SQL file to fix all database issues

-- PART 1: Update admin_users table structure
ALTER TABLE `admin_users` 
    MODIFY COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT;

-- Add is_active column if it doesn't exist
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'admin_users' 
    AND COLUMN_NAME = 'is_active');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `admin_users` ADD COLUMN `is_active` TINYINT(1) DEFAULT 1 AFTER `role`', 
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add unique key on email if it doesn't exist
SET @key_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'admin_users' 
    AND INDEX_NAME = 'email');
SET @sql = IF(@key_exists = 0, 
    'ALTER TABLE `admin_users` ADD UNIQUE KEY `email` (`email`)', 
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- PART 2: Update role column to support new role names
-- Change role column from ENUM to VARCHAR to support exact role names
ALTER TABLE `admin_users` 
    MODIFY COLUMN `role` VARCHAR(50) DEFAULT 'Manager';

-- Update existing roles to new format
UPDATE `admin_users` SET `role` = 'Super Admin' WHERE `role` = 'super_admin';
UPDATE `admin_users` SET `role` = 'Manager' WHERE `role` = 'admin' AND `role` != 'Super Admin';
UPDATE `admin_users` SET `role` = 'Support' WHERE `role` = 'moderator';
UPDATE `admin_users` SET `role` = 'Content Manager' WHERE `role` = 'content_editor';

-- Insert Super Admin if not exists (username: admin, password: admin123)
-- Password hash for 'admin123'
INSERT INTO `admin_users` (`username`, `password_hash`, `full_name`, `email`, `role`, `is_active`, `created_at`, `updated_at`)
SELECT 'admin', '$2y$10$TSodindX8SzS4ptkt5mvT.5EK8dS.EstBskNwerjW9CNlyNbkQ1he', 'Admin Joseph', 'admin@josephspot.com', 'Super Admin', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `admin_users` WHERE `username` = 'admin');

-- PART 3: Fix foreign key in login_activity table to reference admin_users(id) instead of admins(id)
-- First, drop the old foreign key if it exists
SET @constraint_name = (SELECT CONSTRAINT_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'login_activity' 
    AND COLUMN_NAME = 'admin_id' 
    AND REFERENCED_TABLE_NAME = 'admins'
    LIMIT 1);

SET @sql = IF(@constraint_name IS NOT NULL, 
    CONCAT('ALTER TABLE `login_activity` DROP FOREIGN KEY `', @constraint_name, '`'), 
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add new foreign key referencing admin_users
ALTER TABLE `login_activity` 
    ADD CONSTRAINT `fk_login_activity_admin_users` 
    FOREIGN KEY (`admin_id`) REFERENCES `admin_users`(`id`) ON DELETE CASCADE;

-- PART 4: Create admin_permissions table for role-based access
CREATE TABLE IF NOT EXISTS `admin_permissions` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `role` VARCHAR(50) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `permission` ENUM('view', 'create', 'edit', 'delete', 'all') NOT NULL DEFAULT 'view',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `role_module_permission` (`role`, `module`, `permission`),
    INDEX `idx_role` (`role`),
    INDEX `idx_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Update existing permissions to use new role names
UPDATE `admin_permissions` SET `role` = 'Super Admin' WHERE `role` = 'super_admin';
UPDATE `admin_permissions` SET `role` = 'Manager' WHERE `role` = 'manager';
UPDATE `admin_permissions` SET `role` = 'Content Manager' WHERE `role` = 'content_editor';
UPDATE `admin_permissions` SET `role` = 'Support' WHERE `role` = 'support';

-- PART 5: Insert default permissions for roles
-- First, clear existing permissions to avoid duplicates
DELETE FROM `admin_permissions`;

-- Super Admin - Full access to everything (all permissions for all modules)
INSERT INTO `admin_permissions` (`role`, `module`, `permission`) VALUES
('Super Admin', 'dashboard', 'all'),
('Super Admin', 'contact_messages', 'all'),
('Super Admin', 'menu_management', 'all'),
('Super Admin', 'reservations', 'all'),
('Super Admin', 'orders', 'all'),
('Super Admin', 'order_online_menu', 'all'),
('Super Admin', 'reviews', 'all'),
('Super Admin', 'events', 'all'),
('Super Admin', 'gallery', 'all'),
('Super Admin', 'admin_management', 'all'),
('Super Admin', 'settings', 'all'),
('Super Admin', 'customers', 'all')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Manager - Access to most modules except admin management and settings (view only)
INSERT INTO `admin_permissions` (`role`, `module`, `permission`) VALUES
('Manager', 'dashboard', 'all'),
('Manager', 'contact_messages', 'all'),
('Manager', 'menu_management', 'all'),
('Manager', 'reservations', 'all'),
('Manager', 'orders', 'all'),
('Manager', 'order_online_menu', 'all'),
('Manager', 'reviews', 'all'),
('Manager', 'events', 'all'),
('Manager', 'gallery', 'all'),
('Manager', 'settings', 'view'),
('Manager', 'customers', 'view')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Content Manager - Limited to content management (no orders, reservations, admin management, settings)
INSERT INTO `admin_permissions` (`role`, `module`, `permission`) VALUES
('Content Manager', 'dashboard', 'view'),
('Content Manager', 'menu_management', 'all'),
('Content Manager', 'reviews', 'all'),
('Content Manager', 'events', 'all'),
('Content Manager', 'gallery', 'all')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Support - Limited access to customer-facing modules only
INSERT INTO `admin_permissions` (`role`, `module`, `permission`) VALUES
('Support', 'dashboard', 'view'),
('Support', 'contact_messages', 'all'),
('Support', 'orders', 'view'),
('Support', 'reservations', 'view')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Ensure Super Admin has correct password hash (admin123)
UPDATE `admin_users` 
SET `password_hash` = '$2y$10$TSodindX8SzS4ptkt5mvT.5EK8dS.EstBskNwerjW9CNlyNbkQ1he'
WHERE `username` = 'admin' AND `role` = 'Super Admin';

