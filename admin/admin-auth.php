<?php
/**
 * Central Authentication and Permission System
 * Include this file at the top of every admin page
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'joseph_pot_admin');
}

// Get database connection with error handling
function getAuthDBConnection() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($conn->connect_error) {
                error_log("Database connection failed: " . $conn->connect_error);
                header("Location: error.php?type=database");
                exit;
            }
            $conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            error_log("Database connection exception: " . $e->getMessage());
            header("Location: error.php?type=database");
            exit;
        }
    }
    return $conn;
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']) && 
           isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Get current admin data with proper error handling
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    try {
        $conn = getAuthDBConnection();
        $admin_id = $_SESSION['admin_id'];
        
        // Check if status column exists, otherwise use basic query
        $column_check = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'status'");
        $has_status = $column_check && $column_check->num_rows > 0;
        
        // Build query based on available columns
        if ($has_status) {
            $sql = "SELECT id, username, email, full_name, role, last_login, status FROM admin_users WHERE id = ? AND status = 'active'";
        } else {
            // Fallback: check if is_active exists
            $column_check2 = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'is_active'");
            $has_is_active = $column_check2 && $column_check2->num_rows > 0;
            
            if ($has_is_active) {
                $sql = "SELECT id, username, email, full_name, role, last_login, is_active FROM admin_users WHERE id = ? AND is_active = 1";
            } else {
                // No status column, just get basic info
                $sql = "SELECT id, username, email, full_name, role, last_login FROM admin_users WHERE id = ?";
            }
        }
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                return $admin;
            }
        }
    } catch (mysqli_sql_exception $e) {
        error_log("getCurrentAdmin SQL Error: " . $e->getMessage());
        // Don't show technical error to user
        return null;
    } catch (Exception $e) {
        error_log("getCurrentAdmin Error: " . $e->getMessage());
        return null;
    }
    
    return null;
}

// Check if admin has permission for a module with error handling
// Using hasAdminPermission to avoid conflict with rbac.php hasPermission()
function hasAdminPermission($module, $permission = 'view') {
    if (!isAdminLoggedIn()) {
        return false;
    }
    
    try {
        // Get admin role from session
        $role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : null;
        
        if (!$role) {
            // Try to get from database
            $admin = getCurrentAdmin();
            if ($admin && isset($admin['role'])) {
                $role = $admin['role'];
                $_SESSION['admin_role'] = $role;
            } else {
                return false;
            }
        }
        
        // Normalize role names for permission checking
        // Now roles are stored as exact names, but check both old and new formats for compatibility
        $role_map = [
            'Super Admin' => 'Super Admin',
            'super_admin' => 'Super Admin', // Legacy support
            'Manager' => 'Manager',
            'manager' => 'Manager', // Legacy support
            'Content Manager' => 'Content Manager',
            'content_editor' => 'Content Manager', // Legacy support
            'Support' => 'Support',
            'support' => 'Support', // Legacy support
            'Admin' => 'Manager', // Map generic Admin to Manager
            'admin' => 'Manager', // Legacy support
            'moderator' => 'Support' // Legacy support
        ];
        
        // Convert role to standard format
        $db_role = isset($role_map[$role]) ? $role_map[$role] : $role;
        
        // CRITICAL: Super Admin has ALL permissions - check FIRST and bypass all permission checks
        // This MUST be checked before any database queries for performance and security
        if ($role === 'super_admin' || $role === 'Super Admin') {
            return true; // Super Admin bypasses ALL permission checks - full access
        }
        
        $conn = getAuthDBConnection();
        
        // Check if admin_permissions table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'admin_permissions'");
        if ($table_check->num_rows === 0) {
            // Table doesn't exist - DENY access for strict security (fail secure)
            error_log("admin_permissions table not found. Please run admin/fix_database.sql. Access DENIED for security.");
            return false; // STRICT: Deny access if permissions table doesn't exist
        }
        
        // Check for 'all' permission first
        $stmt = $conn->prepare("SELECT id FROM admin_permissions WHERE role = ? AND module = ? AND permission = 'all'");
        if ($stmt) {
            $stmt->bind_param("ss", $db_role, $module);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            
            if ($result->num_rows > 0) {
                return true;
            }
        }
        
        // Check for specific permission
        $stmt = $conn->prepare("SELECT id FROM admin_permissions WHERE role = ? AND module = ? AND permission = ?");
        if ($stmt) {
            $stmt->bind_param("sss", $db_role, $module, $permission);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            
            return $result->num_rows > 0;
        }
    } catch (mysqli_sql_exception $e) {
        error_log("hasPermission SQL Error: " . $e->getMessage());
        // On error, deny access for security
        return false;
    } catch (Exception $e) {
        error_log("hasPermission Error: " . $e->getMessage());
        return false;
    }
    
    return false;
}

// Map page filename to module name
function getModuleFromPage($filename) {
    $page_to_module = [
        'dashboard.php' => 'dashboard',
        'admin-orders.php' => 'orders',
        'admin-reservation.php' => 'reservations',
        'admin-menu-management.php' => 'menu_management',
        'admin-contact-messages.php' => 'contact_messages',
        'admin-reviews.php' => 'reviews',
        'admin-events.php' => 'events',
        'admin-gallery.php' => 'gallery',
        'admin-settings.php' => 'settings',
        'admin-order-online-menu.php' => 'order_online_menu',
        'admin-customers.php' => 'customers',
    ];
    
    $basename = basename($filename);
    return isset($page_to_module[$basename]) ? $page_to_module[$basename] : null;
}

// Helper function to check permission and show/hide UI elements
function canAccess($module, $permission = 'view') {
    return hasAdminPermission($module, $permission);
}

// Alias for backward compatibility - only create if rbac.php hasn't defined it
// This allows both systems to coexist
if (!function_exists('hasPermission')) {
    /**
     * Alias function for backward compatibility
     * Uses admin-auth.php permission system
     */
    function hasPermission($module, $permission = 'view') {
        // Use the admin-auth.php function
        if (function_exists('hasAdminPermission')) {
            return hasAdminPermission($module, $permission);
        }
        return false;
    }
}

// Require authentication - redirect to login if not logged in
// STRICT VERSION - Multiple security checks
function requireAuth() {
    // Check 1: Basic session check
    if (!isAdminLoggedIn()) {
        // Clear any partial session data
        session_unset();
        header("Location: admin-login.php");
        exit;
    }
    
    // Check 2: Verify admin still exists and is active in database
    $admin = getCurrentAdmin();
    if (!$admin) {
        // Admin doesn't exist or is inactive - destroy session completely
        session_unset();
        session_destroy();
        header("Location: admin-login.php?error=account_inactive");
        exit;
    }
    
    // Check 3: Verify session admin_id matches database admin_id (prevent session fixation)
    if (isset($_SESSION['admin_id']) && $admin['id'] != $_SESSION['admin_id']) {
        // Session mismatch - potential security issue
        error_log("Session mismatch detected: Session ID " . $_SESSION['admin_id'] . " vs Database ID " . $admin['id']);
        session_unset();
        session_destroy();
        header("Location: admin-login.php?error=session_error");
        exit;
    }
    
    // Check 4: Update session with latest admin data (refresh from database)
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['admin_full_name'] = $admin['full_name'];
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['last_auth_check'] = time();
}

// Require permission for current page - redirect to unauthorized if no permission
// STRICT: Super Admin bypasses all checks, all others must have explicit permission
function requirePagePermission($module = null, $permission = 'view') {
    requireAuth();
    
    // CRITICAL: Check Super Admin FIRST - bypasses all permission checks
    if (isAdminSuperAdmin()) {
        return; // Super Admin has full access - allow immediately
    }
    
    // If module not provided, try to detect from current page
    if (!$module) {
        $module = getModuleFromPage($_SERVER['PHP_SELF']);
    }
    
    if (!$module) {
        // Can't determine module - deny access for security (fail secure)
        error_log("Unauthorized access attempt: Cannot determine module for page: " . $_SERVER['PHP_SELF'] . " (User ID: " . ($_SESSION['admin_id'] ?? 'unknown') . ")");
        header("Location: unauthorized.php?module=unknown");
        exit;
    }
    
    // STRICT: Check permission - if no permission, deny access immediately
    if (!hasAdminPermission($module, $permission)) {
        // Log unauthorized access attempt for security auditing
        $adminId = $_SESSION['admin_id'] ?? 'unknown';
        $adminRole = $_SESSION['admin_role'] ?? 'unknown';
        error_log("STRICT ACCESS DENIED: User ID $adminId (Role: $adminRole) attempted to access: $module/$permission");
        header("Location: unauthorized.php?module=" . urlencode($module));
        exit;
    }
}

// Check if admin is Super Admin (handles both formats)
// Using isAdminSuperAdmin to avoid conflict with rbac.php isSuperAdmin()
function isAdminSuperAdmin() {
    if (!isAdminLoggedIn()) {
        return false;
    }
    
    $role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : null;
    if (!$role) {
        $admin = getCurrentAdmin();
        if ($admin && isset($admin['role'])) {
            $role = $admin['role'];
        }
    }
    
    // Check both 'super_admin' (database) and 'Super Admin' (display) formats
    return $role === 'super_admin' || $role === 'Super Admin';
}

// Alias for backward compatibility - only create if rbac.php hasn't defined it
if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin() {
        if (function_exists('isAdminSuperAdmin')) {
            return isAdminSuperAdmin();
        }
        return false;
    }
}

// Require Super Admin access
function requireSuperAdmin() {
    requireAuth();
    
    // Double-check authentication
    if (!isAdminLoggedIn()) {
        header("Location: admin-login.php");
        exit;
    }
    
    if (!isAdminSuperAdmin()) {
        error_log("Unauthorized Super Admin access attempt: User ID " . $_SESSION['admin_id'] . " (Role: " . ($_SESSION['admin_role'] ?? 'unknown') . ")");
        header("Location: unauthorized.php?module=admin_management");
        exit;
    }
}

// Auto-check permission for current page (call this at the top of admin pages)
function checkPageAccess() {
    requirePagePermission();
}

// Note: getCurrentAdminData() is defined in dashboard.php
// Use getCurrentAdmin() directly, or getCurrentAdminData() if dashboard.php is loaded

