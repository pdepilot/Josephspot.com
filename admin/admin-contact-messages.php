<?php
// admin-contact-messages.php - COMPLETE WORKING VERSION WITH CALL & EMAIL FUNCTIONALITY
// Central authentication and permission check
require_once 'admin-auth.php';
checkPageAccess(); // This checks authentication and permission for current page

// Database configuration - Use the same as your dashboard
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'joseph_pot_admin';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Authentication and permission checking now handled by admin-auth.php

// Get current admin info
$admin_id = $_SESSION['admin_id'];
$username = 'Admin';
$user_initials = 'AJ';

// Fetch admin data from database
$stmt = $conn->prepare("SELECT id, username, email, created_at FROM admins WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $admin_data = $result->fetch_assoc();
        $username = $admin_data['username'];
        $user_initials = strtoupper(substr($admin_data['username'], 0, 2));
    }
}

// Initialize variables
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$countryFilter = isset($_GET['country']) ? $_GET['country'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build SQL query with filters
$whereConditions = [];
$params = [];
$types = '';

if ($statusFilter != 'all') {
    $whereConditions[] = "status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if (!empty($dateFrom)) {
    $whereConditions[] = "DATE(created_at) >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}

if (!empty($dateTo)) {
    $whereConditions[] = "DATE(created_at) <= ?";
    $params[] = $dateTo;
    $types .= 's';
}

if (!empty($searchQuery)) {
    $whereConditions[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $searchTerm = "%$searchQuery%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= str_repeat('s', 5);
}

if ($countryFilter != 'all') {
    $whereConditions[] = "country = ?";
    $params[] = $countryFilter;
    $types .= 's';
}

$whereSQL = '';
if (!empty($whereConditions)) {
    $whereSQL = "WHERE " . implode(" AND ", $whereConditions);
}

// Create contact_messages table if it doesn't exist
$createTableSQL = "CREATE TABLE IF NOT EXISTS contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'unread',
    ip_address VARCHAR(45),
    country VARCHAR(100),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$conn->query($createTableSQL);

// Insert sample data if table is empty
$checkCount = $conn->query("SELECT COUNT(*) as count FROM contact_messages");
$row = $checkCount->fetch_assoc();
if ($row['count'] == 0) {
    // Insert sample data
    $sample_messages = [
        ["John Doe", "john@example.com", "08012345678", "Table Reservation", "I would like to reserve a table for 4 people this Friday at 7 PM.", "Nigeria"],
        ["Jane Smith", "jane@example.com", "08087654321", "Catering Inquiry", "Looking for catering services for a wedding of 200 guests.", "Nigeria"],
        ["Robert Johnson", "robert@example.com", NULL, "Menu Feedback", "The Ofe Owerri Special was amazing! Best I've ever had.", "USA"],
        ["Sarah Williams", "sarah@example.com", "08055551234", "Private Event", "Interested in booking the restaurant for a private birthday party.", "UK"],
        ["Michael Brown", "michael@example.com", "08099998888", "Job Application", "I'm interested in applying for a chef position at your restaurant.", "Nigeria"],
        ["Emily Davis", "emily@example.com", NULL, "Special Diet", "Do you have gluten-free options on your menu?", "Canada"],
        ["David Wilson", "david@example.com", "08044443333", "Partnership", "Would like to discuss a potential partnership with your restaurant.", "Nigeria"],
        ["Lisa Anderson", "lisa@example.com", "08077776666", "Compliment", "Excellent service and food quality! Highly recommended.", "Ghana"],
        ["James Taylor", "james@example.com", NULL, "Operating Hours", "What are your Sunday opening hours?", "USA"],
        ["Mary Thomas", "mary@example.com", "08022221111", "Takeaway Order", "Can I place a bulk order for takeaway?", "Nigeria"]
    ];
    
    $insert_stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, country) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($sample_messages as $msg) {
        $insert_stmt->bind_param("ssssss", $msg[0], $msg[1], $msg[2], $msg[3], $msg[4], $msg[5]);
        $insert_stmt->execute();
    }
    $insert_stmt->close();
}

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM contact_messages $whereSQL";
if (!empty($params)) {
    $countStmt = $conn->prepare($countQuery);
    if ($countStmt) {
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRows = $countResult->fetch_assoc()['total'];
        $countStmt->close();
    } else {
        $totalRows = 0;
    }
} else {
    $countResult = $conn->query($countQuery);
    $totalRows = $countResult->fetch_assoc()['total'];
}

$totalPages = ceil($totalRows / $limit);

// Get messages with pagination
$messages = [];
$original_params = $params;
$original_types = $types;

if (!empty($whereSQL)) {
    $query = "SELECT * FROM contact_messages $whereSQL ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        $stmt->close();
    }
} else {
    $query = "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

// Get statistics
$stats = [];

// Total messages
$totalQuery = "SELECT COUNT(*) as count FROM contact_messages";
$totalResult = $conn->query($totalQuery);
$stats['total'] = $totalResult->fetch_assoc()['count'];

// Unread messages
$unreadQuery = "SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'";
$unreadResult = $conn->query($unreadQuery);
$stats['unread'] = $unreadResult->fetch_assoc()['count'];

// Today's messages
$todayQuery = "SELECT COUNT(*) as count FROM contact_messages WHERE DATE(created_at) = CURDATE()";
$todayResult = $conn->query($todayQuery);
$stats['today'] = $todayResult->fetch_assoc()['count'];

// Messages with phone numbers
$phoneQuery = "SELECT COUNT(*) as count FROM contact_messages WHERE phone IS NOT NULL AND phone != ''";
$phoneResult = $conn->query($phoneQuery);
$stats['phone'] = $phoneResult->fetch_assoc()['count'];

// Yesterday's messages for comparison
$yesterdayQuery = "SELECT COUNT(*) as count FROM contact_messages WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
$yesterdayResult = $conn->query($yesterdayQuery);
$yesterdayCount = $yesterdayResult->fetch_assoc()['count'];
$stats['today_change'] = $yesterdayCount > 0 ? round((($stats['today'] - $yesterdayCount) / $yesterdayCount) * 100) : 0;

// Last month's messages for comparison
$lastMonthQuery = "SELECT COUNT(*) as count FROM contact_messages WHERE YEAR(created_at) = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH(created_at) = MONTH(CURDATE() - INTERVAL 1 MONTH)";
$lastMonthResult = $conn->query($lastMonthQuery);
$lastMonthCount = $lastMonthResult->fetch_assoc()['count'];
$currentMonthQuery = "SELECT COUNT(*) as count FROM contact_messages WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
$currentMonthResult = $conn->query($currentMonthQuery);
$currentMonthCount = $currentMonthResult->fetch_assoc()['count'];
$stats['month_change'] = $lastMonthCount > 0 ? round((($currentMonthCount - $lastMonthCount) / $lastMonthCount) * 100) : 0;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'mark_read' && isset($_POST['message_ids'])) {
            $ids = json_decode($_POST['message_ids']);
            if (is_array($ids) && !empty($ids)) {
                $ids = array_map('intval', $ids);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $updateQuery = "UPDATE contact_messages SET status = 'read' WHERE id IN ($placeholders)";
                $updateStmt = $conn->prepare($updateQuery);
                $types = str_repeat('i', count($ids));
                $updateStmt->bind_param($types, ...$ids);
                $updateStmt->execute();
                $_SESSION['notification'] = ['type' => 'success', 'message' => 'Messages marked as read successfully!'];
            }
        }
        elseif ($action === 'archive' && isset($_POST['message_ids'])) {
            $ids = json_decode($_POST['message_ids']);
            if (is_array($ids) && !empty($ids)) {
                $ids = array_map('intval', $ids);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $updateQuery = "UPDATE contact_messages SET status = 'archived' WHERE id IN ($placeholders)";
                $updateStmt = $conn->prepare($updateQuery);
                $types = str_repeat('i', count($ids));
                $updateStmt->bind_param($types, ...$ids);
                $updateStmt->execute();
                $_SESSION['notification'] = ['type' => 'success', 'message' => 'Messages archived successfully!'];
            }
        }
        elseif ($action === 'delete' && isset($_POST['message_ids'])) {
            $ids = json_decode($_POST['message_ids']);
            if (is_array($ids) && !empty($ids)) {
                $ids = array_map('intval', $ids);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $deleteQuery = "DELETE FROM contact_messages WHERE id IN ($placeholders)";
                $deleteStmt = $conn->prepare($deleteQuery);
                $types = str_repeat('i', count($ids));
                $deleteStmt->bind_param($types, ...$ids);
                $deleteStmt->execute();
                $_SESSION['notification'] = ['type' => 'success', 'message' => 'Messages deleted successfully!'];
            }
        }
        elseif ($action === 'update_status' && isset($_POST['message_id']) && isset($_POST['status'])) {
            $id = (int)$_POST['message_id'];
            $status = $_POST['status'];
            $updateQuery = "UPDATE contact_messages SET status = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param('si', $status, $id);
            $updateStmt->execute();
            echo json_encode(['success' => true]);
            exit;
        }
        
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
        exit;
    }
}

// Get notification if exists
$notification = null;
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    unset($_SESSION['notification']);
}

// Function to get avatar color based on name
function getAvatarColor($name) {
    $colors = ['#2196F3', '#FF9800', '#4CAF50', '#9C27B0', '#F44336', '#00BCD4', '#FF5722', '#795548'];
    $hash = crc32($name);
    return $colors[$hash % count($colors)];
}

// Function to get initials
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return substr($initials, 0, 2);
}

// Function to format date
function formatDate($date) {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    $dateObj = new DateTime($date);
    $dateOnly = $dateObj->format('Y-m-d');
    
    if ($dateOnly === $today) {
        return 'Today';
    } elseif ($dateOnly === $yesterday) {
        return 'Yesterday';
    } else {
        return $dateObj->format('M j');
    }
}

// Function to format time
function formatTime($date) {
    $dateObj = new DateTime($date);
    return $dateObj->format('g:i A');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Contact Messages - Joseph's Pot Admin</title>
    <link rel="icon" href="../images/logo3.png">
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

        .sidebar-overlay.active {
            display: block;
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
        }

        .logo-area img {
            height: 40px;
            margin-right: 10px;
        }

        .logo-area h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .admin-info {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.1);
            margin: 0 15px 20px;
            border-radius: 10px;
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

        /* Header Styles */
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

        .search-form {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-form input {
            padding: 10px 15px 10px 40px;
            border: none;
            border-radius: 30px;
            background: white;
            box-shadow: var(--shadow);
            width: 100%;
            transition: var(--transition);
        }

        .search-form input:focus {
            outline: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .search-form button {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
        }

        /* User Menu for Smaller Screens */
        .user-menu-mobile {
            display: none;
            position: relative;
            cursor: pointer;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
            background: var(--gray);
        }

        .user-menu-mobile i {
            font-size: 1.3rem;
            color: var(--primary);
            transition: var(--transition);
        }

        .user-menu-mobile:hover {
            background: var(--gray-dark);
        }

        /* Stats Overview Cards */
        .stats-overview {
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
            display: flex;
            align-items: center;
            gap: 12px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .stat-icon.total {
            background: linear-gradient(135deg, var(--info), #64b5f6);
            color: white;
        }

        .stat-icon.unread {
            background: linear-gradient(135deg, var(--warning), #ffb74d);
            color: white;
        }

        .stat-icon.today {
            background: linear-gradient(135deg, var(--success), #81c784);
            color: white;
        }

        .stat-icon.phone {
            background: linear-gradient(135deg, #9C27B0, #BA68C8);
            color: white;
        }

        .stat-info {
            flex: 1;
            min-width: 0;
        }

        .stat-info h3 {
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: var(--text-light);
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .stat-change {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--danger);
        }

        /* Filters Bar */
        .filters-bar {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .filters-form {
            margin-bottom: 15px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 500;
            color: var(--text);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .filter-group select,
        .filter-group input {
            padding: 10px 12px;
            border: 1px solid var(--gray-dark);
            border-radius: 6px;
            background: white;
            color: var(--text);
            width: 100%;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .filter-btn,
        .reset-btn,
        .export-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--transition);
            font-weight: 500;
        }

        .filter-btn {
            background: var(--primary);
            color: white;
        }

        .filter-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .reset-btn {
            background: var(--gray);
            color: var(--text);
        }

        .reset-btn:hover {
            background: var(--gray-dark);
        }

        .export-btn {
            background: var(--success);
            color: white;
        }

        .export-btn:hover {
            background: #388e3c;
        }

        /* Messages Container */
        .messages-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .messages-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .messages-header h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 15px;
            width: 100%;
        }

        .actions-buttons {
            display: flex;
            gap: 10px;
            width: 100%;
        }

        .action-btn {
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: var(--transition);
            font-weight: 500;
            flex: 1;
        }

        .action-btn.mark-read {
            background: var(--info);
            color: white;
        }

        .action-btn.mark-read:hover {
            background: #1976d2;
        }

        .action-btn.delete {
            background: var(--danger);
            color: white;
        }

        .action-btn.delete:hover {
            background: #d32f2f;
        }

        .action-btn.archive {
            background: var(--warning);
            color: white;
        }

        .action-btn.archive:hover {
            background: #f57c00;
        }

        /* Table Container */
        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .messages-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            min-width: 1200px;
        }

        .messages-table th {
            background: var(--primary);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--primary-dark);
        }

        .messages-table td {
            padding: 15px;
            border-bottom: 1px solid var(--gray);
            vertical-align: middle;
        }

        .messages-table tr:hover {
            background: rgba(139, 69, 19, 0.05);
        }

        .messages-table tr.unread {
            background: rgba(33, 150, 243, 0.05);
        }

        .messages-table tr.important {
            background: rgba(255, 152, 0, 0.05);
        }

        .message-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .sender-info {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 200px;
        }

        .sender-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
            font-weight: 500;
            flex-shrink: 0;
        }

        .sender-details {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .sender-name {
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sender-email {
            font-size: 0.8rem;
            color: var(--text-light);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sender-phone {
            font-size: 0.8rem;
            color: var(--primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 2px;
        }

        .subject {
            font-weight: 500;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }

        .message-preview {
            font-size: 0.85rem;
            color: var(--text-light);
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .status-unread {
            background: rgba(33, 150, 243, 0.1);
            color: var(--info);
        }

        .status-read {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }

        .status-archived {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }

        .status-replied {
            background: rgba(156, 39, 176, 0.1);
            color: #9c27b0;
        }

        .location-info {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
            color: var(--text-light);
            min-width: 150px;
        }

        .location-info i {
            color: var(--primary);
        }

        .location-text {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .location-ip {
            font-size: 0.7rem;
            color: var(--text-light);
        }

        .date-time {
            font-size: 0.85rem;
            color: var(--text-light);
            white-space: nowrap;
        }

        .action-icons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            flex-shrink: 0;
        }

        .action-icon:hover {
            transform: scale(1.1);
        }

        .action-icon.view {
            background: var(--info);
            color: white;
        }

        .action-icon.reply {
            background: var(--success);
            color: white;
        }

        .action-icon.archive {
            background: var(--warning);
            color: white;
        }

        .action-icon.delete {
            background: var(--danger);
            color: white;
        }

        .action-icon.phone {
            background: #9C27B0;
            color: white;
        }

        /* Mobile Card View */
        .messages-mobile-view {
            display: none;
        }

        .message-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--info);
            position: relative;
        }

        .message-card.unread {
            border-left-color: var(--warning);
        }

        .message-card.important {
            border-left-color: var(--danger);
        }

        .message-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .message-card-sender {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .message-card-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }

        .message-detail-item {
            display: flex;
            flex-direction: column;
        }

        .message-detail-label {
            font-size: 0.75rem;
            color: var(--text-light);
            margin-bottom: 3px;
        }

        .message-detail-value {
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text);
            word-break: break-word;
        }

        .message-card-content {
            margin-bottom: 15px;
        }

        .message-preview-mobile {
            font-size: 0.9rem;
            color: var(--text-light);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .message-card-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid var(--gray);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .pagination-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid var(--gray-dark);
            background: white;
            color: var(--text);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .pagination-btn:hover:not(:disabled) {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-pages {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .page-number {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid var(--gray-dark);
            background: white;
            color: var(--text);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            font-size: 0.9rem;
            text-decoration: none;
        }

        .page-number:hover,
        .page-number.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .page-info {
            font-size: 0.85rem;
            color: var(--text-light);
            text-align: center;
            width: 100%;
            margin-top: 5px;
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
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-bottom: 1px solid var(--gray);
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

        .modal-body {
            padding: 25px;
        }

        .modal-footer {
            padding: 15px 25px;
            background: var(--gray);
            border-top: 1px solid var(--gray-dark);
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--transition);
            font-weight: 500;
        }

        .modal-btn.reply {
            background: var(--success);
            color: white;
        }

        .modal-btn.archive {
            background: var(--warning);
            color: white;
        }

        .modal-btn.delete {
            background: var(--danger);
            color: white;
        }

        .modal-btn.close {
            background: var(--gray);
            color: var(--text);
        }

        .modal-btn.phone {
            background: #9C27B0;
            color: white;
        }

        .modal-btn:hover {
            transform: translateY(-2px);
        }

        /* Message Detail Modal Styles */
        .message-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .message-detail-grid {
                grid-template-columns: 1fr;
            }
        }

        .message-section {
            background: var(--gray);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .message-section h4 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-item {
            display: flex;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .detail-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: var(--text);
            min-width: 120px;
            font-size: 0.9rem;
        }

        .detail-value {
            flex: 1;
            color: var(--text);
            font-size: 0.95rem;
            word-break: break-word;
        }

        .message-body {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--gray-dark);
            margin-top: 10px;
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

        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            z-index: 10000;
            animation: slideInRight 0.3s ease;
            max-width: 90%;
        }

        .notification.success {
            background: var(--success);
        }

        .notification.warning {
            background: var(--warning);
        }

        .notification.info {
            background: var(--info);
        }

        .notification.danger {
            background: var(--danger);
        }

        .notification-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Notification Bell Styles */
        .notification-user-container {
            display: flex;
            align-items: center;
            gap: 8px;
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
            background: white;
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

        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            min-width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
            pointer-events: none;
            font-weight: 600;
            padding: 0 4px;
        }

        .notification-badge:empty {
            display: none;
        }

        /* Notification Dropdown */
        .notification-dropdown {
            position: absolute;
            top: 50px;
            right: 0;
            width: 350px;
            max-height: 500px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
            overflow: hidden;
        }

        .notification-dropdown.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .notification-dropdown-header {
            padding: 15px 20px;
            background: var(--primary);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .notification-dropdown-header h4 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
        }

        .mark-all-read {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: var(--transition);
        }

        .mark-all-read:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .notification-list {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--gray-dark);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .notification-item:hover {
            background: var(--gray);
        }

        .notification-item.unread {
            background: rgba(139, 69, 19, 0.05);
        }

        .notification-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary);
            margin-top: 6px;
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            color: var(--text);
            margin-bottom: 5px;
            font-size: 0.95rem;
        }

        .notification-message {
            color: var(--text-light);
            font-size: 0.85rem;
            margin-bottom: 5px;
            line-height: 1.4;
        }

        .notification-time {
            color: var(--text-light);
            font-size: 0.75rem;
        }

        .notification-empty {
            padding: 40px 20px;
            text-align: center;
            color: var(--text-light);
        }

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

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            margin-top: 30px;
            color: var(--text-light);
            font-size: 0.9rem;
            border-top: 1px solid var(--gray-dark);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .stats-overview {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .filters-grid {
                grid-template-columns: repeat(2, 1fr);
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

            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
            }

            .user-menu-mobile {
                display: flex;
            }
        }

        @media (max-width: 768px) {
            .header-actions {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .search-form {
                max-width: 100%;
            }

            .stats-overview {
                grid-template-columns: 1fr;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .filter-buttons {
                flex-direction: column;
            }

            .filter-btn,
            .reset-btn,
            .export-btn {
                width: 100%;
            }

            .messages-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .actions-buttons {
                flex-direction: column;
            }

            .table-container {
                display: none;
            }

            .messages-mobile-view {
                display: block;
            }

            .message-card-details {
                grid-template-columns: 1fr;
            }

            .modal-content {
                padding: 20px 15px;
            }

            .modal-footer {
                justify-content: center;
            }

            .modal-btn {
                flex: 1;
                min-width: 120px;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }

            .messages-container {
                padding: 20px 15px;
            }

            .stat-card {
                padding: 15px;
            }

            .stat-number {
                font-size: 1.5rem;
            }

            .message-card-actions {
                justify-content: center;
            }

            .action-icon {
                width: 28px;
                height: 28px;
                font-size: 0.9rem;
            }

            .pagination-btn,
            .page-number {
                width: 35px;
                height: 35px;
            }
        }

        @media (max-width: 480px) {
            .logo-area h1 {
                font-size: 1.2rem;
            }

            .header h2 {
                font-size: 1.3rem;
            }

            .messages-header h3 {
                font-size: 1.2rem;
            }

            .stat-icon {
                width: 45px;
                height: 45px;
                font-size: 1.3rem;
            }

            .action-btn {
                padding: 8px 12px;
                font-size: 0.9rem;
            }

            .modal-btn {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <!-- Notification -->
    <?php if ($notification): ?>
    <div class="notification <?php echo $notification['type']; ?>">
        <span><?php echo htmlspecialchars($notification['message']); ?></span>
        <button class="notification-close">&times;</button>
    </div>
    <?php endif; ?>

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
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-contact-messages.php" class="active">
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
                    <a href="admin-events.php" class="active">
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
                <li class="menu-item">
                    <a href="admin-career.php">
                        <i class="fas fa-briefcase"></i>
                        <span>Careers</span>
                    </a>
                </li>

                <li class="menu-label">Analytics</li>
                <li class="menu-item">
                    <a href="../site-traffic.php">
                        <i class="fas fa-chart-line"></i>
                        <span>Traffic Analytics</span>
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
                    <a href="admin-logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <div class="header">
                <h2><i class="fas fa-envelope"></i> Contact Messages</h2>
                <div class="header-actions">
                    <form method="GET" class="search-form">
                        <button type="submit"><i class="fas fa-search"></i></button>
                        <input type="text" name="search" placeholder="Search messages..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                        <input type="hidden" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">
                        <input type="hidden" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">
                        <input type="hidden" name="country" value="<?php echo htmlspecialchars($countryFilter); ?>">
                    </form>
                    <div class="notification-user-container">
                        <div class="notification-icon" id="contactNotificationIcon">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge" id="contactNotificationBadge"><?php echo $stats['unread']; ?></span>
                            <div class="notification-dropdown" id="contactNotificationDropdown">
                                <div class="notification-dropdown-header">
                                    <h4>Contact Messages</h4>
                                    <button class="mark-all-read" id="markAllContactRead">Mark all as read</button>
                                </div>
                                <ul class="notification-list" id="contactNotificationList">
                                    <!-- Notifications will be loaded here -->
                                </ul>
                            </div>
                        </div>
                        <div class="user-menu" id="userMenuBtn" onclick="window.location.href='admin-settings.php'">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    </div>
                    <div class="user-menu-mobile">
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="stats-overview">
                <div class="stat-card total reveal">
                    <div class="stat-icon total">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo number_format($stats['total']); ?></div>
                        <h3>Total Messages</h3>
                        <div class="stat-change <?php echo $stats['month_change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo ($stats['month_change'] >= 0 ? '+' : '') . $stats['month_change']; ?>% this month
                        </div>
                    </div>
                </div>

                <div class="stat-card unread reveal reveal-delay-1">
                    <div class="stat-icon unread">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo number_format($stats['unread']); ?></div>
                        <h3>Unread Messages</h3>
                        <div class="stat-change">
                            <?php echo ($stats['unread'] > 0 ? 'Needs attention' : 'All caught up'); ?>
                        </div>
                    </div>
                </div>

                <div class="stat-card today reveal reveal-delay-2">
                    <div class="stat-icon today">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo number_format($stats['today']); ?></div>
                        <h3>Today's Messages</h3>
                        <div class="stat-change <?php echo $stats['today_change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo ($stats['today_change'] >= 0 ? '+' : '') . $stats['today_change']; ?>% from yesterday
                        </div>
                    </div>
                </div>

                <div class="stat-card phone reveal reveal-delay-3">
                    <div class="stat-icon phone">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo number_format($stats['phone']); ?></div>
                        <h3>Phone Contacts</h3>
                        <div class="stat-change">
                            <?php echo round(($stats['phone'] / $stats['total']) * 100, 1); ?>% of total
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="filters-bar reveal">
                <form method="GET" class="filters-form">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label><i class="fas fa-filter"></i> Status:</label>
                            <select name="status" id="statusFilter">
                                <option value="all" <?php echo $statusFilter == 'all' ? 'selected' : ''; ?>>All Messages</option>
                                <option value="unread" <?php echo $statusFilter == 'unread' ? 'selected' : ''; ?>>Unread Only</option>
                                <option value="read" <?php echo $statusFilter == 'read' ? 'selected' : ''; ?>>Read Only</option>
                                <option value="replied" <?php echo $statusFilter == 'replied' ? 'selected' : ''; ?>>Replied</option>
                                <option value="archived" <?php echo $statusFilter == 'archived' ? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label><i class="fas fa-calendar"></i> Date From:</label>
                            <input type="date" name="date_from" id="dateFrom" value="<?php echo htmlspecialchars($dateFrom); ?>">
                        </div>

                        <div class="filter-group">
                            <label><i class="fas fa-calendar"></i> Date To:</label>
                            <input type="date" name="date_to" id="dateTo" value="<?php echo htmlspecialchars($dateTo); ?>">
                        </div>

                        <div class="filter-group">
                            <label><i class="fas fa-globe"></i> Country:</label>
                            <select name="country" id="countryFilter">
                                <option value="all" <?php echo $countryFilter == 'all' ? 'selected' : ''; ?>>All Countries</option>
                                <option value="nigeria" <?php echo $countryFilter == 'nigeria' ? 'selected' : ''; ?>>Nigeria</option>
                                <option value="usa" <?php echo $countryFilter == 'usa' ? 'selected' : ''; ?>>United States</option>
                                <option value="uk" <?php echo $countryFilter == 'uk' ? 'selected' : ''; ?>>United Kingdom</option>
                                <option value="ghana" <?php echo $countryFilter == 'ghana' ? 'selected' : ''; ?>>Ghana</option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-buttons">
                        <button type="submit" class="filter-btn">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <a href="admin-contact-messages.php" class="reset-btn">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                        <button type="button" class="export-btn" onclick="exportMessages()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
                </form>
            </div>

            <!-- Messages Container -->
            <div class="messages-container reveal">
                <div class="messages-header">
                    <h3>Contact Messages (Showing <?php echo ($offset + 1) . '-' . min($offset + $limit, $totalRows) . ' of ' . number_format($totalRows); ?>)</h3>
                    <div class="actions-buttons">
                        <form method="POST" id="bulkActionForm">
                            <input type="hidden" name="action" id="bulkAction">
                            <input type="hidden" name="message_ids" id="selectedMessages">
                            <button type="button" class="action-btn mark-read" onclick="performBulkAction('mark_read')">
                                <i class="fas fa-eye"></i> Mark as Read
                            </button>
                            <button type="button" class="action-btn archive" onclick="performBulkAction('archive')">
                                <i class="fas fa-archive"></i> Archive
                            </button>
                            <button type="button" class="action-btn delete" onclick="performBulkAction('delete')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="table-container">
                    <form id="messagesForm">
                        <table class="messages-table">
                            <thead>
                                <tr>
                                    <th style="width: 30px;">
                                        <input type="checkbox" class="message-checkbox" id="selectAll">
                                    </th>
                                    <th>Sender</th>
                                    <th>Phone</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>Date & Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="messagesTableBody">
                                <?php if (empty($messages)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 40px;">
                                        <i class="fas fa-inbox" style="font-size: 3rem; color: var(--gray-dark); margin-bottom: 15px; display: block;"></i>
                                        <h3 style="color: var(--text-light); margin-bottom: 10px;">No messages found</h3>
                                        <p style="color: var(--text-light);">Try adjusting your filters or search terms.</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($messages as $message): ?>
                                    <tr class="<?php echo $message['status'] === 'unread' ? 'unread' : ''; ?>">
                                        <td>
                                            <input type="checkbox" class="message-checkbox" name="message_ids[]" value="<?php echo $message['id']; ?>">
                                        </td>
                                        <td>
                                            <div class="sender-info">
                                                <div class="sender-avatar" style="background: <?php echo getAvatarColor($message['name']); ?>">
                                                    <?php echo getInitials($message['name']); ?>
                                                </div>
                                                <div class="sender-details">
                                                    <div class="sender-name"><?php echo htmlspecialchars($message['name']); ?></div>
                                                    <div class="sender-email"><?php echo htmlspecialchars($message['email']); ?></div>
                                                    <?php if (!empty($message['phone'])): ?>
                                                    <div class="sender-phone">
                                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($message['phone']); ?>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="phone-number"><?php echo !empty($message['phone']) ? htmlspecialchars($message['phone']) : 'N/A'; ?></div>
                                        </td>
                                        <td>
                                            <div class="subject"><?php echo htmlspecialchars($message['subject']); ?></div>
                                        </td>
                                        <td>
                                            <div class="message-preview" title="<?php echo htmlspecialchars($message['message']); ?>">
                                                <?php echo strlen($message['message']) > 50 ? substr($message['message'], 0, 50) . '...' : $message['message']; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = 'status-' . $message['status'];
                                            $statusText = ucfirst($message['status']);
                                            ?>
                                            <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                        </td>
                                        <td>
                                            <div class="location-info">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <div class="location-text">
                                                    <span><?php echo !empty($message['country']) ? htmlspecialchars($message['country']) : 'Unknown'; ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="date-time">
                                                <?php echo formatDate($message['created_at']); ?><br>
                                                <?php echo formatTime($message['created_at']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-icons">
                                                <div class="action-icon view" onclick="viewMessage(<?php echo $message['id']; ?>)" title="View Message">
                                                    <i class="fas fa-eye"></i>
                                                </div>
                                                <div class="action-icon reply" onclick="replyToMessage('<?php echo htmlspecialchars($message['email']); ?>', '<?php echo htmlspecialchars($message['subject']); ?>', <?php echo $message['id']; ?>)" title="Reply">
                                                    <i class="fas fa-reply"></i>
                                                </div>
                                                <?php if (!empty($message['phone'])): ?>
                                                <div class="action-icon phone" onclick="callNumber('<?php echo htmlspecialchars($message['phone']); ?>')" title="Call">
                                                    <i class="fas fa-phone"></i>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </form>
                </div>

                <!-- Mobile Card View -->
                <div class="messages-mobile-view" id="messagesMobileView">
                    <?php if (empty($messages)): ?>
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-inbox" style="font-size: 3rem; color: var(--gray-dark); margin-bottom: 15px; display: block;"></i>
                        <h3 style="color: var(--text-light); margin-bottom: 10px;">No messages found</h3>
                        <p style="color: var(--text-light);">Try adjusting your filters or search terms.</p>
                    </div>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                        <div class="message-card <?php echo $message['status'] === 'unread' ? 'unread' : ''; ?>">
                            <div class="message-card-header">
                                <div class="message-card-sender">
                                    <div class="sender-avatar" style="background: <?php echo getAvatarColor($message['name']); ?>">
                                        <?php echo getInitials($message['name']); ?>
                                    </div>
                                    <div>
                                        <div class="sender-name"><?php echo htmlspecialchars($message['name']); ?></div>
                                        <div class="sender-email"><?php echo htmlspecialchars($message['email']); ?></div>
                                    </div>
                                </div>
                                <?php
                                $statusClass = 'status-' . $message['status'];
                                $statusText = ucfirst($message['status']);
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                            </div>
                            
                            <div class="message-card-details">
                                <div class="message-detail-item">
                                    <span class="message-detail-label">Phone:</span>
                                    <span class="message-detail-value"><?php echo !empty($message['phone']) ? htmlspecialchars($message['phone']) : 'N/A'; ?></span>
                                </div>
                                <div class="message-detail-item">
                                    <span class="message-detail-label">Subject:</span>
                                    <span class="message-detail-value"><?php echo htmlspecialchars($message['subject']); ?></span>
                                </div>
                                <div class="message-detail-item">
                                    <span class="message-detail-label">Location:</span>
                                    <span class="message-detail-value"><?php echo !empty($message['country']) ? htmlspecialchars($message['country']) : 'Unknown'; ?></span>
                                </div>
                                <div class="message-detail-item">
                                    <span class="message-detail-label">Date & Time:</span>
                                    <span class="message-detail-value"><?php echo formatDate($message['created_at']) . ' at ' . formatTime($message['created_at']); ?></span>
                                </div>
                            </div>
                            
                            <div class="message-card-content">
                                <div class="message-preview-mobile"><?php echo htmlspecialchars($message['message']); ?></div>
                            </div>
                            
                            <div class="message-card-actions">
                                <div class="action-icon view" onclick="viewMessage(<?php echo $message['id']; ?>)" title="View Message">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div class="action-icon reply" onclick="replyToMessage('<?php echo htmlspecialchars($message['email']); ?>', '<?php echo htmlspecialchars($message['subject']); ?>', <?php echo $message['id']; ?>)" title="Reply">
                                    <i class="fas fa-reply"></i>
                                </div>
                                <?php if (!empty($message['phone'])): ?>
                                <div class="action-icon phone" onclick="callNumber('<?php echo htmlspecialchars($message['phone']); ?>')" title="Call">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <a href="<?php echo $page > 1 ? '?page=' . ($page - 1) . '&' . http_build_query($_GET) : '#'; ?>" class="pagination-btn <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <div class="pagination-pages">
                        <?php for ($i = 1; $i <= min(5, $totalPages); $i++): ?>
                            <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($_GET); ?>" class="page-number <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        <?php if ($totalPages > 5): ?>
                            <span style="padding: 0 5px;">...</span>
                            <a href="?page=<?php echo $totalPages; ?>&<?php echo http_build_query($_GET); ?>" class="page-number">
                                <?php echo $totalPages; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <a href="<?php echo $page < $totalPages ? '?page=' . ($page + 1) . '&' . http_build_query($_GET) : '#'; ?>" class="pagination-btn <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <div class="page-info">
                        Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Message Detail Modal -->
    <div class="modal" id="messageDetailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Message Details</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body" id="messageDetailContent">
                <!-- Content will be loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button class="modal-btn phone" id="modalCallBtn">
                    <i class="fas fa-phone"></i> Call
                </button>
                <button class="modal-btn reply" id="modalReplyBtn">
                    <i class="fas fa-reply"></i> Reply
                </button>
                <button class="modal-btn close" onclick="closeAllModals()">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2025 Joseph's Pot Admin Dashboard. All rights reserved | Developed By ERIBS tech</p>
    </div>

    <script>
        // Mobile sidebar toggler functionality
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

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

        // Select all functionality
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.message-checkbox:not(#selectAll)');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }

        // Bulk actions
        function performBulkAction(action) {
            const checkboxes = document.querySelectorAll('.message-checkbox:not(#selectAll):checked');
            if (checkboxes.length === 0) {
                showNotification('Please select at least one message.', 'warning');
                return;
            }

            const messageIds = Array.from(checkboxes).map(cb => cb.value);
            
            let confirmMessage = '';
            switch (action) {
                case 'mark_read':
                    confirmMessage = `Mark ${messageIds.length} message(s) as read?`;
                    break;
                case 'archive':
                    confirmMessage = `Archive ${messageIds.length} message(s)?`;
                    break;
                case 'delete':
                    confirmMessage = `Delete ${messageIds.length} message(s)? This action cannot be undone.`;
                    break;
            }

            if (confirm(confirmMessage)) {
                document.getElementById('bulkAction').value = action;
                document.getElementById('selectedMessages').value = JSON.stringify(messageIds);
                document.getElementById('bulkActionForm').submit();
            }
        }

        // View message details - COMPLETE WORKING VERSION
        function viewMessage(messageId) {
            // Show loading state
            const modalContent = document.getElementById('messageDetailContent');
            modalContent.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary);"></i>
                    <p style="margin-top: 15px; color: var(--text-light);">Loading message details...</p>
                </div>
            `;
            
            // Open modal immediately
            openModal('messageDetailModal');
            
            // Fetch message details via AJAX
            fetch(`get-message-details.php?id=${messageId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const message = data.message;
                        
                        // Update modal content with actual message details
                        modalContent.innerHTML = `
                            <div class="message-detail-grid">
                                <div class="message-section">
                                    <h4><i class="fas fa-user-circle"></i> Sender Information</h4>
                                    <div class="detail-item">
                                        <span class="detail-label">Full Name:</span>
                                        <div class="detail-value">${message.name}</div>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Email Address:</span>
                                        <div class="detail-value">
                                            <a href="mailto:${message.email}" style="color: var(--primary); text-decoration: none;">
                                                <i class="fas fa-envelope"></i> ${message.email}
                                            </a>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Phone Number:</span>
                                        <div class="detail-value">
                                            ${message.phone !== 'Not provided' ? 
                                                `<a href="tel:${message.phone}" style="color: var(--success); text-decoration: none;">
                                                    <i class="fas fa-phone"></i> ${message.phone}
                                                </a>` : 
                                                '<span style="color: var(--text-light);">Not provided</span>'
                                            }
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Subject:</span>
                                        <div class="detail-value" style="font-weight: 600;">${message.subject}</div>
                                    </div>
                                </div>

                                <div class="message-section">
                                    <h4><i class="fas fa-info-circle"></i> Technical Information</h4>
                                    <div class="detail-item">
                                        <span class="detail-label">Message ID:</span>
                                        <div class="detail-value">#${message.id}</div>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">IP Address:</span>
                                        <div class="detail-value">${message.ip_address}</div>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Location:</span>
                                        <div class="detail-value">${message.country}</div>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Browser/Device:</span>
                                        <div class="detail-value">${message.user_agent}</div>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Submitted:</span>
                                        <div class="detail-value">${message.created_at}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="message-section" style="margin-top: 25px; border-top: 1px solid var(--gray); padding-top: 20px;">
                                <h4><i class="fas fa-comment-alt"></i> Message Content</h4>
                                <div class="message-body" style="
                                    background: var(--gray);
                                    padding: 20px;
                                    border-radius: 8px;
                                    margin-top: 15px;
                                    border-left: 4px solid var(--primary);
                                ">
                                    <div style="
                                        white-space: pre-wrap;
                                        line-height: 1.6;
                                        color: var(--text);
                                        font-size: 0.95rem;
                                    ">${message.message}</div>
                                </div>
                            </div>
                        `;
                        
                        // Update modal buttons with actual data
                        if (message.phone !== 'Not provided') {
                            document.getElementById('modalCallBtn').style.display = 'block';
                            document.getElementById('modalCallBtn').onclick = () => callNumber(message.phone);
                        } else {
                            document.getElementById('modalCallBtn').style.display = 'none';
                        }
                        
                        document.getElementById('modalReplyBtn').onclick = () => replyToMessage(message.email, message.subject, messageId);
                        
                        // Update the message status in the table if it was unread
                        if (message.status === 'read') {
                            setTimeout(() => {
                                // Update desktop table view
                                const statusBadge = document.querySelector(`.message-checkbox[value="${messageId}"]`)
                                    ?.closest('tr')
                                    ?.querySelector('.status-badge');
                                if (statusBadge) {
                                    statusBadge.textContent = 'Read';
                                    statusBadge.className = 'status-badge status-read';
                                    statusBadge.closest('tr')?.classList.remove('unread');
                                }
                                
                                // Update mobile view
                                const mobileCards = document.querySelectorAll('.message-card');
                                mobileCards.forEach(card => {
                                    const mobileBadge = card.querySelector('.status-badge');
                                    if (mobileBadge && card.querySelector('.sender-name')?.textContent?.includes(message.name)) {
                                        mobileBadge.textContent = 'Read';
                                        mobileBadge.className = 'status-badge status-read';
                                        card.classList.remove('unread');
                                    }
                                });
                                
                                // Update unread counter if exists
                                const unreadStat = document.querySelector('.stat-card.unread .stat-number');
                                if (unreadStat) {
                                    const currentUnread = parseInt(unreadStat.textContent.replace(/,/g, ''));
                                    if (currentUnread > 0) {
                                        unreadStat.textContent = (currentUnread - 1).toLocaleString();
                                    }
                                }
                            }, 500);
                        }
                        
                    } else {
                        // Show error if message not found
                        modalContent.innerHTML = `
                            <div style="text-align: center; padding: 40px;">
                                <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: var(--danger); margin-bottom: 20px;"></i>
                                <h3 style="color: var(--text-light); margin-bottom: 10px;">Error Loading Message</h3>
                                <p style="color: var(--text-light);">${data.message || 'Unable to load message details.'}</p>
                                <button onclick="closeAllModals()" style="
                                    margin-top: 20px;
                                    padding: 10px 20px;
                                    background: var(--primary);
                                    color: white;
                                    border: none;
                                    border-radius: 6px;
                                    cursor: pointer;
                                ">
                                    <i class="fas fa-times"></i> Close
                                </button>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error fetching message:', error);
                    modalContent.innerHTML = `
                        <div style="text-align: center; padding: 40px;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: var(--warning); margin-bottom: 20px;"></i>
                            <h3 style="color: var(--text-light); margin-bottom: 10px;">Network Error</h3>
                            <p style="color: var(--text-light);">Failed to load message details. Please check your connection and try again.</p>
                            <button onclick="viewMessage(${messageId})" style="
                                margin-top: 20px;
                                padding: 10px 20px;
                                background: var(--info);
                                color: white;
                                border: none;
                                border-radius: 6px;
                                cursor: pointer;
                                margin-right: 10px;
                            ">
                                <i class="fas fa-redo"></i> Retry
                            </button>
                            <button onclick="closeAllModals()" style="
                                margin-top: 20px;
                                padding: 10px 20px;
                                background: var(--gray);
                                color: var(--text);
                                border: none;
                                border-radius: 6px;
                                cursor: pointer;
                            ">
                                <i class="fas fa-times"></i> Close
                            </button>
                        </div>
                    `;
                });
        }

        // Call phone number - REAL FUNCTIONALITY
        function callNumber(phone) {
            if (phone && phone !== 'N/A' && phone !== 'Not provided') {
                // Clean phone number (remove non-numeric characters except +)
                const cleanPhone = phone.replace(/[^\d+]/g, '');
                
                // Check if it's a Nigerian number (add country code if missing)
                let formattedPhone = cleanPhone;
                if (cleanPhone.startsWith('0') && cleanPhone.length === 11) {
                    formattedPhone = '+234' + cleanPhone.substring(1);
                } else if (cleanPhone.length === 10) {
                    formattedPhone = '+234' + cleanPhone;
                }
                
                // Create a confirmation dialog with phone options
                const callOptions = `
                    <div style="padding: 20px;">
                        <h4 style="color: var(--primary); margin-bottom: 15px;">Call Options</h4>
                        <div style="margin-bottom: 15px;">
                            <strong>Phone Number:</strong> ${phone}
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <button onclick="initiateCall('${formattedPhone}', 'tel')" style="
                                padding: 12px;
                                background: var(--success);
                                color: white;
                                border: none;
                                border-radius: 6px;
                                cursor: pointer;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 8px;
                            ">
                                <i class="fas fa-phone"></i> Call Now (Mobile)
                            </button>
                            <button onclick="copyPhoneNumber('${phone}')" style="
                                padding: 12px;
                                background: var(--info);
                                color: white;
                                border: none;
                                border-radius: 6px;
                                cursor: pointer;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 8px;
                            ">
                                <i class="fas fa-copy"></i> Copy Phone Number
                            </button>
                            <button onclick="saveContact('${phone}')" style="
                                padding: 12px;
                                background: #9C27B0;
                                color: white;
                                border: none;
                                border-radius: 6px;
                                cursor: pointer;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 8px;
                            ">
                                <i class="fas fa-address-book"></i> Save to Contacts
                            </button>
                        </div>
                    </div>
                `;
                
                // Create a custom modal for call options
                showCustomModal('Call Contact', callOptions);
                
            } else {
                showNotification('No valid phone number available for this contact.', 'warning');
            }
        }

        // Initiate phone call
        function initiateCall(phoneNumber, method) {
            if (method === 'tel') {
                // For mobile devices, this will open the phone dialer
                // For desktop, it may open a VoIP app or do nothing
                window.location.href = `tel:${phoneNumber}`;
                
                // Show notification
                showNotification(`Initiating call to ${phoneNumber}...`, 'info');
                
                // Close any open modals
                closeAllModals();
            }
        }

        // Copy phone number to clipboard
        function copyPhoneNumber(phone) {
            navigator.clipboard.writeText(phone).then(() => {
                showNotification(`Phone number copied to clipboard: ${phone}`, 'success');
                closeAllModals();
            }).catch(err => {
                console.error('Failed to copy: ', err);
                showNotification('Failed to copy phone number', 'danger');
            });
        }

        // Save contact to device (simulated)
        function saveContact(phone) {
            // In a real app, you might integrate with device contacts API
            // For now, we'll simulate it
            showNotification(`Contact with phone ${phone} saved (simulated)`, 'info');
            closeAllModals();
        }

        // Email templates
        const emailTemplates = {
            'thank_you': {
                subject: 'Thank you for contacting Joseph\'s Pot',
                body: 'Dear [Customer Name],\n\nThank you for contacting Joseph\'s Pot. We have received your message and our team will get back to you within 24 hours.\n\nBest regards,\nJoseph\'s Pot Team'
            },
            'reservation': {
                subject: 'Table Reservation at Joseph\'s Pot',
                body: 'Dear [Customer Name],\n\nThank you for your table reservation request. We will confirm your booking shortly.\n\nPlease provide your preferred date and time, and the number of guests.\n\nBest regards,\nJoseph\'s Pot Reservations Team'
            },
            'catering': {
                subject: 'Catering Inquiry - Joseph\'s Pot',
                body: 'Dear [Customer Name],\n\nThank you for your catering inquiry. Our catering team will contact you with menu options and pricing.\n\nWe cater for events from 20 to 500 guests.\n\nBest regards,\nJoseph\'s Pot Catering Team'
            },
            'job_application': {
                subject: 'Job Application Received - Joseph\'s Pot',
                body: 'Dear [Customer Name],\n\nThank you for your interest in joining Joseph\'s Pot. We have received your application and will review it.\n\nWe will contact you if your qualifications match our current openings.\n\nBest regards,\nJoseph\'s Pot HR Team'
            },
            'feedback': {
                subject: 'Thank you for your feedback',
                body: 'Dear [Customer Name],\n\nThank you for taking the time to provide feedback. We value your input and will use it to improve our services.\n\nWe hope to serve you again soon!\n\nBest regards,\nJoseph\'s Pot Management'
            }
        };

        // Reply to message - REAL FUNCTIONALITY
        let currentReplyEmail = '';
        let currentReplySubject = '';
        let currentMessageId = '';

        function replyToMessage(email, subject, messageId = null) {
            currentReplyEmail = email;
            currentReplySubject = subject;
            currentMessageId = messageId;
            
            const replyModalContent = `
                <div style="padding: 20px;">
                    <h4 style="color: var(--primary); margin-bottom: 15px;">Reply to Message</h4>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">To:</label>
                        <input type="text" id="replyTo" value="${email}" readonly style="
                            width: 100%;
                            padding: 10px;
                            border: 1px solid var(--gray-dark);
                            border-radius: 6px;
                            background: var(--gray);
                            margin-bottom: 15px;
                        ">
                        
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Subject:</label>
                        <input type="text" id="replySubject" value="Re: ${subject}" style="
                            width: 100%;
                            padding: 10px;
                            border: 1px solid var(--gray-dark);
                            border-radius: 6px;
                            margin-bottom: 15px;
                        ">
                        
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Your Response:</label>
                        <textarea id="replyMessage" rows="8" placeholder="Type your response here..." style="
                            width: 100%;
                            padding: 12px;
                            border: 1px solid var(--gray-dark);
                            border-radius: 6px;
                            font-family: inherit;
                            resize: vertical;
                            margin-bottom: 15px;
                        "></textarea>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" id="saveTemplate" checked>
                                <span>Save as response template</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; margin-top: 8px;">
                                <input type="checkbox" id="markAsReplied" checked>
                                <span>Mark message as "Replied"</span>
                            </label>
                        </div>
                        
                        <!-- Email Templates -->
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Quick Templates:</label>
                            <select id="emailTemplates" onchange="applyTemplate(this.value)" style="
                                width: 100%;
                                padding: 8px;
                                border: 1px solid var(--gray-dark);
                                border-radius: 6px;
                                margin-bottom: 10px;
                            ">
                                <option value="">Select a template...</option>
                                <option value="thank_you">Thank you for contacting us</option>
                                <option value="reservation">Reservation confirmation</option>
                                <option value="catering">Catering inquiry response</option>
                                <option value="job_application">Job application acknowledgment</option>
                                <option value="feedback">Feedback thank you</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button onclick="sendReplyEmail()" style="
                            padding: 10px 20px;
                            background: var(--success);
                            color: white;
                            border: none;
                            border-radius: 6px;
                            cursor: pointer;
                            display: flex;
                            align-items: center;
                            gap: 8px;
                        ">
                            <i class="fas fa-paper-plane"></i> Send Email
                        </button>
                        <button onclick="sendReplySMS()" style="
                            padding: 10px 20px;
                            background: var(--info);
                            color: white;
                            border: none;
                            border-radius: 6px;
                            cursor: pointer;
                            display: flex;
                            align-items: center;
                            gap: 8px;
                        ">
                            <i class="fas fa-sms"></i> Send SMS
                        </button>
                        <button onclick="closeAllModals()" style="
                            padding: 10px 20px;
                            background: var(--gray);
                            color: var(--text);
                            border: none;
                            border-radius: 6px;
                            cursor: pointer;
                        ">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </div>
            `;
            
            showCustomModal('Send Response', replyModalContent);
        }

        // Apply email template
        function applyTemplate(templateKey) {
            if (templateKey && emailTemplates[templateKey]) {
                const template = emailTemplates[templateKey];
                document.getElementById('replySubject').value = template.subject;
                document.getElementById('replyMessage').value = template.body;
            }
        }

        // Send email via mailto: link (opens user's email client)
        function sendReplyEmail() {
            const to = document.getElementById('replyTo').value;
            const subject = encodeURIComponent(document.getElementById('replySubject').value);
            const body = encodeURIComponent(document.getElementById('replyMessage').value);
            
            if (!body.trim()) {
                showNotification('Please enter a message.', 'warning');
                return;
            }
            
            // Create mailto link
            const mailtoLink = `mailto:${to}?subject=${subject}&body=${body}`;
            
            // Open user's email client
            window.open(mailtoLink, '_blank');
            
            // Update message status if checkbox is checked
            const markAsReplied = document.getElementById('markAsReplied').checked;
            if (markAsReplied && currentMessageId) {
                updateMessageStatus(currentMessageId, 'replied');
            }
            
            // Save template if checkbox is checked
            const saveTemplate = document.getElementById('saveTemplate').checked;
            if (saveTemplate) {
                saveEmailTemplate(subject, body);
            }
            
            showNotification(`Email opened for ${to}. Please send from your email client.`, 'success');
            closeAllModals();
        }

        // Send SMS (simulated - in real app would use SMS API)
        function sendReplySMS() {
            const to = document.getElementById('replyTo').value;
            const message = document.getElementById('replyMessage').value;
            
            if (!message.trim()) {
                showNotification('Please enter a message.', 'warning');
                return;
            }
            
            // For demo purposes
            showNotification(`SMS would be sent to ${to} via SMS gateway.`, 'info');
            closeAllModals();
        }

        // Save email template
        function saveEmailTemplate(subject, body) {
            // In a real app, save to database or localStorage
            const templates = JSON.parse(localStorage.getItem('emailTemplates') || '[]');
            templates.push({
                subject: subject,
                body: body,
                created: new Date().toISOString()
            });
            localStorage.setItem('emailTemplates', JSON.stringify(templates));
        }

        // Custom modal function
        function showCustomModal(title, content) {
            // Remove existing custom modal
            const existingModal = document.getElementById('customModal');
            if (existingModal) existingModal.remove();
            
            // Create modal
            const modal = document.createElement('div');
            modal.id = 'customModal';
            modal.className = 'modal';
            modal.style.display = 'flex';
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 500px;">
                    <div class="modal-header">
                        <h3>${title}</h3>
                        <button class="close-modal" onclick="closeAllModals()">&times;</button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';
        }

        // Update message status function
        function updateMessageStatus(messageId, status) {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('message_id', messageId);
            formData.append('status', status);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI without refreshing
                    const statusBadge = document.querySelector(`.message-checkbox[value="${messageId}"]`)
                        ?.closest('tr')
                        ?.querySelector('.status-badge');
                    if (statusBadge) {
                        statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                        statusBadge.className = `status-badge status-${status}`;
                    }
                    showNotification(`Message marked as ${status}.`, 'success');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error updating message status.', 'danger');
            });
        }

        // Export messages
        function exportMessages() {
            const params = new URLSearchParams(window.location.search);
            params.append('export', 'csv');
            showNotification('Export feature would generate a CSV file.', 'info');
            // In a real implementation: window.location.href = 'export-messages.php?' + params.toString();
        }

        // Delete single message
        function deleteMessage(messageId) {
            if (confirm('Are you sure you want to delete this message?')) {
                const checkboxes = document.querySelectorAll('.message-checkbox:not(#selectAll)');
                checkboxes.forEach(cb => cb.checked = false);
                
                // Find and check the specific message's checkbox
                const targetCheckbox = document.querySelector(`.message-checkbox[value="${messageId}"]`);
                if (targetCheckbox) {
                    targetCheckbox.checked = true;
                }
                
                performBulkAction('delete');
            }
        }

        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeAllModals() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    closeAllModals();
                }
            });
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAllModals();
            }
        });

        // Close notification
        document.querySelectorAll('.notification-close').forEach(button => {
            button.addEventListener('click', function() {
                const notification = this.closest('.notification');
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            });
        });

        // Auto remove notification after 3 seconds
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(notification => {
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.animation = 'slideOutRight 0.3s ease';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 3000);
        });

        function showNotification(message, type) {
            // Remove existing notification
            const existingNotification = document.querySelector('.notification');
            if (existingNotification) {
                existingNotification.remove();
            }

            // Create notification
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            `;

            document.body.appendChild(notification);

            // Close notification
            notification.querySelector('.notification-close').addEventListener('click', function() {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            });

            // Auto remove after 3 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.animation = 'slideOutRight 0.3s ease';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 3000);
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

        // Initialize scroll reveal
        window.addEventListener('scroll', revealOnScroll);
        // Trigger once on load to check initial position
        revealOnScroll();

        // Notification Bell Functionality
        const contactNotificationIcon = document.getElementById('contactNotificationIcon');
        const contactNotificationDropdown = document.getElementById('contactNotificationDropdown');
        const contactNotificationList = document.getElementById('contactNotificationList');
        const contactNotificationBadge = document.getElementById('contactNotificationBadge');
        const markAllContactRead = document.getElementById('markAllContactRead');

        // Toggle notification dropdown
        if (contactNotificationIcon) {
            contactNotificationIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                contactNotificationDropdown.classList.toggle('active');
                if (contactNotificationDropdown.classList.contains('active')) {
                    loadContactNotifications();
                }
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (contactNotificationIcon && !contactNotificationIcon.contains(e.target) && 
                !contactNotificationDropdown.contains(e.target)) {
                contactNotificationDropdown.classList.remove('active');
            }
        });

        // Load contact message notifications
        function loadContactNotifications() {
            fetch('api/get-contact-notifications.php?limit=10')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderContactNotifications(data.data);
                        updateContactNotificationBadge(data.unread_count);
                    }
                })
                .catch(error => {
                    console.error('Error loading contact notifications:', error);
                });
        }

        // Render contact notifications
        function renderContactNotifications(notifications) {
            if (!contactNotificationList) return;
            
            contactNotificationList.innerHTML = '';
            
            if (!notifications || notifications.length === 0) {
                contactNotificationList.innerHTML = '<div class="notification-empty">No new messages</div>';
                return;
            }
            
            notifications.forEach(notification => {
                const notificationItem = document.createElement('li');
                notificationItem.className = `notification-item ${!notification.is_read ? 'unread' : ''}`;
                notificationItem.dataset.id = notification.id;
                
                const timeAgo = getTimeAgo(notification.created_at);
                
                notificationItem.innerHTML = `
                    <div class="notification-dot" style="${!notification.is_read ? 'background: var(--primary)' : 'background: transparent'}"></div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.name || 'New Message'}</div>
                        <div class="notification-message">${notification.subject || notification.message}</div>
                        <div class="notification-time">${timeAgo}</div>
                    </div>
                `;
                
                notificationItem.addEventListener('click', function() {
                    if (!notification.is_read) {
                        markContactAsRead(notification.id);
                    }
                    window.location.href = 'admin-contact-messages.php?message_id=' + notification.id;
                });
                
                contactNotificationList.appendChild(notificationItem);
            });
        }

        // Update notification badge
        function updateContactNotificationBadge(count) {
            if (contactNotificationBadge) {
                contactNotificationBadge.textContent = count || 0;
                contactNotificationBadge.style.display = (count > 0) ? 'flex' : 'none';
            }
        }

        // Mark contact message as read
        function markContactAsRead(messageId) {
            fetch('api/mark-contact-read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message_id: messageId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadContactNotifications();
                }
            })
            .catch(error => {
                console.error('Error marking contact as read:', error);
            });
        }

        // Mark all contact messages as read
        if (markAllContactRead) {
            markAllContactRead.addEventListener('click', function(e) {
                e.stopPropagation();
                fetch('api/mark-all-contact-read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadContactNotifications();
                    }
                })
                .catch(error => {
                    console.error('Error marking all as read:', error);
                });
            });
        }

        // Get time ago
        function getTimeAgo(timestamp) {
            const now = Math.floor(Date.now() / 1000);
            const time = Math.floor(new Date(timestamp).getTime() / 1000);
            const diff = now - time;
            
            if (diff < 60) {
                return 'Just now';
            } else if (diff < 3600) {
                const minutes = Math.floor(diff / 60);
                return minutes + ' min ago';
            } else if (diff < 86400) {
                const hours = Math.floor(diff / 3600);
                return hours + ' hour' + (hours > 1 ? 's' : '') + ' ago';
            } else {
                const days = Math.floor(diff / 86400);
                return days + ' day' + (days > 1 ? 's' : '') + ' ago';
            }
        }

        // Initialize WebSocket for real-time updates
        let contactWS = null;
        try {
            // Load WebSocket client
            const script = document.createElement('script');
            script.src = 'js/websocket-client.js';
            script.onload = function() {
                contactWS = initWebSocket('contact_messages');
                if (contactWS) {
                    contactWS.on('new_message', function(data) {
                        updateContactNotificationBadge(data.count);
                        loadContactNotifications();
                        // Show toast notification
                        showNotification('New contact message received!', 'info');
                    });
                }
            };
            document.head.appendChild(script);
        } catch (e) {
            console.error('WebSocket initialization error:', e);
        }

        // Load notifications on page load
        loadContactNotifications();
        
        // Poll for updates every 30 seconds (fallback if WebSocket fails)
        setInterval(loadContactNotifications, 30000);
    </script>
</body>
</html>