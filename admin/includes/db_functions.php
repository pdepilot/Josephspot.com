<?php
// admin/includes/db_functions.php
require_once 'db_connection.php';

function getOrderStats($pdo) {
    $stats = [];
    
    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $stats['total_orders'] = $stmt->fetch()['total'];
    
    // Total revenue (completed orders only)
    $stmt = $pdo->query("SELECT SUM(total_amount) as revenue FROM orders WHERE order_status = 'completed'");
    $stats['total_revenue'] = $stmt->fetch()['revenue'] ?? 0;
    
    // Total customers (distinct emails)
    $stmt = $pdo->query("SELECT COUNT(DISTINCT customer_email) as customers FROM orders");
    $stats['total_customers'] = $stmt->fetch()['customers'];
    
    // Pending orders
    $stmt = $pdo->query("SELECT COUNT(*) as pending FROM orders WHERE order_status = 'pending'");
    $stats['pending_orders'] = $stmt->fetch()['pending'];
    
    return $stats;
}

function formatCurrency($amount) {
    return '₦' . number_format($amount, 2);
}

function getPaymentMethodText($method) {
    $methods = [
        'cod' => 'Cash on Delivery',
        'bank' => 'Bank Transfer',
        'paystack' => 'Paystack',
        'flutterwave' => 'Flutterwave'
    ];
    
    return $methods[$method] ?? ucfirst($method);
}

function getTimeAgo($dateString) {
    $date = new DateTime($dateString);
    $now = new DateTime();
    $interval = $date->diff($now);
    
    if ($interval->y > 0) return $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
    if ($interval->m > 0) return $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
    if ($interval->d > 0) return $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
    if ($interval->h > 0) return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
    if ($interval->i > 0) return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

function getStatusClass($status) {
    switch($status) {
        case 'pending': return 'status-pending';
        case 'processing': return 'status-processing';
        case 'completed': return 'status-completed';
        case 'cancelled': return 'status-cancelled';
        default: return '';
    }
}
?>