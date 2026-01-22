<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers Dashboard - Joseph's Pot Admin</title>
    <link rel="icon" href="../images/logo3.png">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* ===== ADMIN DASHBOARD STYLES ===== */
        :root {
            --primary: #8B4513;
            --primary-light: #A0522D;
            --primary-dark: #654321;
            --secondary: #D2691E;
            --accent: #FFA500;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --info: #3B82F6;
            --light: #F8F9FA;
            --dark: #1F2937;
            --gray: #6B7280;
            --gray-light: #E5E7EB;
            --white: #FFFFFF;
            --glass-bg: rgba(255, 255, 255, 0.95);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            color: var(--dark);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ===== DASHBOARD CONTAINER ===== */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* ===== MOBILE MENU TOGGLE BUTTON ===== */
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

        /* ===== OVERLAY FOR MOBILE ===== */
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

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            padding: 20px 0;
            box-shadow: var(--shadow-lg);
            z-index: 999;
            transition: var(--transition);
            transform: translateX(0);
            overflow-y: auto;
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar.active {
            transform: translateX(0);
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
            color: white;
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

        /* ===== MAIN CONTENT ===== */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 20px;
            transition: var(--transition);
            width: calc(100% - 280px);
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: 0;
            width: 100%;
        }

        /* ===== TOP NAVBAR ===== */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .nav-left h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--primary-dark);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 2px solid var(--gray-light);
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            width: 250px;
            transition: var(--transition);
            background: var(--white);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        /* ===== FIXED NOTIFICATION AND USER MENU STYLES ===== */
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
        }

        .notification-icon:hover {
            background: var(--gray-light);
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
            background: var(--gray-light);
        }

        .user-menu i {
            font-size: 1.3rem;
            color: var(--primary);
            transition: var(--transition);
        }

        .user-menu:hover i {
            color: var(--secondary);
        }

        /* User Menu Dropdown Styles */
        .user-menu-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 200px;
            background: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            display: none;
            margin-top: 5px;
            overflow: hidden;
        }

        .user-menu-dropdown.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .user-menu-header {
            padding: 15px;
            background: var(--gray-light);
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-menu-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--white);
            font-size: 1rem;
        }

        .user-menu-info h4 {
            font-size: 0.9rem;
            margin-bottom: 2px;
        }

        .user-menu-info p {
            font-size: 0.8rem;
            color: var(--gray);
        }

        .user-menu-items {
            list-style: none;
        }

        .user-menu-item {
            padding: 12px 15px;
            border-bottom: 1px solid var(--gray-light);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-menu-item:hover {
            background: var(--gray-light);
        }

        .user-menu-item i {
            font-size: 1rem;
            color: var(--gray);
            width: 20px;
            text-align: center;
        }

        .user-menu-item span {
            font-size: 0.9rem;
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
            background: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow-lg);
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
            border-bottom: 1px solid var(--gray-light);
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
            border-bottom: 1px solid var(--gray-light);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .notification-item:hover {
            background: var(--gray-light);
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
            color: var(--dark);
        }

        .notification-message {
            font-size: 0.85rem;
            color: var(--gray);
            margin-bottom: 5px;
        }

        .notification-time {
            font-size: 0.75rem;
            color: var(--gray);
        }

        .notification-empty {
            padding: 30px 20px;
            text-align: center;
            color: var(--gray);
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

        /* ===== STATS CARDS ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: var(--transition);
            border: 1px solid var(--gray-light);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), var(--secondary));
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }

        .stat-card:hover::before {
            transform: translateX(0);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: var(--white);
            flex-shrink: 0;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-icon.total {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
        }

        .stat-icon.active {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .stat-icon.applications {
            background: linear-gradient(135deg, var(--info), #2563EB);
        }

        .stat-icon.pending {
            background: linear-gradient(135deg, var(--warning), #D97706);
        }

        .stat-info h3 {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
            line-height: 1;
        }

        .stat-info p {
            color: var(--gray);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .stat-trend.positive {
            color: var(--success);
        }

        .stat-trend.negative {
            color: var(--danger);
        }

        /* ===== COMPACT STATISTICS SECTION ===== */
        .statistics-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .statistics-container {
            background: var(--white);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            position: relative;
            overflow: visible;
            min-height: 300px;
        }

        .statistics-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, transparent, rgba(139, 69, 19, 0.03));
            pointer-events: none;
        }

        .statistics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
        }

        .statistics-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .statistics-header i {
            color: var(--primary);
            font-size: 1.1rem;
        }

        /* Compact Stats Grid */
        .compact-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .compact-stat {
            text-align: center;
            padding: 1rem;
            background: rgba(139, 69, 19, 0.05);
            border-radius: 12px;
            transition: var(--transition);
            min-height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .compact-stat:hover {
            background: rgba(139, 69, 19, 0.1);
            transform: translateY(-3px);
        }

        .compact-stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.25rem;
            line-height: 1;
        }

        .compact-stat-label {
            font-size: 0.8rem;
            color: var(--gray);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Status Breakdown */
        .status-breakdown {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-light);
        }

        .status-breakdown h4 {
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
            color: var(--dark);
            font-weight: 600;
        }

        .status-bars {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-height: none;
            overflow: visible;
        }

        .status-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem 0.75rem;
            background: rgba(139, 69, 19, 0.05);
            border-radius: 8px;
            font-size: 0.85rem;
        }

        .status-bar-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .status-bar-count {
            font-weight: 600;
            color: var(--dark);
        }

        /* ===== JOBS MANAGEMENT ===== */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--gray-light);
        }

        .section-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .add-job-btn {
            background: linear-gradient(135deg, var(--success), #059669);
            color: var(--white);
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            transition: var(--transition);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
        }

        .add-job-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
        }

        /* ===== DATA TABLES ===== */
        .data-table-container {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            position: relative;
            animation: fadeInUp 0.6s ease-out;
            max-height: 500px;
            overflow-y: auto;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .data-table thead {
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .data-table th {
            padding: 1rem 1rem;
            text-align: left;
            color: var(--white);
            font-weight: 500;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .data-table tbody tr {
            border-bottom: 1px solid var(--gray-light);
            transition: var(--transition);
        }

        .data-table tbody tr:hover {
            background: rgba(139, 69, 19, 0.05);
        }

        .data-table td {
            padding: 0.75rem 1rem;
            color: var(--dark);
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Column Widths */
        .data-table th:nth-child(1),
        .data-table td:nth-child(1) { width: 20%; } /* Applicant */
        .data-table th:nth-child(2),
        .data-table td:nth-child(2) { width: 15%; } /* Position */
        .data-table th:nth-child(3),
        .data-table td:nth-child(3) { width: 20%; } /* Email */
        .data-table th:nth-child(4),
        .data-table td:nth-child(4) { width: 10%; } /* Experience */
        .data-table th:nth-child(5),
        .data-table td:nth-child(5) { width: 12%; } /* Status */
        .data-table th:nth-child(6),
        .data-table td:nth-child(6) { width: 13%; } /* Applied Date */
        .data-table th:nth-child(7),
        .data-table td:nth-child(7) { width: 10%; } /* Actions */

        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border-color: rgba(16, 185, 129, 0.2);
        }

        .status-inactive {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border-color: rgba(239, 68, 68, 0.2);
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
            border-color: rgba(245, 158, 11, 0.2);
        }

        .status-reviewed {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
            border-color: rgba(59, 130, 246, 0.2);
        }

        .status-shortlisted {
            background: rgba(168, 85, 247, 0.1);
            color: #8B5CF6;
            border-color: rgba(168, 85, 247, 0.2);
        }

        .status-hired {
            background: rgba(34, 197, 94, 0.1);
            color: #16A34A;
            border-color: rgba(34, 197, 94, 0.2);
        }

        /* Action Buttons - CLEARLY VISIBLE */
        .action-buttons {
            display: flex;
            gap: 0.4rem;
            justify-content: flex-start;
            flex-wrap: nowrap;
        }

        .action-btn {
            min-width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            color: var(--white);
            font-size: 0.8rem;
            padding: 0 0.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .view-btn {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .status-update-btn {
            background: linear-gradient(135deg, #8B5CF6, #7C3AED);
            min-width: 80px;
        }

        .download-btn {
            background: linear-gradient(135deg, var(--warning), #D97706);
        }

        .edit-btn {
            background: linear-gradient(135deg, var(--info), #2563EB);
        }

        .delete-btn {
            background: linear-gradient(135deg, var(--danger), #DC2626);
        }

        /* ===== MODALS ===== */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(5px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            padding: 1rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .modal-overlay.show {
            opacity: 1;
        }

        .modal-content {
            background: var(--white);
            border-radius: 16px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .modal-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .close-modal {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: var(--white);
            font-size: 1.5rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .close-modal:hover {
            background: var(--accent);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 1.5rem;
        }

        /* ===== MESSAGES ===== */
        .message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.25rem;
            border-radius: 10px;
            color: var(--white);
            z-index: 3000;
            animation: slideInRight 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow-lg);
            max-width: 350px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
        }

        .message.success {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .message.error {
            background: linear-gradient(135deg, var(--danger), #DC2626);
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

        /* Spinner */
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--gray-light);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.2);
            }
        }

        /* ===== COMPACT CHARTS ===== */
        .chart-container {
            height: 200px;
            position: relative;
            margin-top: 1rem;
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 1200px) {
            .statistics-section {
                grid-template-columns: 1fr;
                max-height: none;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            .mobile-menu-toggle {
                display: flex;
            }
            
            .sidebar-overlay.active {
                display: block;
            }
            
            .sidebar {
                transform: translateX(-100%);
                width: 250px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1.5rem;
                padding-top: 70px;
            }
            
            .top-nav {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
                padding: 1.25rem;
            }
            
            .nav-right {
                width: 100%;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .search-box input {
                width: 200px;
            }

            .notification-dropdown {
                position: fixed;
                top: 70px;
                right: 15px;
                left: 15px;
                width: auto;
                max-height: 60vh;
            }
            
            .user-menu-dropdown {
                position: fixed;
                top: 70px;
                right: 15px;
                left: 15px;
                width: auto;
            }

            .data-table-container {
                max-height: 400px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
                padding-top: 70px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .action-btn {
                width: 100%;
                min-height: 32px;
                height: 32px;
                font-size: 0.75rem;
            }

            .data-table {
                display: block;
                overflow-x: auto;
            }

            .data-table th,
            .data-table td {
                padding: 0.5rem 0.75rem;
                font-size: 0.85rem;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            
            .add-job-btn {
                width: 100%;
                justify-content: center;
            }

            .notification-dropdown {
                width: calc(100% - 30px);
                left: 15px;
                right: 15px;
            }
            
            .user-menu-dropdown {
                width: calc(100% - 30px);
                left: 15px;
                right: 15px;
            }

            .compact-stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 0.75rem;
                padding-top: 70px;
            }
            
            .top-nav {
                padding: 1rem;
            }
            
            .nav-right {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box input {
                width: 100%;
            }
            
            .modal-content {
                width: 95%;
                margin: 0.5rem;
                max-height: 90vh;
            }
            
            .modal-body {
                padding: 1rem;
            }

            .notification-dropdown {
                width: calc(100% - 20px);
                left: 10px;
                right: 10px;
            }

            .data-table-container {
                max-height: 350px;
            }
            
            .notification-user-container {
                align-self: flex-end;
                margin-left: auto;
            }
        }

        @media (max-width: 480px) {
            .logo-area h1 {
                font-size: 1.2rem;
            }
            
            .nav-left h2 {
                font-size: 1.5rem;
            }
            
            .stat-info h3 {
                font-size: 1.8rem;
            }
            
            .section-header h3,
            .statistics-header h3 {
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
                <div class="admin-avatar">AJ</div>
                <div class="admin-details">
                    <h3>Admin Joseph</h3>
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
                <li class="menu-item">
                    <a href="admin-customers.php">
                        <i class="fas fa-users"></i>
                        <span>Customers</span>
                    </a>
                </li>
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
                <li class="menu-item">
                    <a href="admin-careers.php" class="active">
                        <i class="fas fa-briefcase"></i>
                        <span>Career</span>
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
                    <a href="admin-logout.php" onclick="return confirmLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Top Navbar -->
            <div class="top-nav">
                <div class="nav-left">
                    <h2><i class="fas fa-briefcase"></i> Careers Dashboard</h2>
                </div>
                <div class="nav-right">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search..." id="searchInput">
                    </div>
                    <div class="notification-user-container">
                        <div class="notification-icon" id="notificationIcon">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge" id="notificationBadgeCount" style="display: none;">0</span>
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
                        <div class="user-menu" id="userMenuBtn">
                            <i class="fas fa-user-circle"></i>
                            <!-- User Menu Dropdown -->
                            <div class="user-menu-dropdown" id="userMenuDropdown">
                                <div class="user-menu-header">
                                    <div class="user-menu-avatar">J</div>
                                    <div class="user-menu-info">
                                        <h4>Joseph Admin</h4>
                                        <p>Administrator</p>
                                    </div>
                                </div>
                                <ul class="user-menu-items">
                                    <li class="user-menu-item" onclick="openEditProfileModal()">
                                        <i class="fas fa-user-edit"></i>
                                        <span>Edit My Profile</span>
                                    </li>
                                    <li class="user-menu-item" onclick="window.location.href='admin-settings.php'">
                                        <i class="fas fa-cog"></i>
                                        <span>Account Settings</span>
                                    </li>
                                    <li class="user-menu-item" onclick="window.location.href='admin-logout.php'">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-info">
                        <h3>15</h3>
                        <p>Active Jobs</p>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i> 12% growth
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon applications">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>128</h3>
                        <p>Total Applications</p>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i> 24% increase
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>18</h3>
                        <p>Pending Review</p>
                        <div class="stat-trend negative">
                            <i class="fas fa-arrow-down"></i> 5% decrease
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon active">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>32%</h3>
                        <p>Hiring Rate</p>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i> 8% increase
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compact Statistics Section -->
            <div class="statistics-section">
                <div class="statistics-container">
                    <div class="statistics-header">
                        <h3><i class="fas fa-chart-bar"></i> Application Overview</h3>
                    </div>
                    <div class="compact-stats-grid">
                        <div class="compact-stat">
                            <div class="compact-stat-number" id="statThisWeek">0</div>
                            <div class="compact-stat-label">This Week</div>
                        </div>
                        <div class="compact-stat">
                            <div class="compact-stat-number" id="statThisMonth">0</div>
                            <div class="compact-stat-label">This Month</div>
                        </div>
                        <div class="compact-stat">
                            <div class="compact-stat-number" id="statThisYear">0</div>
                            <div class="compact-stat-label">This Year</div>
                        </div>
                        <div class="compact-stat">
                            <div class="compact-stat-number" id="statGrowthRate">0%</div>
                            <div class="compact-stat-label">Growth Rate</div>
                        </div>
                    </div>
                    
                    <div class="status-breakdown">
                        <h4>Status Breakdown</h4>
                        <div class="status-bars" id="statusBarsContainer">
                            <!-- Status bars will be populated by JavaScript -->
                            <div style="text-align: center; padding: 1rem; color: var(--gray);">Loading...</div>
                        </div>
                    </div>
                </div>
                
                <div class="statistics-container">
                    <div class="statistics-header">
                        <h3><i class="fas fa-chart-pie"></i> Department Distribution</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="categoriesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Applications -->
            <div class="section-header">
                <h3><i class="fas fa-file-alt"></i> Recent Applications</h3>
                <button class="add-job-btn" onclick="refreshApplications()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>

            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Position</th>
                            <th>Email</th>
                            <th>Experience</th>
                            <th>Status</th>
                            <th>Applied Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="applicationsTableBody">
                        <!-- Applications will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Job Vacancies -->
            <div class="section-header">
                <h3><i class="fas fa-briefcase"></i> Job Vacancies</h3>
                <button class="add-job-btn" onclick="openJobModal()">
                    <i class="fas fa-plus"></i> Add New Job
                </button>
            </div>

            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Department</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Applications</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="jobsTableBody">
                        <!-- Jobs will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div id="jobModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-briefcase"></i> <span id="modalTitle">Add New Job</span></h3>
                <button class="close-modal" onclick="closeJobModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="jobForm" onsubmit="saveJob(event)">
                    <input type="hidden" id="jobId" name="id">
                    
                    <div style="display: grid; gap: 1.5rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label for="title" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark);">Job Title *</label>
                                <input type="text" id="title" name="title" required style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-light); border-radius: 8px; font-size: 0.9rem;" placeholder="e.g., Head Chef">
                            </div>
                            
                            <div>
                                <label for="department" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark);">Department *</label>
                                <select id="department" name="department" required style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-light); border-radius: 8px; font-size: 0.9rem;">
                                    <option value="">Select Department</option>
                                    <option value="Kitchen">Kitchen</option>
                                    <option value="Service">Service</option>
                                    <option value="Management">Management</option>
                                    <option value="Front of House">Front of House</option>
                                    <option value="Back of House">Back of House</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Finance">Finance</option>
                                    <option value="HR">HR</option>
                                </select>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label for="job_type" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark);">Job Type *</label>
                                <select id="job_type" name="job_type" required style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-light); border-radius: 8px; font-size: 0.9rem;">
                                    <option value="Full Time">Full Time</option>
                                    <option value="Part Time">Part Time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Internship">Internship</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="location" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark);">Location *</label>
                                <input type="text" id="location" name="location" required style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-light); border-radius: 8px; font-size: 0.9rem;" placeholder="e.g., Owerri, Imo State" value="Owerri, Imo State">
                            </div>
                        </div>
                        
                        <div>
                            <label for="salary_range" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark);">Salary Range</label>
                            <input type="text" id="salary_range" name="salary_range" style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-light); border-radius: 8px; font-size: 0.9rem;" placeholder="e.g., ₦150,000 - ₦250,000">
                        </div>
                        
                        <div>
                            <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark);">Job Description *</label>
                            <textarea id="description" name="description" required rows="4" style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-light); border-radius: 8px; font-size: 0.9rem; font-family: inherit;" placeholder="Describe the role, responsibilities, and what makes this position exciting..."></textarea>
                        </div>
                        
                        <div>
                            <label for="requirements" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark);">Requirements & Qualifications *</label>
                            <textarea id="requirements" name="requirements" required rows="4" style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-light); border-radius: 8px; font-size: 0.9rem; font-family: inherit;" placeholder="List the required qualifications, skills, and experience (one per line)"></textarea>
                            <small style="color: var(--gray); font-size: 0.85rem;">Enter each requirement on a new line</small>
                        </div>
                        
                        <div>
                            <label for="responsibilities" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark);">Key Responsibilities *</label>
                            <textarea id="responsibilities" name="responsibilities" required rows="4" style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-light); border-radius: 8px; font-size: 0.9rem; font-family: inherit;" placeholder="List the main responsibilities and duties (one per line)"></textarea>
                            <small style="color: var(--gray); font-size: 0.85rem;">Enter each responsibility on a new line</small>
                        </div>
                        
                        <div>
                            <label for="benefits" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark);">Benefits & Perks</label>
                            <textarea id="benefits" name="benefits" rows="3" style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-light); border-radius: 8px; font-size: 0.9rem; font-family: inherit;" placeholder="List the benefits and perks offered (one per line)"></textarea>
                            <small style="color: var(--gray); font-size: 0.85rem;">Enter each benefit on a new line</small>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label for="status" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark);">Status *</label>
                                <select id="status" name="status" required style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-light); border-radius: 8px; font-size: 0.9rem;">
                                    <option value="draft">Draft</option>
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="positions_available" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark);">Positions Available</label>
                                <input type="number" id="positions_available" name="positions_available" min="1" value="1" style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-light); border-radius: 8px; font-size: 0.9rem;">
                            </div>
                        </div>
                        
                        <div>
                            <label for="application_deadline" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark);">Application Deadline (Optional)</label>
                            <input type="date" id="application_deadline" name="application_deadline" style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-light); border-radius: 8px; font-size: 0.9rem;">
                        </div>
                    </div>
                    
                    <div class="form-actions" style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--gray-light);">
                        <button type="button" class="action-btn delete-btn" onclick="closeJobModal()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="action-btn status-update-btn">
                            <i class="fas fa-save"></i> Save Job
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Application View Modal -->
    <div id="applicationViewModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3><i class="fas fa-user"></i> Application Details</h3>
                <button class="close-modal" onclick="closeApplicationViewModal()">&times;</button>
            </div>
            <div class="modal-body" id="applicationViewContent">
                <div style="text-align: center; padding: 2rem;">
                    <div class="spinner" style="display: inline-block;"></div>
                    <p>Loading application details...</p>
                </div>
            </div>
        </div>
    </div>

    <div id="statusModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Update Status</h3>
                <button class="close-modal" onclick="closeStatusModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="statusForm" onsubmit="updateStatus(event)">
                    <input type="hidden" id="statusApplicationId">
                    <div style="margin-bottom: 1.5rem;">
                        <label for="newStatus" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark);">New Status *</label>
                        <select id="newStatus" name="status" required style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-light); border-radius: 8px; font-size: 0.9rem;">
                            <option value="pending">Pending</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="shortlisted">Shortlisted</option>
                            <option value="interview">Interview</option>
                            <option value="rejected">Rejected</option>
                            <option value="hired">Hired</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label for="statusNotes" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark);">Notes (Optional)</label>
                        <textarea id="statusNotes" name="notes" rows="3" style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-light); border-radius: 8px; font-size: 0.9rem; font-family: inherit;" placeholder="Add any notes about this status change..."></textarea>
                    </div>
                    <div class="form-actions" style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--gray-light);">
                        <button type="button" class="action-btn delete-btn" onclick="closeStatusModal()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="action-btn status-update-btn">
                            <i class="fas fa-check"></i> Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Message Display -->
    <div id="messageContainer"></div>

    <script>
        // API Base URL
        const API_BASE = '../api/careers-api.php';
        
        // State
        let currentJobs = [];
        let currentApplications = [];
        let currentNotifications = [];
        let careerStats = null;

        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const notificationIcon = document.getElementById('notificationIcon');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationList = document.getElementById('notificationList');
        const markAllReadBtn = document.getElementById('markAllRead');
        const notificationBadge = document.getElementById('notificationBadgeCount') || document.querySelector('.notification-badge');
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userMenuDropdown = document.getElementById('userMenuDropdown');

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            setupMobileMenu();
            loadDashboardData();
            
            // Auto-refresh notifications every 15 seconds for real-time updates
            setInterval(function() {
                loadNotifications();
            }, 15000); // 15 seconds for faster updates
            
            // Also refresh when the page becomes visible (user switches back to tab)
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    loadNotifications();
                    loadDashboardData();
                }
            });
        });

        // Load notifications separately (for auto-refresh)
        async function loadNotifications() {
            try {
                const notificationsResponse = await fetch(`${API_BASE}?action=get_notifications&unread_only=false&limit=50`);
                if (notificationsResponse.ok) {
                    const notifData = await notificationsResponse.json();
                    if (notifData.success && notifData.notifications) {
                        const oldUnreadCount = currentNotifications.filter(n => !n.read).length;
                        const oldNotificationIds = new Set(currentNotifications.map(n => n.id));
                        
                        currentNotifications = notifData.notifications.map(notif => ({
                            id: notif.id,
                            type: notif.type,
                            title: notif.title,
                            message: notif.message,
                            time: formatTimeAgo(notif.created_at),
                            read: notif.is_read === 1 || notif.is_read === true
                        }));
                        
                        const newUnreadCount = currentNotifications.filter(n => !n.read).length;
                        
                        // Always update notifications
                        renderNotifications();
                        updateNotificationBadge();
                        
                        // Show visual feedback if new unread notifications arrived
                        if (newUnreadCount > oldUnreadCount && notificationBadge) {
                            // Pulse animation for new notifications
                            notificationBadge.style.animation = 'pulse 0.5s ease-in-out 3';
                            setTimeout(() => {
                                if (notificationBadge) notificationBadge.style.animation = '';
                            }, 1500);
                        }
                    } else {
                        // No notifications or empty response
                        currentNotifications = [];
                        renderNotifications();
                        updateNotificationBadge();
                    }
                } else {
                    // Handle API errors (like 401 unauthorized)
                    if (notificationsResponse.status === 401) {
                        console.warn('Not authenticated - redirecting to login');
                        window.location.href = 'admin-login.php';
                    } else {
                        console.warn('Failed to load notifications:', notificationsResponse.status);
                    }
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
                // Don't break the page if notifications fail to load
            }
        }

        // Load all dashboard data from API
        async function loadDashboardData() {
            try {
                // Load stats, jobs, applications, and notifications in parallel
                const [statsResponse, jobsResponse, applicationsResponse, notificationsResponse] = await Promise.all([
                    fetch(`${API_BASE}?action=get_stats`),
                    fetch(`${API_BASE}?action=get_jobs`),
                    fetch(`${API_BASE}?action=get_applications`),
                    fetch(`${API_BASE}?action=get_notifications&unread_only=false&limit=50`)
                ]);

                // Process jobs first (needed for charts)
                if (jobsResponse.ok) {
                    const jobsData = await jobsResponse.json();
                    if (jobsData.success) {
                        currentJobs = jobsData.jobs.map(job => ({
                            ...job,
                            applications: 0 // Will be updated when we count applications per job
                        }));
                        // Count applications per job
                        await updateJobApplicationCounts();
                        renderJobsTable();
                    }
                }

                // Process stats (after jobs are loaded for charts)
                if (statsResponse.ok) {
                    const statsData = await statsResponse.json();
                    if (statsData.success) {
                        careerStats = statsData.stats;
                        updateStatsCards();
                        // Update charts after jobs are loaded
                        updateCharts();
                    }
                }

                // Process applications
                if (applicationsResponse.ok) {
                    const appsData = await applicationsResponse.json();
                    if (appsData.success) {
                        currentApplications = appsData.applications.map(app => ({
                            id: app.id,
                            name: app.applicant_name,
                            position: app.job_title || 'N/A',
                            email: app.applicant_email,
                            experience: app.years_experience ? `${app.years_experience} years` : '0 years',
                            status: app.status.charAt(0).toUpperCase() + app.status.slice(1),
                            appliedDate: app.applied_date
                        }));
                        renderApplicationsTable();
                        // Update overview and breakdown after applications are loaded
                        updateApplicationOverview();
                        updateStatusBreakdown();
                    }
                }

                // Process notifications
                if (notificationsResponse.ok) {
                    const notifData = await notificationsResponse.json();
                    if (notifData.success && notifData.notifications) {
                        currentNotifications = notifData.notifications.map(notif => ({
                            id: notif.id,
                            type: notif.type,
                            title: notif.title,
                            message: notif.message,
                            time: formatTimeAgo(notif.created_at),
                            read: notif.is_read === 1 || notif.is_read === true
                        }));
                        renderNotifications();
                        updateNotificationBadge();
                    } else {
                        // No notifications or error
                        currentNotifications = [];
                        renderNotifications();
                        updateNotificationBadge();
                    }
                } else {
                    // API error - log but don't break the page
                    console.warn('Failed to load notifications:', notificationsResponse.status);
                    currentNotifications = [];
                    renderNotifications();
                    updateNotificationBadge();
                }

                // Initialize charts after data is loaded (only if not already initialized)
                // Charts will be initialized/updated by updateCharts() which is called from updateStatsCards()
                // So we don't need to call initCharts() here
            } catch (error) {
                console.error('Error loading dashboard data:', error);
                showMessage('Error loading dashboard data. Please refresh the page.', 'error');
            }
        }

        // Update job application counts
        async function updateJobApplicationCounts() {
            for (let job of currentJobs) {
                try {
                    const response = await fetch(`${API_BASE}?action=get_applications&job_id=${job.id}`);
                    if (response.ok) {
                        const data = await response.json();
                        if (data.success) {
                            job.applications = data.applications.length;
                        }
                    }
                } catch (error) {
                    console.error(`Error counting applications for job ${job.id}:`, error);
                }
            }
        }

        // Update stats cards
        function updateStatsCards() {
            if (!careerStats) return;

            // Update Active Jobs
            const activeJobsCard = document.querySelector('.stat-card:nth-child(1) .stat-info h3');
            if (activeJobsCard && careerStats.jobs) {
                activeJobsCard.textContent = careerStats.jobs.active_jobs || 0;
            }

            // Update Total Applications
            const totalAppsCard = document.querySelector('.stat-card:nth-child(2) .stat-info h3');
            if (totalAppsCard && careerStats.applications) {
                totalAppsCard.textContent = careerStats.applications.total_applications || 0;
            }

            // Update Pending Review
            const pendingCard = document.querySelector('.stat-card:nth-child(3) .stat-info h3');
            if (pendingCard && careerStats.applications) {
                pendingCard.textContent = careerStats.applications.pending_applications || 0;
            }

            // Update Hiring Rate
            const hiringRateCard = document.querySelector('.stat-card:nth-child(4) .stat-info h3');
            if (hiringRateCard && careerStats.applications) {
                const total = careerStats.applications.total_applications || 0;
                const hired = careerStats.applications.hired_applications || 0;
                const rate = total > 0 ? Math.round((hired / total) * 100) : 0;
                hiringRateCard.textContent = `${rate}%`;
            }
            
            // Update Application Overview stats
            updateApplicationOverview();
            
            // Update Status Breakdown
            updateStatusBreakdown();
        }
        
        // Update Application Overview stats
        function updateApplicationOverview() {
            if (!currentApplications || currentApplications.length === 0) {
                document.getElementById('statThisWeek').textContent = '0';
                document.getElementById('statThisMonth').textContent = '0';
                document.getElementById('statThisYear').textContent = '0';
                document.getElementById('statGrowthRate').textContent = '0%';
                return;
            }
            
            const now = new Date();
            const oneWeekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
            const oneMonthAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
            const oneYearAgo = new Date(now.getTime() - 365 * 24 * 60 * 60 * 1000);
            
            const thisWeek = currentApplications.filter(app => {
                const appDate = new Date(app.appliedDate);
                return appDate >= oneWeekAgo;
            }).length;
            
            const thisMonth = currentApplications.filter(app => {
                const appDate = new Date(app.appliedDate);
                return appDate >= oneMonthAgo;
            }).length;
            
            const thisYear = currentApplications.filter(app => {
                const appDate = new Date(app.appliedDate);
                return appDate >= oneYearAgo;
            }).length;
            
            // Calculate growth rate (comparing this month to last month)
            const twoMonthsAgo = new Date(now.getTime() - 60 * 24 * 60 * 60 * 1000);
            const lastMonth = currentApplications.filter(app => {
                const appDate = new Date(app.appliedDate);
                return appDate >= twoMonthsAgo && appDate < oneMonthAgo;
            }).length;
            
            const growthRate = lastMonth > 0 ? Math.round(((thisMonth - lastMonth) / lastMonth) * 100) : (thisMonth > 0 ? 100 : 0);
            
            document.getElementById('statThisWeek').textContent = thisWeek;
            document.getElementById('statThisMonth').textContent = thisMonth;
            document.getElementById('statThisYear').textContent = thisYear;
            document.getElementById('statGrowthRate').textContent = `${growthRate}%`;
        }
        
        // Update Status Breakdown
        function updateStatusBreakdown() {
            const statusBarsContainer = document.getElementById('statusBarsContainer');
            if (!statusBarsContainer) return;
            
            if (!currentApplications || currentApplications.length === 0) {
                statusBarsContainer.innerHTML = '<div style="text-align: center; padding: 1rem; color: var(--gray);">No applications yet</div>';
                return;
            }
            
            // Count applications by status
            const statusCounts = {
                'Pending': 0,
                'Reviewed': 0,
                'Shortlisted': 0,
                'Interview': 0,
                'Rejected': 0,
                'Hired': 0
            };
            
            currentApplications.forEach(app => {
                // Handle case-insensitive status matching
                const status = app.status.charAt(0).toUpperCase() + app.status.slice(1).toLowerCase();
                if (statusCounts.hasOwnProperty(status)) {
                    statusCounts[status]++;
                } else {
                    // If status doesn't match exactly, try to find a match
                    const normalizedStatus = status.toLowerCase();
                    for (const key in statusCounts) {
                        if (key.toLowerCase() === normalizedStatus) {
                            statusCounts[key]++;
                            break;
                        }
                    }
                }
            });
            
            // Status colors
            const statusColors = {
                'Pending': 'var(--warning)',
                'Reviewed': 'var(--info)',
                'Shortlisted': '#8B5CF6',
                'Interview': '#3B82F6',
                'Rejected': 'var(--danger)',
                'Hired': 'var(--success)'
            };
            
            // Build status bars HTML
            let statusBarsHTML = '';
            Object.keys(statusCounts).forEach(status => {
                if (statusCounts[status] > 0) {
                    statusBarsHTML += `
                        <div class="status-bar">
                            <div class="status-bar-label">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: ${statusColors[status]};"></div>
                                <span>${status}</span>
                            </div>
                            <div class="status-bar-count">${statusCounts[status]}</div>
                        </div>
                    `;
                }
            });
            
            if (statusBarsHTML === '') {
                statusBarsHTML = '<div style="text-align: center; padding: 1rem; color: var(--gray);">No applications with status data</div>';
            }
            
            statusBarsContainer.innerHTML = statusBarsHTML;
        }

        // Update charts with real data
        function updateCharts() {
            // Only update if we have jobs data
            if (!currentJobs || currentJobs.length === 0) {
                return;
            }

            // Calculate department distribution
            const departmentCounts = {};
            currentJobs.forEach(job => {
                departmentCounts[job.department] = (departmentCounts[job.department] || 0) + 1;
            });

            const ctx = document.getElementById('categoriesChart');
            if (!ctx) return;

            const existingChart = Chart.getChart(ctx);
            
            if (existingChart) {
                // Update existing chart
                const labels = Object.keys(departmentCounts);
                const data = Object.values(departmentCounts);
                const colors = ['#8B4513', '#D2691E', '#FFA500', '#10B981', '#3B82F6', '#8B5CF6', '#EF4444'];
                
                existingChart.data.labels = labels;
                existingChart.data.datasets[0].data = data;
                existingChart.data.datasets[0].backgroundColor = colors.slice(0, labels.length);
                existingChart.update();
            } else {
                // Initialize chart if it doesn't exist
                initCharts();
            }
        }

        // Format time ago
        function formatTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);

            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} min ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hour${Math.floor(diffInSeconds / 3600) > 1 ? 's' : ''} ago`;
            if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} day${Math.floor(diffInSeconds / 86400) > 1 ? 's' : ''} ago`;
            return date.toLocaleDateString();
        }

        // Mobile Menu Functions
        function setupMobileMenu() {
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
        }

        // Logout confirmation function
        function confirmLogout() {
            return confirm('Are you sure you want to logout?');
        }

        // Notification Functions
        function toggleNotifications() {
            if (notificationIcon) {
                notificationDropdown.classList.toggle('active');
                // Close user menu dropdown if open
                if (userMenuDropdown) {
                    userMenuDropdown.classList.remove('active');
                }
            }
        }

        async function markAllAsRead() {
            try {
                // Mark all unread notifications as read
                const unreadNotifications = currentNotifications.filter(n => !n.read);
                const promises = unreadNotifications.map(notif => 
                    fetch(`${API_BASE}?action=mark_notification_read&id=${notif.id}`, {
                        method: 'POST'
                    })
                );
                
                await Promise.all(promises);
                await loadDashboardData(); // Reload to get updated notifications
                showMessage('All notifications marked as read', 'success');
            } catch (error) {
                console.error('Error marking notifications as read:', error);
                showMessage('Error marking notifications as read', 'error');
            }
        }

        function renderNotifications() {
            if (!notificationList) return;
            
            notificationList.innerHTML = '';
            
            if (currentNotifications.length === 0) {
                notificationList.innerHTML = '<div class="notification-empty">No notifications</div>';
                return;
            }
            
            currentNotifications.forEach(notification => {
                const item = document.createElement('li');
                item.className = `notification-item ${notification.read ? '' : 'unread'}`;
                item.innerHTML = `
                    <div class="notification-dot" style="${!notification.read ? 'background: var(--primary)' : 'background: transparent'}"></div>
                    <div class="notification-content">
                        <div class="notification-title">${escapeHtml(notification.title)}</div>
                        <div class="notification-message">${escapeHtml(notification.message)}</div>
                        <div class="notification-time">${escapeHtml(notification.time)}</div>
                    </div>
                `;
                
                if (!notification.read) {
                    item.addEventListener('click', function() {
                        markAsRead(notification.id);
                    });
                }
                
                notificationList.appendChild(item);
            });
        }

        async function markAsRead(notificationId) {
            try {
                const response = await fetch(`${API_BASE}?action=mark_notification_read&id=${notificationId}`, {
                    method: 'POST'
                });
                
                const result = await response.json();
                if (result.success) {
                    const notification = currentNotifications.find(n => n.id === notificationId);
                    if (notification) {
                        notification.read = true;
                        renderNotifications();
                        updateNotificationBadge();
                    }
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
                // Still update locally even if API call fails
                const notification = currentNotifications.find(n => n.id === notificationId);
                if (notification) {
                    notification.read = true;
                    renderNotifications();
                    updateNotificationBadge();
                }
            }
        }

        function updateNotificationBadge() {
            const unreadCount = currentNotifications.filter(n => !n.read).length;
            const badge = document.getElementById('notificationBadgeCount') || document.querySelector('.notification-badge');
            if (badge) {
                badge.textContent = unreadCount;
                badge.style.display = (unreadCount > 0) ? 'flex' : 'none';
            }
        }

        // User Menu functionality
        if (userMenuBtn) {
            userMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userMenuDropdown.classList.toggle('active');
                // Close notification dropdown if open
                notificationDropdown.classList.remove('active');
            });
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            // Close notification dropdown
            if (notificationIcon && !notificationIcon.contains(e.target) && notificationDropdown && !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.remove('active');
            }
            
            // Close user menu dropdown
            if (userMenuBtn && !userMenuBtn.contains(e.target) && userMenuDropdown && !userMenuDropdown.contains(e.target)) {
                userMenuDropdown.classList.remove('active');
            }
        });

        // Edit Profile Modal function (placeholder)
        function openEditProfileModal() {
            showMessage('Edit profile feature coming soon', 'info');
            if (userMenuDropdown) {
                userMenuDropdown.classList.remove('active');
            }
        }

        // Original Functions
        function initCharts() {
            const ctx = document.getElementById('categoriesChart');
            if (!ctx) return;
            
            // Check if a chart already exists and destroy it
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }
            
            if (currentJobs.length > 0) {
                // Calculate department distribution
                const departmentCounts = {};
                currentJobs.forEach(job => {
                    departmentCounts[job.department] = (departmentCounts[job.department] || 0) + 1;
                });

                const labels = Object.keys(departmentCounts);
                const data = Object.values(departmentCounts);
                const colors = ['#8B4513', '#D2691E', '#FFA500', '#10B981', '#3B82F6', '#8B5CF6', '#EF4444'];

                // Only create chart if we have data
                if (labels.length > 0 && data.length > 0) {
                    new Chart(ctx.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: data,
                                backgroundColor: colors.slice(0, labels.length),
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { font: { size: 10 } } }
                            }
                        }
                    });
                }
            }
        }

        function renderTables() {
            renderJobsTable();
            renderApplicationsTable();
        }

        function renderJobsTable() {
            const tbody = document.getElementById('jobsTableBody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            
            currentJobs.forEach(job => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${job.title}</td>
                    <td>${job.department}</td>
                    <td>${job.type}</td>
                    <td><span class="status-badge ${job.status === 'active' ? 'status-active' : 'status-inactive'}">${job.status}</span></td>
                    <td>${job.applications}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn edit-btn" onclick="editJob(${job.id})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteJob(${job.id})" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function renderApplicationsTable() {
            const tbody = document.getElementById('applicationsTableBody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            
            if (currentApplications.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: var(--gray);">No applications found.</td></tr>';
                return;
            }
            
            currentApplications.forEach(app => {
                const date = new Date(app.appliedDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                const statusClass = app.status.toLowerCase().replace(/\s+/g, '-');
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${escapeHtml(app.name)}</td>
                    <td>${escapeHtml(app.position)}</td>
                    <td style="font-size: 0.85rem;">${escapeHtml(app.email)}</td>
                    <td>${escapeHtml(app.experience)}</td>
                    <td><span class="status-badge status-${statusClass}">${escapeHtml(app.status)}</span></td>
                    <td>${date}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view-btn" onclick="viewApplication(${app.id})" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn status-update-btn" onclick="updateApplicationStatus(${app.id})" title="Update Status">
                                <i class="fas fa-edit"></i> Update
                            </button>
                            <button class="action-btn download-btn" onclick="downloadCV(${app.id})" title="Download CV">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Application Functions
        function updateApplicationStatus(appId) {
            showMessage(`Opening status update for application #${appId}`, 'info');
        }

        async function viewApplication(appId) {
            const modal = document.getElementById('applicationViewModal');
            const content = document.getElementById('applicationViewContent');
            
            if (!modal || !content) {
                showMessage('Application view modal not found', 'error');
                return;
            }
            
            // Show modal with loading state
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
            
            content.innerHTML = '<div style="text-align: center; padding: 2rem;"><div class="spinner" style="display: inline-block;"></div><p>Loading application details...</p></div>';
            
            try {
                const response = await fetch(`${API_BASE}?action=get_application&id=${appId}`);
                const result = await response.json();
                
                if (result.success) {
                    const app = result.application;
                    const appliedDate = new Date(app.applied_date).toLocaleString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    content.innerHTML = `
                        <div style="display: grid; gap: 1.5rem;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; padding: 1rem; background: var(--gray-light); border-radius: 8px;">
                                <div>
                                    <strong style="color: var(--gray); font-size: 0.85rem; text-transform: uppercase;">Applicant Name</strong>
                                    <p style="margin: 0.5rem 0 0 0; font-size: 1.1rem; font-weight: 600;">${escapeHtml(app.applicant_name)}</p>
                                </div>
                                <div>
                                    <strong style="color: var(--gray); font-size: 0.85rem; text-transform: uppercase;">Position Applied</strong>
                                    <p style="margin: 0.5rem 0 0 0; font-size: 1.1rem; font-weight: 600;">${escapeHtml(app.job_title || 'N/A')}</p>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <strong style="color: var(--gray); font-size: 0.85rem; text-transform: uppercase;">Email</strong>
                                    <p style="margin: 0.5rem 0 0 0;">${escapeHtml(app.applicant_email)}</p>
                                </div>
                                <div>
                                    <strong style="color: var(--gray); font-size: 0.85rem; text-transform: uppercase;">Phone</strong>
                                    <p style="margin: 0.5rem 0 0 0;">${escapeHtml(app.applicant_phone || 'N/A')}</p>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                                <div>
                                    <strong style="color: var(--gray); font-size: 0.85rem; text-transform: uppercase;">Experience</strong>
                                    <p style="margin: 0.5rem 0 0 0;">${app.years_experience || 0} years</p>
                                </div>
                                <div>
                                    <strong style="color: var(--gray); font-size: 0.85rem; text-transform: uppercase;">Status</strong>
                                    <p style="margin: 0.5rem 0 0 0;"><span class="status-badge status-${app.status.toLowerCase()}">${app.status.charAt(0).toUpperCase() + app.status.slice(1)}</span></p>
                                </div>
                                <div>
                                    <strong style="color: var(--gray); font-size: 0.85rem; text-transform: uppercase;">Applied Date</strong>
                                    <p style="margin: 0.5rem 0 0 0;">${appliedDate}</p>
                                </div>
                            </div>
                            
                            ${app.current_position ? `
                            <div>
                                <strong style="color: var(--gray); font-size: 0.85rem; text-transform: uppercase;">Current Position</strong>
                                <p style="margin: 0.5rem 0 0 0;">${escapeHtml(app.current_position)}</p>
                            </div>
                            ` : ''}
                            
                            ${app.current_company ? `
                            <div>
                                <strong style="color: var(--gray); font-size: 0.85rem; text-transform: uppercase;">Current Company</strong>
                                <p style="margin: 0.5rem 0 0 0;">${escapeHtml(app.current_company)}</p>
                            </div>
                            ` : ''}
                            
                            ${app.cover_letter ? `
                            <div>
                                <strong style="color: var(--gray); font-size: 0.85rem; text-transform: uppercase;">Cover Letter</strong>
                                <div style="margin: 0.5rem 0 0 0; padding: 1rem; background: var(--gray-light); border-radius: 8px; white-space: pre-wrap; line-height: 1.6;">${escapeHtml(app.cover_letter)}</div>
                            </div>
                            ` : ''}
                            
                            ${app.resume_path ? `
                            <div>
                                <strong style="color: var(--gray); font-size: 0.85rem; text-transform: uppercase;">Resume</strong>
                                <p style="margin: 0.5rem 0 0 0;">
                                    <a href="../${escapeHtml(app.resume_path)}" target="_blank" style="color: var(--primary); text-decoration: none;">
                                        <i class="fas fa-download"></i> Download Resume
                                    </a>
                                </p>
                            </div>
                            ` : ''}
                        </div>
                        
                        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--gray-light);">
                            <button class="action-btn delete-btn" onclick="closeApplicationViewModal()">
                                <i class="fas fa-times"></i> Close
                            </button>
                            <button class="action-btn status-update-btn" onclick="closeApplicationViewModal(); updateApplicationStatus(${app.id});">
                                <i class="fas fa-edit"></i> Update Status
                            </button>
                        </div>
                    `;
                } else {
                    content.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--danger);"><p>Application not found</p><button class="action-btn delete-btn" onclick="closeApplicationViewModal()">Close</button></div>';
                }
            } catch (error) {
                console.error('Error loading application:', error);
                content.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--danger);"><p>Error loading application details</p><button class="action-btn delete-btn" onclick="closeApplicationViewModal()">Close</button></div>';
            }
        }
        
        function closeApplicationViewModal() {
            const modal = document.getElementById('applicationViewModal');
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        }

        function downloadCV(appId) {
            showMessage(`Downloading CV for application #${appId}`, 'info');
        }

        // Job Functions
        function openJobModal() {
            // Reset form
            const form = document.getElementById('jobForm');
            if (form) {
                form.reset();
            }
            const jobIdInput = document.getElementById('jobId');
            if (jobIdInput) {
                jobIdInput.value = '';
            }
            const modalTitle = document.getElementById('modalTitle');
            if (modalTitle) {
                modalTitle.textContent = 'Add New Job';
            }
            
            // Show modal
            const modal = document.getElementById('jobModal');
            if (modal) {
                modal.style.display = 'flex';
                // Trigger animation
                setTimeout(() => {
                    modal.classList.add('show');
                }, 10);
            }
        }

        async function editJob(jobId) {
            try {
                const response = await fetch(`${API_BASE}?action=get_job&id=${jobId}`);
                const result = await response.json();
                
                if (result.success) {
                    const job = result.job;
                    
                    // Populate form fields
                    document.getElementById('jobId').value = job.id;
                    document.getElementById('modalTitle').textContent = 'Edit Job';
                    document.getElementById('title').value = job.title || '';
                    document.getElementById('department').value = job.department || '';
                    document.getElementById('job_type').value = job.job_type || 'Full Time';
                    document.getElementById('location').value = job.location || '';
                    document.getElementById('salary_range').value = job.salary_range || '';
                    document.getElementById('description').value = job.description || '';
                    document.getElementById('requirements').value = job.requirements || '';
                    document.getElementById('responsibilities').value = job.responsibilities || '';
                    document.getElementById('benefits').value = job.benefits || '';
                    document.getElementById('status').value = job.status || 'active';
                    document.getElementById('positions_available').value = job.positions_available || 1;
                    
                    if (job.application_deadline) {
                        document.getElementById('application_deadline').value = job.application_deadline;
                    }
                    
                    // Show modal
                    const modal = document.getElementById('jobModal');
                    if (modal) {
                        modal.style.display = 'flex';
                        setTimeout(() => {
                            modal.classList.add('show');
                        }, 10);
                    }
                } else {
                    showMessage('Job not found', 'error');
                }
            } catch (error) {
                console.error('Error loading job:', error);
                showMessage('Error loading job details', 'error');
            }
        }

        async function deleteJob(jobId) {
            if (!confirm('Are you sure you want to delete this job? This action cannot be undone.')) {
                return;
            }

            try {
                const response = await fetch(`${API_BASE}?action=delete_job&id=${jobId}`, {
                    method: 'POST'
                });

                const result = await response.json();
                if (result.success) {
                    showMessage('Job deleted successfully', 'success');
                    loadDashboardData(); // Reload data
                } else {
                    showMessage(result.message || 'Failed to delete job', 'error');
                }
            } catch (error) {
                console.error('Error deleting job:', error);
                showMessage('Error deleting job', 'error');
            }
        }

        function refreshApplications() {
            showMessage('Refreshing applications...', 'info');
            setTimeout(() => {
                showMessage('Applications refreshed', 'success');
            }, 1000);
        }

        // Utility
        function showMessage(text, type) {
            const container = document.getElementById('messageContainer');
            if (!container) return;
            
            const message = document.createElement('div');
            message.className = `message ${type}`;
            message.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'info'}"></i> ${text}`;
            container.appendChild(message);
            
            setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 300);
            }, 3000);
        }

        // Close modals functions
        function closeJobModal() {
            const modal = document.getElementById('jobModal');
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        }
        
        // Close modal when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            const jobModal = document.getElementById('jobModal');
            if (jobModal) {
                jobModal.addEventListener('click', function(e) {
                    if (e.target === jobModal) {
                        closeJobModal();
                    }
                });
            }
            
            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const jobModal = document.getElementById('jobModal');
                    const applicationViewModal = document.getElementById('applicationViewModal');
                    const statusModal = document.getElementById('statusModal');
                    
                    if (jobModal && jobModal.style.display === 'flex') {
                        closeJobModal();
                    } else if (applicationViewModal && applicationViewModal.style.display === 'flex') {
                        closeApplicationViewModal();
                    } else if (statusModal && statusModal.style.display === 'flex') {
                        closeStatusModal();
                    }
                }
            });
            
            // Close application view modal when clicking outside
            const applicationViewModal = document.getElementById('applicationViewModal');
            if (applicationViewModal) {
                applicationViewModal.addEventListener('click', function(e) {
                    if (e.target === applicationViewModal) {
                        closeApplicationViewModal();
                    }
                });
            }
            
            // Close status modal when clicking outside
            const statusModal = document.getElementById('statusModal');
            if (statusModal) {
                statusModal.addEventListener('click', function(e) {
                    if (e.target === statusModal) {
                        closeStatusModal();
                    }
                });
            }
        });

        function closeStatusModal() {
            const modal = document.getElementById('statusModal');
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        }

        async function saveJob(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const jobId = document.getElementById('jobId').value;
            
            const data = {};
            formData.forEach((value, key) => {
                // Skip the 'id' field when creating a new job
                if (key !== 'id' || jobId) {
                    data[key] = value;
                }
            });
            
            // Remove id from data if it's empty (new job)
            if (!jobId) {
                delete data.id;
            }

            try {
                const url = jobId 
                    ? `${API_BASE}?action=update_job&id=${jobId}`
                    : `${API_BASE}?action=create_job`;
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {
                    showMessage(jobId ? 'Job updated successfully' : 'Job created successfully', 'success');
                    closeJobModal();
                    loadDashboardData(); // Reload data
                } else {
                    showMessage(result.message || 'Failed to save job', 'error');
                }
            } catch (error) {
                console.error('Error saving job:', error);
                showMessage('Error saving job: ' + error.message, 'error');
            }
        }

        function updateStatus(event) {
            event.preventDefault();
            showMessage('Status update feature - implement form', 'info');
        }

        // Event Listeners
        if (notificationIcon) {
            notificationIcon.addEventListener('click', toggleNotifications);
        }

        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', markAllAsRead);
        }

        // Initialize notification dropdown
        if (notificationIcon) {
            notificationIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('active');
            });
        }
    </script>
</body>
</html>