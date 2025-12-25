# RBAC Implementation Guide

## Quick Start

### 1. Run Setup Script First
```
http://localhost/josephspot.com/admin/setup_rbac.php
```

### 2. Add to Any Admin Page

Add these lines **immediately after** `session_start()` and database config:

```php
// Include RBAC functions
require_once 'includes/rbac.php';

// Check if logged in
if (!isLoggedIn()) {
    header("Location: admin-login.php");
    exit;
}

// Check permission for this page
requirePermission($_SESSION['admin_id'], 'module_name', 'view');
```

## Implementation Checklist

### Pages to Update

- [x] dashboard.php - ✅ Already updated
- [ ] admin-orders.php - Add `requirePermission($_SESSION['admin_id'], 'orders', 'view')`
- [ ] admin-reservation.php - Add `requirePermission($_SESSION['admin_id'], 'reservations', 'view')`
- [ ] admin-menu-management.php - Add `requirePermission($_SESSION['admin_id'], 'menu', 'view')`
- [ ] admin-customers.php - Add `requirePermission($_SESSION['admin_id'], 'customers', 'view')`
- [ ] admin-reviews.php - Add `requirePermission($_SESSION['admin_id'], 'reviews', 'view')`
- [ ] admin-gallery.php - Add `requirePermission($_SESSION['admin_id'], 'gallery', 'view')`
- [ ] admin-events.php - Add `requirePermission($_SESSION['admin_id'], 'events', 'view')`
- [ ] admin-contact-messages.php - Add `requirePermission($_SESSION['admin_id'], 'messages', 'view')`
- [ ] admin-settings.php - Add `requirePermission($_SESSION['admin_id'], 'settings', 'view')`

### API Endpoints to Update

For each API endpoint that modifies data, add permission checks:

```php
// Example: api/delete-order.php
session_start();
require_once '../includes/rbac.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!hasPermission($_SESSION['admin_id'], 'orders', 'delete')) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

// Proceed with deletion...
```

### API Endpoints Checklist

- [ ] api/manage-admin.php - Check permissions for admin CRUD operations
- [ ] api/update-order-status.php - Check 'orders.edit' permission
- [ ] api/delete_order.php (if exists) - Check 'orders.delete' permission
- [ ] api/save_menu_item.php - Check 'menu.create' or 'menu.edit'
- [ ] api/delete_menu_item.php - Check 'menu.delete'
- [ ] Other API endpoints as needed

## Code Pattern Examples

### Pattern 1: Simple Page Protection
```php
<?php
session_start();
require_once 'includes/db_config.php'; // Your DB config
require_once 'includes/rbac.php';

if (!isLoggedIn()) {
    header("Location: admin-login.php");
    exit;
}

requirePermission($_SESSION['admin_id'], 'orders', 'view');

// Rest of your page code...
?>
```

### Pattern 2: Conditional UI Elements
```php
<?php
// Show edit button only if user has edit permission
if (hasPermission($_SESSION['admin_id'], 'orders', 'edit')) {
    echo '<button onclick="editOrder()">Edit</button>';
}

// Show delete button only if user has delete permission
if (hasPermission($_SESSION['admin_id'], 'orders', 'delete')) {
    echo '<button onclick="deleteOrder()">Delete</button>';
}
?>
```

### Pattern 3: API Endpoint Protection
```php
<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/rbac.php';

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check specific permission
if (!hasPermission($_SESSION['admin_id'], 'orders', 'delete')) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

// Proceed with action
try {
    // Your code here
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
```

### Pattern 4: Super Admin Bypass (if needed)
```php
<?php
// Allow Super Admin to bypass certain checks
if (isSuperAdmin($_SESSION['admin_id'])) {
    // Super admin can do anything
    $canDelete = true;
} else {
    $canDelete = hasPermission($_SESSION['admin_id'], 'orders', 'delete');
}
?>
```

## Testing Your Implementation

1. **Test as Super Admin** - Should have access to everything
2. **Test as Manager** - Should have access to orders, reservations, menu, etc.
3. **Test as Content Manager** - Should have access to menu, gallery, events
4. **Test as Support** - Should only have view access to orders/reservations
5. **Test unauthorized access** - Try accessing pages without permission, should redirect to unauthorized.php

## Common Issues

### Issue: "Permission denied" for Super Admin
**Solution**: Make sure Super Admin role exists and all permissions are assigned. Run setup_rbac.php again.

### Issue: "admin_id is null" errors
**Solution**: Make sure session_start() is called before checking permissions.

### Issue: Foreign key constraint errors
**Solution**: The setup script handles this, but if you see errors, check that roles table exists and has data before setting role_id.

### Issue: Include path errors
**Solution**: Adjust the path to rbac.php based on your file structure. Use relative paths from the current file location.

