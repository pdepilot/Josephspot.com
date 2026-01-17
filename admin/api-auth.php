<?php
/**
 * API Authentication and Permission System
 * Include this file at the top of API endpoints
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include main auth file
require_once __DIR__ . '/admin-auth.php';

// Check API authentication and permission
function requireAPIAuth($module = null, $permission = 'view') {
    // Check authentication
    if (!isAdminLoggedIn()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized - Please login']);
        exit;
    }
    
    // Verify admin exists
    $admin = getCurrentAdmin();
    if (!$admin) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Account not found or inactive']);
        exit;
    }
    
    // If module provided, check permission
    if ($module && !hasAdminPermission($module, $permission)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Access denied - You do not have permission for this action',
            'module' => $module,
            'permission' => $permission
        ]);
        exit;
    }
}

// Helper for API endpoints - checks permission for specific module
function requireAPIPermission($module, $permission = 'view') {
    requireAPIAuth($module, $permission);
}

