<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers Dashboard - Joseph's Pot Admin</title>
    <link rel="icon" href="./images/logo3.png">
    
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
            max-height: 400px;
            overflow: hidden;
        }

        .statistics-container {
            background: var(--white);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            height: 100%;
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
                <img src="./images/logo3.png" alt="Joseph's Pot Logo">
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
                            <span class="notification-badge">3</span>
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
                            <div class="compact-stat-number">45</div>
                            <div class="compact-stat-label">This Week</div>
                        </div>
                        <div class="compact-stat">
                            <div class="compact-stat-number">128</div>
                            <div class="compact-stat-label">This Month</div>
                        </div>
                        <div class="compact-stat">
                            <div class="compact-stat-number">890</div>
                            <div class="compact-stat-label">This Year</div>
                        </div>
                        <div class="compact-stat">
                            <div class="compact-stat-number">32%</div>
                            <div class="compact-stat-label">Growth Rate</div>
                        </div>
                    </div>
                    
                    <div class="status-breakdown">
                        <h4>Status Breakdown</h4>
                        <div class="status-bars">
                            <div class="status-bar">
                                <div class="status-bar-label">
                                    <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--warning);"></div>
                                    <span>Pending</span>
                                </div>
                                <div class="status-bar-count">18</div>
                            </div>
                            <div class="status-bar">
                                <div class="status-bar-label">
                                    <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--info);"></div>
                                    <span>Reviewed</span>
                                </div>
                                <div class="status-bar-count">45</div>
                            </div>
                            <div class="status-bar">
                                <div class="status-bar-label">
                                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #8B5CF6;"></div>
                                    <span>Shortlisted</span>
                                </div>
                                <div class="status-bar-count">12</div>
                            </div>
                            <div class="status-bar">
                                <div class="status-bar-label">
                                    <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--success);"></div>
                                    <span>Hired</span>
                                </div>
                                <div class="status-bar-count">9</div>
                            </div>
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
                    <input type="hidden" id="jobId">
                    <!-- Form content same as before -->
                    <div class="form-actions">
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

    <div id="statusModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Update Status</h3>
                <button class="close-modal" onclick="closeStatusModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="statusForm" onsubmit="updateStatus(event)">
                    <input type="hidden" id="statusApplicationId">
                    <div class="form-actions">
                        <button type="button" class="action-btn delete-btn" onclick="closeStatusModal()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="action-btn status-update-btn">
                            <i class="fas fa-check"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Message Display -->
    <div id="messageContainer"></div>

    <script>
        // Mock Data
        const mockJobs = [
            { id: 1, title: "Head Chef", department: "Kitchen", type: "Full Time", status: "active", applications: 45 },
            { id: 2, title: "Restaurant Manager", department: "Service", type: "Full Time", status: "active", applications: 32 },
            { id: 3, title: "Sous Chef", department: "Kitchen", type: "Full Time", status: "active", applications: 28 },
            { id: 4, title: "Wait Staff", department: "Service", type: "Part Time", status: "active", applications: 65 },
            { id: 5, title: "Marketing Coordinator", department: "Marketing", type: "Full Time", status: "inactive", applications: 18 }
        ];

        const mockApplications = [
            { id: 1, name: "John Okoro", position: "Head Chef", email: "john.okoro@email.com", experience: "5 years", status: "Pending", appliedDate: "2024-01-25" },
            { id: 2, name: "Chioma Nwosu", position: "Restaurant Manager", email: "chioma.n@email.com", experience: "4 years", status: "Reviewed", appliedDate: "2024-01-24" },
            { id: 3, name: "Emeka Eze", position: "Sous Chef", email: "emeka.eze@email.com", experience: "3 years", status: "Shortlisted", appliedDate: "2024-01-23" },
            { id: 4, name: "Aisha Yusuf", position: "Wait Staff", email: "aisha.y@email.com", experience: "2 years", status: "Pending", appliedDate: "2024-01-22" },
            { id: 5, name: "David Okafor", position: "Marketing Coordinator", email: "david.okafor@email.com", experience: "3 years", status: "Interview", appliedDate: "2024-01-21" }
        ];

        const mockNotifications = [
            { id: 1, type: "application", title: "New Application", message: "John Okoro applied for Head Chef", time: "10 min ago", read: false },
            { id: 2, type: "reminder", title: "Interview Reminder", message: "Interview with Sarah Johnson tomorrow", time: "1 hour ago", read: false },
            { id: 3, type: "alert", title: "Deadline Alert", message: "Marketing Coordinator closes in 3 days", time: "3 hours ago", read: true }
        ];

        // State
        let currentJobs = [...mockJobs];
        let currentApplications = [...mockApplications];
        let currentNotifications = [...mockNotifications];

        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const notificationIcon = document.getElementById('notificationIcon');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationList = document.getElementById('notificationList');
        const markAllReadBtn = document.getElementById('markAllRead');
        const notificationBadge = document.querySelector('.notification-badge');
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userMenuDropdown = document.getElementById('userMenuDropdown');

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
            renderTables();
            renderNotifications();
            updateNotificationBadge();
            setupMobileMenu();
        });

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

        function markAllAsRead() {
            currentNotifications = currentNotifications.map(n => ({ ...n, read: true }));
            renderNotifications();
            updateNotificationBadge();
            showMessage('All notifications marked as read', 'success');
        }

        function renderNotifications() {
            if (!notificationList) return;
            
            notificationList.innerHTML = '';
            
            currentNotifications.forEach(notification => {
                const item = document.createElement('li');
                item.className = `notification-item ${notification.read ? '' : 'unread'}`;
                item.innerHTML = `
                    <div class="notification-dot" style="${!notification.read ? 'background: var(--primary)' : 'background: transparent'}"></div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-message">${notification.message}</div>
                        <div class="notification-time">${notification.time}</div>
                    </div>
                `;
                
                if (!notification.read) {
                    item.addEventListener('click', function() {
                        markAsRead(notification.id);
                    });
                }
                
                notificationList.appendChild(item);
            });
            
            if (currentNotifications.length === 0) {
                notificationList.innerHTML = '<div class="notification-empty">No notifications</div>';
            }
        }

        function markAsRead(notificationId) {
            const notification = currentNotifications.find(n => n.id === notificationId);
            if (notification) {
                notification.read = true;
                renderNotifications();
                updateNotificationBadge();
            }
        }

        function updateNotificationBadge() {
            const unreadCount = currentNotifications.filter(n => !n.read).length;
            if (notificationBadge) {
                notificationBadge.textContent = unreadCount;
                notificationBadge.style.display = (unreadCount > 0) ? 'flex' : 'none';
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
            if (ctx) {
                new Chart(ctx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Kitchen', 'Service', 'Marketing', 'Finance', 'HR'],
                        datasets: [{
                            data: [40, 30, 15, 10, 5],
                            backgroundColor: ['#8B4513', '#D2691E', '#FFA500', '#10B981', '#3B82F6'],
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
            
            currentApplications.forEach(app => {
                const date = new Date(app.appliedDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${app.name}</td>
                    <td>${app.position}</td>
                    <td style="font-size: 0.85rem;">${app.email}</td>
                    <td>${app.experience}</td>
                    <td><span class="status-badge status-${app.status.toLowerCase()}">${app.status}</span></td>
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

        // Application Functions
        function updateApplicationStatus(appId) {
            showMessage(`Opening status update for application #${appId}`, 'info');
        }

        function viewApplication(appId) {
            showMessage(`Viewing application #${appId} details`, 'info');
        }

        function downloadCV(appId) {
            showMessage(`Downloading CV for application #${appId}`, 'info');
        }

        // Job Functions
        function openJobModal() {
            showMessage('Opening job creation form', 'info');
        }

        function editJob(jobId) {
            showMessage(`Editing job #${jobId}`, 'info');
        }

        function deleteJob(jobId) {
            if (confirm('Are you sure you want to delete this job?')) {
                currentJobs = currentJobs.filter(job => job.id !== jobId);
                renderJobsTable();
                showMessage('Job deleted successfully', 'success');
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

        // Close modals functions (placeholder)
        function closeJobModal() {
            const modal = document.getElementById('jobModal');
            if (modal) modal.style.display = 'none';
            showMessage('Job modal closed', 'info');
        }

        function closeStatusModal() {
            const modal = document.getElementById('statusModal');
            if (modal) modal.style.display = 'none';
            showMessage('Status modal closed', 'info');
        }

        function saveJob(event) {
            event.preventDefault();
            showMessage('Job saved successfully', 'success');
        }

        function updateStatus(event) {
            event.preventDefault();
            showMessage('Status updated successfully', 'success');
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