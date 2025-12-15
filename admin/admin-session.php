<?php
// admin-session.php - Shared session configuration for all admin pages

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define session timeout (1 hour)
define('SESSION_TIMEOUT', 3600);

// Check if user is logged in
function isAdminLoggedIn() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        return false;
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        // Session expired
        session_unset();
        session_destroy();
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    return true;
}

// Require login - use this at the top of admin pages
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        // Store the requested URL for redirect after login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: admin-login.php");
        exit();
    }
}

// Get admin info safely
function getAdminInfo() {
    return [
        'name' => $_SESSION['admin_name'] ?? 'Administrator',
        'email' => $_SESSION['admin_email'] ?? '',
        'role' => $_SESSION['admin_role'] ?? 'Admin',
        'avatar' => $_SESSION['admin_avatar'] ?? substr($_SESSION['admin_name'] ?? 'A', 0, 2)
    ];
}
?>