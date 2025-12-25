<?php
/**
 * RBAC Helper Template
 * Copy this code pattern to the top of your admin pages
 */

/*
// 1. Start session (if not already started)
session_start();

// 2. Include database configuration (if not already included)
// This should define DB_HOST, DB_USER, DB_PASS, DB_NAME
require_once '../config/db_config.php'; // Adjust path as needed

// 3. Include RBAC functions
require_once 'includes/rbac.php'; // Adjust path as needed

// 4. Check if user is logged in
if (!isLoggedIn()) {
    header("Location: admin-login.php");
    exit;
}

// 5. Check permission for the specific page/module
// Example: For admin-orders.php, use:
requirePermission($_SESSION['admin_id'], 'orders', 'view');

// 6. Optional: Check for specific actions within the page
// Example: Before deleting an order:
if (!hasPermission($_SESSION['admin_id'], 'orders', 'delete')) {
    die(json_encode(['success' => false, 'message' => 'Permission denied']));
}
*/

/**
 * Permission mapping for admin pages:
 * 
 * dashboard.php          -> requirePermission($admin_id, 'dashboard', 'view')
 * admin-orders.php       -> requirePermission($admin_id, 'orders', 'view')
 * admin-reservation.php  -> requirePermission($admin_id, 'reservations', 'view')
 * admin-menu-management.php -> requirePermission($admin_id, 'menu', 'view')
 * admin-customers.php    -> requirePermission($admin_id, 'customers', 'view')
 * admin-reviews.php      -> requirePermission($admin_id, 'reviews', 'view')
 * admin-gallery.php      -> requirePermission($admin_id, 'gallery', 'view')
 * admin-events.php       -> requirePermission($admin_id, 'events', 'view')
 * admin-contact-messages.php -> requirePermission($admin_id, 'messages', 'view')
 * admin-settings.php     -> requirePermission($admin_id, 'settings', 'view')
 * 
 * For API endpoints, check permissions before executing actions:
 * - Creating: hasPermission($admin_id, 'module', 'create')
 * - Editing: hasPermission($admin_id, 'module', 'edit')
 * - Deleting: hasPermission($admin_id, 'module', 'delete')
 * - Exporting: hasPermission($admin_id, 'module', 'export')
 */

?>

