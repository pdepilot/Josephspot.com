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
    <title>admin-menu-management</title>
    <link rel="icon" href="../images/logo3.png">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS VARIABLES */
        :root {
            --brown: #8b4513;
            --brown-light: #a0522d;
            --brown-dark: #654321;
            --white: #ffffff;
            --pale-orange: #ffe4b5;
            --pale-orange-light: #fff8dc;
            --accent: #d2691e;
            --text: #333333;
            --text-light: #666666;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
            --radius: 8px;
            
            /* Additional colors from customers dashboard */
            --primary: #8b4513;
            --primary-light: #a0522d;
            --primary-dark: #654321;
            --secondary: #d2691e;
            --light: #fff8dc;
            --dark: #333333;
            --success: #4CAF50;
            --warning: #FF9800;
            --danger: #F44336;
            --info: #2196F3;
            --gray: #f5f5f5;
            --gray-dark: #e0e0e0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* SIDEBAR FROM CUSTOMERS MANAGEMENT DASHBOARD */
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
            background: var(--brown);
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

        /* Sidebar Styles from customers management */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--brown) 0%, var(--brown-dark) 100%);
            color: white;
            padding: 20px 0;
            box-shadow: var(--shadow);
            z-index: 999;
            transition: var(--transition);
            position: fixed;
            height: 100vh;
            overflow-x: auto;
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
            color: white;
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
            background: var(--accent);
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
            color: white;
        }

        .admin-details p {
            font-size: 0.8rem;
            opacity: 0.8;
            color: rgba(255, 255, 255, 0.8);
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
            color: rgba(255, 255, 255, 0.7);
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

        /* TOPBAR - MATCHING NAVBAR STYLES */
        .topbar {
            background: var(--pale-orange);
            padding: 15px 25px;
            border-radius: var(--radius);
            border: 2px solid var(--brown);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }

        .search-box {
            display: flex;
            align-items: center;
            background: var(--white);
            border: 2px solid var(--brown);
            border-radius: 30px;
            padding: 10px 20px;
            width: 300px;
        }

        .search-box i {
            color: var(--brown);
            margin-right: 10px;
        }

        .search-box input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            font-family: "Poppins", sans-serif;
            color: var(--text);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            font-size: 1.3rem;
            color: var(--brown);
            cursor: pointer;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: var(--white);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* DASHBOARD STATS - IN MENU CARD STYLE */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: var(--transition);
            border-left: 4px solid var(--brown);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-left-color: var(--accent);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: var(--pale-orange);
            color: var(--brown);
        }

        .stat-info h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--brown);
            margin-bottom: 5px;
        }

        .stat-info p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        /* SECTION HEADER - EXACT MATCHING MENU SECTION STYLES */
        .section-header {
            background: var(--brown);
            color: var(--white);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }

        .section-header h2 {
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
        }

        /* BUTTONS - EXACT MATCHING MENU STYLES */
        .btn {
            padding: 12px 25px;
            border: 2px solid var(--brown);
            border-radius: 30px;
            font-family: "Poppins", sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            background: var(--white);
            color: var(--brown);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-primary {
            background: var(--brown);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--brown-light);
            border-color: var(--brown-light);
        }

        /* CATEGORY FILTER - EXACT MATCHING MENU FILTER STYLES */
        .category-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 25px;
            padding: 20px;
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
            border: 2px solid var(--pale-orange-light);
        }

        .filter-btn {
            background: var(--white);
            border: 2px solid var(--brown);
            color: var(--brown);
            padding: 10px 20px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            font-family: "Poppins", sans-serif;
        }

        .filter-btn.active,
        .filter-btn:hover {
            background: var(--brown);
            color: var(--white);
        }

        /* MENU ITEMS CONTAINER - EXACT SAME AS menu.php */
        .menu-wrapper {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        /* EXACT SAME MENU SECTION STYLES AS menu.php */
        .menu-section {
            margin-bottom: 50px;
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            opacity: 1;
            transform: translateY(0);
        }

        .menu-section.hidden {
            display: none;
        }

        /* EXACT SAME SECTION HEADER */
        .section-header-admin {
            background: var(--brown);
            color: var(--white);
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            border-radius: 15px;
        }

        .section-header-admin h2 {
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* EXACT SAME MENU ITEMS GRID FROM menu.php */
        .menu-items {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        /* EXACT SAME MENU ITEM CARD STYLES FROM menu.php */
        .menu-item-card {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px;
            background: var(--pale-orange-light);
            border-radius: 10px;
            transition: var(--transition);
            border-left: 4px solid transparent;
            position: relative;
        }

        .menu-item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border-left-color: var(--brown);
        }

        .menu-item-card.unavailable {
            opacity: 0.6;
            background: rgba(220, 53, 69, 0.05);
        }

        .menu-item-card.unavailable .item-name {
            text-decoration: line-through;
            color: var(--text-light);
        }

        .menu-item-card.special-item {
            background: linear-gradient(135deg, var(--brown), var(--brown-dark));
            color: var(--white);
        }

        .menu-item-card.special-item .item-name {
            color: var(--white);
        }

        .menu-item-card.special-item .item-description {
            color: rgba(255, 255, 255, 0.9);
        }

        .menu-item-card.special-item .item-price {
            color: var(--pale-orange);
        }

        .menu-item-card.special-item .tag {
            background: rgba(255, 255, 255, 0.2);
            color: var(--white);
        }

        /* EXACT SAME ITEM INFO FROM menu.php */
        .item-info {
            flex: 1;
        }

        .item-name {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--brown);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .item-name i {
            color: var(--accent);
        }

        .item-description {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .item-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 10px;
        }

        .tag {
            background: var(--pale-orange);
            color: var(--brown);
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .item-price {
            font-weight: 700;
            color: var(--brown);
            font-size: 1.3rem;
            white-space: nowrap;
            margin-left: 15px;
        }

        .price-note {
            font-size: 0.8rem;
            color: var(--text-light);
            display: block;
            margin-top: 5px;
        }

        /* ADMIN ACTION BUTTONS ON CARDS */
        .admin-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
            opacity: 0;
            transition: var(--transition);
        }

        .menu-item-card:hover .admin-actions {
            opacity: 1;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            color: var(--white);
            font-size: 0.9rem;
        }

        .edit-btn {
            background: var(--brown);
        }

        .edit-btn:hover {
            background: var(--brown-light);
            transform: scale(1.1);
        }

        .delete-btn {
            background: #dc3545;
        }

        .delete-btn:hover {
            background: #c82333;
            transform: scale(1.1);
        }

        .toggle-btn {
            background: var(--accent);
        }

        .toggle-btn:hover {
            background: var(--brown-light);
            transform: scale(1.1);
        }

        /* AVAILABILITY BADGE */
        .availability-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 1;
        }

        .available-badge {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .unavailable-badge {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        /* PAGINATION */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
        }

        .pagination-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--brown);
            background: var(--white);
            color: var(--brown);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            font-weight: 600;
        }

        .pagination-btn:hover,
        .pagination-btn.active {
            background: var(--brown);
            color: var(--white);
        }

        /* MODAL STYLES - MATCHING MENU THEME */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            backdrop-filter: blur(3px);
        }

        .modal {
            background: var(--white);
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            animation: modalSlideIn 0.3s ease;
            border: 2px solid var(--brown);
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
            padding: 25px;
            border-bottom: 2px solid var(--pale-orange);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--brown);
            color: var(--white);
        }

        .modal-header h3 {
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--white);
            transition: var(--transition);
        }

        .modal-close:hover {
            color: var(--pale-orange);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--brown);
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--pale-orange);
            border-radius: 10px;
            font-family: "Poppins", sans-serif;
            transition: var(--transition);
            background: var(--pale-orange-light);
            color: var(--text);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--brown);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid var(--pale-orange);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .form-check input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: var(--brown);
        }

        .form-check label {
            margin-bottom: 0;
            cursor: pointer;
        }

        /* TOAST NOTIFICATION */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--brown);
            color: var(--white);
            padding: 20px 25px;
            border-radius: 10px;
            box-shadow: var(--shadow-lg);
            display: none;
            align-items: center;
            gap: 15px;
            z-index: 3000;
            animation: slideInRight 0.3s ease;
            border-left: 4px solid var(--accent);
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

        /* RESPONSIVE DESIGN */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding-top: 70px;
            }

            .mobile-menu-toggle {
                display: flex;
            }

            .sidebar-overlay.active {
                display: block;
            }

            .search-box {
                width: 250px;
            }

            .menu-items {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .menu-items {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .dashboard-stats {
                grid-template-columns: 1fr;
            }

            .search-box {
                width: 200px;
            }

            .topbar {
                flex-wrap: wrap;
                gap: 15px;
            }

            .section-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .action-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
            }

            .category-filter {
                justify-content: center;
            }

            .filter-btn {
                padding: 8px 15px;
                font-size: 0.9rem;
            }

            .modal {
                width: 95%;
                margin: 10px;
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
        <!-- Sidebar from customers management -->
        <div class="sidebar" id="sidebar">
            <div class="logo-area">
                <img src="../images/logo3.png" alt="Joseph's Pot Logo">
                <h1>Admin Panel</h1>
            </div>
            
            <div class="admin-info">
                <div class="admin-avatar">JD</div>
                <div class="admin-details">
                    <h3>Joseph De Chef</h3>
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
                    <a href="admin-menu-management.php" class="active">
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
            <!-- Topbar -->
            <header class="topbar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search menu items...">
                </div>
                <div class="topbar-actions">
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                    <div class="user-info">
                        <div class="user-avatar">JD</div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Stats -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="statsTotalItems">65</h3>
                        <p>Total Menu Items</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="statsUnavailable">8</h3>
                        <p>Unavailable Items</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="statsCategories">9</h3>
                        <p>Categories</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="statsSpecial">15</h3>
                        <p>Special Items</p>
                    </div>
                </div>
            </div>

            <!-- Menu Management Section -->
            <section class="menu-management">
                <div class="section-header">
                    <h2><i class="fas fa-utensils"></i> Menu Management</h2>
                    <div class="action-buttons">
                        <button class="btn btn-primary" onclick="openAddModal()">
                            <i class="fas fa-plus"></i> Add New Item
                        </button>
                        <button class="btn" onclick="exportMenu()">
                            <i class="fas fa-download"></i> Export Menu
                        </button>
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="category-filter" id="categoryFilter">
                    <!-- Filter buttons will be generated dynamically -->
                </div>

                <!-- Menu Items Container -->
                <div class="menu-wrapper">
                    <div class="section-header-admin">
                        <h2><i class="fas fa-utensils"></i> <span id="currentCategoryTitle">All Menu Items</span></h2>
                        <span class="badge" id="categoryItemCount">65 items</span>
                    </div>

                    <div class="menu-items" id="menuItemsContainer">
                        <!-- Menu items will be loaded here in card format -->
                    </div>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <button class="pagination-btn" onclick="changePage(-1)"><i class="fas fa-chevron-left"></i></button>
                    <span style="padding: 0 10px; color: var(--brown);">Page <span id="currentPage">1</span> of <span id="totalPages">1</span></span>
                    <button class="pagination-btn" onclick="changePage(1)"><i class="fas fa-chevron-right"></i></button>
                </div>
            </section>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal-overlay" id="itemModal">
        <div class="modal">
            <div class="modal-header">
                <h3><i class="fas fa-utensils"></i> <span id="modalTitle">Add New Menu Item</span></h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="menuItemForm" onsubmit="saveMenuItem(event)">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="itemName">Item Name *</label>
                            <input type="text" id="itemName" class="form-control" required placeholder="e.g., Jollof Rice">
                        </div>
                        <div class="form-group">
                            <label for="itemCategory">Category *</label>
                            <select id="itemCategory" class="form-control" required>
                                <option value="">Select Category</option>
                                <option value="main-course">Main Course</option>
                                <option value="proteins">Proteins</option>
                                <option value="swallow">Swallow</option>
                                <option value="bulk-orders">Bulk Orders</option>
                                <option value="breakfast">Breakfast</option>
                                <option value="drinks">Drinks</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="itemPrice">Price (₦) *</label>
                            <input type="number" id="itemPrice" class="form-control" step="100" min="0" required placeholder="e.g., 3900">
                        </div>
                        <div class="form-group">
                            <label for="itemDisplayPrice">Display Price</label>
                            <input type="text" id="itemDisplayPrice" class="form-control" placeholder="e.g., ₦3,900">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="itemDescription">Description *</label>
                        <textarea id="itemDescription" class="form-control" rows="3" required placeholder="Describe the menu item..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="itemIcon">Icon Class (Font Awesome)</label>
                            <input type="text" id="itemIcon" class="form-control" placeholder="fas fa-utensils">
                            <small style="color: var(--text-light); font-size: 0.85rem;">e.g., fas fa-crown, fas fa-drumstick-bite</small>
                        </div>
                        <div class="form-group">
                            <label for="itemTags">Tags (comma separated)</label>
                            <input type="text" id="itemTags" class="form-control" placeholder="Popular, Chef's Special, Premium">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="isSpecial">
                                <label for="isSpecial">Mark as Special Item</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="isAvailable" checked>
                                <label for="isAvailable">Item is Available</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="modalSubmitBtn">
                            <i class="fas fa-save"></i> Add Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal" style="max-width: 500px;">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h3>
                <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong>"<span id="deleteItemName"></span>"</strong>?</p>
                <p style="color: #dc3545; font-weight: 600; margin-top: 15px;">
                    <i class="fas fa-exclamation-circle"></i> This action cannot be undone.
                </p>
                <div class="form-actions">
                    <button type="button" class="btn" onclick="closeDeleteModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="confirmDelete()" style="background: #dc3545; border-color: #dc3545;">
                        <i class="fas fa-trash"></i> Delete Item
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">Operation completed successfully!</span>
    </div>

    <script>
    // Logout confirmation function
    function confirmLogout() {
        return confirm('Are you sure you want to logout?');
    }

    // Menu items array - will be populated from database
    let menuItems = [];
    
    // API base URL
    const API_BASE = 'api/';

    // Categories from your menu.php
    const categories = [
        { id: 'all', name: 'All Items', icon: 'fas fa-utensils' },
        { id: 'main-course', name: 'Main Course', icon: 'fas fa-utensils' },
        { id: 'proteins', name: 'Proteins', icon: 'fas fa-drumstick-bite' },
        { id: 'swallow', name: 'Swallow', icon: 'fas fa-bread-slice' },
        { id: 'bulk-orders', name: 'Bulk Orders', icon: 'fas fa-people-carry' },
        { id: 'breakfast', name: 'Breakfast', icon: 'fas fa-sun' },
        { id: 'drinks', name: 'Drinks', icon: 'fas fa-trophy' }
        
    ];

    // App state
    let currentFilter = 'all';
    let currentPage = 1;
    const itemsPerPage = 12;
    let currentEditId = null;
    let currentDeleteId = null;

    // DOM Elements for sidebar functionality
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // Initialize dashboard
    document.addEventListener('DOMContentLoaded', async function() {
        initializeDashboard();
        await fetchMenuItems();
        loadCategoryFilter();
        loadMenuItems();
        updateStats();
        
        // Initialize scroll reveal
        window.addEventListener('scroll', revealOnScroll);
        revealOnScroll();
    });
    
    // Fetch menu items from database
    async function fetchMenuItems() {
        try {
            const response = await fetch(API_BASE + 'get_menu_items_admin.php');
            const result = await response.json();
            
            if(result.success) {
                menuItems = result.data || [];
            } else {
                console.error('Error loading menu items:', result.message);
                showToast('Error loading menu items: ' + result.message, 'error');
                menuItems = [];
            }
        } catch(error) {
            console.error('Error loading menu items:', error);
            showToast('Error loading menu items. Please check your connection.', 'error');
            menuItems = [];
        }
    }

    function initializeDashboard() {
        // Mobile menu toggle
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
                if (window.innerWidth <= 1024) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            searchMenuItems(e.target.value);
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1024) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            }
            revealOnScroll();
        });
    }

    function loadCategoryFilter() {
        const container = document.getElementById('categoryFilter');
        container.innerHTML = '';
        
        categories.forEach(category => {
            const count = category.id === 'all' 
                ? menuItems.length 
                : menuItems.filter(item => item.category === category.id).length;
            
            const button = document.createElement('button');
            button.className = `filter-btn ${category.id === currentFilter ? 'active' : ''}`;
            button.dataset.filter = category.id;
            button.innerHTML = `<i class="${category.icon}"></i> ${category.name} (${count})`;
            button.addEventListener('click', () => {
                currentFilter = category.id;
                currentPage = 1;
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                updateCategoryTitle();
                loadMenuItems();
            });
            
            container.appendChild(button);
        });
    }

    function updateCategoryTitle() {
        const category = categories.find(c => c.id === currentFilter);
        const count = currentFilter === 'all' 
            ? menuItems.length 
            : menuItems.filter(item => item.category === currentFilter).length;
        
        document.getElementById('currentCategoryTitle').textContent = category.name;
        document.getElementById('categoryItemCount').textContent = `${count} items`;
    }

    function loadMenuItems() {
        const container = document.getElementById('menuItemsContainer');
        let filteredItems = menuItems;

        // Apply category filter
        if (currentFilter !== 'all') {
            filteredItems = menuItems.filter(item => item.category === currentFilter);
        }

        // Calculate pagination
        const totalItems = filteredItems.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
        const pageItems = filteredItems.slice(startIndex, endIndex);

        // Clear container
        container.innerHTML = '';

        // Add items to container in card format
        pageItems.forEach((item, index) => {
            const card = document.createElement('div');
            const delayClass = index < 4 ? `reveal-delay-${index + 1}` : '';
            const unavailableClass = !item.isAvailable ? 'unavailable' : '';
            const specialClass = item.isSpecial ? 'special-item' : '';
            card.className = `menu-item-card reveal ${delayClass} ${unavailableClass} ${specialClass}`.trim();
            
            let displayPrice = item.displayPrice || `₦${item.price.toLocaleString()}`;
            let priceNote = '';
            
            if (item.category === 'bulk-orders') {
                const takeawayPrice = item.price + 2000;
                priceNote = `<span class="price-note">Takeaway: ₦${takeawayPrice.toLocaleString()}</span>`;
            }
            
            card.innerHTML = `
                <div class="availability-badge ${item.isAvailable ? 'available-badge' : 'unavailable-badge'}">
                    <i class="fas fa-${item.isAvailable ? 'check' : 'times'}"></i>
                    ${item.isAvailable ? 'Available' : 'Unavailable'}
                </div>
                
                <div class="admin-actions">
                    <button class="action-btn edit-btn" onclick="editMenuItem(${item.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-btn" onclick="deleteMenuItem(${item.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button class="action-btn toggle-btn" onclick="toggleAvailability(${item.id})" title="${item.isAvailable ? 'Mark Unavailable' : 'Mark Available'}">
                        <i class="fas fa-${item.isAvailable ? 'toggle-on' : 'toggle-off'}"></i>
                    </button>
                </div>
                
                <div class="item-info">
                    <div class="item-name">
                        ${item.icon ? `<i class="${item.icon}"></i>` : ''}
                        ${item.name}
                    </div>
                    <p class="item-description">${item.description}</p>
                    ${item.tags && item.tags.length > 0 ? `
                        <div class="item-tags">
                            ${item.tags.map(tag => `<span class="tag">${tag}</span>`).join('')}
                        </div>
                    ` : ''}
                </div>
                <div class="item-price">
                    ${displayPrice}
                    ${priceNote}
                </div>
            `;
            container.appendChild(card);
        });

        // Update pagination
        document.getElementById('currentPage').textContent = currentPage;
        document.getElementById('totalPages').textContent = totalPages;
        
        // Update category title
        updateCategoryTitle();
        
        // Trigger reveal animation
        setTimeout(() => revealOnScroll(), 100);
    }

    function updateStats() {
        const totalItems = menuItems.length;
        const unavailableItems = menuItems.filter(item => !item.isAvailable).length;
        const specialItems = menuItems.filter(item => item.isSpecial).length;
        const uniqueCategories = [...new Set(menuItems.map(item => item.category))].length;

        document.getElementById('statsTotalItems').textContent = totalItems;
        document.getElementById('statsUnavailable').textContent = unavailableItems;
        document.getElementById('statsSpecial').textContent = specialItems;
        document.getElementById('statsCategories').textContent = uniqueCategories;
    }

    function searchMenuItems(searchTerm) {
        const searchLower = searchTerm.toLowerCase().trim();
        if (!searchLower) {
            loadMenuItems();
            return;
        }

        const filteredItems = menuItems.filter(item => 
            item.name.toLowerCase().includes(searchLower) ||
            item.description.toLowerCase().includes(searchLower) ||
            (item.tags && item.tags.some(tag => tag.toLowerCase().includes(searchLower)))
        );

        const container = document.getElementById('menuItemsContainer');
        
        if (filteredItems.length === 0) {
            container.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-search fa-2x" style="color: var(--text-light);"></i><p style="margin-top: 15px; color: var(--text-light);">No menu items found matching your search</p></div>';
            return;
        }

        // Display filtered items without pagination
        container.innerHTML = '';
        
        filteredItems.forEach(item => {
            const card = document.createElement('div');
            const unavailableClass = !item.isAvailable ? 'unavailable' : '';
            const specialClass = item.isSpecial ? 'special-item' : '';
            card.className = `menu-item-card ${unavailableClass} ${specialClass}`.trim();
            
            let displayPrice = item.displayPrice || `₦${item.price.toLocaleString()}`;
            let priceNote = '';
            
            if (item.category === 'bulk-orders') {
                const takeawayPrice = item.price + 2000;
                priceNote = `<span class="price-note">Takeaway: ₦${takeawayPrice.toLocaleString()}</span>`;
            }
            
            card.innerHTML = `
                <div class="availability-badge ${item.isAvailable ? 'available-badge' : 'unavailable-badge'}">
                    <i class="fas fa-${item.isAvailable ? 'check' : 'times'}"></i>
                    ${item.isAvailable ? 'Available' : 'Unavailable'}
                </div>
                
                <div class="admin-actions">
                    <button class="action-btn edit-btn" onclick="editMenuItem(${item.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-btn" onclick="deleteMenuItem(${item.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button class="action-btn toggle-btn" onclick="toggleAvailability(${item.id})" title="${item.isAvailable ? 'Mark Unavailable' : 'Mark Available'}">
                        <i class="fas fa-${item.isAvailable ? 'toggle-on' : 'toggle-off'}"></i>
                    </button>
                </div>
                
                <div class="item-info">
                    <div class="item-name">
                        ${item.icon ? `<i class="${item.icon}"></i>` : ''}
                        ${item.name}
                    </div>
                    <p class="item-description">${item.description}</p>
                    ${item.tags && item.tags.length > 0 ? `
                        <div class="item-tags">
                            ${item.tags.map(tag => `<span class="tag">${tag}</span>`).join('')}
                        </div>
                    ` : ''}
                </div>
                <div class="item-price">
                    ${displayPrice}
                    ${priceNote}
                </div>
            `;
            container.appendChild(card);
        });
    }

    function changePage(direction) {
        const totalItems = currentFilter === 'all' 
            ? menuItems.length 
            : menuItems.filter(item => item.category === currentFilter).length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        
        const newPage = currentPage + direction;
        if (newPage >= 1 && newPage <= totalPages) {
            currentPage = newPage;
            loadMenuItems();
        }
    }

    // Modal Functions
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Menu Item';
        document.getElementById('modalSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Add Item';
        document.getElementById('menuItemForm').reset();
        currentEditId = null;
        document.getElementById('itemModal').style.display = 'flex';
    }

    function editMenuItem(id) {
        const item = menuItems.find(i => i.id === id);
        if (!item) return;

        document.getElementById('modalTitle').textContent = 'Edit Menu Item';
        document.getElementById('modalSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Update Item';
        
        // Fill form with item data
        document.getElementById('itemName').value = item.name;
        document.getElementById('itemCategory').value = item.category;
        document.getElementById('itemPrice').value = item.price;
        document.getElementById('itemDisplayPrice').value = item.displayPrice || '';
        document.getElementById('itemDescription').value = item.description || '';
        document.getElementById('itemIcon').value = item.icon || '';
        document.getElementById('itemTags').value = item.tags ? item.tags.join(', ') : '';
        document.getElementById('isSpecial').checked = item.isSpecial || false;
        document.getElementById('isAvailable').checked = item.isAvailable;

        currentEditId = id;
        document.getElementById('itemModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('itemModal').style.display = 'none';
    }

    async function saveMenuItem(event) {
        event.preventDefault();
        
        // Get form values
        const formData = {
            name: document.getElementById('itemName').value.trim(),
            category: document.getElementById('itemCategory').value,
            price: parseFloat(document.getElementById('itemPrice').value),
            displayPrice: document.getElementById('itemDisplayPrice').value.trim(),
            description: document.getElementById('itemDescription').value.trim(),
            icon: document.getElementById('itemIcon').value.trim(),
            tags: document.getElementById('itemTags').value.split(',').map(tag => tag.trim()).filter(tag => tag),
            isSpecial: document.getElementById('isSpecial').checked,
            isAvailable: document.getElementById('isAvailable').checked
        };

        // Validate
        if (!formData.name || !formData.category || !formData.price || !formData.description) {
            showToast('Please fill in all required fields', 'error');
            return;
        }

        // Format display price if not provided
        if (!formData.displayPrice) {
            formData.displayPrice = `₦${formData.price.toLocaleString()}`;
        }

        // Add ID if editing
        if (currentEditId) {
            formData.id = currentEditId;
        }

        try {
            const response = await fetch(API_BASE + 'save_menu_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                showToast(result.message || 'Menu item saved successfully!', 'success');
                closeModal();
                await fetchMenuItems();
                loadMenuItems();
                updateStats();
                loadCategoryFilter();
            } else {
                showToast(result.message || 'Error saving menu item', 'error');
            }
        } catch (error) {
            console.error('Error saving menu item:', error);
            showToast('Error saving menu item. Please try again.', 'error');
        }
    }

    function deleteMenuItem(id) {
        const item = menuItems.find(i => i.id === id);
        if (!item) return;

        document.getElementById('deleteItemName').textContent = item.name;
        currentDeleteId = id;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        currentDeleteId = null;
    }

    async function confirmDelete() {
        if (!currentDeleteId) return;

        try {
            const response = await fetch(API_BASE + 'delete_menu_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: currentDeleteId })
            });

            const result = await response.json();

            if (result.success) {
                showToast(result.message || 'Menu item deleted successfully!', 'success');
                closeDeleteModal();
                await fetchMenuItems();
                loadMenuItems();
                updateStats();
                loadCategoryFilter();
            } else {
                showToast(result.message || 'Error deleting menu item', 'error');
                closeDeleteModal();
            }
        } catch (error) {
            console.error('Error deleting menu item:', error);
            showToast('Error deleting menu item. Please try again.', 'error');
            closeDeleteModal();
        }
    }

    async function toggleAvailability(id) {
        try {
            const response = await fetch(API_BASE + 'toggle_menu_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            });

            const result = await response.json();

            if (result.success) {
                showToast(result.message || 'Availability updated successfully!', 'success');
                await fetchMenuItems();
                loadMenuItems();
                updateStats();
            } else {
                showToast(result.message || 'Error updating availability', 'error');
            }
        } catch (error) {
            console.error('Error toggling availability:', error);
            showToast('Error updating availability. Please try again.', 'error');
        }
    }

    function exportMenu() {
        const dataStr = JSON.stringify(menuItems, null, 2);
        const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
        
        const exportFileDefaultName = `josephs-pot-menu-${new Date().toISOString().split('T')[0]}.json`;
        
        const linkElement = document.createElement('a');
        linkElement.setAttribute('href', dataUri);
        linkElement.setAttribute('download', exportFileDefaultName);
        linkElement.click();
        
        showToast('Menu exported successfully!', 'success');
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        
        toastMessage.textContent = message;
        toast.className = `toast ${type}`;
        toast.style.display = 'flex';
        
        // Set appropriate icon and color
        const icon = toast.querySelector('i');
        switch(type) {
            case 'success':
                icon.className = 'fas fa-check-circle';
                toast.style.background = 'var(--brown)';
                toast.style.borderLeftColor = 'var(--accent)';
                break;
            case 'error':
                icon.className = 'fas fa-exclamation-circle';
                toast.style.background = '#dc3545';
                toast.style.borderLeftColor = '#c82333';
                break;
            case 'info':
                icon.className = 'fas fa-info-circle';
                toast.style.background = '#17a2b8';
                toast.style.borderLeftColor = '#138496';
                break;
        }
        
        setTimeout(() => {
            toast.style.display = 'none';
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

    // Close modals when clicking outside
    window.onclick = function(event) {
        const itemModal = document.getElementById('itemModal');
        const deleteModal = document.getElementById('deleteModal');
        
        if (event.target === itemModal) {
            closeModal();
        }
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    }
</script>
</body>
</html>