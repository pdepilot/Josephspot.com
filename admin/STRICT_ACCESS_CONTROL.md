# Strict Access Control Implementation

## Overview
This document describes the strict access control system that prevents unauthorized admins from accessing pages they don't have permission for.

## Security Layers Implemented

### Layer 1: Page-Level Access Control
**Location:** `admin/admin-auth.php`

**Function:** `checkPageAccess()`
- Called at the top of EVERY admin page
- Enforces authentication AND permission checks
- Redirects to `unauthorized.php` if no permission
- Logs all unauthorized access attempts

**Implementation:**
```php
require_once 'admin-auth.php';
checkPageAccess(); // Enforces strict access control
```

**Pages Protected:**
- ✅ dashboard.php
- ✅ admin-orders.php
- ✅ admin-reservation.php
- ✅ admin-menu-management.php
- ✅ admin-contact-messages.php
- ✅ admin-reviews.php
- ✅ admin-events.php
- ✅ admin-gallery.php
- ✅ admin-settings.php
- ✅ admin-order-online-menu.php
- ✅ admin-customers.php

### Layer 2: Multiple Authentication Checks
**Function:** `requireAuth()`
- Checks if admin is logged in
- Verifies admin exists in database
- Prevents session hijacking by verifying session matches database
- Updates session with latest admin data

### Layer 3: Strict Permission Checking
**Function:** `requirePagePermission()`
- Detects current page module automatically
- Checks permission against database
- **FAIL SECURE:** If module cannot be determined, access is DENIED
- Double-checks permission to prevent race conditions
- Logs all unauthorized attempts

### Layer 4: API Endpoint Protection
**Location:** `admin/api-auth.php`

**Function:** `requireAPIPermission($module, $permission)`
- Checks authentication
- Verifies permission for specific action
- Returns JSON error response for API calls
- Prevents unauthorized API access

**Protected Endpoints:**
- ✅ api/update-order-status.php (requires 'orders', 'edit')
- ✅ api/save_menu_item.php (requires 'menu_management', 'create')
- ✅ api/delete_menu_item.php (requires 'menu_management', 'delete')
- ✅ api/toggle_menu_item.php (requires 'menu_management', 'edit')
- ✅ api/manage-admin.php (requires Super Admin)

## How It Works

### 1. Page Access Flow
```
User requests page
    ↓
checkPageAccess() called
    ↓
requireAuth() - Verify logged in
    ↓
getCurrentAdmin() - Verify exists in DB
    ↓
getModuleFromPage() - Detect module
    ↓
hasPermission() - Check permission
    ↓
If NO permission → Redirect to unauthorized.php
If YES permission → Allow access
```

### 2. Permission Check Process
```
hasPermission($module, $permission)
    ↓
Check if Super Admin → Always allow
    ↓
Check admin_permissions table
    ↓
Look for 'all' permission first
    ↓
If not found, check specific permission
    ↓
Return true/false
```

### 3. Unauthorized Access Handling
```
No permission detected
    ↓
Log attempt to error log
    ↓
Redirect to unauthorized.php
    ↓
Show friendly message
    ↓
Display list of authorized pages
    ↓
Provide navigation options
```

## Security Features

### ✅ Fail Secure
- If module cannot be determined → Access DENIED
- If permission check fails → Access DENIED
- If admin not found → Access DENIED
- Default is DENY, not ALLOW

### ✅ Session Security
- Session verified against database on every request
- Session mismatch detected and handled
- Inactive accounts automatically logged out
- Session data refreshed from database

### ✅ Logging
- All unauthorized access attempts logged
- Includes: User ID, Role, Attempted Module, Timestamp
- Helps identify security issues

### ✅ No Bypass Methods
- Direct URL access → Blocked
- Session manipulation → Detected
- Missing permission → Blocked
- Unknown modules → Blocked

## Testing Access Control

### Test 1: Direct URL Access
1. Login as Support admin
2. Try: `admin-menu-management.php` → Should redirect to unauthorized.php
3. Try: `admin-orders.php` → Should show page (has view permission)

### Test 2: Permission Enforcement
1. Login as Content Manager
2. Try to access Orders page → Should redirect
3. Try to access Menu Management → Should show page

### Test 3: API Protection
1. Login as Support admin
2. Try API: `update-order-status.php` → Should return 403 error
3. Try API: `save_menu_item.php` → Should return 403 error

### Test 4: Super Admin Access
1. Login as Super Admin
2. All pages should be accessible
3. All APIs should work

## Module-to-Page Mapping

| Module | Page | Required Permission |
|--------|------|---------------------|
| dashboard | dashboard.php | view |
| orders | admin-orders.php | view |
| reservations | admin-reservation.php | view |
| menu_management | admin-menu-management.php | view |
| contact_messages | admin-contact-messages.php | view |
| reviews | admin-reviews.php | view |
| events | admin-events.php | view |
| gallery | admin-gallery.php | view |
| settings | admin-settings.php | view |
| order_online_menu | admin-order-online-menu.php | view |
| customers | admin-customers.php | view |
| admin_management | dashboard.php#admin-management | view (Super Admin only) |

## Role Permissions Summary

### Super Admin
- ✅ All modules: Full access
- ✅ All APIs: Full access
- ✅ Admin Management: Full access

### Manager
- ✅ Dashboard, Orders, Reservations, Menu, Contact, Reviews, Events, Gallery
- ❌ Admin Management
- ⚠️ Settings: View only

### Content Manager
- ✅ Dashboard (view), Menu Management, Reviews, Events, Gallery
- ❌ Orders, Reservations, Contact Messages, Settings, Admin Management

### Support
- ✅ Dashboard (view), Contact Messages, Orders (view), Reservations (view)
- ❌ All other modules

## Important Notes

1. **No Exceptions:** The system does NOT allow access if permission is missing
2. **Automatic Detection:** Module is detected from filename automatically
3. **Fail Secure:** Unknown modules = Access DENIED
4. **Logging:** All attempts are logged for security auditing
5. **User-Friendly:** Unauthorized page shows what user CAN access

## Maintenance

### Adding New Admin Pages
1. Add page to `getModuleFromPage()` mapping in `admin-auth.php`
2. Add `require_once 'admin-auth.php';` at top
3. Add `checkPageAccess();` after require
4. Add permission entry in `admin_permissions` table

### Adding New API Endpoints
1. Add `require_once __DIR__ . '/../api-auth.php';` at top
2. Add `requireAPIPermission('module', 'permission');`
3. Ensure proper error handling

### Updating Permissions
1. Update `admin_permissions` table
2. Test with different roles
3. Verify unauthorized access is blocked

