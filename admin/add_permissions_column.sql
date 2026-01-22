-- Add permissions column to admin_users table
-- This column stores JSON-structured permissions per admin
-- Note: This script checks if column exists before adding it

SET @dbname = DATABASE();
SET @tablename = 'admin_users';
SET @columnname = 'permissions';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TEXT NULL DEFAULT NULL AFTER `role`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index for better query performance (optional)
-- CREATE INDEX idx_permissions ON admin_users(permissions(255));
