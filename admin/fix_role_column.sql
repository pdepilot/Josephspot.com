-- URGENT FIX: Change role column from ENUM to VARCHAR to support new role names
-- This script fixes the role column to accept: 'Super Admin', 'Manager', 'Content Manager', 'Support'

-- Step 1: Change role column from ENUM to VARCHAR(50)
ALTER TABLE `admin_users` 
    MODIFY COLUMN `role` VARCHAR(50) DEFAULT 'Manager';

-- Step 2: Update existing roles to new format
UPDATE `admin_users` SET `role` = 'Super Admin' WHERE `role` = 'super_admin';
UPDATE `admin_users` SET `role` = 'Manager' WHERE `role` = 'admin';
UPDATE `admin_users` SET `role` = 'Support' WHERE `role` = 'moderator';

-- Step 3: Verify the change
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'admin_users' 
  AND COLUMN_NAME = 'role';

