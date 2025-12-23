<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit;
}

// Database connection
require_once 'db_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../images/logo3.png">
    <title>Joseph's Pot - Orders Management Dashboard</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #8b4513;
            --primary-light: #a0522d;
            --primary-dark: #654321;
            --secondary: #d2691e;
            --accent: #ffe4b5;
            --light: #fff8dc;
            --white: #ffffff;
            --dark: #333333;
            --gray: #666666;
            --light-gray: #f5f5f5;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
            --info: #2196f3;
            --shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.15);
            --radius: 12px;
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
            color: var(--dark);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Notification messages */
        .notification-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            box-shadow: var(--shadow);
            animation: slideInRight 0.3s ease, fadeOut 0.3s ease 3s forwards;
        }

        .notification-message.success {
            background: var(--success);
        }

        .notification-message.error {
            background: var(--danger);
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

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateX(100%);
            }
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

        /* Custom scrollbar for sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
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

        /* Real-time Clock */
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
            color: var(--gray);
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
            color: var(--gray);
        }

        /* Notification and User Menu */
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
            background: var(--light-gray);
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
            background: var(--light-gray);
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
            top: 50px;
            right: 0;
            width: 350px;
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

        .notification-header {
            padding: 15px 20px;
            background: var(--primary);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-header h4 {
            font-size: 1rem;
            font-weight: 600;
        }

        .mark-all-read {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: var(--transition);
        }

        .mark-all-read:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .notification-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--light-gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .notification-item:hover {
            background: var(--light-gray);
        }

        .notification-item.unread {
            background: rgba(33, 150, 243, 0.05);
        }

        .notification-title {
            font-weight: 500;
            margin-bottom: 5px;
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

        /* User Dropdown Menu */
        .user-dropdown {
            position: absolute;
            top: 50px;
            right: 0;
            width: 200px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
            overflow: hidden;
        }

        .user-dropdown.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .user-dropdown-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--dark);
            text-decoration: none;
            transition: var(--transition);
        }

        .user-dropdown-item:hover {
            background: var(--light-gray);
        }

        .user-dropdown-item i {
            margin-right: 10px;
            color: var(--primary);
            width: 20px;
        }

        .user-dropdown-divider {
            height: 1px;
            background: var(--light-gray);
            margin: 5px 0;
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        .stat-card.total-orders .stat-icon {
            background: rgba(139, 69, 19, 0.1);
            color: var(--primary);
        }

        .stat-card.pending-orders .stat-icon {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }

        .stat-card.revenue .stat-icon {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }

        .stat-card.today-orders .stat-icon {
            background: rgba(33, 150, 243, 0.1);
            color: var(--info);
        }

        .stat-content h3 {
            font-size: 0.9rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .stat-content .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .stat-content .trend {
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .trend.positive {
            color: var(--success);
        }

        .trend.negative {
            color: var(--danger);
        }

        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .chart-card {
            background: var(--white);
            padding: 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .chart-header h3 {
            color: var(--primary);
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-actions {
            display: flex;
            gap: 10px;
        }

        .chart-action-btn {
            padding: 8px 15px;
            border: 1px solid var(--light-gray);
            background: var(--white);
            border-radius: 6px;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .chart-action-btn.active {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        /* Proof of Payment Button */
        .proof-btn {
            margin-left: 8px;
            padding: 4px 8px;
            background: var(--info);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .proof-btn:hover {
            background: #1976d2;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        /* Orders Table */
        .orders-section {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 40px;
        }

        .section-header {
            padding: 25px 30px;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header h2 {
            color: var(--primary);
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filters {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-select {
            padding: 10px 20px;
            border: 2px solid var(--light-gray);
            border-radius: 8px;
            background: var(--white);
            color: var(--dark);
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transition);
            min-width: 150px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .table-container {
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table thead {
            background: var(--light-gray);
        }

        .orders-table th {
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            white-space: nowrap;
            border-bottom: 2px solid var(--primary);
        }

        .orders-table td {
            padding: 18px 20px;
            border-bottom: 1px solid var(--light-gray);
            vertical-align: middle;
        }

        .orders-table tbody tr {
            transition: var(--transition);
        }

        .orders-table tbody tr:hover {
            background: var(--light);
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning);
            border: 1px solid rgba(255, 152, 0, 0.3);
        }

        .status-processing {
            background: rgba(33, 150, 243, 0.1);
            color: var(--info);
            border: 1px solid rgba(33, 150, 243, 0.3);
        }

        .status-completed {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .status-cancelled {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        /* Payment Badges */
        .payment-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            background: rgba(139, 69, 19, 0.1);
            color: var(--primary);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-view {
            background: rgba(139, 69, 19, 0.1);
            color: var(--primary);
        }

        .btn-view:hover {
            background: var(--primary);
            color: var(--white);
        }

        .btn-process {
            background: rgba(33, 150, 243, 0.1);
            color: var(--info);
        }

        .btn-process:hover {
            background: var(--info);
            color: var(--white);
        }

        .btn-complete {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }

        .btn-complete:hover {
            background: var(--success);
            color: var(--white);
        }

        .btn-cancel {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }

        .btn-cancel:hover {
            background: var(--danger);
            color: var(--white);
        }

        /* Print Button Style */
        .btn-print {
            background: rgba(33, 150, 243, 0.1);
            color: var(--info);
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-print:hover {
            background: var(--info);
            color: var(--white);
        }

        /* Modal Actions Container */
        .modal-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Delete button style */
        .btn-delete {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }

        .btn-delete:hover {
            background: var(--danger);
            color: var(--white);
        }

        /* Order Details Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-content {
            background: var(--white);
            width: 90%;
            max-width: 900px;
            max-height: 90vh;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 25px 30px;
            color: var(--white);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-modal {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: var(--white);
            font-size: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .close-modal:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px;
            overflow-y: auto;
            max-height: calc(90vh - 90px);
        }

        .order-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .detail-card {
            background: var(--light);
            padding: 25px;
            border-radius: var(--radius);
            border-left: 4px solid var(--primary);
        }

        .detail-card h3 {
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-item {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .detail-item:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            font-weight: 500;
            color: var(--gray);
        }

        .detail-value {
            font-weight: 500;
            color: var(--dark);
            text-align: right;
            max-width: 200px;
        }

        /* Footer */
        .admin-footer {
            text-align: center;
            padding: 20px;
            color: var(--gray);
            font-size: 0.9rem;
            border-top: 1px solid var(--light-gray);
            background: var(--white);
        }

        /* Print-specific styles */
        @media print {
            body * {
                visibility: hidden;
            }
            
            .modal-content,
            .modal-content * {
                visibility: visible;
            }
            
            .modal-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
                border: none;
                max-width: 100%;
                max-height: 100%;
            }
            
            .modal-header {
                background: #8b4513 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .btn-print,
            .close-modal {
                display: none !important;
            }
            
            .modal-body {
                padding: 20px;
                max-height: none;
                overflow: visible;
            }
            
            .order-details-grid {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            
            .detail-card {
                break-inside: avoid;
                page-break-inside: avoid;
                border: 1px solid #ddd;
                margin-bottom: 15px;
            }
            
            /* Ensure proper spacing for printed pages */
            @page {
                margin: 0.5cm;
            }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .charts-section {
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
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .charts-section {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .chart-card {
                padding: 20px;
            }
            
            .notification-dropdown {
                width: 300px;
                right: -50px;
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
            
            .notification-user-container {
                align-self: flex-end;
                margin-left: auto;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .filters {
                flex-wrap: wrap;
            }
            
            .orders-table {
                font-size: 0.9rem;
            }
            
            .orders-table th,
            .orders-table td {
                padding: 12px 10px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .notification-dropdown {
                width: 280px;
                right: -70px;
            }
            
            .modal-actions {
                flex-direction: column;
                align-items: flex-end;
                gap: 5px;
            }
            
            .btn-print {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .real-time-clock {
                padding: 10px 15px;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .clock-container {
                width: 100%;
                justify-content: space-between;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .chart-card {
                padding: 15px;
            }
            
            .filters {
                flex-direction: column;
                width: 100%;
            }
            
            .filter-select {
                width: 100%;
            }
            
            .notification-dropdown {
                width: 250px;
                right: -100px;
            }
            
            .modal-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .modal-actions {
                align-self: flex-end;
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
            
            .notification-dropdown {
                width: 220px;
                right: -120px;
            }
            
            .modal-header {
                padding: 20px;
            }
            
            .modal-body {
                padding: 15px;
            }
            
            .detail-card {
                padding: 15px;
            }
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Loading Skeleton */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 4px;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
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
                    <a href="admin-orders.php" class="active">
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
                    <a href="order-online.php">
                        <i class="fas fa-store"></i>
                        <span>Back to Store</span>
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
            <!-- Real-time Clock -->
            <div class="real-time-clock">
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
                <h2>Orders Dashboard</h2>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="globalSearch" placeholder="Search orders...">
                    </div>
                    <div class="notification-user-container">
                        <!-- Notification Dropdown -->
                        <div class="notification-icon" id="notificationIcon">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                            <div class="notification-dropdown" id="notificationDropdown">
                                <div class="notification-header">
                                    <h4>Notifications</h4>
                                    <button class="mark-all-read" id="markAllRead">Mark all as read</button>
                                </div>
                                <div class="notification-list" id="notificationList">
                                    <!-- Notifications will be dynamically added here -->
                                    <div class="notification-empty" id="emptyNotifications">
                                        <i class="fas fa-bell-slash" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                                        <p>No new notifications</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- User Dropdown Menu -->
                        <div class="user-menu" id="userMenu">
                            <i class="fas fa-user-circle"></i>
                            <div class="user-dropdown" id="userDropdown">
                                <a href="admin-profile.php" class="user-dropdown-item">
                                    <i class="fas fa-user"></i>
                                    <span>My Profile</span>
                                </a>
                                <a href="admin-settings.php" class="user-dropdown-item">
                                    <i class="fas fa-cog"></i>
                                    <span>Settings</span>
                                </a>
                                <div class="user-dropdown-divider"></div>
                                <a href="admin-logout.php" class="user-dropdown-item" onclick="return confirmLogout()">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card total-orders">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Orders</h3>
                        <div class="value" id="totalOrders">0</div>
                        <div class="trend positive">
                            <i class="fas fa-arrow-up"></i> 12% from last month
                        </div>
                    </div>
                </div>
                
                <div class="stat-card pending-orders">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Pending Orders</h3>
                        <div class="value" id="pendingOrders">0</div>
                        <div class="trend positive">
                            <i class="fas fa-bell"></i> Needs attention
                        </div>
                    </div>
                </div>
                
                <div class="stat-card revenue">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Revenue</h3>
                        <div class="value" id="totalRevenue">₦0</div>
                        <div class="trend positive">
                            <i class="fas fa-arrow-up"></i> 18% from last month
                        </div>
                    </div>
                </div>
                
                <div class="stat-card today-orders">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Today's Orders</h3>
                        <div class="value" id="todayOrders">0</div>
                        <div class="trend positive">
                            <i class="fas fa-chart-line"></i> 5 today
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-bar"></i> Orders Overview</h3>
                        <div class="chart-actions">
                            <button class="chart-action-btn active" data-period="week">Week</button>
                            <button class="chart-action-btn" data-period="month">Month</button>
                            <button class="chart-action-btn" data-period="year">Year</button>
                        </div>
                    </div>
                    <canvas id="ordersChart"></canvas>
                </div>
                
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-pie"></i> Revenue Distribution</h3>
                    </div>
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="orders-section">
                <div class="section-header">
                    <h2><i class="fas fa-list"></i> Recent Orders</h2>
                    <div class="filters">
                        <select class="filter-select" id="statusFilter">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <select class="filter-select" id="dateFilter">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <!-- Orders will be loaded here -->
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">
                                    <div class="skeleton" style="height: 50px; width: 100%;"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer -->
                <?php include "footer.php"; ?>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal-overlay" id="orderDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="orderDetailsTitle"><i class="fas fa-receipt"></i> Order Details</h2>
                <div class="modal-actions">
                    <button class="action-btn btn-print" id="printOrderBtn" title="Print Order Details">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button class="close-modal">&times;</button>
                </div>
            </div>
            <div class="modal-body" id="orderDetailsBody">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Proof of Payment Modal -->
    <div class="modal-overlay" id="proofModal" style="display: none;">
        <div class="modal-content" style="max-width: 90%; max-height: 90vh; overflow: auto;">
            <div class="modal-header">
                <h2 id="proofModalTitle"><i class="fas fa-image"></i> Proof of Payment</h2>
                <button class="close-modal" onclick="closeProofModal()">&times;</button>
            </div>
            <div class="modal-body" style="text-align: center; padding: 20px;">
                <div id="proofImageContainer" style="display: flex; justify-content: center; align-items: center; min-height: 400px;">
                    <img id="proofImage" src="" alt="Proof of Payment" style="max-width: 100%; max-height: 70vh; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                </div>
            </div>
        </div>
    </div>

    <script>
        // Real-time Clock Functionality
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';

            // Convert to 12-hour format
            hours = hours % 12;
            hours = hours ? hours : 12;

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

        // Mobile sidebar functionality
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const notificationIcon = document.getElementById('notificationIcon');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const userMenu = document.getElementById('userMenu');
        const userDropdown = document.getElementById('userDropdown');
        const markAllReadBtn = document.getElementById('markAllRead');
        const notificationList = document.getElementById('notificationList');
        const emptyNotifications = document.getElementById('emptyNotifications');

        let activeDropdown = null;

        // Sample notification data
        const notifications = [
            {
                id: 1,
                title: 'New Order Received',
                message: 'Order #GD10005 just placed',
                time: '10 minutes ago',
                read: false,
                type: 'order'
            },
            {
                id: 2,
                title: 'Order Status Updated',
                message: 'Order #GD10003 is now processing',
                time: '2 hours ago',
                read: false,
                type: 'update'
            },
            {
                id: 3,
                title: 'Low Inventory Alert',
                message: 'Chicken stock is running low',
                time: '1 day ago',
                read: true,
                type: 'warning'
            }
        ];

        // Mobile sidebar toggle
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            closeAllDropdowns();
        });

        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            closeAllDropdowns();
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

        // Load notifications from API
        function loadNotifications() {
            fetch('api/get-order-notifications.php?limit=10')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderNotifications(data.data);
                        updateNotificationBadge(data.unread_count);
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                });
        }

        // Render notifications
        function renderNotifications(notifications) {
            if (!notificationList) return;
            
            notificationList.innerHTML = '';
            
            if (!notifications || notifications.length === 0) {
                if (emptyNotifications) {
                    emptyNotifications.style.display = 'block';
                }
                return;
            }
            
            if (emptyNotifications) {
                emptyNotifications.style.display = 'none';
            }
            
            notifications.forEach(notification => {
                const notificationItem = document.createElement('div');
                notificationItem.className = `notification-item ${!notification.is_read ? 'unread' : ''}`;
                notificationItem.dataset.id = notification.id;
                notificationItem.innerHTML = `
                    <div class="notification-title">${escapeHtml(notification.title)}</div>
                    <div class="notification-message">${escapeHtml(notification.message)}</div>
                    <div class="notification-time">${getTimeAgo(notification.created_at)}</div>
                `;
                
                notificationItem.addEventListener('click', () => {
                    if (!notification.is_read) {
                        markNotificationAsRead(notification.id);
                    }
                    if (notification.reference_id) {
                        // Navigate to order details
                        showOrderDetails(notification.reference_id);
                    }
                });
                
                notificationList.appendChild(notificationItem);
            });
        }

        function markNotificationAsRead(notificationId) {
            fetch('api/mark-notification-read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ notification_id: notificationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        }

        function markAllNotificationsAsRead() {
            fetch('api/mark-all-notifications-read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error marking all as read:', error);
            });
        }

        function updateNotificationBadge(count) {
            const badge = notificationIcon ? notificationIcon.querySelector('.notification-badge') : null;
            if (badge) {
                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            }
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

        // Play notification sound
        function playNotificationSound() {
            try {
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTQ8OUKfk8LZjHAY4kdfyzHksBSR3x/DdkEAKFF606euoVRQKRp/g8r5sIQUrgc7y2Yk2CBtpvfDknE0PDlCn5PC2YxwGOJHX8sx5LAUkd8fw3ZBAC');
                audio.volume = 0.3;
                audio.play().catch(e => console.log('Sound play failed:', e));
            } catch (e) {
                console.log('Sound not available');
            }
        }

        // Dropdown functionality
        function toggleDropdown(dropdown) {
            closeAllDropdowns();
            if (activeDropdown !== dropdown) {
                dropdown.classList.add('active');
                activeDropdown = dropdown;
            } else {
                activeDropdown = null;
            }
        }

        function closeAllDropdowns() {
            notificationDropdown.classList.remove('active');
            userDropdown.classList.remove('active');
            activeDropdown = null;
        }

        // Event listeners for notification and user menu
        notificationIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown(notificationDropdown);
        });

        userMenu.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown(userDropdown);
        });

        markAllReadBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            markAllNotificationsAsRead();
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!notificationIcon.contains(event.target) && !notificationDropdown.contains(event.target)) {
                notificationDropdown.classList.remove('active');
            }
            if (!userMenu.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.classList.remove('active');
            }
        });

        // Close dropdowns with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAllDropdowns();
            }
        });

        // Logout confirmation
        function confirmLogout() {
            return confirm('Are you sure you want to logout?');
        }

        // Initialize WebSocket for real-time order notifications
        let orderWS = null;
        
        // Load notifications on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadNotifications();
            
            // Initialize dashboard
            initDashboard();
            
            // Initialize WebSocket for real-time updates
            try {
                const script = document.createElement('script');
                script.src = 'js/websocket-client.js';
                script.onload = function() {
                    orderWS = initWebSocket('orders');
                    if (orderWS) {
                        orderWS.on('new_order', function(data) {
                            // Update notification badge
                            updateNotificationBadge(data.count);
                            
                            // Reload notifications
                            loadNotifications();
                            
                            // Show toast notification
                            showToast(`New order received! (${data.count} new)`, 'info');
                            
                            // Play sound alert
                            playNotificationSound();
                            
                            // Auto-update order list
                            loadOrders();
                            updateStats();
                        });
                    }
                };
                document.head.appendChild(script);
            } catch (e) {
                console.error('WebSocket initialization error:', e);
            }
            
            // Poll for updates every 30 seconds (fallback if WebSocket fails)
            setInterval(() => {
                loadNotifications();
                loadOrders();
                updateStats();
            }, 30000);
        });

        // Chart instances
        let ordersChart = null;
        let revenueChart = null;
        let currentPeriod = 'week'; // Track current period for orders chart

        // Helper function to escape HTML (must be defined early)
        function escapeHtml(text) {
            if (text === null || text === undefined) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        // Initialize dashboard
        function initDashboard() {
            initCharts();
            loadOrders();
            updateStats();
            
            // Set up real-time updates every 30 seconds
            setInterval(() => {
                loadOrders(); // Reload orders to get latest data
                updateStats(); // Update statistics
                loadChartData(currentPeriod); // Update charts with latest data
            }, 30000); // Update every 30 seconds
        }

        // Load chart data from API
        async function loadChartData(period = 'week') {
            try {
                const response = await fetch(`api/get-chart-data.php?period=${period}`);
                const data = await response.json();
                
                if (data.success) {
                    updateOrdersChart(data.orders_overview);
                    updateRevenueChart(data.revenue_distribution);
                } else {
                    console.error('Failed to load chart data:', data.message);
                }
            } catch (error) {
                console.error('Error loading chart data:', error);
            }
        }
        
        // Update Orders Chart
        function updateOrdersChart(ordersData) {
            const ordersCtx = document.getElementById('ordersChart');
            
            // Destroy existing chart if it exists
            if (ordersChart) {
                ordersChart.destroy();
            }
            
            ordersChart = new Chart(ordersCtx, {
                type: 'line',
                data: {
                    labels: ordersData.labels,
                    datasets: [{
                        label: 'Orders',
                        data: ordersData.data,
                        borderColor: 'rgba(139, 69, 19, 1)',
                        backgroundColor: 'rgba(139, 69, 19, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(139, 69, 19, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: { size: 14 },
                            bodyFont: { size: 13 },
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    return `Orders: ${context.parsed.y}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    size: 12
                                },
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Update Revenue Chart
        function updateRevenueChart(revenueData) {
            const revenueCtx = document.getElementById('revenueChart');
            
            // Destroy existing chart if it exists
            if (revenueChart) {
                revenueChart.destroy();
            }
            
            // Calculate total for percentage display
            const total = revenueData.data.reduce((sum, val) => sum + val, 0);
            
            // Store counts for tooltip access
            const orderCounts = revenueData.counts || [];
            
            revenueChart = new Chart(revenueCtx, {
                type: 'doughnut',
                data: {
                    labels: revenueData.labels.map((label, index) => {
                        const value = revenueData.data[index];
                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                        return `${label} (${percentage}%)`;
                    }),
                    datasets: [{
                        data: revenueData.data,
                        backgroundColor: [
                            'rgba(139, 69, 19, 0.8)',
                            'rgba(210, 105, 30, 0.8)',
                            'rgba(255, 228, 181, 0.8)',
                            'rgba(101, 67, 33, 0.8)'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                font: {
                                    size: 12
                                },
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            bodyFont: { size: 13 },
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    const label = context.label.split(' (')[0];
                                    const value = context.parsed;
                                    const count = orderCounts[context.dataIndex] || 0;
                                    return [
                                        label,
                                        `Revenue: ₦${value.toLocaleString()}`,
                                        `Orders: ${count}`
                                    ];
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Initialize charts
        function initCharts() {
            // Load initial chart data
            loadChartData(currentPeriod);
        }

        // Load orders from API
        function loadOrders() {
            // Show loading state
            const tbody = document.getElementById('ordersTableBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px;">
                        <div class="skeleton" style="height: 50px; width: 100%;"></div>
                    </td>
                </tr>
            `;

            // Get filter parameters
            const statusFilter = document.getElementById('statusFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;
            const searchTerm = document.getElementById('globalSearch').value.trim();

            // Build query string
            const params = new URLSearchParams();
            if (statusFilter !== 'all') params.append('status', statusFilter);
            if (dateFilter !== 'all') params.append('date', dateFilter);
            if (searchTerm) params.append('search', searchTerm);

            // Fetch orders from API
            fetch(`api/get-orders.php?${params.toString()}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Transform database format to display format
                        const orders = data.orders.map(order => ({
                            id: order.order_id,
                            order_id: order.order_id,
                            customerName: order.customer_name,
                            customerEmail: order.customer_email,
                            customerPhone: order.customer_phone,
                            customerAddress: order.delivery_address,
                            customerState: order.customer_state,
                            items: order.items || [],
                            subtotal: parseFloat(order.subtotal),
                            deliveryFee: parseFloat(order.delivery_fee),
                            total: parseFloat(order.total_amount),
                            paymentMethod: order.payment_method,
                            paymentProof: order.payment_proof || null,
                            status: order.order_status,
                            date: order.created_at,
                            updatedAt: order.updated_at
                        }));

                        // Update table
                        updateOrdersTable(orders);
                        
                        // Update stats
                        if (data.stats) {
                            updateStatsFromAPI(data.stats);
                        }
                    } else {
                        showToast(data.message || 'Failed to load orders', 'error');
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">
                                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #f44336; margin-bottom: 15px;"></i>
                                    <p style="color: #666; font-size: 1.1rem;">${data.message || 'Error loading orders'}</p>
                                </td>
                            </tr>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading orders:', error);
                    showToast('Error loading orders. Please try again.', 'error');
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #f44336; margin-bottom: 15px;"></i>
                                <p style="color: #666; font-size: 1.1rem;">Error loading orders. Please refresh the page.</p>
                            </td>
                        </tr>
                    `;
                });
        }

        // Update orders table
        function updateOrdersTable(orders) {
            const tbody = document.getElementById('ordersTableBody');
            
            if (orders.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;">
                            <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                            <p style="color: #666; font-size: 1.1rem;">No orders found</p>
                        </td>
                    </tr>
                `;
                return;
            }
            
            let tableHTML = '';
            
            orders.forEach(order => {
                const itemsCount = order.items ? order.items.length : 0;
                const itemsText = itemsCount === 1 ? '1 item' : `${itemsCount} items`;
                const date = new Date(order.date);
                const formattedDate = date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
                const formattedTime = date.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                let statusClass = '';
                let statusText = '';
                
                switch(order.status) {
                    case 'pending':
                        statusClass = 'status-pending';
                        statusText = 'Pending';
                        break;
                    case 'processing':
                        statusClass = 'status-processing';
                        statusText = 'Processing';
                        break;
                    case 'completed':
                        statusClass = 'status-completed';
                        statusText = 'Completed';
                        break;
                    case 'cancelled':
                        statusClass = 'status-cancelled';
                        statusText = 'Cancelled';
                        break;
                }
                
                let paymentText = '';
                switch(order.paymentMethod) {
                    case 'cod':
                        paymentText = 'COD';
                        break;
                    case 'bank':
                        paymentText = 'Bank Transfer';
                        break;
                    case 'paystack':
                        paymentText = 'Paystack';
                        break;
                    case 'flutterwave':
                        paymentText = 'Flutterwave';
                        break;
                }
                
                // Check if proof of payment exists for bank transfers
                const hasProof = order.paymentMethod === 'bank' && order.paymentProof && order.paymentProof.trim() !== '';
                const proofImageData = hasProof ? order.paymentProof : null;
                
                const escapedOrderId = escapeHtml(order.id);
                const escapedCustomerName = escapeHtml(order.customerName);
                const escapedCustomerPhone = escapeHtml(order.customerPhone);
                
                tableHTML += `
                    <tr class="fade-in">
                        <td><strong>${escapedOrderId}</strong></td>
                        <td>
                            <div style="font-weight: 600;">${escapedCustomerName}</div>
                            <div style="font-size: 0.85rem; color: #666;">${escapedCustomerPhone}</div>
                        </td>
                        <td>${itemsText}</td>
                        <td><strong>₦${(order.total || 0).toLocaleString()}</strong></td>
                        <td>
                            <span class="payment-badge">${paymentText}</span>
                            ${hasProof ? `
                                <button class="proof-btn" onclick="viewProofOfPayment('${escapedOrderId}', ${JSON.stringify(proofImageData).replace(/"/g, '&quot;')})" title="View Proof of Payment" style="margin-left: 8px; padding: 4px 8px; background: var(--info); color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85rem;">
                                    <i class="fas fa-image"></i> Proof
                                </button>
                            ` : ''}
                        </td>
                        <td>
                            <div>${formattedDate}</div>
                            <div style="font-size: 0.85rem; color: #666;">${formattedTime}</div>
                        </td>
                        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn btn-view" onclick="viewOrderDetails('${escapedOrderId}')" title="View Order Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                ${order.status === 'pending' ? `
                                    <button class="action-btn btn-process" onclick="updateOrderStatus('${escapedOrderId}', 'processing')" title="Mark as Processing">
                                        <i class="fas fa-spinner"></i>
                                    </button>
                                    <button class="action-btn btn-complete" onclick="updateOrderStatus('${escapedOrderId}', 'completed')" title="Mark as Completed">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="action-btn btn-cancel" onclick="updateOrderStatus('${escapedOrderId}', 'cancelled')" title="Cancel Order">
                                        <i class="fas fa-times"></i>
                                    </button>
                                ` : order.status === 'processing' ? `
                                    <button class="action-btn btn-complete" onclick="updateOrderStatus('${escapedOrderId}', 'completed')" title="Mark as Completed">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="action-btn btn-cancel" onclick="updateOrderStatus('${escapedOrderId}', 'cancelled')" title="Cancel Order">
                                        <i class="fas fa-times"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = tableHTML;
        }

        // Update statistics from API data
        function updateStatsFromAPI(stats) {
            document.getElementById('totalOrders').textContent = stats.total_orders || 0;
            document.getElementById('pendingOrders').textContent = stats.pending_orders || 0;
            document.getElementById('totalRevenue').textContent = `₦${(stats.total_revenue || 0).toLocaleString()}`;
            document.getElementById('todayOrders').textContent = stats.today_orders || 0;
        }

        // Update statistics (fetch fresh data)
        function updateStats() {
            fetch('api/get-orders.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.stats) {
                        updateStatsFromAPI(data.stats);
                    }
                })
                .catch(error => {
                    console.error('Error updating stats:', error);
                });
        }

        // View order details
        window.viewOrderDetails = function(orderId) {
            // Show loading state
            document.getElementById('orderDetailsBody').innerHTML = '<div style="text-align: center; padding: 40px;"><div class="skeleton" style="height: 200px; width: 100%;"></div></div>';
            document.getElementById('orderDetailsModal').style.display = 'flex';
            
            // Fetch order details from API
            fetch(`api/get-order-details.php?order_id=${encodeURIComponent(orderId)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.order) {
                        const order = data.order;
                        
                        const date = new Date(order.created_at);
                        const formattedDate = date.toLocaleDateString('en-US', {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        
                        let itemsHTML = '';
                        if (order.items && order.items.length > 0) {
                            itemsHTML = order.items.map(item => `
                                <div class="detail-item">
                                    <span class="detail-label">${escapeHtml(item.item_name)} x ${item.quantity}</span>
                                    <span class="detail-value">₦${(parseFloat(item.item_price) * parseInt(item.quantity)).toLocaleString()}</span>
                                </div>
                            `).join('');
                        }
                        
                        let statusClass = '';
                        let statusText = '';
                        
                        switch(order.order_status) {
                            case 'pending':
                                statusClass = 'status-pending';
                                statusText = 'Pending';
                                break;
                            case 'processing':
                                statusClass = 'status-processing';
                                statusText = 'Processing';
                                break;
                            case 'completed':
                                statusClass = 'status-completed';
                                statusText = 'Completed';
                                break;
                            case 'cancelled':
                                statusClass = 'status-cancelled';
                                statusText = 'Cancelled';
                                break;
                        }
                        
                        let paymentText = '';
                        switch(order.payment_method) {
                            case 'cod':
                                paymentText = 'Cash on Delivery';
                                break;
                            case 'bank':
                                paymentText = 'Bank Transfer';
                                break;
                            case 'paystack':
                                paymentText = 'Paystack';
                                break;
                            case 'flutterwave':
                                paymentText = 'Flutterwave';
                                break;
                        }
                        
                        // Check if proof of payment exists for bank transfers
                        const hasProof = order.payment_method === 'bank' && order.payment_proof && order.payment_proof.trim() !== '';
                        const proofImageData = hasProof ? order.payment_proof : null;
                        
                        const detailsHTML = `
                            <div class="order-details-grid">
                                <div class="detail-card">
                                    <h3><i class="fas fa-user"></i> Customer Information</h3>
                                    <div class="detail-item">
                                        <span class="detail-label">Name</span>
                                        <span class="detail-value">${escapeHtml(order.customer_name)}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Email</span>
                                        <span class="detail-value">${escapeHtml(order.customer_email)}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Phone</span>
                                        <span class="detail-value">${escapeHtml(order.customer_phone)}</span>
                                    </div>
                                    ${order.customer_state ? `
                                    <div class="detail-item">
                                        <span class="detail-label">State</span>
                                        <span class="detail-value">${escapeHtml(order.customer_state)}</span>
                                    </div>
                                    ` : ''}
                                    <div class="detail-item">
                                        <span class="detail-label">Address</span>
                                        <span class="detail-value">${escapeHtml(order.delivery_address)}</span>
                                    </div>
                                    ${order.delivery_instructions ? `
                                    <div class="detail-item">
                                        <span class="detail-label">Delivery Instructions</span>
                                        <span class="detail-value">${escapeHtml(order.delivery_instructions)}</span>
                                    </div>
                                    ` : ''}
                                </div>
                                
                                <div class="detail-card">
                                    <h3><i class="fas fa-receipt"></i> Order Information</h3>
                                    <div class="detail-item">
                                        <span class="detail-label">Order ID</span>
                                        <span class="detail-value">${escapeHtml(order.order_id)}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Order Date</span>
                                        <span class="detail-value">${formattedDate}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Status</span>
                                        <span class="detail-value"><span class="status-badge ${statusClass}">${statusText}</span></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Payment Method</span>
                                        <span class="detail-value">${paymentText}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Payment Status</span>
                                        <span class="detail-value">${escapeHtml(order.payment_status)}</span>
                                    </div>
                                    ${hasProof ? `
                                    <div class="detail-item">
                                        <span class="detail-label">Proof of Payment</span>
                                        <span class="detail-value">
                                            <button class="proof-btn" onclick="viewProofOfPayment('${escapeHtml(order.order_id)}', ${JSON.stringify(proofImageData).replace(/"/g, '&quot;')})" style="padding: 8px 15px; background: var(--info); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem;">
                                                <i class="fas fa-image"></i> View Proof
                                            </button>
                                        </span>
                                    </div>
                                    ` : ''}
                                </div>
                                
                                <div class="detail-card">
                                    <h3><i class="fas fa-shopping-cart"></i> Order Items</h3>
                                    ${itemsHTML || '<p style="color: #666; padding: 10px;">No items found</p>'}
                                    <div class="detail-item">
                                        <span class="detail-label">Subtotal</span>
                                        <span class="detail-value">₦${parseFloat(order.subtotal).toLocaleString()}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Delivery Fee</span>
                                        <span class="detail-value">₦${parseFloat(order.delivery_fee).toLocaleString()}</span>
                                    </div>
                                    <div class="detail-item" style="border-top: 2px solid var(--primary); padding-top: 15px; margin-top: 15px;">
                                        <span class="detail-label" style="font-weight: 700; font-size: 1.1rem;">Total</span>
                                        <span class="detail-value" style="font-weight: 700; font-size: 1.1rem;">₦${parseFloat(order.total_amount).toLocaleString()}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        document.getElementById('orderDetailsTitle').innerHTML = `
                            <i class="fas fa-receipt"></i> Order Details - ${escapeHtml(order.order_id)}
                        `;
                        document.getElementById('orderDetailsBody').innerHTML = detailsHTML;
                    } else {
                        showToast(data.message || 'Order not found!', 'error');
                        document.getElementById('orderDetailsModal').style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error loading order details:', error);
                    showToast('Error loading order details. Please try again.', 'error');
                    document.getElementById('orderDetailsModal').style.display = 'none';
                });
        };

        // Print order details
        function printOrderDetails() {
            // Create a print-friendly version
            const printContent = document.getElementById('orderDetailsBody').innerHTML;
            const orderTitle = document.getElementById('orderDetailsTitle').textContent;
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>${orderTitle} - Joseph's Pot</title>
                    <style>
                        body {
                            font-family: 'Poppins', sans-serif;
                            margin: 0;
                            padding: 20px;
                            color: #333;
                        }
                        .print-header {
                            text-align: center;
                            margin-bottom: 30px;
                            padding-bottom: 20px;
                            border-bottom: 2px solid #8b4513;
                        }
                        .print-header h1 {
                            color: #8b4513;
                            margin-bottom: 10px;
                        }
                        .print-meta {
                            color: #666;
                            font-size: 0.9em;
                        }
                        .order-details-grid {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                            gap: 20px;
                            margin-bottom: 30px;
                        }
                        .detail-card {
                            border: 1px solid #ddd;
                            border-radius: 8px;
                            padding: 20px;
                            background: #f9f9f9;
                            break-inside: avoid;
                        }
                        .detail-card h3 {
                            color: #8b4513;
                            margin-top: 0;
                            margin-bottom: 20px;
                            padding-bottom: 10px;
                            border-bottom: 1px solid #ddd;
                        }
                        .detail-item {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 10px;
                            padding-bottom: 10px;
                            border-bottom: 1px dotted #ddd;
                        }
                        .detail-label {
                            font-weight: 600;
                            color: #666;
                        }
                        .detail-value {
                            text-align: right;
                            max-width: 200px;
                        }
                        .status-badge {
                            display: inline-block;
                            padding: 4px 10px;
                            border-radius: 20px;
                            font-size: 0.8em;
                            font-weight: 600;
                            text-transform: uppercase;
                        }
                        .print-footer {
                            margin-top: 40px;
                            padding-top: 20px;
                            border-top: 1px solid #ddd;
                            text-align: center;
                            color: #666;
                            font-size: 0.9em;
                        }
                        @media print {
                            @page {
                                margin: 0.5in;
                            }
                            body {
                                padding: 0;
                            }
                            .detail-card {
                                break-inside: avoid;
                                page-break-inside: avoid;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <h1>Joseph's Pot - Order Details</h1>
                        <div class="print-meta">
                            <p><strong>${orderTitle}</strong></p>
                            <p>Printed: ${new Date().toLocaleString()}</p>
                        </div>
                    </div>
                    ${printContent}
                    <div class="print-footer">
                        <p>Thank you for choosing Joseph's Pot!</p>
                        <p>Owerri, Nigeria | Phone: (234) 123-4567 | Email: info@josephspot.com</p>
                    </div>
                    <script>
                        window.onload = function() {
                            window.print();
                            setTimeout(() => window.close(), 1000);
                        };
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }

        // Add print button event listener
        document.getElementById('printOrderBtn').addEventListener('click', function(e) {
            e.preventDefault();
            printOrderDetails();
        });

        // View proof of payment
        window.viewProofOfPayment = function(orderId, imageData) {
            if (!imageData || imageData.trim() === '') {
                showToast('No proof of payment available for this order', 'error');
                return;
            }
            
            const proofModal = document.getElementById('proofModal');
            const proofModalTitle = document.getElementById('proofModalTitle');
            const proofImageContainer = document.getElementById('proofImageContainer');
            
            // Reset container
            proofImageContainer.innerHTML = '<img id="proofImage" src="" alt="Proof of Payment" style="max-width: 100%; max-height: 70vh; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">';
            const img = document.getElementById('proofImage');
            
            // Set the image source - handle both base64 data URLs and regular URLs
            if (imageData.startsWith('data:') || imageData.startsWith('http://') || imageData.startsWith('https://')) {
                img.src = imageData;
            } else {
                // If it's a base64 string without the data: prefix, add it
                img.src = imageData.startsWith('/9j/') || imageData.startsWith('iVBORw0KG') 
                    ? `data:image/jpeg;base64,${imageData}` 
                    : `data:image/png;base64,${imageData}`;
            }
            
            proofModalTitle.innerHTML = `<i class="fas fa-image"></i> Proof of Payment - ${escapeHtml(orderId)}`;
            proofModal.style.display = 'flex';
            
            // Handle image load error
            img.onerror = function() {
                proofImageContainer.innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #f44336; margin-bottom: 15px;"></i>
                        <p style="color: #666; font-size: 1.1rem;">Failed to load proof of payment image</p>
                        <p style="color: #999; font-size: 0.9rem; margin-top: 10px;">The image data may be corrupted or in an unsupported format.</p>
                    </div>
                `;
            };
        };
        
        // Close proof modal
        function closeProofModal() {
            document.getElementById('proofModal').style.display = 'none';
            const proofImage = document.getElementById('proofImage');
            if (proofImage) {
                proofImage.src = '';
            }
        }
        
        // Close proof modal when clicking outside
        document.getElementById('proofModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeProofModal();
            }
        });
        
        // Update order status
        window.updateOrderStatus = function(orderId, newStatus) {
            const statusLabels = {
                'pending': 'Pending',
                'processing': 'Processing',
                'completed': 'Completed',
                'cancelled': 'Cancelled'
            };
            
            Swal.fire({
                title: 'Update Order Status',
                text: `Change order ${orderId} status to ${statusLabels[newStatus] || newStatus}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#8b4513',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, update it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send update request to API
                    fetch('api/update-order-status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            order_id: orderId,
                            status: newStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(`Order ${orderId} status updated to ${statusLabels[newStatus] || newStatus}!`, 'success');
                            loadOrders(); // Reload orders to reflect changes
                            loadChartData(currentPeriod); // Update charts with latest data
                            updateStats(); // Update statistics
                        } else {
                            showToast(data.message || 'Failed to update order status', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error updating order status:', error);
                        showToast('Error updating order status. Please try again.', 'error');
                    });
                }
            });
        };


        // Event Listeners
        document.getElementById('globalSearch').addEventListener('input', function() {
            loadOrders();
        });

        document.getElementById('statusFilter').addEventListener('change', function() {
            loadOrders();
        });

        document.getElementById('dateFilter').addEventListener('change', function() {
            loadOrders();
        });

        // Close modal - this button works perfectly
        document.querySelector('.close-modal').addEventListener('click', function() {
            document.getElementById('orderDetailsModal').style.display = 'none';
        });

        // Close modal when clicking outside
        document.getElementById('orderDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const orderModal = document.getElementById('orderDetailsModal');
                if (orderModal && orderModal.style.display === 'flex') {
                    orderModal.style.display = 'none';
                }
            }
        });

        // Chart period switching
        document.querySelectorAll('.chart-action-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.chart-action-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const period = this.getAttribute('data-period');
                currentPeriod = period;
                loadChartData(period);
            });
        });

        // Utility functions
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                <span>${message}</span>
            `;
            
            // Create toast container if it doesn't exist
            let toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toastContainer';
                toastContainer.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                `;
                document.body.appendChild(toastContainer);
            }
            
            toastContainer.appendChild(toast);
            
            // Add toast styles if not already added
            if (!document.querySelector('#toastStyles')) {
                const style = document.createElement('style');
                style.id = 'toastStyles';
                style.textContent = `
                    .toast {
                        background: #8b4513;
                        color: white;
                        padding: 15px 20px;
                        border-radius: 8px;
                        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        animation: slideIn 0.3s ease, fadeOut 0.5s ease 2.5s forwards;
                        max-width: 350px;
                    }
                    .toast.success { background: #4caf50; }
                    .toast.error { background: #f44336; }
                    .toast.info { background: #2196f3; }
                    @keyframes slideIn {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                    @keyframes fadeOut {
                        from { opacity: 1; transform: translateX(0); }
                        to { opacity: 0; transform: translateX(100%); visibility: hidden; }
                    }
                `;
                document.head.appendChild(style);
            }
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        function convertToCSV(data) {
            const headers = ['Order ID', 'Customer Name', 'Email', 'Phone', 'Address', 'Total Amount', 'Status', 'Payment Method', 'Date'];
            const rows = data.map(order => [
                order.id,
                order.customerName,
                order.customerEmail,
                order.customerPhone,
                order.customerAddress,
                `₦${order.total}`,
                order.status,
                order.paymentMethod,
                new Date(order.date).toLocaleString()
            ]);
            
            return [headers, ...rows].map(row => 
                row.map(cell => `"${cell}"`).join(',')
            ).join('\n');
        }

        function downloadCSV(csv, filename) {
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>
</html>