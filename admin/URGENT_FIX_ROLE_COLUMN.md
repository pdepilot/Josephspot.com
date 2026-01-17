# URGENT FIX: Role Column Issue

## Problem Identified

The `admin_users.role` column is currently:
- **Type:** `ENUM('super_admin','admin','moderator')`
- **Default:** `admin`

But the code is trying to insert values like:
- `'Super Admin'`
- `'Manager'`
- `'Content Manager'`
- `'Support'`

These values **DO NOT MATCH** the ENUM values, so MySQL either:
1. Rejects the insert (if strict mode is on)
2. Uses the default value `'admin'` (if strict mode is off)

## Solution

Change the `role` column from ENUM to VARCHAR(50) to accept the new role names.

## How to Fix

### Option 1: Run the PHP Fix Script (Recommended)
1. Open in browser: `http://localhost/josephspot.com/admin/fix_role_column.php`
2. The script will automatically:
   - Change column type from ENUM to VARCHAR(50)
   - Update existing records to new role format
   - Verify the changes

### Option 2: Run SQL Script Manually
1. Open phpMyAdmin or MySQL command line
2. Select database: `joseph_pot_admin`
3. Run the SQL from: `admin/fix_role_column.sql`

```sql
-- Change role column from ENUM to VARCHAR(50)
ALTER TABLE `admin_users` 
    MODIFY COLUMN `role` VARCHAR(50) DEFAULT 'Manager';

-- Update existing roles to new format
UPDATE `admin_users` SET `role` = 'Super Admin' WHERE `role` = 'super_admin';
UPDATE `admin_users` SET `role` = 'Manager' WHERE `role` = 'admin';
UPDATE `admin_users` SET `role` = 'Support' WHERE `role` = 'moderator';
```

## After Fix

1. Run `debug_role_save.php` again to verify the column type is now VARCHAR(50)
2. Try creating a new admin from the dashboard
3. Check that the role is saved correctly
4. Verify in database that the role value matches what was selected

## Role Mapping Reference

| Old ENUM Value | New VARCHAR Value |
|----------------|-------------------|
| super_admin    | Super Admin       |
| admin          | Manager           |
| moderator      | Support           |
| (new)          | Content Manager   |

