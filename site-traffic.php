<?php
// Admin authentication check
require_once __DIR__ . '/admin/admin-auth.php';
checkPageAccess();

// Load analytics functions
require_once __DIR__ . '/includes/analytics-functions.php';

// Get analytics data
$timeRange = isset($_GET['range']) ? $_GET['range'] : 'week';
$days = 7;
switch($timeRange) {
    case 'today':
        $days = 1;
        break;
    case 'week':
        $days = 7;
        break;
    case 'month':
        $days = 30;
        break;
    case 'year':
        $days = 365;
        break;
}

$visitorsData = getVisitorsOverTime('day', $days);
$trafficSources = getTrafficSources(10);
$topPages = getTopPages(10);
$topCountries = getTopCountries(10);
$deviceTypes = getDeviceTypes();
$browserUsage = getBrowserUsage(10);
$activeUsers = getActiveUsers();
$totalSessions = getTotalSessions($days);

// Convert PHP data to JSON for JavaScript
$visitorsDataJson = json_encode($visitorsData);
$trafficSourcesJson = json_encode($trafficSources);
$topPagesJson = json_encode($topPages);
$topCountriesJson = json_encode($topCountries);
$deviceTypesJson = json_encode($deviceTypes);
$browserUsageJson = json_encode($browserUsage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Traffic Analytics - Joseph's Pot Admin</title>
    <link rel="icon" href="images/logo3.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            --gradient-dark: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
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

        /* Time Filter */
        .time-filter {
            display: flex;
            gap: 10px;
            background: white;
            padding: 8px;
            border-radius: 30px;
            box-shadow: var(--shadow);
            flex-wrap: wrap;
        }

        .time-filter button {
            padding: 10px 20px;
            border: none;
            background: transparent;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            color: var(--text-light);
            white-space: nowrap;
        }

        .time-filter button:hover {
            background: var(--gray);
        }

        .time-filter button.active {
            background: var(--primary);
            color: white;
            box-shadow: var(--shadow);
        }

        /* Top Stats Cards */
        .top-stats {
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
            right: 0;
            height: 4px;
            background: var(--gradient);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.visitors { background: linear-gradient(135deg, var(--info) 0%, #64b5f6 100%); }
        .stat-icon.sessions { background: linear-gradient(135deg, var(--success) 0%, #81c784 100%); }
        .stat-icon.bounce { background: linear-gradient(135deg, var(--warning) 0%, #ffb74d 100%); }
        .stat-icon.duration { background: linear-gradient(135deg, #9C27B0 0%, #BA68C8 100%); }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .stat-trend.positive { color: var(--success); }
        .stat-trend.negative { color: var(--danger); }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 15px;
        }

        .stat-progress {
            height: 6px;
            background: var(--gray);
            border-radius: 3px;
            overflow: hidden;
        }

        .stat-progress-bar {
            height: 100%;
            border-radius: 3px;
            background: var(--gradient);
            transition: width 1.5s ease-out;
        }

        /* Real-time Widget */
        .realtime-widget {
            background: var(--gradient-dark);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }

        .realtime-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .realtime-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: white;
        }

        .live-badge {
            background: var(--danger);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            animation: blink 1.5s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .visitor-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .visitor-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            transition: var(--transition);
        }

        .visitor-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .visitor-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .visitor-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--accent) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
        }

        .visitor-details h4 {
            font-size: 0.95rem;
            margin-bottom: 3px;
        }

        .visitor-details p {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .visitor-time {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 992px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
        }

        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark);
        }

        .chart-container {
            height: 320px;
            position: relative;
        }

        /* Analytics Grid */
        .analytics-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }
        }

        .analytics-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
        }

        .analytics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .analytics-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark);
        }

        .analytics-header .view-all {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .analytics-header .view-all:hover {
            color: var(--primary-dark);
        }

        /* Pages List */
        .pages-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .page-visit {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--gray);
        }

        .page-visit:last-child {
            border-bottom: none;
        }

        .page-url {
            font-weight: 500;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .page-stats {
            display: flex;
            gap: 15px;
            color: var(--text-light);
            font-size: 0.85rem;
        }

        /* Table Styles */
        .analytics-table {
            width: 100%;
            border-collapse: collapse;
        }

        .analytics-table th {
            text-align: left;
            padding: 12px 0;
            border-bottom: 2px solid var(--gray);
            color: var(--text-light);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .analytics-table td {
            padding: 12px 0;
            border-bottom: 1px solid var(--gray);
        }

        /* Map Container */
        .map-container {
            height: 200px;
            background: linear-gradient(135deg, #f8f8f8 0%, #e8e8e8 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .map-placeholder {
            text-align: center;
            color: var(--text-light);
        }

        .map-placeholder i {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: var(--gray-dark);
        }

        .map-point {
            position: absolute;
            width: 12px;
            height: 12px;
            background: var(--primary);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 0 0 4px rgba(139, 69, 19, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(139, 69, 19, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(139, 69, 19, 0); }
            100% { box-shadow: 0 0 0 0 rgba(139, 69, 19, 0); }
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            color: var(--text-light);
            font-size: 0.9rem;
            border-top: 1px solid var(--gray-dark);
            margin-top: 30px;
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

        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }
        .reveal-delay-4 { transition-delay: 0.4s; }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .top-stats {
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

            .top-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-actions {
                flex-direction: column;
                gap: 15px;
            }

            .time-filter {
                width: 100%;
                justify-content: center;
            }

            .top-stats {
                grid-template-columns: 1fr;
            }

            .chart-container {
                height: 250px;
            }

            .page-stats {
                flex-direction: column;
                gap: 5px;
                align-items: flex-end;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }

            .stat-card {
                padding: 15px;
            }

            .stat-value {
                font-size: 1.8rem;
            }

            .time-filter button {
                padding: 8px 15px;
                font-size: 0.9rem;
            }

            .chart-card, .analytics-card {
                padding: 20px 15px;
            }
        }

        @media (max-width: 480px) {
            .header h2 {
                font-size: 1.3rem;
            }

            .stat-icon {
                width: 45px;
                height: 45px;
                font-size: 1.3rem;
            }

            .visitor-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .visitor-time {
                align-self: flex-end;
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
                <img src="images/logo3.png" alt="Joseph's Pot Logo">
                <h1>Admin Panel</h1>
            </div>

            <div class="admin-info">
                <div class="admin-avatar">AJ</div>
                <div class="admin-details">
                    <h3>Admin User</h3>
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
                    <a href="admin-order-online-menu.php">
                        <i class="fas fa-car"></i>
                        <span>Order-Online Menu</span>
                    </a>
                </li>

                <li class="menu-label">Analytics</li>
                <li class="menu-item">
                    <a href="#" class="active">
                        <i class="fas fa-chart-line"></i>
                        <span>Traffic Analytics</span>
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
                    <a href="admin-career.php">
                        <i class="fas fa-briefcase"></i>
                        <span>Careers</span>
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
                <h2><i class="fas fa-chart-line"></i> Traffic Analytics Dashboard</h2>
                <div class="header-actions">
                    <div class="time-filter">
                        <button class="active" onclick="setTimeRange('today')">Today</button>
                        <button onclick="setTimeRange('week')">This Week</button>
                        <button onclick="setTimeRange('month')">This Month</button>
                        <button onclick="setTimeRange('year')">This Year</button>
                        <button onclick="setTimeRange('custom')">Custom</button>
                    </div>
                </div>
            </div>

            <!-- Top Stats -->
            <div class="top-stats">
                <div class="stat-card reveal">
                    <div class="stat-header">
                        <div class="stat-icon visitors">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="visitorCount"><?php echo number_format($activeUsers); ?></div>
                    <div class="stat-label">Active Users (Last 5 min)</div>
                </div>

                <div class="stat-card reveal reveal-delay-1">
                    <div class="stat-header">
                        <div class="stat-icon sessions">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="sessionCount"><?php echo number_format($totalSessions); ?></div>
                    <div class="stat-label">Total Sessions (<?php echo $days; ?> days)</div>
                </div>

                <div class="stat-card reveal reveal-delay-2">
                    <div class="stat-header">
                        <div class="stat-icon bounce">
                            <i class="fas fa-running"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="bounceRate">—</div>
                    <div class="stat-label">Bounce Rate (Calculating...)</div>
                </div>

                <div class="stat-card reveal reveal-delay-3">
                    <div class="stat-header">
                        <div class="stat-icon duration">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="avgDuration">—</div>
                    <div class="stat-label">Avg. Session Duration (Calculating...)</div>
                </div>
            </div>

            <!-- Real-time Widget -->
            <div class="realtime-widget reveal">
                <div class="realtime-header">
                    <h3>Active Users Right Now (Real-time)</h3>
                    <span class="live-badge">LIVE</span>
                </div>
                <div class="visitor-list" id="activeVisitors">
                    <div class="visitor-item" style="justify-content: center; padding: 30px;">
                        <div class="visitor-info" style="flex-direction: column; align-items: center; text-align: center;">
                            <div class="visitor-details">
                                <h4 style="color: var(--info);">
                                    <i class="fas fa-spinner fa-spin"></i> Loading...
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <div class="chart-card reveal">
                    <div class="chart-header">
                        <h3>Visitors Over Time</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="visitorsChart"></canvas>
                    </div>
                </div>

                <div class="chart-card reveal reveal-delay-1">
                    <div class="chart-header">
                        <h3>Traffic Sources</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="sourcesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Analytics Grid -->
            <div class="analytics-grid">
                <!-- Top Pages -->
                <div class="analytics-card reveal">
                    <div class="analytics-header">
                        <h3>Most Visited Pages</h3>
                        <a href="#" class="view-all" onclick="return false;">Real-time Data</a>
                    </div>
                    <div class="pages-list" id="topPages">
                        <!-- Pages will be populated here -->
                    </div>
                </div>

                <!-- Geographic Data -->
                <div class="analytics-card reveal reveal-delay-1">
                    <div class="analytics-header">
                        <h3>Top Countries</h3>
                        <a href="#" class="view-all" onclick="return false;">Real-time Data</a>
                    </div>
                    <div class="map-container">
                        <!-- World map visualization -->
                        <div class="map-placeholder">
                            <i class="fas fa-globe-americas"></i>
                            <p>World Traffic Map</p>
                        </div>
                        <!-- Map points will be added dynamically -->
                    </div>
                    <table class="analytics-table" style="margin-top: 20px;">
                        <thead>
                            <tr>
                                <th>Country</th>
                                <th>Visitors</th>
                                <th>Trend</th>
                            </tr>
                        </thead>
                        <tbody id="topCountries">
                            <!-- Countries will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Device & Browser Analytics -->
            <div class="analytics-grid">
                <!-- Device Types -->
                <div class="analytics-card reveal">
                    <div class="analytics-header">
                        <h3>Device Types</h3>
                        <a href="#" class="view-all">View Details</a>
                    </div>
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="devicesChart"></canvas>
                    </div>
                </div>

                <!-- Browser Usage -->
                <div class="analytics-card reveal reveal-delay-1">
                    <div class="analytics-header">
                        <h3>Browser Usage</h3>
                        <a href="#" class="view-all">View Details</a>
                    </div>
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Browser</th>
                                <th>Users</th>
                                <th>Share</th>
                            </tr>
                        </thead>
                        <tbody id="browsersTable">
                            <!-- Browsers will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>&copy; 2025 Joseph's Pot Admin Dashboard. All rights reserved | Developed By ERIBS tech</p>
                <p style="font-size: 0.8rem; margin-top: 5px; opacity: 0.7;">
                    Traffic Analytics Dashboard • Last updated: <span id="lastUpdated">Just now</span>
                </p>
            </div>
        </div>
    </div>

    <script>
        /**
         * Real-time Analytics Dashboard
         * Uses real data from PHP/MySQL analytics system
         */
        
        // Load data from PHP
        const visitorsData = <?php echo $visitorsDataJson; ?>;
        const trafficSources = <?php echo $trafficSourcesJson; ?>;
        const topPages = <?php echo $topPagesJson; ?>;
        const topCountries = <?php echo $topCountriesJson; ?>;
        const deviceTypes = <?php echo $deviceTypesJson; ?>;
        const browserUsage = <?php echo $browserUsageJson; ?>;
        
        // Debug: Log data to console
        console.log('Top Countries Data:', topCountries);
        
        // Chart instances
        let visitorsChart = null, sourcesChart = null, devicesChart = null;

        /**
         * Initialize charts with real data
         */
        function initCharts() {
            // Visitors Over Time Chart
            if (visitorsData && visitorsData.length > 0) {
                const visitorsCtx = document.getElementById('visitorsChart');
                const labels = visitorsData.map(d => {
                    const date = new Date(d.time_period);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                });
                
                visitorsChart = new Chart(visitorsCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Visitors',
                                data: visitorsData.map(d => parseInt(d.visitors)),
                                borderColor: '#8b4513',
                                backgroundColor: 'rgba(139, 69, 19, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Page Views',
                                data: visitorsData.map(d => parseInt(d.page_views)),
                                borderColor: '#4CAF50',
                                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString();
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            } else {
                // Show empty state
                const visitorsContainer = document.getElementById('visitorsChart').parentElement;
                visitorsContainer.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; padding: 40px; text-align: center;">
                        <i class="fas fa-chart-line" style="font-size: 3rem; color: var(--text-light); margin-bottom: 20px; opacity: 0.5;"></i>
                        <h4 style="color: var(--text); margin-bottom: 10px; font-size: 1.1rem;">No Data Available</h4>
                        <p style="color: var(--text-light); font-size: 0.9rem; max-width: 400px;">
                            Visitor data will appear here once tracking is active.
                        </p>
                    </div>
                `;
            }

            // Traffic Sources Chart
            if (trafficSources && trafficSources.length > 0) {
                const sourcesCtx = document.getElementById('sourcesChart');
                const colors = ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#F44336', '#00BCD4', '#FFC107'];
                
                sourcesChart = new Chart(sourcesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: trafficSources.map(d => d.source),
                        datasets: [{
                            data: trafficSources.map(d => parseInt(d.visits)),
                            backgroundColor: trafficSources.map((d, i) => colors[i % colors.length]),
                            borderWidth: 0,
                            hoverOffset: 15
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                const sourcesContainer = document.getElementById('sourcesChart').parentElement;
                sourcesContainer.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; padding: 40px; text-align: center;">
                        <i class="fas fa-share-alt" style="font-size: 3rem; color: var(--text-light); margin-bottom: 20px; opacity: 0.5;"></i>
                        <h4 style="color: var(--text); margin-bottom: 10px; font-size: 1.1rem;">No Traffic Source Data</h4>
                        <p style="color: var(--text-light); font-size: 0.9rem; max-width: 400px;">
                            Traffic source data will appear here once visitors arrive.
                        </p>
                    </div>
                `;
            }

            // Device Types Chart
            if (deviceTypes && deviceTypes.length > 0) {
                const devicesCtx = document.getElementById('devicesChart');
                const deviceColors = {
                    'desktop': '#8b4513',
                    'mobile': '#4CAF50',
                    'tablet': '#FF9800',
                    'unknown': '#9E9E9E'
                };
                
                devicesChart = new Chart(devicesCtx, {
                    type: 'pie',
                    data: {
                        labels: deviceTypes.map(d => d.device_type.charAt(0).toUpperCase() + d.device_type.slice(1)),
                        datasets: [{
                            data: deviceTypes.map(d => parseInt(d.visits)),
                            backgroundColor: deviceTypes.map(d => deviceColors[d.device_type] || '#9E9E9E'),
                            borderWidth: 0,
                            hoverOffset: 15
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    padding: 15,
                                    usePointStyle: true
                                }
                            }
                        }
                    }
                });
            } else {
                const devicesContainer = document.getElementById('devicesChart').parentElement;
                devicesContainer.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; padding: 40px; text-align: center;">
                        <i class="fas fa-mobile-alt" style="font-size: 3rem; color: var(--text-light); margin-bottom: 20px; opacity: 0.5;"></i>
                        <h4 style="color: var(--text); margin-bottom: 10px; font-size: 1.1rem;">No Device Data</h4>
                        <p style="color: var(--text-light); font-size: 0.9rem; max-width: 400px;">
                            Device data will appear here once tracking is active.
                        </p>
                    </div>
                `;
            }
        }

        /**
         * Populate top pages section with real data
         */
        function populateTopPages() {
            const container = document.getElementById('topPages');
            container.innerHTML = '';
            
            if (topPages && topPages.length > 0) {
                topPages.forEach(page => {
                    const pageElement = document.createElement('div');
                    pageElement.className = 'page-visit';
                    pageElement.innerHTML = `
                        <div class="page-url">
                            <i class="fas fa-file-alt" style="color: var(--primary);"></i>
                            ${page.page_url}
                        </div>
                        <div class="page-stats">
                            <span>${parseInt(page.visits).toLocaleString()} visits</span>
                            <span>${parseInt(page.unique_visitors).toLocaleString()} unique</span>
                        </div>
                    `;
                    container.appendChild(pageElement);
                });
            } else {
                container.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; text-align: center; min-height: 200px;">
                        <i class="fas fa-file-alt" style="font-size: 2.5rem; color: var(--text-light); margin-bottom: 15px; opacity: 0.5;"></i>
                        <h4 style="color: var(--text); margin-bottom: 10px; font-size: 1rem;">No Page Data Yet</h4>
                        <p style="color: var(--text-light); font-size: 0.85rem; max-width: 350px; line-height: 1.5;">
                            Page view data will appear here once visitors start browsing.
                        </p>
                    </div>
                `;
            }
        }

        /**
         * Populate top countries section with real data
         */
        function populateTopCountries() {
            const container = document.getElementById('topCountries');
            container.innerHTML = '';
            
            console.log('populateTopCountries called, data:', topCountries);
            
            if (topCountries && topCountries.length > 0) {
                topCountries.forEach(country => {
                    const row = document.createElement('tr');
                    const countryName = country.country || 'Unknown';
                    const visitorCount = parseInt(country.visitors) || 0;
                    
                    row.innerHTML = `
                        <td>
                            <i class="fas fa-map-marker-alt" style="color: var(--primary); margin-right: 8px;"></i>
                            ${countryName}
                        </td>
                        <td>${visitorCount.toLocaleString()}</td>
                        <td>
                            <i class="fas fa-arrow-up" style="color: var(--success);"></i>
                        </td>
                    `;
                    container.appendChild(row);
                });
            } else {
                container.innerHTML = `
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 40px;">
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                <i class="fas fa-globe-americas" style="font-size: 2.5rem; color: var(--text-light); margin-bottom: 15px; opacity: 0.5;"></i>
                                <h4 style="color: var(--text); margin-bottom: 10px; font-size: 1rem;">No Geographic Data Yet</h4>
                                <p style="color: var(--text-light); font-size: 0.85rem; max-width: 350px; line-height: 1.5;">
                                    Country data will appear here once visitors arrive. Check browser console for debugging info.
                                </p>
                            </div>
                        </td>
                    </tr>
                `;
            }
            
            // Update map container
            const mapContainer = document.querySelector('.map-container');
            if (mapContainer && topCountries && topCountries.length > 0) {
                // Keep map placeholder for now (can be enhanced with actual map visualization)
                mapContainer.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; padding: 20px;">
                        <i class="fas fa-map-marked-alt" style="font-size: 2rem; color: var(--text-light); margin-bottom: 10px; opacity: 0.5;"></i>
                        <p style="color: var(--text-light); font-size: 0.85rem;">${topCountries.length} countries tracked</p>
                    </div>
                `;
            }
        }

        /**
         * Populate browsers table with real data
         */
        function populateBrowsersTable() {
            const container = document.getElementById('browsersTable');
            container.innerHTML = '';
            
            if (browserUsage && browserUsage.length > 0) {
                // Calculate total for percentage
                const total = browserUsage.reduce((sum, b) => sum + parseInt(b.users), 0);
                
                browserUsage.forEach(browser => {
                    const row = document.createElement('tr');
                    const percentage = total > 0 ? Math.round((parseInt(browser.users) / total) * 100) : 0;
                    const browserIcon = getBrowserIcon(browser.browser);
                    
                    row.innerHTML = `
                        <td>
                            ${browserIcon}
                            ${browser.browser}
                        </td>
                        <td>${parseInt(browser.users).toLocaleString()}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 100px; height: 6px; background: var(--gray); border-radius: 3px; overflow: hidden;">
                                    <div style="width: ${percentage}%; height: 100%; background: var(--primary); border-radius: 3px;"></div>
                                </div>
                                <span>${percentage}%</span>
                            </div>
                        </td>
                    `;
                    container.appendChild(row);
                });
            } else {
                container.innerHTML = `
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 40px;">
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                <i class="fas fa-window-restore" style="font-size: 2.5rem; color: var(--text-light); margin-bottom: 15px; opacity: 0.5;"></i>
                                <h4 style="color: var(--text); margin-bottom: 10px; font-size: 1rem;">No Browser Data Yet</h4>
                                <p style="color: var(--text-light); font-size: 0.85rem; max-width: 350px; line-height: 1.5;">
                                    Browser data will appear here once visitors arrive.
                                </p>
                            </div>
                        </td>
                    </tr>
                `;
            }
        }
        
        /**
         * Get browser icon
         */
        function getBrowserIcon(browser) {
            const icons = {
                'Chrome': 'fab fa-chrome',
                'Safari': 'fab fa-safari',
                'Firefox': 'fab fa-firefox',
                'Edge': 'fab fa-edge',
                'Opera': 'fab fa-opera',
                'IE': 'fab fa-internet-explorer'
            };
            return `<i class="${icons[browser] || 'fas fa-globe'}" style="margin-right: 10px; color: var(--primary);"></i>`;
        }

        /**
         * Fetch real-time active users from database
         */
        async function fetchRealtimeActiveUsers() {
            try {
                const response = await fetch('api/get-active-users.php');
                const data = await response.json();
                
                if (data.success && data.activeUsers !== undefined) {
                    const container = document.getElementById('activeVisitors');
                    container.innerHTML = `
                        <div class="visitor-item" style="justify-content: center; padding: 30px;">
                            <div class="visitor-info" style="flex-direction: column; align-items: center; text-align: center;">
                                <div class="visitor-avatar" style="width: 80px; height: 80px; font-size: 2.5rem; margin-bottom: 15px;">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="visitor-details">
                                    <h4 style="font-size: 2rem; margin-bottom: 10px; color: white;">${data.activeUsers}</h4>
                                    <p style="font-size: 1rem; opacity: 0.9;">Active Users (Last 5 min)</p>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('visitorCount').textContent = data.activeUsers.toLocaleString();
                    return data.activeUsers;
                } else {
                    document.getElementById('visitorCount').textContent = '0';
                    return 0;
                }
            } catch (error) {
                console.error('Error fetching active users:', error);
                document.getElementById('visitorCount').textContent = '0';
                return 0;
            }
        }

        // Populate active visitors (legacy function for compatibility)
        function populateActiveVisitors() {
            fetchRealtimeActiveUsers();
        }

        // Update stats display
        async function updateStats() {
            try {
                // Fetch real-time active users
                const activeUsers = await fetchRealtimeActiveUsers();
                
                // Update visitor count (already updated in fetchRealtimeActiveUsers)
                // Estimate sessions based on active users (rough estimate: 1.5-2x active users)
                const sessionCount = document.getElementById('sessionCount');
                if (activeUsers > 0) {
                    sessionCount.textContent = Math.floor(activeUsers * 1.8).toLocaleString();
                }
                
                // Update last updated time
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                document.getElementById('lastUpdated').textContent = `${timeString}`;
            } catch (error) {
                console.error('Error updating stats:', error);
                // Update last updated time even on error
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                document.getElementById('lastUpdated').textContent = `${timeString} (Error)`;
            }
        }

        /**
         * Time range filter - reload page with new range
         */
        function setTimeRange(range) {
            // Update active button
            document.querySelectorAll('.time-filter button').forEach(btn => {
                btn.classList.remove('active');
            });
            if (event && event.target) {
                event.target.classList.add('active');
            }
            
            // Reload page with new time range
            const url = new URL(window.location);
            url.searchParams.set('range', range);
            window.location.href = url.toString();
        }

        // Mobile sidebar functionality
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

        // Scroll Reveal Functionality
        function revealOnScroll() {
            const reveals = document.querySelectorAll('.reveal');
            const windowHeight = window.innerHeight;
            const elementVisible = 150;

            reveals.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                if (elementTop < windowHeight - elementVisible) {
                    element.classList.add('active');
                } else {
                    element.classList.remove('active');
                }
            });
        }

        // Initialize everything
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts
            initCharts();
            
            // Populate all data
            populateTopPages();
            populateTopCountries();
            populateBrowsersTable();
            populateActiveVisitors();
            
            // Initialize Firebase Analytics display
            updateStats();
            
            // Update stats display periodically
            setInterval(updateStats, 60000);
            
            // Initialize scroll reveal
            window.addEventListener('scroll', revealOnScroll);
            revealOnScroll(); // Check initial position
        });
    </script>
</body>
</html>