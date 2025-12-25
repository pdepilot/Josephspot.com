# Role-Based Access Control (RBAC) System

## Overview
This RBAC system provides fine-grained permission management for the admin panel. It allows you to control which admins can access which pages and perform which actions.

## Setup Instructions

### Step 1: Run Database Setup
Navigate to `setup_rbac.php` in your browser:
```
http://localhost/josephspot.com/admin/setup_rbac.php
```

This script will:
- Create the `roles`, `permissions`, and `role_permissions` tables
- Add `role_id` and `status` columns to `admin_users` table
- Insert default roles and permissions
- Migrate existing admin data

### Step 2: Include RBAC in Your Pages
Add this code to the top of each admin page (after session_start and database config):

```php
// Include RBAC functions
require_once 'includes/rbac.php';

// Check permission
requirePermission($_SESSION['admin_id'], 'module', 'action');
```

## Database Structure

### Tables Created

1. **roles** - Defines user roles (Super Admin, Manager, etc.)
2. **permissions** - Defines all possible permissions (module.action combinations)
3. **role_permissions** - Links roles to their permissions
4. **admin_users** - Modified to use `role_id` instead of `role` enum

### Default Roles

- **Super Admin** - Has all permissions
- **Manager** - Can manage orders, reservations, menu, customers, reviews, messages
- **Content Manager** - Can manage menu, gallery, events, reviews
- **Support** - Can view orders/reservations and respond to messages

## Usage Examples

### Basic Permission Check
```php
// At the top of admin-orders.php
requirePermission($_SESSION['admin_id'], 'orders', 'view');
```

### Conditional Permission Check
```php
// In your code, check before allowing an action
if (hasPermission($_SESSION['admin_id'], 'orders', 'delete')) {
    // Allow deletion
} else {
    echo "Permission denied";
}
```

### Check if Super Admin
```php
if (isSuperAdmin($_SESSION['admin_id'])) {
    // Super admin only code
}
```

### Get Admin's Role
```php
$role = getAdminRole($_SESSION['admin_id']);
echo "Current role: " . $role['name'];
```

## Available Functions

### Core Functions

- `hasPermission($admin_id, $module, $action)` - Check if admin has specific permission
- `requirePermission($admin_id, $module, $action, $redirect_url)` - Require permission or redirect
- `getAdminPermissions($admin_id)` - Get all permissions for an admin
- `getRolePermissions($role_id)` - Get all permissions for a role
- `getAdminRole($admin_id)` - Get admin's role information
- `isSuperAdmin($admin_id)` - Check if admin is Super Admin
- `hasModuleAccess($admin_id, $module)` - Check if admin has any permission in module

### Management Functions

- `assignPermissionToRole($role_id, $permission_id)` - Grant permission to role
- `revokePermissionFromRole($role_id, $permission_id)` - Remove permission from role
- `getAllRoles()` - Get all roles
- `getAllPermissions()` - Get all permissions
- `getPermissionsByModule()` - Get permissions grouped by module

## Module and Action Names

### Modules
- `dashboard` - Dashboard page
- `orders` - Orders management
- `reservations` - Reservations management
- `menu` - Menu management
- `customers` - Customer management
- `reviews` - Reviews management
- `gallery` - Gallery management
- `events` - Events management
- `messages` - Contact messages
- `settings` - Settings page
- `admins` - Admin management

### Actions
- `view` - View/list items
- `create` - Create new items
- `edit` - Edit existing items
- `delete` - Delete items
- `export` - Export data
- `approve` - Approve items (for reviews)

## Permission Mapping for Pages

| Page | Module | Action |
|------|--------|--------|
| dashboard.php | dashboard | view |
| admin-orders.php | orders | view |
| admin-reservation.php | reservations | view |
| admin-menu-management.php | menu | view |
| admin-customers.php | customers | view |
| admin-reviews.php | reviews | view |
| admin-gallery.php | gallery | view |
| admin-events.php | events | view |
| admin-contact-messages.php | messages | view |
| admin-settings.php | settings | view |

## API Endpoint Permission Checks

For API endpoints, check permissions before executing:

```php
// In api/update-order-status.php
session_start();
require_once '../includes/rbac.php';

if (!hasPermission($_SESSION['admin_id'], 'orders', 'edit')) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

// Proceed with order update...
```

## Updating Admin Roles

When creating or updating admins, you'll need to set the `role_id` instead of `role`. Use the roles table to get role IDs:

```php
// Get role ID by name
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id FROM roles WHERE name = ?");
$stmt->bind_param("s", $roleName);
$stmt->execute();
$result = $stmt->get_result();
$role = $result->fetch_assoc();
$roleId = $role['id'];
```

## Security Notes

1. Always check permissions at the top of admin pages
2. Check permissions in API endpoints before executing actions
3. Super Admin bypasses all permission checks (check with `isSuperAdmin()` if needed)
4. Inactive or suspended admins are automatically denied access
5. Always validate admin_id from session, never trust user input

## Troubleshooting

### "Permission denied" but should have access
1. Check if admin's `role_id` is set correctly
2. Verify role has the required permission assigned
3. Check admin's `status` is 'active'
4. Verify permission exists in permissions table

### Foreign key constraint errors
- The setup script handles foreign key constraints automatically
- If migration fails, you may need to manually set role_id for existing admins

### Role not found
- Run `setup_rbac.php` to create default roles
- Check roles table exists and has data

