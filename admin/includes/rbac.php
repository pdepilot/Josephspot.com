<?php
/**
 * Role-Based Access Control (RBAC) System
 * Core functions for permission checking and role management
 */

if (!defined('DB_HOST')) {
    die("Database configuration not found. Please include this file after database config.");
}

/**
 * Get database connection (reuse existing function if available)
 */
function getRBACConnection() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("RBAC Connection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
    }
    return $conn;
}

/**
 * Check if RBAC tables are set up
 * 
 * @return bool True if RBAC is set up, false otherwise
 */
function isRBACSetup() {
    $conn = getRBACConnection();
    
    // Check if role_id column exists in admin_users
    $result = $conn->query("SHOW COLUMNS FROM `admin_users` LIKE 'role_id'");
    if ($result->num_rows === 0) {
        return false;
    }
    
    // Check if roles table exists
    $result = $conn->query("SHOW TABLES LIKE 'roles'");
    if ($result->num_rows === 0) {
        return false;
    }
    
    // Check if permissions table exists
    $result = $conn->query("SHOW TABLES LIKE 'permissions'");
    if ($result->num_rows === 0) {
        return false;
    }
    
    return true;
}

/**
 * Check if admin has permission for a module and action
 * 
 * @param int $admin_id Admin user ID
 * @param string $module Module name (e.g., 'dashboard', 'orders')
 * @param string $action Action name (e.g., 'view', 'edit', 'delete')
 * @return bool True if admin has permission, false otherwise
 */
function hasPermission($admin_id, $module, $action) {
    if (empty($admin_id) || empty($module) || empty($action)) {
        return false;
    }
    
    $conn = getRBACConnection();
    
    // Check if RBAC is set up, if not, use fallback to old role system
    if (!isRBACSetup()) {
        // Fallback: Check old role column for Super Admin
        $result = $conn->query("SHOW COLUMNS FROM `admin_users` LIKE 'role'");
        if ($result->num_rows > 0) {
            $stmt = $conn->prepare("SELECT role FROM admin_users WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $admin_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $admin = $result->fetch_assoc();
                    $stmt->close();
                    // If Super Admin in old system, grant all permissions temporarily
                    if ($admin['role'] === 'super_admin') {
                        return true;
                    }
                }
            }
        }
        // If RBAC not set up and not super admin, deny access
        return false;
    }
    
    // Get admin's role_id and status
    $stmt = $conn->prepare("SELECT role_id, status FROM admin_users WHERE id = ?");
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return false;
    }
    
    $admin = $result->fetch_assoc();
    $stmt->close();
    
    // Check if admin is active
    if (isset($admin['status']) && $admin['status'] !== 'active') {
        return false;
    }
    
    // If no role assigned, deny access
    if (empty($admin['role_id'])) {
        return false;
    }
    
    // Check if role has the permission
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM role_permissions rp
        INNER JOIN permissions p ON rp.permission_id = p.id
        WHERE rp.role_id = ? AND p.module = ? AND p.action = ?
    ");
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("iss", $admin['role_id'], $module, $action);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return ($row['count'] > 0);
}

/**
 * Get all permissions for an admin user
 * 
 * @param int $admin_id Admin user ID
 * @return array Array of permissions with module and action
 */
function getAdminPermissions($admin_id) {
    if (empty($admin_id)) {
        return [];
    }
    
    // Check if RBAC is set up
    if (!isRBACSetup()) {
        return [];
    }
    
    $conn = getRBACConnection();
    
    // Get admin's role_id
    $statusCheck = "status = 'active'";
    $result = $conn->query("SHOW COLUMNS FROM `admin_users` LIKE 'status'");
    if ($result->num_rows === 0) {
        $statusCheck = "1=1"; // Status column doesn't exist yet
    }
    
    $stmt = $conn->prepare("SELECT role_id FROM admin_users WHERE id = ? AND $statusCheck");
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0 || !($admin = $result->fetch_assoc()) || empty($admin['role_id'])) {
        $stmt->close();
        return [];
    }
    
    $role_id = $admin['role_id'];
    $stmt->close();
    
    // Get all permissions for this role
    return getRolePermissions($role_id);
}

/**
 * Get all permissions for a role
 * 
 * @param int $role_id Role ID
 * @return array Array of permissions with module, action, and description
 */
function getRolePermissions($role_id) {
    if (empty($role_id)) {
        return [];
    }
    
    $conn = getRBACConnection();
    
    $stmt = $conn->prepare("
        SELECT p.id, p.module, p.action, p.description
        FROM role_permissions rp
        INNER JOIN permissions p ON rp.permission_id = p.id
        WHERE rp.role_id = ?
        ORDER BY p.module, p.action
    ");
    
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $permissions = [];
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row;
    }
    
    $stmt->close();
    return $permissions;
}

/**
 * Get admin's role information
 * 
 * @param int $admin_id Admin user ID
 * @return array|null Role information or null if not found
 */
function getAdminRole($admin_id) {
    if (empty($admin_id)) {
        return null;
    }
    
    // Check if RBAC is set up
    if (!isRBACSetup()) {
        // Fallback: Return role from old system
        $conn = getRBACConnection();
        $result = $conn->query("SHOW COLUMNS FROM `admin_users` LIKE 'role'");
        if ($result->num_rows > 0) {
            $stmt = $conn->prepare("SELECT role FROM admin_users WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $admin_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $admin = $result->fetch_assoc();
                    $stmt->close();
                    // Map old role to new role name
                    $roleMap = [
                        'super_admin' => 'Super Admin',
                        'admin' => 'Manager',
                        'moderator' => 'Support'
                    ];
                    $roleName = isset($roleMap[$admin['role']]) ? $roleMap[$admin['role']] : 'Manager';
                    return [
                        'id' => null,
                        'name' => $roleName,
                        'description' => 'Legacy role (RBAC not set up)',
                        'is_default' => 0
                    ];
                }
            }
        }
        return null;
    }
    
    $conn = getRBACConnection();
    
    $statusCheck = "au.status = 'active'";
    $result = $conn->query("SHOW COLUMNS FROM `admin_users` LIKE 'status'");
    if ($result->num_rows === 0) {
        $statusCheck = "1=1"; // Status column doesn't exist yet
    }
    
    $stmt = $conn->prepare("
        SELECT r.id, r.name, r.description, r.is_default
        FROM admin_users au
        INNER JOIN roles r ON au.role_id = r.id
        WHERE au.id = ? AND $statusCheck
    ");
    
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }
    
    $role = $result->fetch_assoc();
    $stmt->close();
    
    return $role;
}

/**
 * Assign permission to a role
 * 
 * @param int $role_id Role ID
 * @param int $permission_id Permission ID
 * @return bool True on success, false on failure
 */
function assignPermissionToRole($role_id, $permission_id) {
    if (empty($role_id) || empty($permission_id)) {
        return false;
    }
    
    $conn = getRBACConnection();
    
    $stmt = $conn->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("ii", $role_id, $permission_id);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

/**
 * Revoke permission from a role
 * 
 * @param int $role_id Role ID
 * @param int $permission_id Permission ID
 * @return bool True on success, false on failure
 */
function revokePermissionFromRole($role_id, $permission_id) {
    if (empty($role_id) || empty($permission_id)) {
        return false;
    }
    
    $conn = getRBACConnection();
    
    $stmt = $conn->prepare("DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?");
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("ii", $role_id, $permission_id);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

/**
 * Get all roles
 * 
 * @return array Array of all roles
 */
function getAllRoles() {
    $conn = getRBACConnection();
    
    $result = $conn->query("SELECT id, name, description, is_default FROM roles ORDER BY name");
    if (!$result) {
        return [];
    }
    
    $roles = [];
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
    }
    
    return $roles;
}

/**
 * Get all permissions
 * 
 * @return array Array of all permissions grouped by module
 */
function getAllPermissions() {
    $conn = getRBACConnection();
    
    $result = $conn->query("SELECT id, module, action, description FROM permissions ORDER BY module, action");
    if (!$result) {
        return [];
    }
    
    $permissions = [];
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row;
    }
    
    return $permissions;
}

/**
 * Get permissions grouped by module
 * 
 * @return array Permissions grouped by module
 */
function getPermissionsByModule() {
    $permissions = getAllPermissions();
    
    $grouped = [];
    foreach ($permissions as $perm) {
        if (!isset($grouped[$perm['module']])) {
            $grouped[$perm['module']] = [];
        }
        $grouped[$perm['module']][] = $perm;
    }
    
    return $grouped;
}

/**
 * Check if admin has any permission in a module
 * 
 * @param int $admin_id Admin user ID
 * @param string $module Module name
 * @return bool True if admin has any permission in the module
 */
function hasModuleAccess($admin_id, $module) {
    $permissions = getAdminPermissions($admin_id);
    
    foreach ($permissions as $perm) {
        if ($perm['module'] === $module) {
            return true;
        }
    }
    
    return false;
}

/**
 * Require permission - redirects to unauthorized page if permission not granted
 * Use this function at the top of admin pages
 * 
 * @param int $admin_id Admin user ID
 * @param string $module Module name
 * @param string $action Action name
 * @param string $redirect_url Optional redirect URL (default: unauthorized.php)
 */
function requirePermission($admin_id, $module, $action, $redirect_url = 'unauthorized.php') {
    // Check if RBAC is set up, if not, show helpful message
    if (!isRBACSetup()) {
        // For now, allow access but show warning
        // In production, you might want to redirect to setup page
        error_log("RBAC not set up. Please run setup_rbac.php to initialize the RBAC system.");
        // Temporarily allow access until RBAC is set up
        // Uncomment the line below to require setup:
        // header("Location: setup_rbac.php?message=Please+run+RBAC+setup+first");
        // exit;
        return; // Allow access temporarily
    }
    
    if (!hasPermission($admin_id, $module, $action)) {
        header("Location: " . $redirect_url);
        exit;
    }
}

/**
 * Check if admin is Super Admin (has all permissions)
 * Super Admin role ID is typically 1, but we check by name for safety
 * 
 * @param int $admin_id Admin user ID
 * @return bool True if admin is Super Admin
 */
function isSuperAdmin($admin_id) {
    $role = getAdminRole($admin_id);
    return ($role && $role['name'] === 'Super Admin');
}

/**
 * Get admin status
 * 
 * @param int $admin_id Admin user ID
 * @return string|null Admin status or null if not found
 */
function getAdminStatus($admin_id) {
    if (empty($admin_id)) {
        return null;
    }
    
    $conn = getRBACConnection();
    
    $stmt = $conn->prepare("SELECT status FROM admin_users WHERE id = ?");
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }
    
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['status'];
}

?>

