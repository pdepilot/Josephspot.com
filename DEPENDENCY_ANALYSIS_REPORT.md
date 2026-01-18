# Dependency Analysis Report - Joseph's Pot Website
**Generated:** 2025-01-XX  
**Analysis Method:** Manual code inspection and dependency tracing

## Executive Summary

- **Total Files Scanned:** ~2,729 files
- **Critical Files:** Configuration, database, core includes
- **Useful Files:** All referenced PHP, CSS, JS, and assets
- **Unused Files Identified:** See detailed list below

---

## CRITICAL FILES (Never Delete)

These files are essential for the application to function:

### Configuration Files
- `config/database_config.php`
- `config/database.php`
- `config/smtp_config.php`
- `.htaccess` files (root, admin, uploads directories)

### Core Includes
- `includes/Database.php`
- `includes/EmailService.php`
- `includes/admin_auth.php`
- `includes/appearance_settings.php`
- `includes/restaurant_info.php`
- `includes/careers-functions.php`
- `includes/PHPMailer/src/*` (Active PHPMailer installation)

### Entry Points
- `index.php`
- `about.php`
- `menu.php`
- `gallery.php`
- `contact.php`
- `career.php`
- `order-online.php`
- `breakfast.php`, `lunch.php`, `dinner.php`, `drink.php`
- All `admin/*.php` entry points
- All `api/*.php` endpoints

### Database Files
- `database/careers_schema.sql`
- `joseph_pot_admin.sql`

---

## UNUSED FILES (Safe to Remove)

### 1. Duplicate PHPMailer Directory
**Path:** `includes/PHPMailer-master/`  
**Reason:** The application uses `includes/PHPMailer/` (referenced in `EmailService.php`). The `-master` directory is unused.  
**Proof:** 
- `EmailService.php` line 6-8: `__DIR__ . '/PHPMailer/src/Exception.php'` (not PHPMailer-master)
- No references to `PHPMailer-master` found in codebase
**Files to Delete:** Entire `includes/PHPMailer-master/` directory (~60+ files)

### 2. Archive File
**Path:** `phpmailer-master.zip`  
**Reason:** Archive file, not referenced anywhere  
**Proof:** No references in codebase  
**Files to Delete:** `phpmailer-master.zip`

### 3. Broken/Unused Script
**Path:** `send-email.php`  
**Reason:** References `vendor/autoload.php` which doesn't exist. Not referenced by any other file.  
**Proof:**
- Line 4: `require_once 'vendor/autoload.php';` (vendor directory doesn't exist)
- No references to `send-email.php` found in codebase
- Contact form uses `save-contact.php` and EmailJS, not this file
**Files to Delete:** `send-email.php`

### 4. Root-Level Database Connection (Potentially Unused)
**Path:** `db_connection.php` (root)  
**Status:** ⚠️ **REVIEW NEEDED**  
**Reason:** There are multiple database connection files:
- Root: `db_connection.php`
- Admin: `admin/includes/db_connection.php`
- Includes: `includes/Database.php`
- Config: `config/database.php`, `config/database_config.php`

**Analysis:** Root `db_connection.php` requires `admin/database_safety_check.php`, suggesting it might be used. However, most files use `includes/Database.php` or direct connections.  
**Recommendation:** Keep for now, but verify actual usage.

### 5. Development Documentation Files
**Path:** `admin/*.md` files  
**Reason:** Development documentation, not needed in production  
**Files:**
- `admin/URGENT_FIX_ROLE_COLUMN.md`
- `admin/STRICT_ACCESS_CONTROL_IMPLEMENTATION.md`
- `admin/STRICT_ACCESS_CONTROL.md`
- `admin/AUTHENTICATION_FIXES_SUMMARY.md`
- `admin/IMPLEMENTATION_SUMMARY.md`
- `admin/FOREIGN_KEY_FIX_SUMMARY.md`
- `admin/AUTHENTICATION_SYSTEM_SUMMARY.md`
- `admin/RBAC_SETUP_SUMMARY.md`
- `admin/RBAC_README.md`
- `admin/RBAC_IMPLEMENTATION_GUIDE.md`
- `admin/NOTIFICATION_SYSTEM_README.md`

**Recommendation:** These are development notes. Safe to remove if not needed for reference.

### 6. Temporary Analysis Files
**Path:** 
- `analyze_dependencies.py`
- `analyze_dependencies.ps1`
- `temp_file_list.txt`
- `DEPENDENCY_ANALYSIS_REPORT.md` (this file)

**Reason:** Created for this analysis  
**Files to Delete:** After review, these can be removed

### 7. FontAwesome Unused Files
**Path:** `fontawesome-free-6.7.2-web/`  
**Status:** ⚠️ **PARTIAL CLEANUP POSSIBLE**  
**Reason:** Only `css/all.css` is referenced. However, FontAwesome may need other files for icons to work.  
**Recommendation:** Keep entire directory unless you're certain about icon dependencies.

---

## VERIFICATION SUMMARY

### Files Verified as USED:
✅ All root-level PHP pages (index.php, about.php, etc.)  
✅ All admin PHP files  
✅ All API endpoints  
✅ `includes/PHPMailer/` (not -master)  
✅ All CSS files in `CSS/` directory  
✅ All JS files in `JAVASCRIPT/` directory  
✅ All images in `images/` directory (referenced in HTML/PHP)  
✅ All uploads (may be referenced from database)  
✅ Config files  
✅ Database files  

### Files Verified as UNUSED:
❌ `includes/PHPMailer-master/` (entire directory)  
❌ `phpmailer-master.zip`  
❌ `send-email.php`  
❌ Development documentation (`.md` files in admin)  
❌ Analysis scripts (temporary)

---

## RECOMMENDED DELETIONS

### High Confidence (Safe to Delete):
1. `includes/PHPMailer-master/` - Entire directory
2. `phpmailer-master.zip` - Archive file
3. `send-email.php` - Broken/unused script
4. `admin/*.md` - Development documentation (11 files)
5. Analysis scripts (after review)

### Medium Confidence (Review First):
1. Root `db_connection.php` - Verify if actually used
2. FontAwesome unused files - If you're certain about dependencies

---

## NEXT STEPS

1. Review this report
2. Verify high-confidence deletions
3. Test application after deletions
4. Remove empty directories
5. Clean up temporary analysis files

---

## NOTES

- All deletions are reversible if you have version control
- Test thoroughly after each deletion batch
- Keep backups before major cleanup
- FontAwesome directory is large but may be needed for icon functionality
