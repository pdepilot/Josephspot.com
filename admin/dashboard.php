<?php
// admin-dashboard.php
session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'joseph_pot_admin');

// Debug mode
define('DEBUG', true);

// Get database connection
function getDBConnection()
{
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    }
    return $conn;
}

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Get admin user data
function getAdminData($admin_id)
{
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, email, created_at FROM admins WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
    }
    return null;
}

// Get login history for a user
function getLoginHistory($admin_id, $limit = 10)
{
    $conn = getDBConnection();

    // Check if login_activity table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'login_activity'");
    if ($table_check->num_rows === 0) {
        return [];
    }

    $stmt = $conn->prepare("SELECT * FROM login_activity WHERE admin_id = ? ORDER BY login_time DESC LIMIT ?");
    if ($stmt) {
        $stmt->bind_param("ii", $admin_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }

        return $history;
    }

    return [];
}

// Get dashboard statistics
function getDashboardStats($conn)
{
    $stats = [
        'today_orders' => 0,
        'total_revenue' => 0,
        'total_customers' => 0,
        'today_reservations' => 0
    ];

    // Check if tables exist and get actual data
    $tables = $conn->query("SHOW TABLES");
    $table_list = [];
    while ($table = $tables->fetch_array()) {
        $table_list[] = $table[0];
    }

    // Today's orders
    if (in_array('orders', $table_list)) {
        $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['today_orders'] = $row['count'];
        }
    } else {
        $stats['today_orders'] = rand(120, 180); // Fallback random data
    }

    // Total revenue
    if (in_array('orders', $table_list)) {
        $result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['total_revenue'] = $row['total'] ? $row['total'] : 0;
        }
    } else {
        $stats['total_revenue'] = rand(280000, 350000); // Fallback random data
    }

    // Total customers
    if (in_array('customers', $table_list)) {
        $result = $conn->query("SELECT COUNT(*) as count FROM customers");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['total_customers'] = $row['count'];
        }
    } else {
        $stats['total_customers'] = rand(2500, 3000); // Fallback random data
    }

    // Today's reservations
    if (in_array('reservations', $table_list)) {
        $result = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE DATE(reservation_date) = CURDATE()");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['today_reservations'] = $row['count'];
        }
    } else {
        $stats['today_reservations'] = rand(30, 45); // Fallback random data
    }

    return $stats;
}

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: admin-login.php");
    exit;
}

// Get admin data for display
$admin_data = getAdminData($_SESSION['admin_id']);
$username = 'Admin';
$user_initials = 'AJ';

if ($admin_data) {
    $username = $admin_data['username'];
    $user_initials = strtoupper(substr($admin_data['username'], 0, 2));
}

// Get dashboard statistics
$conn = getDBConnection();
$dashboard_stats = getDashboardStats($conn);

// Get login history
$login_history = getLoginHistory($_SESSION['admin_id'], 5);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/logo3.png">
    <title>Admin Dashboard - Joseph's Pot</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #8b4513;
            --primary-light: #a0522d;
            --primary-dark: #654321;
            --secondary: #d2691e;
            --accent: #ff7b54;
            --light: #fff8dc;
            --dark: #333333;
            --success: #4CAF50;
            --warning: #FF9800;
            --danger: #F44336;
            --info: #2196F3;
            --gray: #f5f5f5;
            --gray-dark: #e0e0e0;
            --text: #333333;
            --text-light: #666666;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Mobile Menu Toggle Button */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            width: 45px;
            height: 45px;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: var(--shadow);
            align-items: center;
            justify-content: center;
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 20px 0;
            box-shadow: var(--shadow);
            z-index: 999;
            transition: var(--transition);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transform: translateX(0);
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .logo-area {
            display: flex;
            align-items: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
            transition: var(--transition);
        }

        .logo-area img {
            height: 40px;
            margin-right: 10px;
        }

        .logo-area h1 {
            font-size: 1.5rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .admin-info {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.1);
            margin: 0 15px 20px;
            border-radius: 10px;
            transition: var(--transition);
        }

        .admin-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .admin-details h3 {
            font-size: 1rem;
            margin-bottom: 3px;
        }

        .admin-details p {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .menu-items {
            list-style: none;
            padding: 0 15px;
        }

        .menu-item {
            margin-bottom: 5px;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
        }

        .menu-item a:hover,
        .menu-item a.active {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .menu-item i {
            margin-right: 12px;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .menu-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 20px 15px 10px;
            opacity: 0.7;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 20px;
            transition: var(--transition);
            width: calc(100% - 260px);
        }

        .main-content.expanded {
            margin-left: 0;
            width: 100%;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .header h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
            width: 100%;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            width: 100%;
            justify-content: space-between;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            padding: 10px 15px 10px 40px;
            border: none;
            border-radius: 30px;
            background: white;
            box-shadow: var(--shadow);
            width: 100%;
            transition: var(--transition);
        }

        .search-box input:focus {
            outline: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        /* FIXED NOTIFICATION AND USER MENU STYLES */
        .notification-user-container {
            display: flex;
            align-items: center;
            gap: 8px; /* Reduced gap */
        }

        .notification-icon {
            position: relative;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
        }

        .notification-icon:hover {
            background: var(--gray);
        }

        .notification-icon i {
            font-size: 1.3rem;
            color: var(--primary);
            transition: var(--transition);
        }

        .notification-icon:hover i {
            color: var(--secondary);
        }

        .user-menu {
            position: relative;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
        }

        .user-menu:hover {
            background: var(--gray);
        }

        .user-menu i {
            font-size: 1.3rem;
            color: var(--primary);
            transition: var(--transition);
        }

        .user-menu:hover i {
            color: var(--secondary);
        }

        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
            pointer-events: none;
        }

        /* Notification Dropdown */
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 320px;
            max-height: 400px;
            overflow-y: auto;
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            z-index: 1000;
            display: none;
            margin-top: 5px;
        }

        .notification-dropdown.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .notification-dropdown-header {
            padding: 15px;
            border-bottom: 1px solid var(--gray-dark);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-dropdown-header h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
        }

        .notification-dropdown-header .mark-all-read {
            background: none;
            border: none;
            color: var(--info);
            cursor: pointer;
            font-size: 0.85rem;
            transition: var(--transition);
        }

        .notification-dropdown-header .mark-all-read:hover {
            color: var(--primary);
        }

        .notification-list {
            list-style: none;
        }

        .notification-item {
            padding: 12px 15px;
            border-bottom: 1px solid var(--gray);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .notification-item:hover {
            background: var(--gray);
        }

        .notification-item.unread {
            background: #f9f9f9;
        }

        .notification-dot {
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
            margin-top: 5px;
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 3px;
            color: var(--text);
        }

        .notification-message {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .notification-time {
            font-size: 0.75rem;
            color: var(--text-light);
        }

        .notification-empty {
            padding: 30px 20px;
            text-align: center;
            color: var(--text-light);
        }

        /* Real-time Clock Styles */
        .real-time-clock {
            background: white;
            border-radius: 10px;
            padding: 12px 20px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-left: 4px solid var(--primary);
            flex-wrap: wrap;
        }

        .clock-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .clock-icon {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .time-display {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
        }

        .date-display {
            font-size: 0.9rem;
            color: var(--text-light);
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }

        .stat-card.orders::before {
            background: var(--info);
        }

        .stat-card.revenue::before {
            background: var(--success);
        }

        .stat-card.customers::before {
            background: var(--warning);
        }

        .stat-card.reservations::before {
            background: var(--danger);
        }

        .stat-card i {
            font-size: 2rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .stat-card.orders i {
            color: var(--info);
        }

        .stat-card.revenue i {
            color: var(--success);
        }

        .stat-card.customers i {
            color: var(--warning);
        }

        .stat-card.reservations i {
            color: var(--danger);
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .stat-change {
            font-size: 0.8rem;
            margin-top: 10px;
            display: flex;
            align-items: center;
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--danger);
        }

        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .chart-header h3 {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .chart-actions select {
            padding: 8px 12px;
            border: 1px solid var(--gray-dark);
            border-radius: 6px;
            background: white;
            color: var(--text);
            width: 100%;
            max-width: 200px;
        }

        .chart-container {
            height: 300px;
            position: relative;
            overflow: hidden;
        }

        /* Activity Section */
        .activity-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .activity-card,
        .top-items-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .activity-card h3,
        .top-items-card h3 {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--gray);
            flex-wrap: wrap;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            flex-shrink: 0;
        }

        .activity-icon.order {
            background: var(--info);
        }

        .activity-icon.reservation {
            background: var(--danger);
        }

        .activity-icon.review {
            background: var(--warning);
        }

        .activity-icon.payment {
            background: var(--success);
        }

        .activity-details {
            flex: 1;
            min-width: 200px;
            margin-bottom: 10px;
        }

        .activity-details h4 {
            font-size: 0.95rem;
            margin-bottom: 5px;
        }

        .activity-details p {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .activity-time {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-left: auto;
        }

        /* Top Items */
        .top-items-list {
            list-style: none;
        }

        .top-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--gray);
            flex-wrap: wrap;
        }

        .top-item:last-child {
            border-bottom: none;
        }

        .item-rank {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .item-rank.rank-1 {
            background: gold;
            color: var(--dark);
        }

        .item-rank.rank-2 {
            background: silver;
            color: var(--dark);
        }

        .item-rank.rank-3 {
            background: #cd7f32;
            color: white;
        }

        .item-details {
            flex: 1;
            min-width: 150px;
            margin-bottom: 10px;
        }

        .item-details h4 {
            font-size: 0.95rem;
            margin-bottom: 3px;
        }

        .item-details p {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .item-sales {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--primary);
            margin-left: auto;
        }

        /* Admin Management Section */
        .admin-management {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-top: 30px;
        }

        .admin-management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .admin-management-header h3 {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .add-admin-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .add-admin-btn:hover {
            background: var(--primary-dark);
        }

        .admins-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .admin-card {
            background: var(--gray);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: var(--transition);
        }

        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .admin-card-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
        }

        .admin-card-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .admin-card-role {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .admin-card-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .admin-card-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .admin-card-btn.edit {
            background: var(--info);
            color: white;
        }

        .admin-card-btn.delete {
            background: var(--danger);
            color: white;
        }

        .admin-card-btn:hover {
            transform: scale(1.1);
        }

        /* Login History Section */
        .login-history {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-top: 30px;
            overflow-x: auto;
        }

        .login-history h3 {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .login-history-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .login-history-table th,
        .login-history-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray);
        }

        .login-history-table th {
            background: var(--gray);
            font-weight: 600;
        }

        .login-history-table tr:hover {
            background: var(--gray);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-success {
            background: #e8f5e8;
            color: var(--success);
        }

        .status-failed {
            background: #ffeaea;
            color: var(--danger);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--gray-dark);
            border-radius: 6px;
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: var(--gray);
            color: var(--text);
        }

        .btn-secondary:hover {
            background: var(--gray-dark);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            margin-top: 30px;
            color: var(--text-light);
            font-size: 0.9rem;
            border-top: 1px solid var(--gray-dark);
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Scroll Reveal Animation Styles */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        .reveal-delay-1 {
            transition-delay: 0.1s;
        }

        .reveal-delay-2 {
            transition-delay: 0.2s;
        }

        .reveal-delay-3 {
            transition-delay: 0.3s;
        }

        .reveal-delay-4 {
            transition-delay: 0.4s;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
            
            .activity-section {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .mobile-menu-toggle {
                display: flex;
            }
            
            .sidebar-overlay.active {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding-top: 70px;
            }
            
            .header h2 {
                font-size: 1.5rem;
            }
            
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .notification-dropdown {
                position: fixed;
                top: 70px;
                right: 15px;
                left: 15px;
                width: auto;
                max-height: 60vh;
            }
        }

        @media (max-width: 768px) {
            .header-actions {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .search-box {
                max-width: 100%;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .real-time-clock {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .clock-container {
                margin-bottom: 15px;
            }
            
            .activity-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .activity-details {
                margin-bottom: 10px;
            }
            
            .activity-time {
                margin-left: 0;
            }
            
            .top-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .item-details {
                margin-bottom: 10px;
            }
            
            .item-sales {
                margin-left: 0;
            }
            
            .admins-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .notification-user-container {
                align-self: flex-end;
                margin-left: auto;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .chart-container {
                height: 250px;
            }
            
            .admin-management-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .add-admin-btn {
                width: 100%;
                justify-content: center;
            }
            
            .admins-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                padding: 20px 15px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .notification-dropdown {
                width: calc(100% - 30px);
                left: 15px;
                right: 15px;
            }
        }

        @media (max-width: 480px) {
            .logo-area h1 {
                font-size: 1.2rem;
            }
            
            .header h2 {
                font-size: 1.3rem;
            }
            
            .stat-value {
                font-size: 1.5rem;
            }
            
            .chart-header h3,
            .activity-card h3,
            .top-items-card h3,
            .login-history h3,
            .admin-management-header h3 {
                font-size: 1.1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Mobile Menu Toggle Button -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Overlay for mobile sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="logo-area">
                <img src="../images/logo3.png" alt="Joseph's Pot Logo">
                <h1>Admin Panel</h1>
            </div>

            <div class="admin-info">
                <div class="admin-avatar"><?php echo $user_initials; ?></div>
                <div class="admin-details">
                    <h3><?php echo htmlspecialchars($username); ?></h3>
                    <p>Super Admin</p>
                </div>
            </div>

            <ul class="menu-items">
                <li class="menu-label">Main</li>
                <li class="menu-item">
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-contact-messages.php">
                        <i class="fas fa-envelope"></i>
                        <span>Contact Messages</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-menu-management.php">
                        <i class="fas fa-utensils"></i>
                        <span>Menu Management</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-reservation.php">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Reservations</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-orders.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-order-online-menu.php">
                        <i class="fas fa-car"></i>
                        <span>Order-Online Menu</span>
                    </a>
                </li>

                <li class="menu-label">Content</li>
                <!-- <li class="menu-item">
                    <a href="admin-customers.php">
                        <i class="fas fa-users"></i>
                        <span>Customers</span>
                    </a>
                </li> -->
                <li class="menu-item">
                    <a href="admin-reviews.php">
                        <i class="fas fa-star"></i>
                        <span>Reviews</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-events.php">
                        <i class="fa-solid fa-calendar"></i>
                        <span>Events</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-gallery.php">
                        <i class="fas fa-image"></i>
                        <span>Gallery</span>
                    </a>
                </li>

                <li class="menu-label">Settings</li>
                <li class="menu-item">
                    <a href="admin-settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="./admin-logout.php" onclick="return confirmLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Real-time Clock -->
            <div class="real-time-clock reveal">
                <div class="clock-container">
                    <i class="fas fa-clock clock-icon"></i>
                    <div>
                        <div class="time-display" id="currentTime">Loading...</div>
                        <div class="date-display" id="currentDate">Loading...</div>
                    </div>
                </div>
                <div class="location-info">
                    <i class="fas fa-map-marker-alt"></i> Owerri, Nigeria
                </div>
            </div>

            <div class="header">
                <h2>Dashboard Overview</h2>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search...">
                    </div>
                    <div class="notification-user-container">
                        <div class="notification-icon" id="notificationIcon">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">5</span>
                            <div class="notification-dropdown" id="notificationDropdown">
                                <div class="notification-dropdown-header">
                                    <h4>Notifications</h4>
                                    <button class="mark-all-read" id="markAllRead">Mark all as read</button>
                                </div>
                                <ul class="notification-list" id="notificationList">
                                    <!-- Notifications will be loaded here -->
                                </ul>
                            </div>
                        </div>
                        <div class="user-menu">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card orders reveal">
                    <i class="fas fa-shopping-bag"></i>
                    <div class="stat-value"><?php echo $dashboard_stats['today_orders']; ?></div>
                    <div class="stat-label">Today's Orders</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 12% from yesterday
                    </div>
                </div>

                <div class="stat-card revenue reveal reveal-delay-1">
                    <i class="fa-solid fa-naira-sign"></i>
                    <div class="stat-value">₦<?php echo number_format($dashboard_stats['total_revenue']); ?></div>
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 8% from last week
                    </div>
                </div>

                <div class="stat-card customers reveal reveal-delay-2">
                    <i class="fas fa-users"></i>
                    <div class="stat-value"><?php echo $dashboard_stats['total_customers']; ?></div>
                    <div class="stat-label">Total Customers</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 5% from last month
                    </div>
                </div>

                <div class="stat-card reservations reveal reveal-delay-3">
                    <i class="fas fa-calendar-check"></i>
                    <div class="stat-value"><?php echo $dashboard_stats['today_reservations']; ?></div>
                    <div class="stat-label">Today's Reservations</div>
                    <div class="stat-change negative">
                        <i class="fas fa-arrow-down"></i> 3% from yesterday
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <div class="chart-card reveal">
                    <div class="chart-header">
                        <h3>Revenue Overview</h3>
                        <div class="chart-actions">
                            <select>
                                <option>Last 7 Days</option>
                                <option>Last 30 Days</option>
                                <option>Last 3 Months</option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-container">
                        <!-- Chart would be rendered here with a library like Chart.js -->
                        <div style="display: flex; align-items: flex-end; height: 100%; gap: 10px; padding: 20px 0;">
                            <div style="flex: 1; background: linear-gradient(to top, var(--info), #a8d8ff); height: 40%; border-radius: 5px;"></div>
                            <div style="flex: 1; background: linear-gradient(to top, var(--info), #a8d8ff); height: 60%; border-radius: 5px;"></div>
                            <div style="flex: 1; background: linear-gradient(to top, var(--info), #a8d8ff); height: 80%; border-radius: 5px;"></div>
                            <div style="flex: 1; background: linear-gradient(to top, var(--success), #a8f0b0); height: 100%; border-radius: 5px;"></div>
                            <div style="flex: 1; background: linear-gradient(to top, var(--success), #a8f0b0); height: 70%; border-radius: 5px;"></div>
                            <div style="flex: 1; background: linear-gradient(to top, var(--success), #a8f0b0); height: 90%; border-radius: 5px;"></div>
                            <div style="flex: 1; background: linear-gradient(to top, var(--warning), #ffd8a8); height: 50%; border-radius: 5px;"></div>
                        </div>
                    </div>
                </div>

                <div class="chart-card reveal reveal-delay-1">
                    <div class="chart-header">
                        <h3>Order Status</h3>
                    </div>
                    <div class="chart-container">
                        <!-- Pie chart would be rendered here -->
                        <div style="display: flex; justify-content: center; align-items: center; height: 100%;">
                            <div style="width: 200px; height: 200px; border-radius: 50%; background: conic-gradient(var(--success) 0% 65%, var(--warning) 65% 85%, var(--danger) 85% 100%);"></div>
                        </div>
                        <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center;">
                                <div style="width: 12px; height: 12px; background: var(--success); border-radius: 50%; margin-right: 5px;"></div>
                                <span>Completed (65%)</span>
                            </div>
                            <div style="display: flex; align-items: center;">
                                <div style="width: 12px; height: 12px; background: var(--warning); border-radius: 50%; margin-right: 5px;"></div>
                                <span>Pending (20%)</span>
                            </div>
                            <div style="display: flex; align-items: center;">
                                <div style="width: 12px; height: 12px; background: var(--danger); border-radius: 50%; margin-right: 5px;"></div>
                                <span>Cancelled (15%)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Section -->
            <div class="activity-section">
                <div class="activity-card reveal">
                    <h3>Recent Activity</h3>
                    <ul class="activity-list">
                        <?php
                        // Try to get reservations from database
                        $reservations = [];
                        try {
                            $result = $conn->query("SELECT name, guests, created_at, status FROM reservations ORDER BY created_at DESC LIMIT 5");
                            if ($result) {
                                while ($row = $result->fetch_assoc()) {
                                    $reservations[] = $row;
                                }
                            }
                        } catch (Exception $e) {
                            // If error, use empty array
                        }

                        if (!empty($reservations)):
                            foreach ($reservations as $reservation):
                                $time_ago = '';
                                $now = time();
                                $activity_time = strtotime($reservation['created_at']);
                                $time_diff = $now - $activity_time;

                                if ($time_diff < 60) {
                                    $time_ago = 'Just now';
                                } elseif ($time_diff < 3600) {
                                    $minutes = floor($time_diff / 60);
                                    $time_ago = $minutes . ' min ago';
                                } elseif ($time_diff < 86400) {
                                    $hours = floor($time_diff / 3600);
                                    $time_ago = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
                                } else {
                                    $days = floor($time_diff / 86400);
                                    $time_ago = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
                                }
                        ?>
                                <li class="activity-item">
                                    <div class="activity-icon reservation">
                                        <i class="fas fa-calendar-plus"></i>
                                    </div>
                                    <div class="activity-details">
                                        <h4>Table Reservation</h4>
                                        <p><?php echo htmlspecialchars($reservation['name']); ?> reserved a table for <?php echo $reservation['guests']; ?> people</p>
                                    </div>
                                    <div class="activity-time"><?php echo $time_ago; ?></div>
                                </li>
                            <?php endforeach;
                        else:
                            // Fallback to static data if no reservations
                            ?>
                            <li class="activity-item">
                                <div class="activity-icon order">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="activity-details">
                                    <h4>New Order Received</h4>
                                    <p>Order #JP-2847 for 2 people</p>
                                </div>
                                <div class="activity-time">10 min ago</div>
                            </li>
                            <li class="activity-item">
                                <div class="activity-icon reservation">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <div class="activity-details">
                                    <h4>Table Reservation</h4>
                                    <p>John Smith reserved a table for 4</p>
                                </div>
                                <div class="activity-time">25 min ago</div>
                            </li>
                            <li class="activity-item">
                                <div class="activity-icon review">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="activity-details">
                                    <h4>New Review Posted</h4>
                                    <p>Sarah Johnson rated 5 stars</p>
                                </div>
                                <div class="activity-time">1 hour ago</div>
                            </li>
                            <li class="activity-item">
                                <div class="activity-icon payment">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div class="activity-details">
                                    <h4>Payment Received</h4>
                                    <p>₦12,500 for Order #JP-2841</p>
                                </div>
                                <div class="activity-time">2 hours ago</div>
                            </li>
                            <li class="activity-item">
                                <div class="activity-icon order">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="activity-details">
                                    <h4>Order Completed</h4>
                                    <p>Order #JP-2839 marked as delivered</p>
                                </div>
                                <div class="activity-time">3 hours ago</div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="top-items-card reveal reveal-delay-1">
                    <h3>Top Menu Items</h3>
                    <ul class="top-items-list">
                        <li class="top-item">
                            <div class="item-rank rank-1">1</div>
                            <div class="item-details">
                                <h4>Ofe Owerri Special</h4>
                                <p>Traditional Igbo soup</p>
                            </div>
                            <div class="item-sales">142 sales</div>
                        </li>
                        <li class="top-item">
                            <div class="item-rank rank-2">2</div>
                            <div class="item-details">
                                <h4>Nkwobi</h4>
                                <p>Spicy cow foot</p>
                            </div>
                            <div class="item-sales">128 sales</div>
                        </li>
                        <li class="top-item">
                            <div class="item-rank rank-3">3</div>
                            <div class="item-details">
                                <h4>Egusi Delight</h4>
                                <p>Melon seed soup</p>
                            </div>
                            <div class="item-sales">115 sales</div>
                        </li>
                        <li class="top-item">
                            <div class="item-rank">4</div>
                            <div class="item-details">
                                <h4>Palm Wine</h4>
                                <p>Traditional drink</p>
                            </div>
                            <div class="item-sales">98 sales</div>
                        </li>
                        <li class="top-item">
                            <div class="item-rank">5</div>
                            <div class="item-details">
                                <h4>Jollof Rice</h4>
                                <p>Party special</p>
                            </div>
                            <div class="item-sales">87 sales</div>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- Login History Section -->
            <div class="login-history reveal">
                <h3>Recent Login Activity</h3>
                <table class="login-history-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>IP Address</th>
                            <th>Location</th>
                            <th>Device</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($login_history)): ?>
                            <?php foreach ($login_history as $login): ?>
                                <tr>
                                    <td><?php echo date('M j, Y g:i A', strtotime($login['login_time'])); ?></td>
                                    <td><?php echo htmlspecialchars($login['ip_address']); ?></td>
                                    <td><?php echo htmlspecialchars($login['city'] . ', ' . $login['country']); ?></td>
                                    <td><?php echo htmlspecialchars($login['device_type'] . ' - ' . $login['browser']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $login['status'] === 'success' ? 'status-success' : 'status-failed'; ?>">
                                            <?php echo ucfirst($login['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No login history found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Admin Management Section -->
            <div class="admin-management reveal">
                <div class="admin-management-header">
                    <h3>Admin Management</h3>
                    <button class="add-admin-btn" id="addAdminBtn">
                        <i class="fas fa-plus"></i>
                        Add New Admin
                    </button>
                </div>
                <div class="admins-grid" id="adminsGrid">
                    <!-- Admin cards will be dynamically added here -->
                </div>
            </div>

            <div class="footer">
                <p>&copy; 2025 Joseph's Pot Admin Dashboard. All rights reserved | Developed By ERIBS tech</p>
            </div>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal" id="addAdminModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Admin</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <form id="adminForm">
                <div class="form-group">
                    <label for="adminName">Full Name</label>
                    <input type="text" id="adminName" required>
                </div>
                <div class="form-group">
                    <label for="adminEmail">Email Address</label>
                    <input type="email" id="adminEmail" required>
                </div>
                <div class="form-group">
                    <label for="adminRole">Role</label>
                    <select id="adminRole" required>
                        <option value="">Select Role</option>
                        <option value="Super Admin">Super Admin</option>
                        <option value="Manager">Manager</option>
                        <option value="Content Manager">Content Manager</option>
                        <option value="Support">Support</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="adminPermissions">Permissions</label>
                    <select id="adminPermissions" required>
                        <option value="">Select Permissions</option>
                        <option value="Full Access">Full Access</option>
                        <option value="Limited Access">Limited Access</option>
                        <option value="View Only">View Only</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Admin</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Logout confirmation function
        function confirmLogout() {
            return confirm('Are you sure you want to logout?');
        }

        // Real-time Clock Functionality
        function updateClock() {
            const now = new Date();

            // Format time
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';

            // Convert to 12-hour format
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'

            // Add leading zeros
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;

            // Format date
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const dateString = now.toLocaleDateString('en-US', options);

            // Update the DOM
            document.getElementById('currentTime').textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
            document.getElementById('currentDate').textContent = dateString;
        }

        // Update the clock immediately and then every second
        updateClock();
        setInterval(updateClock, 1000);

        // Sample admin data
        const admins = [{
                id: 1,
                name: 'Admin Joseph',
                role: 'Super Admin',
                avatar: 'AJ'
            },
            {
                id: 2,
                name: 'Manager David',
                role: 'Manager',
                avatar: 'MD'
            },
            {
                id: 3,
                name: 'Content Sarah',
                role: 'Content Manager',
                avatar: 'CS'
            },
            {
                id: 4,
                name: 'Support Mike',
                role: 'Support',
                avatar: 'SM'
            }
        ];

        // Sample notification data
        const notifications = [
            {
                id: 1,
                title: 'New Order',
                message: 'Order #JP-2848 has been placed',
                time: '2 minutes ago',
                unread: true
            },
            {
                id: 2,
                title: 'Reservation Confirmed',
                message: 'Table reservation for 4 people confirmed',
                time: '15 minutes ago',
                unread: true
            },
            {
                id: 3,
                title: 'Payment Received',
                message: '₦8,500 payment confirmed for Order #JP-2845',
                time: '1 hour ago',
                unread: false
            },
            {
                id: 4,
                title: 'New Review',
                message: 'Customer left a 5-star review',
                time: '3 hours ago',
                unread: false
            },
            {
                id: 5,
                title: 'System Update',
                message: 'Dashboard has been updated to version 2.1',
                time: '1 day ago',
                unread: false
            }
        ];

        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const addAdminBtn = document.getElementById('addAdminBtn');
        const addAdminModal = document.getElementById('addAdminModal');
        const closeModal = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const adminForm = document.getElementById('adminForm');
        const adminsGrid = document.getElementById('adminsGrid');
        const notificationIcon = document.getElementById('notificationIcon');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationList = document.getElementById('notificationList');
        const markAllReadBtn = document.getElementById('markAllRead');
        const notificationBadge = document.querySelector('.notification-badge');

        // Mobile sidebar toggler functionality
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });

        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });

        // Close sidebar when clicking on a menu item on mobile
        const menuItems = document.querySelectorAll('.menu-item a');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 992) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            }
        });

        // Modal functionality
        addAdminBtn.addEventListener('click', function() {
            addAdminModal.style.display = 'flex';
        });

        closeModal.addEventListener('click', function() {
            addAdminModal.style.display = 'none';
        });

        cancelBtn.addEventListener('click', function() {
            addAdminModal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === addAdminModal) {
                addAdminModal.style.display = 'none';
            }
        });

        // Handle form submission
        adminForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const name = document.getElementById('adminName').value;
            const role = document.getElementById('adminRole').value;

            // Generate avatar initials
            const avatar = name.split(' ').map(n => n[0]).join('').toUpperCase();

            // Create new admin object
            const newAdmin = {
                id: admins.length + 1,
                name: name,
                role: role,
                avatar: avatar
            };

            // Add to admins array
            admins.push(newAdmin);

            // Update UI
            renderAdmins();

            // Close modal and reset form
            addAdminModal.style.display = 'none';
            adminForm.reset();

            // Show success message
            alert(`Admin ${name} added successfully!`);
        });

        // Render admins in the grid
        function renderAdmins() {
            adminsGrid.innerHTML = '';

            admins.forEach(admin => {
                const adminCard = document.createElement('div');
                adminCard.className = 'admin-card';
                adminCard.innerHTML = `
                    <div class="admin-card-avatar">${admin.avatar}</div>
                    <div class="admin-card-name">${admin.name}</div>
                    <div class="admin-card-role">${admin.role}</div>
                    <div class="admin-card-actions">
                        <button class="admin-card-btn edit" title="Edit Admin">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="admin-card-btn delete" title="Delete Admin">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;

                adminsGrid.appendChild(adminCard);
            });
        }

        // Notification functionality
        function renderNotifications() {
            notificationList.innerHTML = '';
            
            if (notifications.length === 0) {
                notificationList.innerHTML = '<div class="notification-empty">No notifications</div>';
                return;
            }
            
            notifications.forEach(notification => {
                const notificationItem = document.createElement('li');
                notificationItem.className = `notification-item ${notification.unread ? 'unread' : ''}`;
                notificationItem.dataset.id = notification.id;
                notificationItem.innerHTML = `
                    <div class="notification-dot" style="${notification.unread ? 'background: var(--primary)' : 'background: transparent'}"></div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-message">${notification.message}</div>
                        <div class="notification-time">${notification.time}</div>
                    </div>
                `;
                
                notificationItem.addEventListener('click', function() {
                    markAsRead(notification.id);
                });
                
                notificationList.appendChild(notificationItem);
            });
            
            // Update badge count
            updateNotificationBadge();
        }

        function updateNotificationBadge() {
            const unreadCount = notifications.filter(n => n.unread).length;
            if (notificationBadge) {
                notificationBadge.textContent = unreadCount;
                notificationBadge.style.display = unreadCount > 0 ? 'flex' : 'none';
            }
        }

        function markAsRead(notificationId) {
            const notification = notifications.find(n => n.id === notificationId);
            if (notification && notification.unread) {
                notification.unread = false;
                renderNotifications();
            }
        }

        function markAllAsRead() {
            notifications.forEach(notification => {
                notification.unread = false;
            });
            renderNotifications();
        }

        // Toggle notification dropdown
        notificationIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationIcon.contains(e.target) && !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.remove('active');
            }
        });

        // Mark all as read button
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                markAllAsRead();
            });
        }

        // Scroll Reveal Functionality
        function revealOnScroll() {
            const reveals = document.querySelectorAll('.reveal');

            for (let i = 0; i < reveals.length; i++) {
                const windowHeight = window.innerHeight;
                const elementTop = reveals[i].getBoundingClientRect().top;
                const elementVisible = 150;

                if (elementTop < windowHeight - elementVisible) {
                    reveals[i].classList.add('active');
                } else {
                    reveals[i].classList.remove('active');
                }
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Simple animation for stats cards on load
            const statCards = document.querySelectorAll('.stat-card');

            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Set initial state for animation
            statCards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            });

            // Initialize admin cards
            renderAdmins();
            
            // Initialize notifications
            renderNotifications();

            // Initialize scroll reveal
            window.addEventListener('scroll', revealOnScroll);
            // Trigger once on load to check initial position
            revealOnScroll();
        });
    </script>
</body>
</html>