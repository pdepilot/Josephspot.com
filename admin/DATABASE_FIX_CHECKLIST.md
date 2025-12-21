# Database Fix Checklist

## ✅ Completed Steps

### 1. Database Migration
- [x] Created migration script (`admin/migrate_database.php`)
- [x] Migrated all tables from `josep_pot_admin` to `joseph_pot_admin`
- [x] Renamed `men_manager` table to `food_menu_manager`
- [x] Verified all data was transferred successfully

### 2. PHP Files Updated
- [x] `db_connection.php` - Updated to `joseph_pot_admin`
- [x] `menu.php` - Updated table reference to `food_menu_manager`
- [x] `admin/admin-settings.php` - Updated to `joseph_pot_admin`
- [x] `admin/api/get_menu_items_admin.php` - Updated DB and table
- [x] `admin/api/save_menu_item.php` - Updated DB and table
- [x] `admin/api/delete_menu_item.php` - Updated DB and table
- [x] `admin/api/toggle_menu_item.php` - Updated DB and table
- [x] `admin/api/get_menu_items.php` - Updated table reference
- [x] `admin/api/save_settings.php` - Updated to `joseph_pot_admin`
- [x] `admin/api/upload_file.php` - Updated to `joseph_pot_admin`
- [x] `admin/api/backup_settings.php` - Updated to `joseph_pot_admin`
- [x] `admin/api/download_backup.php` - Updated to `joseph_pot_admin`
- [x] `admin/get-gallery.php` - Updated to `joseph_pot_admin`
- [x] `admin/create-gallery.php` - Updated to `joseph_pot_admin`
- [x] `admin/create_menu_table.php` - Updated DB and table
- [x] `admin/create_settings_tables.php` - Updated to `joseph_pot_admin`
- [x] `admin/verify_table.php` - Updated DB and table
- [x] `admin/verify_gallery_table.php` - Updated to `joseph_pot_admin`
- [x] `admin/create_gallery_table.php` - Updated to `joseph_pot_admin`
- [x] `admin/db_config.php` - Already using `joseph_pot_admin`
- [x] `admin/includes/db_connection.php` - Already using `joseph_pot_admin`
- [x] `admin/admin-login.php` - Already using `joseph_pot_admin`
- [x] `admin/setup_database.php` - Already using `joseph_pot_admin`
- [x] `admin/includes/ReservationDatabase.php` - Already using `joseph_pot_admin`

### 3. SQL Files Updated
- [x] `admin/create_men_manager_table.sql` - Updated table name to `food_menu_manager`

### 4. Safety Measures
- [x] Created verification script (`admin/verify_database_fix.php`)
- [x] Created safety check file (`admin/database_safety_check.php`)
- [x] Created cleanup script (`admin/drop_old_database.php`)

## 🔍 Verification Steps

### Step 1: Run Verification Script
```bash
php admin/verify_database_fix.php
```
Expected: All checks should pass ✓

### Step 2: Test Application
- [ ] Test admin login
- [ ] Test menu management (CRUD operations)
- [ ] Test settings page
- [ ] Test gallery management
- [ ] Test frontend menu display
- [ ] Verify all data loads correctly

### Step 3: Check for Remaining References
```bash
# Search for any remaining references to old database
grep -r "josep_pot_admin" . --exclude-dir=node_modules --exclude="*.log"
```
Expected: Only `admin/migrate_database.php` should contain it (as source reference)

### Step 4: Drop Old Database (After Verification)
```bash
php admin/drop_old_database.php
```
⚠️ **WARNING**: This permanently deletes the old database. Only run after full verification.

## 📋 Final Checklist

Before considering the fix complete:

- [ ] All PHP files use `joseph_pot_admin`
- [ ] All table references use `food_menu_manager` (not `men_manager`)
- [ ] Verification script passes all checks
- [ ] Application works correctly in all areas
- [ ] No errors in error logs
- [ ] Old database has been dropped (optional, after verification)

## 🛡️ Safety Features

1. **Database Name Validation**: `admin/database_safety_check.php` prevents use of wrong database
2. **Verification Script**: `admin/verify_database_fix.php` checks everything
3. **Migration Script**: `admin/migrate_database.php` safely transfers data

## 📝 Notes

- The only file that should reference `josep_pot_admin` is `admin/migrate_database.php` (as the source database name)
- All other files must use `joseph_pot_admin` exclusively
- The table `men_manager` has been renamed to `food_menu_manager` in the new database

## ✅ Success Criteria

The fix is complete when:
1. ✅ All files use `joseph_pot_admin`
2. ✅ All table references use `food_menu_manager`
3. ✅ Verification script passes
4. ✅ Application works correctly
5. ✅ Old database is removed (optional)

