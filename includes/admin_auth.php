<?php
// includes/admin_auth.php
// Admin authentication functions

function checkAdminLogin() {
    session_start();
    
    // If not logged in, redirect to login page
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: admin-login.php');
        exit();
    }
}

function adminLogout() {
    session_start();
    
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header('Location: admin-login.php');
    exit();
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    // Remove all non-numeric characters except + sign
    $phone = preg_replace('/[^\d\+]/', '', $phone);
    return !empty($phone) && strlen($phone) >= 10;
}
?>