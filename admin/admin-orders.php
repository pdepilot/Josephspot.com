<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/logo3.png">
    <title>Orders Management - Joseph's Pot</title>
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
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 20px 0;
            box-shadow: var(--shadow);
            z-index: 100;
            transition: var(--transition);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transform: translateX(0);
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar.collapsed .logo-area h1,
        .sidebar.collapsed .admin-details,
        .sidebar.collapsed .menu-label,
        .sidebar.collapsed .menu-item span {
            display: none;
        }

        .sidebar.collapsed .admin-info {
            justify-content: center;
            padding: 15px 10px;
        }

        .sidebar.collapsed .menu-item a {
            justify-content: center;
            padding: 15px;
        }

        .sidebar.collapsed .menu-item i {
            margin-right: 0;
        }

        /* Mobile Sidebar Slide Effect */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                transform: translateX(0);
            }

            .sidebar .logo-area h1,
            .sidebar .admin-details,
            .sidebar .menu-label,
            .sidebar .menu-item span {
                display: none;
            }

            .sidebar .admin-info {
                justify-content: center;
                padding: 15px 10px;
            }

            .sidebar .menu-item a {
                justify-content: center;
                padding: 15px;
            }

            .sidebar .menu-item i {
                margin-right: 0;
            }

            .sidebar:hover {
                width: 260px;
                transform: translateX(0);
            }

            .sidebar:hover .logo-area h1,
            .sidebar:hover .admin-details,
            .sidebar:hover .menu-label,
            .sidebar:hover .menu-item span {
                display: block;
            }

            .sidebar:hover .admin-info {
                justify-content: flex-start;
                padding: 15px 20px;
            }

            .sidebar:hover .menu-item a {
                justify-content: flex-start;
                padding: 12px 15px;
            }

            .sidebar:hover .menu-item i {
                margin-right: 12px;
            }

            .main-content {
                margin-left: 80px;
            }
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
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 80px;
            }
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            margin-bottom: 25px;
        }

        .header h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 10px 15px 10px 40px;
            border: none;
            border-radius: 30px;
            background: white;
            box-shadow: var(--shadow);
            width: 250px;
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

        .notification-icon,
        .user-menu {
            position: relative;
            cursor: pointer;
        }

        .notification-icon i,
        .user-menu i {
            font-size: 1.3rem;
            color: var(--primary);
            transition: var(--transition);
        }

        .notification-icon:hover i,
        .user-menu:hover i {
            color: var(--secondary);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
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

        .stat-card.pending::before {
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

        .stat-card.pending i {
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

        /* Order Filters */
        .order-filters {
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
        }

        .filter-btn.active {
            background: var(--primary);
            color: white;
        }

        .filter-btn:hover:not(.active) {
            background: var(--gray);
        }

        /* Orders Table */
        .orders-table-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th {
            text-align: left;
            padding: 15px;
            border-bottom: 2px solid var(--gray);
            color: var(--primary);
            font-weight: 600;
        }

        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid var(--gray);
            color: var(--text) !important;
        }

        .orders-table tr.reveal.active td {
            color: var(--text) !important;
            opacity: 1 !important;
        }

        .orders-table tr.reveal td {
            color: var(--text) !important;
        }

        /* Force visibility for all table cells */
        .orders-table td {
            opacity: 1 !important;
            visibility: visible !important;
        }

        .orders-table tr:last-child td {
            border-bottom: none;
        }

        .orders-table tr:hover {
            background: rgba(139, 69, 19, 0.05);
        }

        .order-id {
            font-weight: 600;
            color: var(--primary);
        }

        .order-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-pending {
            background: rgba(255, 152, 0, 0.2);
            color: var(--warning);
        }

        .status-completed {
            background: rgba(76, 175, 80, 0.2);
            color: var(--success);
        }

        .status-cancelled {
            background: rgba(244, 67, 54, 0.2);
            color: var(--danger);
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .payment-method i {
            font-size: 1.2rem;
        }

        .payment-cash {
            color: var(--success);
        }

        .payment-card {
            color: var(--info);
        }

        .payment-transfer {
            color: var(--primary);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .action-btn.view {
            background: var(--info);
            color: white;
        }

        .action-btn.edit {
            background: var(--warning);
            color: white;
        }

        .action-btn.delete {
            background: var(--danger);
            color: white;
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        /* Order Details Modal */
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

        .order-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .order-detail-group {
            margin-bottom: 20px;
        }

        .order-detail-group h4 {
            font-size: 1rem;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .order-detail-group p {
            margin-bottom: 5px;
        }

        .order-items {
            margin-top: 20px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-name {
            font-weight: 500;
        }

        .item-price {
            color: var(--text-light);
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid var(--gray);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .payment-proof {
            margin-top: 20px;
            text-align: center;
        }

        .payment-proof img {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-top: 10px;
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

        /* Responsive Design */
        @media (max-width: 1200px) {
            .order-details-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }

            .search-box input {
                width: 180px;
            }

            .order-filters {
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .stats-cards {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .header-actions {
                width: 100%;
                justify-content: space-between;
            }

            .search-box input {
                width: 100%;
            }

            .order-filters {
                flex-direction: column;
            }

            .filter-btn {
                width: 100%;
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
    </style>
</head>

<body>
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
                    <a href="admin-order-online.php" class="active">
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
                    <a href="admin-gallery.php">
                        <i class="fas fa-image"></i>
                        <span>Gallery</span>
                    </a>
                </li>
                <!-- <li class="menu-item">
                    <a href="#">
                        <i class="fas fa-newspaper"></i>
                        <span>Blog</span>
                    </a>
                </li> -->

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
                <h2>Orders Management</h2>
                <div class="header-actions">
                    <button class="btn btn-primary" id="refreshOrdersBtn" style="margin-right: 10px;">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search orders...">
                    </div>
                    <div class="notification-icon">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    <div class="user-menu">
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card orders reveal">
                    <i class="fas fa-shopping-bag"></i>
                    <div class="stat-value">142</div>
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 12% from yesterday
                    </div>
                </div>

                <div class="stat-card revenue reveal reveal-delay-1">
                    <i class="fa-solid fa-naira-sign"></i>
                    <div class="stat-value">₦324,580</div>
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 8% from last week
                    </div>
                </div>

                <div class="stat-card customers reveal reveal-delay-2">
                    <i class="fas fa-users"></i>
                    <div class="stat-value">2,847</div>
                    <div class="stat-label">Total Customers</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 5% from last month
                    </div>
                </div>

                <div class="stat-card pending reveal reveal-delay-3">
                    <i class="fas fa-clock"></i>
                    <div class="stat-value">38</div>
                    <div class="stat-label">Pending Orders</div>
                    <div class="stat-change negative">
                        <i class="fas fa-arrow-down"></i> 3% from yesterday
                    </div>
                </div>
            </div>

            <!-- Order Filters -->
            <div class="order-filters">
                <button class="filter-btn active" data-filter="all">All Orders</button>
                <button class="filter-btn" data-filter="pending">Pending</button>
                <button class="filter-btn" data-filter="completed">Completed</button>
                <button class="filter-btn" data-filter="cancelled">Cancelled</button>
                <button class="filter-btn" data-filter="payment-pending">Payment Pending</button>
            </div>

            <!-- Orders Table -->
            <div class="orders-table-container reveal">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody">
                        <!-- Orders will be dynamically added here -->
                    </tbody>
                </table>
            </div>

            <div class="footer">
                <p>&copy; 2025 Joseph's Pot Admin Dashboard. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal" id="orderDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Order Details - <span id="modalOrderId"></span></h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="order-details-grid">
                    <div>
                        <div class="order-detail-group">
                            <h4>Customer Information</h4>
                            <p><strong>Name:</strong> <span id="customerName"></span></p>
                            <p><strong>Phone:</strong> <span id="customerPhone"></span></p>
                            <p><strong>Email:</strong> <span id="customerEmail"></span></p>
                        </div>

                        <div class="order-detail-group">
                            <h4>Delivery Information</h4>
                            <p><strong>Address:</strong> <span id="deliveryAddress"></span></p>
                            <p><strong>Instructions:</strong> <span id="deliveryInstructions"></span></p>
                        </div>
                    </div>

                    <div>
                        <div class="order-detail-group">
                            <h4>Order Information</h4>
                            <p><strong>Order Date:</strong> <span id="orderDate"></span></p>
                            <p><strong>Payment Method:</strong> <span id="paymentMethod"></span></p>
                            <p><strong>Status:</strong> <span id="orderStatus"></span></p>
                        </div>

                        <div class="order-detail-group" id="paymentProofSection" style="display: none;">
                            <h4>Payment Proof</h4>
                            <div class="payment-proof">
                                <img id="paymentProofImage" src="" alt="Payment Proof">
                                <p><small>Bank transfer receipt provided by customer</small></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="order-items">
                    <h4>Order Items</h4>
                    <div id="orderItemsList">
                        <!-- Order items will be dynamically added here -->
                    </div>
                    <div class="order-total">
                        <span>Total:</span>
                        <span id="orderTotalAmount"></span>
                    </div>
                </div>

                <div class="modal-actions">
                    <button class="btn btn-secondary" id="cancelOrderBtn">Cancel Order</button>
                    <button class="btn btn-success" id="completeOrderBtn">Mark as Completed</button>
                    <button class="btn btn-primary" id="printOrderBtn">Print Receipt</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Orders data from database
        let orders = [];
        let currentOrderId = null;

        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const ordersTableBody = document.getElementById('ordersTableBody');
        const orderDetailsModal = document.getElementById('orderDetailsModal');
        const closeModal = document.getElementById('closeModal');
        const filterBtns = document.querySelectorAll('.filter-btn');
        const searchBox = document.querySelector('.search-box input');
        const refreshOrdersBtn = document.getElementById('refreshOrdersBtn');

        // Verify critical elements exist
        console.log('DOM Elements check:');
        console.log('ordersTableBody:', ordersTableBody);
        console.log('orderDetailsModal:', orderDetailsModal);
        console.log('closeModal:', closeModal);

        if (!ordersTableBody) {
            console.error('CRITICAL: ordersTableBody element not found!');
        }

        // Modal elements
        const modalOrderId = document.getElementById('modalOrderId');
        const customerName = document.getElementById('customerName');
        const customerPhone = document.getElementById('customerPhone');
        const customerEmail = document.getElementById('customerEmail');
        const deliveryAddress = document.getElementById('deliveryAddress');
        const deliveryInstructions = document.getElementById('deliveryInstructions');
        const orderDate = document.getElementById('orderDate');
        const paymentMethod = document.getElementById('paymentMethod');
        const orderStatus = document.getElementById('orderStatus');
        const paymentProofSection = document.getElementById('paymentProofSection');
        const paymentProofImage = document.getElementById('paymentProofImage');
        const orderItemsList = document.getElementById('orderItemsList');
        const orderTotalAmount = document.getElementById('orderTotalAmount');
        const cancelOrderBtn = document.getElementById('cancelOrderBtn');
        const completeOrderBtn = document.getElementById('completeOrderBtn');
        const printOrderBtn = document.getElementById('printOrderBtn');

        // Fetch orders from database
        async function fetchOrders(filter = 'all') {
            // Show loading state
            ordersTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Loading orders...</td></tr>';

            try {
                const response = await fetch(`api/get-orders.php?filter=${filter}`);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                console.log('API Response:', result);

                if (result.success && result.orders) {
                    console.log('Raw API orders:', result.orders);

                    // Transform database format to expected format
                    orders = result.orders.map(order => {
                        const transformed = {
                            id: order.order_id || order.id,
                            customer: order.customer_name || '',
                            phone: order.customer_phone || '',
                            email: order.customer_email || '',
                            date: order.created_at || new Date().toISOString(),
                            amount: parseFloat(order.total_amount || 0),
                            paymentMethod: order.payment_method || 'cod',
                            status: order.order_status || 'pending',
                            address: order.delivery_address || '',
                            instructions: order.delivery_instructions || '',
                            items: order.items || [],
                            paymentProof: order.payment_proof || '',
                            paymentStatus: order.payment_status || 'pending'
                        };
                        console.log('Transformed order:', transformed);
                        return transformed;
                    });

                    console.log('All transformed orders:', orders);
                    console.log('Orders table body element:', ordersTableBody);

                    if (ordersTableBody) {
                        renderOrders(orders);
                        updateStats();
                    } else {
                        console.error('ordersTableBody element not found!');
                    }
                } else {
                    console.error('Failed to fetch orders:', result.message || 'Unknown error');
                    orders = [];
                    if (result.orders && result.orders.length === 0) {
                        ordersTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;">No orders found</td></tr>';
                    } else {
                        ordersTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: red;">Error: ' + (result.message || 'Failed to load orders') + '</td></tr>';
                    }
                }
            } catch (error) {
                console.error('Error fetching orders:', error);
                orders = [];
                ordersTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: red;">Error loading orders: ' + error.message + '</td></tr>';
            }
        }

        // Filter orders
        function filterOrders(filter) {
            fetchOrders(filter);
        }

        // Render orders in table
        function renderOrders(ordersToRender) {
            console.log('=== RENDER ORDERS CALLED ===');
            console.log('Orders to render:', ordersToRender);
            console.log('Table body element:', ordersTableBody);

            if (!ordersTableBody) {
                console.error('ERROR: ordersTableBody is null!');
                return;
            }

            ordersTableBody.innerHTML = '';

            if (!ordersToRender || ordersToRender.length === 0) {
                console.log('No orders to render');
                ordersTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;">No orders found</td></tr>';
                return;
            }

            console.log(`Rendering ${ordersToRender.length} orders`);
            let renderedCount = 0;

            ordersToRender.forEach((order, index) => {
                console.log(`Processing order ${index + 1}:`, order);

                if (!order || !order.id) {
                    console.warn(`Skipping invalid order at index ${index}:`, order);
                    return;
                }

                const row = document.createElement('tr');
                row.className = 'reveal active'; // Add 'active' immediately so rows are visible

                // Format date - handle both string and Date object
                let orderDateObj;
                if (order.date instanceof Date) {
                    orderDateObj = order.date;
                } else if (typeof order.date === 'string') {
                    orderDateObj = new Date(order.date);
                } else {
                    orderDateObj = new Date();
                }

                // Check if date is valid
                if (isNaN(orderDateObj.getTime())) {
                    console.warn('Invalid date for order:', order.id, order.date);
                    orderDateObj = new Date();
                }

                const formattedDate = `${orderDateObj.toLocaleDateString()} ${orderDateObj.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;

                // Status badge
                let statusClass = '';
                let statusText = '';

                switch (order.status) {
                    case 'pending':
                        statusClass = 'status-pending';
                        statusText = 'Pending';
                        break;
                    case 'processing':
                        statusClass = 'status-pending';
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

                // Payment method icon
                let paymentIcon = '';
                let paymentText = '';
                let paymentClass = '';

                switch (order.paymentMethod) {
                    case 'paystack':
                        paymentIcon = '<i class="fas fa-credit-card"></i>';
                        paymentText = 'Paystack';
                        paymentClass = 'payment-card';
                        break;
                    case 'cod':
                        paymentIcon = '<i class="fas fa-money-bill-wave"></i>';
                        paymentText = 'Cash on Delivery';
                        paymentClass = 'payment-cash';
                        break;
                    case 'bank':
                        paymentIcon = '<i class="fas fa-university"></i>';
                        paymentText = 'Bank Transfer';
                        paymentClass = 'payment-transfer';
                        break;
                    case 'flutterwave':
                        paymentIcon = '<i class="fas fa-credit-card"></i>';
                        paymentText = 'Flutterwave';
                        paymentClass = 'payment-card';
                        break;
                    default:
                        paymentIcon = '<i class="fas fa-money-bill-wave"></i>';
                        paymentText = order.paymentMethod || 'Unknown';
                        paymentClass = 'payment-cash';
                }

                try {
                    // Create cells individually with explicit styling to ensure visibility
                    const idCell = document.createElement('td');
                    idCell.className = 'order-id';
                    idCell.textContent = order.id || 'N/A';
                    idCell.style.color = '#8b4513';
                    idCell.style.fontWeight = '600';

                    const customerCell = document.createElement('td');
                    customerCell.textContent = order.customer || 'N/A';
                    customerCell.style.color = '#333333';

                    const dateCell = document.createElement('td');
                    dateCell.textContent = formattedDate;
                    dateCell.style.color = '#333333';

                    const amountCell = document.createElement('td');
                    amountCell.textContent = `₦${(order.amount || 0).toLocaleString()}`;
                    amountCell.style.color = '#333333';

                    const paymentCell = document.createElement('td');
                    paymentCell.innerHTML = `
                        <div class="payment-method ${paymentClass}">
                            ${paymentIcon}
                            <span>${paymentText}</span>
                        </div>
                    `;

                    const statusCell = document.createElement('td');
                    statusCell.innerHTML = `<span class="order-status ${statusClass}">${statusText}</span>`;

                    const actionsCell = document.createElement('td');
                    actionsCell.innerHTML = `
                        <div class="action-buttons">
                            <button class="action-btn view" data-id="${order.id}" title="View Order">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn edit" data-id="${order.id}" title="Edit Order">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete" data-id="${order.id}" title="Delete Order">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;

                    // Append cells to row
                    row.appendChild(idCell);
                    row.appendChild(customerCell);
                    row.appendChild(dateCell);
                    row.appendChild(amountCell);
                    row.appendChild(paymentCell);
                    row.appendChild(statusCell);
                    row.appendChild(actionsCell);

                    ordersTableBody.appendChild(row);

                    // Force the row to be visible by adding active class and setting opacity
                    row.classList.add('active');
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';

                    renderedCount++;
                    console.log(`✓ Successfully rendered order ${order.id} (${renderedCount}/${ordersToRender.length})`);
                } catch (error) {
                    console.error(`✗ Error rendering order ${order.id}:`, error);
                    console.error('Error details:', error.stack);
                }
            });

            console.log(`=== RENDERING COMPLETE: ${renderedCount} orders rendered ===`);

            if (renderedCount === 0 && ordersToRender.length > 0) {
                console.error('WARNING: No orders were rendered despite having orders!');
                ordersTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: red;">Error: Failed to render orders. Check console for details.</td></tr>';
            }

            // Add event listeners to action buttons
            document.querySelectorAll('.action-btn.view').forEach(btn => {
                btn.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-id');
                    showOrderDetails(orderId);
                });
            });

            document.querySelectorAll('.action-btn.delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-id');
                    if (confirm('Are you sure you want to delete this order?')) {
                        deleteOrder(orderId);
                    }
                });
            });

            document.querySelectorAll('.action-btn.edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-id');
                    showOrderDetails(orderId);
                });
            });
        }

        // Show order details in modal
        function showOrderDetails(orderId) {
            const order = orders.find(o => o.id === orderId);

            if (!order) return;

            // Format date
            const orderDateObj = new Date(order.date);
            const formattedDate = `${orderDateObj.toLocaleDateString()} ${orderDateObj.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;

            // Update modal content
            modalOrderId.textContent = order.id;
            customerName.textContent = order.customer;
            customerPhone.textContent = order.phone;
            customerEmail.textContent = order.email;
            deliveryAddress.textContent = order.address;
            deliveryInstructions.textContent = order.instructions || 'None';
            orderDate.textContent = formattedDate;

            // Payment method
            let paymentText = '';
            switch (order.paymentMethod) {
                case 'paystack':
                    paymentText = 'Paystack';
                    break;
                case 'cod':
                    paymentText = 'Cash on Delivery';
                    break;
                case 'bank':
                    paymentText = 'Bank Transfer';
                    break;
                case 'flutterwave':
                    paymentText = 'Flutterwave';
                    break;
                default:
                    paymentText = order.paymentMethod || 'Unknown';
            }
            paymentMethod.textContent = paymentText;

            // Status
            let statusText = '';
            switch (order.status) {
                case 'pending':
                    statusText = 'Pending';
                    break;
                case 'processing':
                    statusText = 'Processing';
                    break;
                case 'completed':
                    statusText = 'Completed';
                    break;
                case 'cancelled':
                    statusText = 'Cancelled';
                    break;
            }
            orderStatus.textContent = statusText;

            // Payment proof (only for bank transfers)
            if (order.paymentMethod === 'bank' && order.paymentProof) {
                paymentProofSection.style.display = 'block';
                paymentProofImage.src = order.paymentProof;
            } else {
                paymentProofSection.style.display = 'none';
            }

            // Order items
            orderItemsList.innerHTML = '';
            if (order.items && order.items.length > 0) {
                order.items.forEach(item => {
                    const itemElement = document.createElement('div');
                    itemElement.className = 'order-item';
                    const itemName = item.item_name || item.name || 'Unknown Item';
                    const itemPrice = parseFloat(item.item_price || item.price || 0);
                    const quantity = parseInt(item.quantity || 1);
                    itemElement.innerHTML = `
                        <div>
                            <span class="item-name">${itemName}</span>
                            <div><small>Qty: ${quantity}</small></div>
                        </div>
                        <div class="item-price">₦${(itemPrice * quantity).toLocaleString()}</div>
                    `;
                    orderItemsList.appendChild(itemElement);
                });
            }

            // Total amount
            orderTotalAmount.textContent = `₦${order.amount.toLocaleString()}`;

            // Store current order ID for actions
            currentOrderId = order.id;

            // Show modal
            orderDetailsModal.style.display = 'flex';
        }

        // Update order status
        async function updateOrderStatus(orderId, status) {
            try {
                const response = await fetch('api/update-order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        action: 'update_status',
                        status: status
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                console.log('Update response:', result);

                if (result.success) {
                    // Refresh orders
                    const activeFilter = document.querySelector('.filter-btn.active')?.getAttribute('data-filter') || 'all';
                    await fetchOrders(activeFilter);
                    orderDetailsModal.style.display = 'none';
                    alert('Order status updated successfully!');
                    return true;
                } else {
                    alert('Failed to update order: ' + (result.message || 'Unknown error'));
                    return false;
                }
            } catch (error) {
                console.error('Error updating order:', error);
                alert('Error updating order: ' + error.message);
                return false;
            }
        }

        // Delete order
        async function deleteOrder(orderId) {
            try {
                const response = await fetch('api/update-order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        action: 'delete'
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                console.log('Delete response:', result);

                if (result.success) {
                    // Refresh orders
                    const activeFilter = document.querySelector('.filter-btn.active')?.getAttribute('data-filter') || 'all';
                    await fetchOrders(activeFilter);
                    if (orderDetailsModal.style.display === 'flex') {
                        orderDetailsModal.style.display = 'none';
                    }
                    alert('Order deleted successfully!');
                    return true;
                } else {
                    alert('Failed to delete order: ' + (result.message || 'Unknown error'));
                    return false;
                }
            } catch (error) {
                console.error('Error deleting order:', error);
                alert('Error deleting order: ' + error.message);
                return false;
            }
        }

        // Update stats cards
        function updateStats() {
            const totalOrders = orders.length;
            const pendingOrders = orders.filter(o => o.status === 'pending').length;
            const completedOrders = orders.filter(o => o.status === 'completed');
            const totalRevenue = completedOrders.reduce((sum, o) => sum + o.amount, 0);

            // Update stat cards if they exist
            const ordersStat = document.querySelector('.stat-card.orders .stat-value');
            const pendingStat = document.querySelector('.stat-card.pending .stat-value');
            const revenueStat = document.querySelector('.stat-card.revenue .stat-value');

            if (ordersStat) ordersStat.textContent = totalOrders;
            if (pendingStat) pendingStat.textContent = pendingOrders;
            if (revenueStat) revenueStat.textContent = `₦${totalRevenue.toLocaleString()}`;
        }

        // Close modal
        closeModal.addEventListener('click', function() {
            orderDetailsModal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === orderDetailsModal) {
                orderDetailsModal.style.display = 'none';
            }
        });

        // Refresh button
        if (refreshOrdersBtn) {
            refreshOrdersBtn.addEventListener('click', function() {
                const activeFilter = document.querySelector('.filter-btn.active')?.getAttribute('data-filter') || 'all';
                fetchOrders(activeFilter);
            });
        } else {
            console.warn('Refresh button not found');
        }

        // Filter buttons event listeners
        if (filterBtns && filterBtns.length > 0) {
            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    filterOrders(this.getAttribute('data-filter'));
                });
            });
        } else {
            console.warn('Filter buttons not found');
        }

        // Search functionality
        if (searchBox) {
            searchBox.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const filteredOrders = orders.filter(order =>
                    order.id.toLowerCase().includes(searchTerm) ||
                    order.customer.toLowerCase().includes(searchTerm) ||
                    order.phone.includes(searchTerm) ||
                    order.email.toLowerCase().includes(searchTerm)
                );
                renderOrders(filteredOrders);
            });
        }

        // Order action buttons
        if (completeOrderBtn) {
            completeOrderBtn.addEventListener('click', function() {
                if (currentOrderId) {
                    updateOrderStatus(currentOrderId, 'completed');
                }
            });
        }

        if (cancelOrderBtn) {
            cancelOrderBtn.addEventListener('click', function() {
                if (currentOrderId && confirm('Are you sure you want to cancel this order?')) {
                    updateOrderStatus(currentOrderId, 'cancelled');
                }
            });
        }

        printOrderBtn.addEventListener('click', function() {
            alert('Printing receipt...');
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

        // Initialize - wait for DOM
        function initializePage() {
            console.log('=== INITIALIZING ADMIN ORDERS PAGE ===');
            console.log('ordersTableBody exists:', !!ordersTableBody);

            if (!ordersTableBody) {
                console.error('ordersTableBody not found, retrying in 100ms...');
                setTimeout(initializePage, 100);
                return;
            }

            // Fetch and render orders from database
            console.log('Fetching orders...');
            fetchOrders('all');

            // Initialize scroll reveal
            window.addEventListener('scroll', revealOnScroll);
            // Trigger once on load to check initial position
            revealOnScroll();
        }

        // Try multiple initialization methods
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializePage);
        } else {
            // DOM already loaded
            initializePage();
        }

        // Also try on window load as backup
        window.addEventListener('load', function() {
            console.log('Window loaded, checking orders...');
            if (orders.length === 0 && ordersTableBody) {
                console.log('No orders found, retrying fetch...');
                fetchOrders('all');
            }
        });
    </script>
</body>

</html>