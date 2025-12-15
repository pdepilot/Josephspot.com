<?php
// Add PHP session and authentication check at the top
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit;
}

// Get admin user data
$username = 'Admin Joseph';
$user_initials = 'AJ';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Online Menu Management - Joseph's Pot</title>
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

        /* Notification and User Menu Styles */
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

        /* Menu Management Styles */
        .menu-management {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .menu-management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .menu-management-header h3 {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .add-item-btn {
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

        .add-item-btn:hover {
            background: var(--primary-dark);
        }

        .menu-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-btn {
            background: var(--gray);
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-btn.active {
            background: var(--primary);
            color: white;
        }

        .menu-items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .menu-item-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
        }

        .menu-item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .menu-item-card.out-of-stock {
            opacity: 0.7;
        }

        .item-status {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--success);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            z-index: 2;
        }

        .item-status.out-of-stock {
            background: var(--danger);
        }

        .item-image {
            height: 180px;
            width: 100%;
            background: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            overflow: hidden;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-details {
            padding: 15px;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .item-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .item-price {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
            margin-top: 5px;
        }

        .item-description {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .item-category {
            display: inline-block;
            background: var(--light);
            color: var(--primary);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-bottom: 15px;
        }

        .item-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .item-action-btn {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: var(--transition);
            font-size: 0.9rem;
            min-width: 80px;
        }

        .edit-btn {
            background: var(--info);
            color: white;
        }

        .toggle-stock-btn {
            background: var(--warning);
            color: white;
        }

        .delete-btn {
            background: var(--danger);
            color: white;
        }

        .item-action-btn:hover {
            transform: translateY(-2px);
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
            max-width: 600px;
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

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--gray-dark);
            border-radius: 6px;
            font-size: 1rem;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .image-upload {
            border: 2px dashed var(--gray-dark);
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .image-upload:hover {
            border-color: var(--primary);
        }

        .image-upload i {
            font-size: 2rem;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            display: none;
            border-radius: 6px;
            object-fit: contain;
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

        /* Responsive Design */
        @media (max-width: 1200px) {
            .menu-items-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
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
            
            .menu-management-header h3 {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 768px) {
            .menu-items-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .menu-management-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .add-item-btn {
                width: 100%;
                justify-content: center;
            }
            
            .item-actions {
                flex-direction: column;
            }
            
            .item-action-btn {
                width: 100%;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .modal-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .menu-items-grid {
                grid-template-columns: 1fr;
            }
            
            .real-time-clock {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .header-actions {
                flex-direction: column;
                gap: 15px;
            }
            
            .search-box {
                max-width: 100%;
            }
            
            .notification-user-container {
                align-self: flex-end;
                margin-left: auto;
            }
            
            .menu-filters {
                flex-direction: column;
            }
            
            .filter-btn {
                width: 100%;
                text-align: center;
            }
            
            .item-header {
                flex-direction: column;
            }
            
            .item-price {
                align-self: flex-start;
            }
            
            .modal-content {
                padding: 20px 15px;
            }
            
            .image-upload {
                padding: 15px;
            }
            
            .image-upload i {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .logo-area h1 {
                font-size: 1.2rem;
            }
            
            .header h2 {
                font-size: 1.3rem;
            }
            
            .item-name {
                font-size: 1rem;
            }
            
            .item-price {
                font-size: 1rem;
            }
            
            .item-description {
                font-size: 0.85rem;
            }
            
            .modal-header h3 {
                font-size: 1.1rem;
            }
            
            .add-item-btn, .filter-btn {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            
            .item-action-btn {
                padding: 10px;
                font-size: 0.9rem;
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
                    <a href="admin-orders.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="active">
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
                    <a href="admin-logout.php" onclick="return confirmLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
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
                    <i class="fas fa-map-marker-alt"></i> Lagos, Nigeria
                </div>
            </div>
            
            <div class="header">
                <h2>Order Online Menu Management</h2>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchMenu" placeholder="Search menu items...">
                    </div>
                    <div class="notification-user-container">
                        <div class="notification-icon">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </div>
                        <div class="user-menu">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Menu Management Section -->
            <div class="menu-management reveal">
                <div class="menu-management-header">
                    <h3>Manage Online Menu Items</h3>
                    <button class="add-item-btn" id="addItemBtn">
                        <i class="fas fa-plus"></i>
                        Add New Item
                    </button>
                </div>
                
                <div class="menu-filters">
                    <button class="filter-btn active" data-filter="all">All Items</button>
                    <button class="filter-btn" data-filter="available">Available</button>
                    <button class="filter-btn" data-filter="out-of-stock">Out of Stock</button>
                    <button class="filter-btn" data-filter="soups">Soups</button>
                    <button class="filter-btn" data-filter="starters">Starters</button>
                    <button class="filter-btn" data-filter="main">Main Courses</button>
                    <button class="filter-btn" data-filter="noodles">Noodles</button>
                    <button class="filter-btn" data-filter="drinks">Drinks</button>
                </div>
                
                <div class="menu-items-grid" id="menuItemsGrid">
                    <!-- Menu items will be dynamically added here -->
                </div>
            </div>
            
            <div class="footer">
                <p>&copy; 2025 Joseph's Pot Admin Dashboard. All rights reserved | Developed By ERIBS tech</p>
            </div>
        </div>
    </div>

    <!-- Add/Edit Item Modal -->
    <div class="modal" id="itemModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Menu Item</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <form id="itemForm">
                <div class="form-group">
                    <label for="itemName">Item Name</label>
                    <input type="text" id="itemName" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="itemPrice">Price (₦)</label>
                        <input type="number" id="itemPrice" min="0" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="itemCategory">Category</label>
                        <select id="itemCategory" required>
                            <option value="">Select Category</option>
                            <option value="soups">Soups</option>
                            <option value="main">Main Dishes</option>
                            <option value="sides">Side Dishes</option>
                            <option value="drinks">Drinks</option>
                            <option value="desserts">Desserts</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="itemDescription">Description</label>
                    <textarea id="itemDescription" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="itemIngredients">Ingredients (comma separated)</label>
                    <input type="text" id="itemIngredients" placeholder="e.g. Chicken, Pepper, Onions, Spices">
                </div>
                
                <div class="form-group">
                    <label for="itemCookingTime">Estimated Cooking Time (minutes)</label>
                    <input type="number" id="itemCookingTime" min="0" value="20">
                </div>
                
                <div class="form-group">
                    <label>Item Image</label>
                    <div class="image-upload" id="imageUpload">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Click to upload item image</p>
                        <img id="imagePreview" class="image-preview" src="" alt="Preview">
                    </div>
                    <input type="file" id="itemImage" accept="image/*" style="display: none;">
                </div>
                
                <div class="form-group">
                    <label for="itemAvailability">Availability</label>
                    <select id="itemAvailability" required>
                        <option value="available">Available</option>
                        <option value="out-of-stock">Out of Stock</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Add Item</button>
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
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateString = now.toLocaleDateString('en-US', options);
            
            // Update the DOM
            document.getElementById('currentTime').textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
            document.getElementById('currentDate').textContent = dateString;
        }
        
        // Update the clock immediately and then every second
        updateClock();
        setInterval(updateClock, 1000);

        // Sample menu data - Renamed to avoid conflict with DOM elements
        const sampleMenuData = [
            {
                id: 1,
                name: "Ofe Owerri Special",
                price: 3500,
                category: "soups",
                description: "A rich traditional Igbo soup made with assorted meats, fish and vegetables.",
                ingredients: "Assorted meat, Stockfish, Ugwu leaves, Palm oil, Pepper",
                cookingTime: 45,
                image: "",
                available: true
            },
            {
                id: 2,
                name: "Nkwobi",
                price: 2800,
                category: "main",
                description: "Spicy cow foot delicacy served with a special sauce.",
                ingredients: "Cow foot, Palm oil, Utazi leaves, Ehuru, Pepper",
                cookingTime: 30,
                image: "",
                available: true
            },
            {
                id: 3,
                name: "Egusi Delight",
                price: 3200,
                category: "soups",
                description: "Melon seed soup with assorted meat and fish, served with fufu.",
                ingredients: "Egusi, Assorted meat, Stockfish, Bitter leaf, Palm oil",
                cookingTime: 40,
                image: "",
                available: false
            },
            {
                id: 4,
                name: "Jollof Rice",
                price: 2500,
                category: "main",
                description: "Popular party rice cooked with tomatoes, peppers and spices.",
                ingredients: "Rice, Tomatoes, Pepper, Onions, Chicken, Spices",
                cookingTime: 35,
                image: "",
                available: true
            },
            {
                id: 5,
                name: "Palm Wine",
                price: 1500,
                category: "drinks",
                description: "Fresh traditional palm wine, naturally fermented.",
                ingredients: "Palm wine",
                cookingTime: 0,
                image: "",
                available: true
            },
            {
                id: 6,
                name: "Pepper Soup",
                price: 2200,
                category: "soups",
                description: "Spicy broth with goat meat, perfect for cold days.",
                ingredients: "Goat meat, Pepper, Uziza, Ehuru, Utazi",
                cookingTime: 25,
                image: "",
                available: true
            }
        ];

        // DOM Elements
        const addItemBtn = document.getElementById('addItemBtn');
        const itemModal = document.getElementById('itemModal');
        const closeModal = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const itemForm = document.getElementById('itemForm');
        const menuItemsGrid = document.getElementById('menuItemsGrid');
        const searchMenu = document.getElementById('searchMenu');
        const filterBtns = document.querySelectorAll('.filter-btn');
        const imageUpload = document.getElementById('imageUpload');
        const itemImage = document.getElementById('itemImage');
        const imagePreview = document.getElementById('imagePreview');
        const modalTitle = document.getElementById('modalTitle');
        const submitBtn = document.getElementById('submitBtn');
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebar = document.getElementById('sidebar');

        // Use a different variable name for the menu items array
        let menuData = [...sampleMenuData];
        let currentFilter = 'all';
        let currentEditId = null;

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
        const menuItemLinks = document.querySelectorAll('.menu-item a');
        menuItemLinks.forEach(item => {
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
        addItemBtn.addEventListener('click', function() {
            currentEditId = null;
            modalTitle.textContent = 'Add New Menu Item';
            submitBtn.textContent = 'Add Item';
            itemForm.reset();
            imagePreview.style.display = 'none';
            itemModal.style.display = 'flex';
        });

        closeModal.addEventListener('click', function() {
            itemModal.style.display = 'none';
        });

        cancelBtn.addEventListener('click', function() {
            itemModal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === itemModal) {
                itemModal.style.display = 'none';
            }
        });

        // Image upload functionality
        imageUpload.addEventListener('click', function() {
            itemImage.click();
        });

        itemImage.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Check file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size exceeds 5MB limit. Please choose a smaller file.');
                    return;
                }
                
                // Check file type
                if (!file.type.match('image.*')) {
                    alert('Please select an image file (JPG, PNG, or GIF).');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Filter functionality
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.getAttribute('data-filter');
                renderMenuItems();
            });
        });

        // Search functionality
        searchMenu.addEventListener('input', function() {
            renderMenuItems();
        });

        // Handle form submission
        itemForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('itemName').value;
            const price = parseFloat(document.getElementById('itemPrice').value);
            const category = document.getElementById('itemCategory').value;
            const description = document.getElementById('itemDescription').value;
            const ingredients = document.getElementById('itemIngredients').value;
            const cookingTime = parseInt(document.getElementById('itemCookingTime').value);
            const available = document.getElementById('itemAvailability').value === 'available';
            
            if (currentEditId) {
                // Edit existing item
                const index = menuData.findIndex(item => item.id === currentEditId);
                if (index !== -1) {
                    menuData[index] = {
                        ...menuData[index],
                        name,
                        price,
                        category,
                        description,
                        ingredients,
                        cookingTime,
                        available,
                        image: imagePreview.src || menuData[index].image
                    };
                }
            } else {
                // Add new item
                const newItem = {
                    id: menuData.length > 0 ? Math.max(...menuData.map(item => item.id)) + 1 : 1,
                    name,
                    price,
                    category,
                    description,
                    ingredients,
                    cookingTime,
                    image: imagePreview.src || "",
                    available
                };
                
                menuData.push(newItem);
            }
            
            // Update UI
            renderMenuItems();
            
            // Close modal and reset form
            itemModal.style.display = 'none';
            itemForm.reset();
            imagePreview.style.display = 'none';
            
            // Show success message
            alert(`Menu item ${currentEditId ? 'updated' : 'added'} successfully!`);
        });

        // Render menu items in the grid
        function renderMenuItems() {
            menuItemsGrid.innerHTML = '';
            
            const searchTerm = searchMenu.value.toLowerCase();
            
            const filteredItems = menuData.filter(item => {
                const matchesSearch = item.name.toLowerCase().includes(searchTerm) || 
                                     item.description.toLowerCase().includes(searchTerm);
                
                if (currentFilter === 'all') return matchesSearch;
                if (currentFilter === 'available') return matchesSearch && item.available;
                if (currentFilter === 'out-of-stock') return matchesSearch && !item.available;
                return matchesSearch && item.category === currentFilter;
            });
            
            if (filteredItems.length === 0) {
                menuItemsGrid.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-light);">
                        <i class="fas fa-utensils" style="font-size: 3rem; margin-bottom: 15px;"></i>
                        <h3>No menu items found</h3>
                        <p>Try adjusting your search or filter criteria</p>
                    </div>
                `;
                return;
            }
            
            filteredItems.forEach(item => {
                const menuItemCard = document.createElement('div');
                menuItemCard.className = `menu-item-card ${!item.available ? 'out-of-stock' : ''} reveal`;
                menuItemCard.innerHTML = `
                    <div class="item-status ${item.available ? '' : 'out-of-stock'}">
                        ${item.available ? 'Available' : 'Out of Stock'}
                    </div>
                    <div class="item-image">
                        ${item.image ? `<img src="${item.image}" alt="${item.name}">` : 
                          `<i class="fas fa-utensils"></i>`}
                    </div>
                    <div class="item-details">
                        <div class="item-header">
                            <div>
                                <div class="item-name">${item.name}</div>
                                <div class="item-category">${getCategoryName(item.category)}</div>
                            </div>
                            <div class="item-price">₦${item.price.toLocaleString()}</div>
                        </div>
                        <div class="item-description">${item.description}</div>
                        <div class="item-actions">
                            <button class="item-action-btn edit-btn" data-id="${item.id}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="item-action-btn toggle-stock-btn" data-id="${item.id}">
                                <i class="fas ${item.available ? 'fa-times' : 'fa-check'}"></i> 
                                ${item.available ? 'Out of Stock' : 'Available'}
                            </button>
                            <button class="item-action-btn delete-btn" data-id="${item.id}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                `;
                
                menuItemsGrid.appendChild(menuItemCard);
            });
            
            // Add event listeners to action buttons
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = parseInt(this.getAttribute('data-id'));
                    editMenuItem(id);
                });
            });
            
            document.querySelectorAll('.toggle-stock-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = parseInt(this.getAttribute('data-id'));
                    toggleStock(id);
                });
            });
            
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = parseInt(this.getAttribute('data-id'));
                    deleteMenuItem(id);
                });
            });
            
            // Trigger reveal animations
            setTimeout(() => {
                const reveals = menuItemsGrid.querySelectorAll('.reveal');
                reveals.forEach(reveal => {
                    const windowHeight = window.innerHeight;
                    const elementTop = reveal.getBoundingClientRect().top;
                    const elementVisible = 150;
                    
                    if (elementTop < windowHeight - elementVisible) {
                        reveal.classList.add('active');
                    }
                });
            }, 100);
        }

        // Get category display name
        function getCategoryName(category) {
            const categories = {
                'soups': 'Soups',
                'main': 'Main Dishes',
                'sides': 'Side Dishes',
                'drinks': 'Drinks',
                'desserts': 'Desserts'
            };
            return categories[category] || category;
        }

        // Edit menu item
        function editMenuItem(id) {
            const item = menuData.find(item => item.id === id);
            if (!item) return;
            
            currentEditId = id;
            modalTitle.textContent = 'Edit Menu Item';
            submitBtn.textContent = 'Update Item';
            
            document.getElementById('itemName').value = item.name;
            document.getElementById('itemPrice').value = item.price;
            document.getElementById('itemCategory').value = item.category;
            document.getElementById('itemDescription').value = item.description;
            document.getElementById('itemIngredients').value = item.ingredients;
            document.getElementById('itemCookingTime').value = item.cookingTime;
            document.getElementById('itemAvailability').value = item.available ? 'available' : 'out-of-stock';
            
            if (item.image) {
                imagePreview.src = item.image;
                imagePreview.style.display = 'block';
            } else {
                imagePreview.style.display = 'none';
            }
            
            itemModal.style.display = 'flex';
        }

        // Toggle stock status
        function toggleStock(id) {
            const item = menuData.find(item => item.id === id);
            if (!item) return;
            
            item.available = !item.available;
            renderMenuItems();
            
            // Show confirmation message
            alert(`"${item.name}" is now ${item.available ? 'available' : 'out of stock'} on the online menu.`);
        }

        // Delete menu item
        function deleteMenuItem(id) {
            if (!confirm('Are you sure you want to delete this menu item? This action cannot be undone.')) {
                return;
            }
            
            const itemIndex = menuData.findIndex(item => item.id === id);
            if (itemIndex === -1) return;
            
            const itemName = menuData[itemIndex].name;
            menuData.splice(itemIndex, 1);
            renderMenuItems();
            
            // Show confirmation message
            alert(`"${itemName}" has been removed from the online menu.`);
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Simple animation for real-time clock
            const realTimeClock = document.querySelector('.real-time-clock');
            realTimeClock.style.opacity = '0';
            realTimeClock.style.transform = 'translateY(20px)';
            realTimeClock.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            setTimeout(() => {
                realTimeClock.style.opacity = '1';
                realTimeClock.style.transform = 'translateY(0)';
            }, 100);
            
            renderMenuItems();
            
            // Initialize scroll reveal
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
            
            window.addEventListener('scroll', revealOnScroll);
            // Trigger once on load to check initial position
            revealOnScroll();
        });
    </script>
</body>
</html>