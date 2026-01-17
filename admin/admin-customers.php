<?php
// Central authentication and permission check
require_once 'admin-auth.php';
checkPageAccess(); // This checks authentication and permission for current page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/logo3.png">
    <title>Customers Management - Joseph's Pot</title>
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

        .menu-item a:hover, .menu-item a.active {
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

        /* FIXED NOTIFICATION AND USER MENU STYLES - ENHANCED */
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

        /* Notification Dropdown - ENHANCED */
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

        /* User Dropdown Menu - ADDED */
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 200px;
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            z-index: 1000;
            display: none;
            margin-top: 5px;
            overflow: hidden;
        }

        .user-dropdown.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .user-dropdown-item {
            padding: 12px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: var(--transition);
            color: var(--text);
            text-decoration: none;
        }

        .user-dropdown-item:hover {
            background: var(--gray);
        }

        .user-dropdown-item i {
            color: var(--primary);
            width: 20px;
        }

        /* Real-time Clock Styles - ADDED */
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

        .stat-card.total::before {
            background: var(--info);
        }

        .stat-card.new::before {
            background: var(--success);
        }

        .stat-card.active::before {
            background: var(--warning);
        }

        .stat-card.vip::before {
            background: var(--danger);
        }

        .stat-card i {
            font-size: 2rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .stat-card.total i {
            color: var(--info);
        }

        .stat-card.new i {
            color: var(--success);
        }

        .stat-card.active i {
            color: var(--warning);
        }

        .stat-card.vip i {
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

        /* Customer Filters */
        .customer-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 30px;
            background: white;
            color: var(--text);
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow);
            font-weight: 500;
            flex: 1;
            min-width: 140px;
            text-align: center;
        }

        .filter-btn.active {
            background: var(--primary);
            color: white;
        }

        .filter-btn:hover:not(.active) {
            background: var(--gray);
            transform: translateY(-2px);
        }

        /* Customers Grid */
        .customers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .customer-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
        }

        .customer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .customer-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .customer-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
        }

        .customer-info h3 {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .customer-info p {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 3px;
        }

        .customer-tag {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            margin-top: 5px;
        }

        .tag-new {
            background: rgba(76, 175, 80, 0.2);
            color: var(--success);
        }

        .tag-vip {
            background: rgba(244, 67, 54, 0.2);
            color: var(--danger);
        }

        .tag-regular {
            background: rgba(33, 150, 243, 0.2);
            color: var(--info);
        }

        .customer-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 15px 0;
        }

        .stat-item {
            text-align: center;
            padding: 10px;
            background: var(--gray);
            border-radius: 8px;
        }

        .stat-value-small {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
        }

        .stat-label-small {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .customer-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-btn.view {
            background: var(--info);
            color: white;
        }

        .action-btn.message {
            background: var(--success);
            color: white;
        }

        .action-btn.edit {
            background: var(--warning);
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        /* Customer Details Modal */
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
            max-width: 900px;
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

        .customer-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .customer-detail-group {
            margin-bottom: 20px;
        }

        .customer-detail-group h4 {
            font-size: 1rem;
            margin-bottom: 10px;
            color: var(--primary);
            border-bottom: 1px solid var(--gray);
            padding-bottom: 5px;
        }

        .customer-detail-group p {
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }

        .detail-label {
            font-weight: 500;
            color: var(--text-light);
        }

        .detail-value {
            font-weight: 500;
        }

        .order-history {
            margin-top: 20px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--gray);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-id {
            font-weight: 600;
            color: var(--primary);
        }

        .order-status {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .status-completed {
            background: rgba(76, 175, 80, 0.2);
            color: var(--success);
        }

        .status-pending {
            background: rgba(255, 152, 0, 0.2);
            color: var(--warning);
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--gray);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
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

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #3d8b40;
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

        /* Animation */
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Design - ENHANCED */
        @media (max-width: 1200px) {
            .customer-details-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-cards {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
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
            
            /* Notification dropdown responsive */
            .notification-dropdown {
                position: fixed;
                top: 70px;
                right: 15px;
                left: 15px;
                width: auto;
                max-height: 60vh;
            }
            
            .user-dropdown {
                position: fixed;
                top: 70px;
                right: 15px;
                width: auto;
                min-width: 200px;
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
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .customer-filters {
                justify-content: center;
            }
            
            .filter-btn {
                min-width: 120px;
            }
            
            .customers-grid {
                grid-template-columns: 1fr;
            }
            
            .customer-card-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .customer-avatar {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .customer-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .action-btn {
                width: 100%;
                justify-content: center;
            }
            
            .customer-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .modal-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .customer-filters {
                flex-direction: column;
            }
            
            .filter-btn {
                width: 100%;
            }
            
            .customer-card {
                padding: 15px;
            }
            
            .modal-content {
                padding: 20px 15px;
            }
            
            .customer-details-grid {
                gap: 20px;
            }
            
            .customer-detail-group p {
                flex-direction: column;
                gap: 5px;
            }
            
            .detail-label, .detail-value {
                width: 100%;
            }
            
            .order-item {
                flex-direction: column;
                gap: 10px;
            }
            
            .modal-actions {
                flex-direction: column;
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
            
            .customer-avatar {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
            
            .customer-info h3 {
                font-size: 1rem;
            }
            
            .customer-info p {
                font-size: 0.8rem;
            }
            
            .stat-value-small {
                font-size: 1rem;
            }
            
            .stat-label-small {
                font-size: 0.7rem;
            }
            
            .action-btn {
                padding: 6px 12px;
                font-size: 0.75rem;
            }
        }

        /* Hover animations for touch devices */
        @media (hover: none) and (pointer: coarse) {
            .customer-card:hover {
                transform: none;
            }
            
            .action-btn:hover {
                transform: none;
            }
            
            .stat-card:hover {
                transform: none;
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
                    <a href="admin-order-online.php">
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
                    <a href="admin-customers.php" class="active">
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
                <h2>Customers Management</h2>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search customers...">
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
                        <div class="user-menu" id="userMenu">
                            <i class="fas fa-user-circle"></i>
                            <div class="user-dropdown" id="userDropdown">
                                <a href="#" class="user-dropdown-item">
                                    <i class="fas fa-user"></i>
                                    <span>My Profile</span>
                                </a>
                                <a href="#" class="user-dropdown-item">
                                    <i class="fas fa-cog"></i>
                                    <span>Settings</span>
                                </a>
                                <a href="admin-logout.php" class="user-dropdown-item" onclick="return confirmLogout()">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card total reveal">
                    <i class="fas fa-users"></i>
                    <div class="stat-value">2,847</div>
                    <div class="stat-label">Total Customers</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 12% from last month
                    </div>
                </div>
                
                <div class="stat-card new reveal reveal-delay-1">
                    <i class="fas fa-user-plus"></i>
                    <div class="stat-value">142</div>
                    <div class="stat-label">New This Month</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 8% from last month
                    </div>
                </div>
                
                <div class="stat-card active reveal reveal-delay-2">
                    <i class="fas fa-user-check"></i>
                    <div class="stat-value">1,924</div>
                    <div class="stat-label">Active Customers</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 5% from last month
                    </div>
                </div>
                
                <div class="stat-card vip reveal reveal-delay-3">
                    <i class="fas fa-crown"></i>
                    <div class="stat-value">324</div>
                    <div class="stat-label">VIP Customers</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 3% from last month
                    </div>
                </div>
            </div>
            
            <!-- Customer Filters -->
            <div class="customer-filters">
                <button class="filter-btn active" data-filter="all">All Customers</button>
                <button class="filter-btn" data-filter="new">New</button>
                <button class="filter-btn" data-filter="active">Active</button>
                <button class="filter-btn" data-filter="vip">VIP</button>
                <button class="filter-btn" data-filter="inactive">Inactive</button>
            </div>
            
            <!-- Customers Grid -->
            <div class="customers-grid" id="customersGrid">
                <!-- Customer cards will be dynamically added here -->
            </div>
            
            <div class="footer">
                <p>&copy; 2025 Joseph's Pot Admin Dashboard. All rights reserved. | Developed by ERIBS Tech</p>
            </div>
        </div>
    </div>

    <!-- Customer Details Modal -->
    <div class="modal" id="customerDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Customer Details</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="customer-details-grid">
                    <div>
                        <div class="customer-detail-group">
                            <h4>Personal Information</h4>
                            <p>
                                <span class="detail-label">Full Name:</span>
                                <span class="detail-value" id="customerFullName"></span>
                            </p>
                            <p>
                                <span class="detail-label">Email:</span>
                                <span class="detail-value" id="customerEmail"></span>
                            </p>
                            <p>
                                <span class="detail-label">Phone:</span>
                                <span class="detail-value" id="customerPhone"></span>
                            </p>
                            <p>
                                <span class="detail-label">Join Date:</span>
                                <span class="detail-value" id="customerJoinDate"></span>
                            </p>
                            <p>
                                <span class="detail-label">Customer Type:</span>
                                <span class="detail-value" id="customerType"></span>
                            </p>
                        </div>
                        
                        <div class="customer-detail-group">
                            <h4>Address Information</h4>
                            <p>
                                <span class="detail-label">Primary Address:</span>
                                <span class="detail-value" id="customerAddress"></span>
                            </p>
                            <p>
                                <span class="detail-label">City:</span>
                                <span class="detail-value" id="customerCity"></span>
                            </p>
                            <p>
                                <span class="detail-label">State:</span>
                                <span class="detail-value" id="customerState"></span>
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <div class="customer-detail-group">
                            <h4>Order Statistics</h4>
                            <p>
                                <span class="detail-label">Total Orders:</span>
                                <span class="detail-value" id="totalOrders"></span>
                            </p>
                            <p>
                                <span class="detail-label">Total Spent:</span>
                                <span class="detail-value" id="totalSpent"></span>
                            </p>
                            <p>
                                <span class="detail-label">Average Order Value:</span>
                                <span class="detail-value" id="avgOrderValue"></span>
                            </p>
                            <p>
                                <span class="detail-label">Last Order Date:</span>
                                <span class="detail-value" id="lastOrderDate"></span>
                            </p>
                            <p>
                                <span class="detail-label">Favorite Cuisine:</span>
                                <span class="detail-value" id="favoriteCuisine"></span>
                            </p>
                        </div>
                        
                        <div class="customer-detail-group">
                            <h4>Preferences</h4>
                            <p>
                                <span class="detail-label">Preferred Payment:</span>
                                <span class="detail-value" id="preferredPayment"></span>
                            </p>
                            <p>
                                <span class="detail-label">Delivery Preference:</span>
                                <span class="detail-value" id="deliveryPreference"></span>
                            </p>
                            <p>
                                <span class="detail-label">Special Instructions:</span>
                                <span class="detail-value" id="specialInstructions"></span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="order-history">
                    <h4>Recent Orders</h4>
                    <div id="recentOrdersList">
                        <!-- Recent orders will be dynamically added here -->
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button class="btn btn-secondary" id="sendMessageBtn">
                        <i class="fas fa-envelope"></i>
                        Send Message
                    </button>
                    <button class="btn btn-success" id="createOrderBtn">
                        <i class="fas fa-plus"></i>
                        Create Order
                    </button>
                    <button class="btn btn-primary" id="editCustomerBtn">
                        <i class="fas fa-edit"></i>
                        Edit Customer
                    </button>
                </div>
            </div>
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
            
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            
            hours = hours % 12;
            hours = hours ? hours : 12;
            
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateString = now.toLocaleDateString('en-US', options);
            
            document.getElementById('currentTime').textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
            document.getElementById('currentDate').textContent = dateString;
        }
        
        updateClock();
        setInterval(updateClock, 1000);

        // ============================================
        // NOTIFICATION SYSTEM
        // ============================================
        // Sample notification data
        const notifications = [
            {
                id: 1,
                title: 'New Customer Registration',
                message: 'John Smith registered as a new customer',
                time: '2 minutes ago',
                unread: true
            },
            {
                id: 2,
                title: 'VIP Status Upgrade',
                message: 'Sarah Johnson upgraded to VIP customer',
                time: '15 minutes ago',
                unread: true
            },
            {
                id: 3,
                title: 'Customer Order',
                message: 'Michael Brown placed an order worth ₦15,300',
                time: '1 hour ago',
                unread: false
            },
            {
                id: 4,
                title: 'Customer Feedback',
                message: 'Grace Williams left a 5-star review',
                time: '3 hours ago',
                unread: false
            },
            {
                id: 5,
                title: 'System Update',
                message: 'Customer management system updated',
                time: '1 day ago',
                unread: false
            }
        ];

        // Sample customers data
        const customers = [
            {
                id: 1,
                name: 'John Smith',
                email: 'john.smith@example.com',
                phone: '+234 801 234 5678',
                joinDate: '2024-01-15',
                type: 'vip',
                address: '123 Victoria Island, Lagos',
                city: 'Lagos',
                state: 'Lagos',
                totalOrders: 24,
                totalSpent: 186500,
                avgOrderValue: 7771,
                lastOrderDate: '2025-03-15',
                favoriteCuisine: 'Ofe Owerri',
                preferredPayment: 'Card',
                deliveryPreference: 'Home Delivery',
                specialInstructions: 'Leave at security post',
                orders: [
                    { id: 'JP-2847', date: '2025-03-15', amount: 12500, status: 'completed' },
                    { id: 'JP-2812', date: '2025-03-10', amount: 9800, status: 'completed' },
                    { id: 'JP-2795', date: '2025-03-05', amount: 15300, status: 'completed' }
                ]
            },
            {
                id: 2,
                name: 'Sarah Johnson',
                email: 'sarah.j@example.com',
                phone: '+234 802 345 6789',
                joinDate: '2025-02-20',
                type: 'new',
                address: '45 Ikeja GRA, Lagos',
                city: 'Lagos',
                state: 'Lagos',
                totalOrders: 3,
                totalSpent: 24600,
                avgOrderValue: 8200,
                lastOrderDate: '2025-03-15',
                favoriteCuisine: 'Nkwobi',
                preferredPayment: 'Transfer',
                deliveryPreference: 'Office Delivery',
                specialInstructions: 'Call upon arrival',
                orders: [
                    { id: 'JP-2848', date: '2025-03-15', amount: 8200, status: 'pending' },
                    { id: 'JP-2820', date: '2025-03-01', amount: 10400, status: 'completed' },
                    { id: 'JP-2805', date: '2025-02-25', amount: 6000, status: 'completed' }
                ]
            },
            {
                id: 3,
                name: 'Michael Brown',
                email: 'm.brown@example.com',
                phone: '+234 803 456 7890',
                joinDate: '2024-11-10',
                type: 'active',
                address: '78 Lekki Phase 1, Lagos',
                city: 'Lagos',
                state: 'Lagos',
                totalOrders: 12,
                totalSpent: 112400,
                avgOrderValue: 9367,
                lastOrderDate: '2025-03-15',
                favoriteCuisine: 'Egusi Soup',
                preferredPayment: 'Cash',
                deliveryPreference: 'Home Delivery',
                specialInstructions: 'No instructions',
                orders: [
                    { id: 'JP-2849', date: '2025-03-15', amount: 15300, status: 'pending' },
                    { id: 'JP-2830', date: '2025-03-08', amount: 9800, status: 'completed' },
                    { id: 'JP-2815', date: '2025-03-01', amount: 7500, status: 'completed' }
                ]
            },
            {
                id: 4,
                name: 'Grace Williams',
                email: 'grace.w@example.com',
                phone: '+234 804 567 8901',
                joinDate: '2024-08-05',
                type: 'vip',
                address: '22 Surulere, Lagos',
                city: 'Lagos',
                state: 'Lagos',
                totalOrders: 31,
                totalSpent: 243200,
                avgOrderValue: 7845,
                lastOrderDate: '2025-03-15',
                favoriteCuisine: 'Ofada Rice',
                preferredPayment: 'Card',
                deliveryPreference: 'Home Delivery',
                specialInstructions: 'Ring bell twice',
                orders: [
                    { id: 'JP-2850', date: '2025-03-15', amount: 9600, status: 'completed' },
                    { id: 'JP-2835', date: '2025-03-12', amount: 11200, status: 'completed' },
                    { id: 'JP-2822', date: '2025-03-08', amount: 8400, status: 'completed' }
                ]
            },
            {
                id: 5,
                name: 'David Okonkwo',
                email: 'd.okonkwo@example.com',
                phone: '+234 805 678 9012',
                joinDate: '2025-01-30',
                type: 'active',
                address: '14 Yaba, Lagos',
                city: 'Lagos',
                state: 'Lagos',
                totalOrders: 8,
                totalSpent: 67400,
                avgOrderValue: 8425,
                lastOrderDate: '2025-03-15',
                favoriteCuisine: 'Banga Soup',
                preferredPayment: 'Transfer',
                deliveryPreference: 'Office Delivery',
                specialInstructions: 'Leave with neighbor if not home',
                orders: [
                    { id: 'JP-2851', date: '2025-03-15', amount: 11200, status: 'pending' },
                    { id: 'JP-2838', date: '2025-03-10', amount: 9800, status: 'completed' },
                    { id: 'JP-2825', date: '2025-03-05', amount: 7600, status: 'completed' }
                ]
            },
            {
                id: 6,
                name: 'Chinwe Okafor',
                email: 'c.okafor@example.com',
                phone: '+234 806 789 0123',
                joinDate: '2024-12-18',
                type: 'active',
                address: '56 Gwarinpa, Abuja',
                city: 'Abuja',
                state: 'FCT',
                totalOrders: 15,
                totalSpent: 128500,
                avgOrderValue: 8567,
                lastOrderDate: '2025-03-14',
                favoriteCuisine: 'Pepper Soup',
                preferredPayment: 'Card',
                deliveryPreference: 'Home Delivery',
                specialInstructions: 'Call before delivery',
                orders: [
                    { id: 'JP-2845', date: '2025-03-14', amount: 10500, status: 'completed' },
                    { id: 'JP-2832', date: '2025-03-08', amount: 9200, status: 'completed' },
                    { id: 'JP-2818', date: '2025-03-02', amount: 7800, status: 'completed' }
                ]
            }
        ];

        // DOM Elements
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const customersGrid = document.getElementById('customersGrid');
        const customerDetailsModal = document.getElementById('customerDetailsModal');
        const closeModal = document.getElementById('closeModal');
        const filterBtns = document.querySelectorAll('.filter-btn');
        const searchInput = document.getElementById('searchInput');

        // Notification elements
        const notificationIcon = document.getElementById('notificationIcon');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationList = document.getElementById('notificationList');
        const markAllReadBtn = document.getElementById('markAllRead');

        // User menu elements
        const userMenu = document.getElementById('userMenu');
        const userDropdown = document.getElementById('userDropdown');

        // Modal elements
        const customerFullName = document.getElementById('customerFullName');
        const customerEmail = document.getElementById('customerEmail');
        const customerPhone = document.getElementById('customerPhone');
        const customerJoinDate = document.getElementById('customerJoinDate');
        const customerType = document.getElementById('customerType');
        const customerAddress = document.getElementById('customerAddress');
        const customerCity = document.getElementById('customerCity');
        const customerState = document.getElementById('customerState');
        const totalOrdersElement = document.getElementById('totalOrders');
        const totalSpentElement = document.getElementById('totalSpent');
        const avgOrderValueElement = document.getElementById('avgOrderValue');
        const lastOrderDateElement = document.getElementById('lastOrderDate');
        const favoriteCuisineElement = document.getElementById('favoriteCuisine');
        const preferredPaymentElement = document.getElementById('preferredPayment');
        const deliveryPreferenceElement = document.getElementById('deliveryPreference');
        const specialInstructionsElement = document.getElementById('specialInstructions');
        const recentOrdersList = document.getElementById('recentOrdersList');
        const sendMessageBtn = document.getElementById('sendMessageBtn');
        const createOrderBtn = document.getElementById('createOrderBtn');
        const editCustomerBtn = document.getElementById('editCustomerBtn');

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

        // ============================================
        // NOTIFICATION FUNCTIONALITY
        // ============================================
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
            const notificationBadge = document.querySelector('.notification-badge');
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
        if (notificationIcon) {
            notificationIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('active');
                // Close user dropdown if open
                userDropdown.classList.remove('active');
            });
        }

        // Toggle user dropdown
        if (userMenu) {
            userMenu.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
                // Close notification dropdown if open
                notificationDropdown.classList.remove('active');
            });
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (notificationIcon && !notificationIcon.contains(e.target) && notificationDropdown && !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.remove('active');
            }
            
            if (userMenu && !userMenu.contains(e.target) && userDropdown && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        });

        // Mark all as read button
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                markAllAsRead();
            });
        }

        // ============================================
        // CUSTOMERS MANAGEMENT FUNCTIONALITY
        // ============================================
        // Filter customers
        function filterCustomers(filter) {
            let filteredCustomers = customers;
            
            if (filter !== 'all') {
                filteredCustomers = customers.filter(customer => {
                    if (filter === 'new') {
                        // Customers who joined in the last 30 days
                        const joinDate = new Date(customer.joinDate);
                        const thirtyDaysAgo = new Date();
                        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                        return joinDate > thirtyDaysAgo;
                    } else if (filter === 'active') {
                        // Customers with orders in the last 30 days
                        const lastOrder = new Date(customer.lastOrderDate);
                        const thirtyDaysAgo = new Date();
                        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                        return lastOrder > thirtyDaysAgo && customer.type !== 'vip';
                    } else if (filter === 'vip') {
                        return customer.type === 'vip';
                    } else if (filter === 'inactive') {
                        // Customers with no orders in the last 90 days
                        const lastOrder = new Date(customer.lastOrderDate);
                        const ninetyDaysAgo = new Date();
                        ninetyDaysAgo.setDate(ninetyDaysAgo.getDate() - 90);
                        return lastOrder < ninetyDaysAgo;
                    }
                    return customer.type === filter;
                });
            }
            
            renderCustomers(filteredCustomers);
        }

        // Render customers in grid
        function renderCustomers(customersToRender) {
            customersGrid.innerHTML = '';
            
            customersToRender.forEach((customer, index) => {
                const card = document.createElement('div');
                card.className = `customer-card reveal ${index < 4 ? `reveal-delay-${index + 1}` : ''}`;
                
                // Format join date
                const joinDate = new Date(customer.joinDate);
                const formattedJoinDate = joinDate.toLocaleDateString();
                
                // Customer tag
                let tagClass = '';
                let tagText = '';
                
                if (customer.type === 'vip') {
                    tagClass = 'tag-vip';
                    tagText = 'VIP';
                } else if (new Date(customer.joinDate) > new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)) {
                    tagClass = 'tag-new';
                    tagText = 'New';
                } else {
                    tagClass = 'tag-regular';
                    tagText = 'Regular';
                }
                
                // Customer avatar initials
                const nameParts = customer.name.split(' ');
                const initials = nameParts.map(part => part[0]).join('').toUpperCase();
                
                card.innerHTML = `
                    <div class="customer-card-header">
                        <div class="customer-avatar">${initials}</div>
                        <div class="customer-info">
                            <h3>${customer.name}</h3>
                            <p>${customer.email}</p>
                            <p>${customer.phone}</p>
                            <span class="customer-tag ${tagClass}">${tagText}</span>
                        </div>
                    </div>
                    
                    <div class="customer-stats">
                        <div class="stat-item">
                            <div class="stat-value-small">${customer.totalOrders}</div>
                            <div class="stat-label-small">Orders</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value-small">₦${(customer.totalSpent / 1000).toFixed(0)}K</div>
                            <div class="stat-label-small">Spent</div>
                        </div>
                    </div>
                    
                    <div class="customer-actions">
                        <button class="action-btn view" data-id="${customer.id}">
                            <i class="fas fa-eye"></i>
                            View
                        </button>
                        <button class="action-btn message" data-id="${customer.id}">
                            <i class="fas fa-envelope"></i>
                            Message
                        </button>
                        <button class="action-btn edit" data-id="${customer.id}">
                            <i class="fas fa-edit"></i>
                            Edit
                        </button>
                    </div>
                `;
                
                customersGrid.appendChild(card);
            });
            
            // Add event listeners to action buttons
            document.querySelectorAll('.action-btn.view').forEach(btn => {
                btn.addEventListener('click', function() {
                    const customerId = parseInt(this.getAttribute('data-id'));
                    showCustomerDetails(customerId);
                });
            });
            
            document.querySelectorAll('.action-btn.message').forEach(btn => {
                btn.addEventListener('click', function() {
                    const customerId = parseInt(this.getAttribute('data-id'));
                    const customer = customers.find(c => c.id === customerId);
                    alert(`Opening message composer for ${customer.name}`);
                });
            });
            
            document.querySelectorAll('.action-btn.edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const customerId = parseInt(this.getAttribute('data-id'));
                    const customer = customers.find(c => c.id === customerId);
                    alert(`Opening edit form for ${customer.name}`);
                });
            });
        }

        // Show customer details in modal
        function showCustomerDetails(customerId) {
            const customer = customers.find(c => c.id === customerId);
            
            if (!customer) return;
            
            // Format dates
            const joinDate = new Date(customer.joinDate);
            const formattedJoinDate = joinDate.toLocaleDateString();
            
            const lastOrder = new Date(customer.lastOrderDate);
            const formattedLastOrderDate = lastOrder.toLocaleDateString();
            
            // Update modal content
            customerFullName.textContent = customer.name;
            customerEmail.textContent = customer.email;
            customerPhone.textContent = customer.phone;
            customerJoinDate.textContent = formattedJoinDate;
            customerType.textContent = customer.type.toUpperCase();
            customerAddress.textContent = customer.address;
            customerCity.textContent = customer.city;
            customerState.textContent = customer.state;
            totalOrdersElement.textContent = customer.totalOrders;
            totalSpentElement.textContent = `₦${customer.totalSpent.toLocaleString()}`;
            avgOrderValueElement.textContent = `₦${customer.avgOrderValue.toLocaleString()}`;
            lastOrderDateElement.textContent = formattedLastOrderDate;
            favoriteCuisineElement.textContent = customer.favoriteCuisine;
            preferredPaymentElement.textContent = customer.preferredPayment;
            deliveryPreferenceElement.textContent = customer.deliveryPreference;
            specialInstructionsElement.textContent = customer.specialInstructions || 'None';
            
            // Recent orders
            recentOrdersList.innerHTML = '';
            customer.orders.forEach(order => {
                const orderElement = document.createElement('div');
                orderElement.className = 'order-item';
                
                const orderDate = new Date(order.date);
                const formattedOrderDate = orderDate.toLocaleDateString();
                
                let statusClass = '';
                let statusText = '';
                
                if (order.status === 'completed') {
                    statusClass = 'status-completed';
                    statusText = 'Completed';
                } else if (order.status === 'pending') {
                    statusClass = 'status-pending';
                    statusText = 'Pending';
                }
                
                orderElement.innerHTML = `
                    <div>
                        <span class="order-id">${order.id}</span>
                        <div><small>${formattedOrderDate}</small></div>
                    </div>
                    <div>
                        <div>₦${order.amount.toLocaleString()}</div>
                        <span class="order-status ${statusClass}">${statusText}</span>
                    </div>
                `;
                recentOrdersList.appendChild(orderElement);
            });
            
            // Show modal
            customerDetailsModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Close sidebar on mobile when modal opens
            if (window.innerWidth <= 992 && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            }
            
            // Close dropdowns
            notificationDropdown.classList.remove('active');
            userDropdown.classList.remove('active');
        }

        // Close modal
        closeModal.addEventListener('click', function() {
            customerDetailsModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === customerDetailsModal) {
                customerDetailsModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && customerDetailsModal.style.display === 'flex') {
                customerDetailsModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Filter buttons event listeners
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                filterCustomers(this.getAttribute('data-filter'));
            });
        });

        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const filteredCustomers = customers.filter(customer => 
                customer.name.toLowerCase().includes(searchTerm) ||
                customer.email.toLowerCase().includes(searchTerm) ||
                customer.phone.includes(searchTerm)
            );
            renderCustomers(filteredCustomers);
        });

        // Modal action buttons
        sendMessageBtn.addEventListener('click', function() {
            alert('Opening message composer...');
        });

        createOrderBtn.addEventListener('click', function() {
            alert('Creating new order for customer...');
        });

        editCustomerBtn.addEventListener('click', function() {
            alert('Opening customer edit form...');
        });

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

        // Initialize
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

            // Initialize notifications
            renderNotifications();
            
            // Render all customers initially
            renderCustomers(customers);
            
            // Initialize scroll reveal
            window.addEventListener('scroll', revealOnScroll);
            // Trigger once on load to check initial position
            revealOnScroll();
        });

        // Handle window resize
        window.addEventListener('resize', revealOnScroll);
    </script>
</body>
</html>