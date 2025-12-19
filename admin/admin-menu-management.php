<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/logo3.png">
    <title>Menu Management - Joseph's Pot</title>
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

        /* User Menu Dropdown Styles */
        .user-menu-dropdown {
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

        .user-menu-dropdown.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .user-menu-header {
            padding: 15px;
            background: var(--gray);
            border-bottom: 1px solid var(--gray-dark);
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
            color: white;
            font-size: 1rem;
        }

        .user-menu-info h4 {
            font-size: 0.9rem;
            margin-bottom: 2px;
        }

        .user-menu-info p {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .user-menu-items {
            list-style: none;
        }

        .user-menu-item {
            padding: 12px 15px;
            border-bottom: 1px solid var(--gray);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-menu-item:hover {
            background: var(--gray);
        }

        .user-menu-item i {
            font-size: 1rem;
            color: var(--text-light);
            width: 20px;
            text-align: center;
        }

        .user-menu-item span {
            font-size: 0.9rem;
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

        /* Notification Badge */
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

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            transition: opacity 0.3s ease;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid var(--gray);
            border-top: 5px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Menu Management Styles */
        .menu-management {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .menu-header h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .add-item-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            font-weight: 500;
        }

        .add-item-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .menu-tabs {
            display: flex;
            border-bottom: 2px solid var(--gray);
            margin-bottom: 25px;
            overflow-x: auto;
        }

        .menu-tab {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-light);
            transition: var(--transition);
            position: relative;
            white-space: nowrap;
        }

        .menu-tab.active {
            color: var(--primary);
        }

        .menu-tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary);
        }

        .menu-tab:hover {
            color: var(--primary);
        }

        .menu-content {
            display: none;
            min-height: 300px;
        }

        .menu-content.active {
            display: block;
        }

        .menu-items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .menu-item-card {
            background: var(--gray);
            border-radius: 10px;
            overflow: hidden;
            transition: var(--transition);
            box-shadow: var(--shadow);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s ease forwards;
            position: relative;
        }

        .menu-item-card.unavailable {
            opacity: 0.6;
            position: relative;
        }

        .menu-item-card.unavailable::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
            border-radius: 10px;
        }

        .unavailable-badge {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--danger);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            z-index: 2;
            pointer-events: none;
        }

        .unavailable-badge i {
            margin-right: 5px;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .menu-item-image {
            height: 180px;
            background: var(--gray);
            position: relative;
            overflow: hidden;
        }

        .menu-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
            background: linear-gradient(45deg, var(--gray), var(--gray-dark));
        }

        .menu-item-card:hover .menu-item-image img {
            transform: scale(1.05);
        }

        .menu-item-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--accent);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            z-index: 2;
        }

        .menu-item-details {
            padding: 20px;
            position: relative;
            z-index: 2;
        }

        .menu-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .menu-item-name {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .menu-item-price {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.2rem;
        }

        .menu-item-description {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .menu-item-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: var(--text-light);
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .menu-item-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-btn {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            min-width: 100px;
        }

        .edit-btn {
            background: var(--info);
            color: white;
        }

        .delete-btn {
            background: var(--danger);
            color: white;
        }

        .availability-btn {
            background: var(--success);
            color: white;
        }

        .availability-btn.unavailable {
            background: var(--warning);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Special item styling */
        .special-item-indicator {
            position: absolute;
            top: 10px;
            left: 10px;
            background: gold;
            color: var(--dark);
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 2;
        }

        /* Filter buttons */
        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 16px;
            background: var(--gray);
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            color: var(--text);
        }

        .filter-btn.active {
            background: var(--primary);
            color: white;
        }

        .filter-btn:hover {
            background: var(--primary-light);
            color: white;
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--gray-dark);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-actions {
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

        /* Checkbox styles */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        /* Image Upload Styles */
        .image-upload-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .image-upload-box {
            border: 2px dashed var(--gray-dark);
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .image-upload-box:hover {
            border-color: var(--primary);
            background-color: rgba(139, 69, 19, 0.05);
        }

        .image-upload-box i {
            font-size: 2.5rem;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .image-upload-box p {
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .image-upload-box small {
            color: var(--text-light);
            font-size: 0.8rem;
        }

        .image-preview {
            display: none;
            width: 100%;
            max-height: 200px;
            object-fit: contain;
            border-radius: 8px;
            margin-top: 15px;
        }

        .image-upload-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--gray-dark);
        }

        .empty-state h4 {
            font-size: 1.2rem;
            margin-bottom: 10px;
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

        /* Responsive Design */
        @media (max-width: 1200px) {
            .menu-items-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
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
            
            .menu-header h3 {
                font-size: 1.2rem;
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
        }

        @media (max-width: 768px) {
            .menu-items-grid {
                grid-template-columns: 1fr;
            }
            
            .menu-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .add-item-btn {
                width: 100%;
                justify-content: center;
            }
            
            .menu-item-actions {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
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
            
            .menu-management {
                padding: 20px;
            }
            
            .menu-tabs {
                flex-wrap: wrap;
            }
            
            .menu-tab {
                flex: 1;
                min-width: 120px;
                text-align: center;
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            
            .menu-item-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .menu-item-price {
                margin-top: 5px;
            }
            
            .menu-item-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .filter-buttons {
                flex-direction: column;
            }
            
            .filter-btn {
                width: 100%;
                text-align: center;
            }
            
            .modal-content {
                padding: 20px 15px;
            }
            
            .image-upload-box {
                padding: 20px 15px;
            }
            
            .image-upload-box i {
                font-size: 2rem;
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
        }

        @media (max-width: 480px) {
            .logo-area h1 {
                font-size: 1.2rem;
            }
            
            .header h2 {
                font-size: 1.3rem;
            }
            
            .menu-header h3 {
                font-size: 1.1rem;
            }
            
            .menu-item-name {
                font-size: 1rem;
            }
            
            .menu-item-price {
                font-size: 1.1rem;
            }
            
            .menu-item-description {
                font-size: 0.85rem;
            }
            
            .modal-header h3 {
                font-size: 1.1rem;
            }
            
            .add-item-btn, .filter-btn {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            
            .action-btn {
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
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
        <p>Loading menu data...</p>
    </div>

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
                    <a href="admin-logout.php">
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
                    <i class="fas fa-map-marker-alt"></i> Owerri, Nigeria
                </div>
            </div>

            <div class="header">
                <h2>Menu Management</h2>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search menu items...">
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
                                    <div class="user-menu-avatar">AJ</div>
                                    <div class="user-menu-info">
                                        <h4>Admin Joseph</h4>
                                        <p>Super Admin</p>
                                    </div>
                                </div>
                                <ul class="user-menu-items">
                                    <li class="user-menu-item" onclick="window.location.href='admin-settings.php'">
                                        <i class="fas fa-user-cog"></i>
                                        <span>Profile Settings</span>
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
            
            <!-- Menu Management Section -->
            <div class="menu-management">
                <div class="menu-header">
                    <h3>Restaurant Menu</h3>
                    <button class="add-item-btn" id="addItemBtn">
                        <i class="fas fa-plus"></i>
                        Add New Item
                    </button>
                </div>
                
                <!-- Availability Filter -->
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all">All Items</button>
                    <button class="filter-btn" data-filter="available">Available</button>
                    <button class="filter-btn" data-filter="unavailable">Unavailable</button>
                </div>
                
                <div class="menu-tabs">
                    <button class="menu-tab active" data-tab="main-course">Main Course</button>
                    <button class="menu-tab" data-tab="proteins">Proteins</button>
                    <button class="menu-tab" data-tab="swallow">Swallow</button>
                    <button class="menu-tab" data-tab="bulk-orders">Bulk Orders</button>
                    <button class="menu-tab" data-tab="breakfast">Breakfast</button>
                    <button class="menu-tab" data-tab="lunch">Lunch</button>
                    <button class="menu-tab" data-tab="dinner">Dinner</button>
                    <button class="menu-tab" data-tab="drinks">Drinks</button>
                </div>
                
                <!-- Main Course Menu -->
                <div class="menu-content active" id="main-course">
                    <div class="menu-items-grid" id="mainCourseItems">
                        <!-- Main course items will be dynamically added here -->
                    </div>
                </div>
                
                <!-- Proteins Menu -->
                <div class="menu-content" id="proteins">
                    <div class="menu-items-grid" id="proteinsItems">
                        <!-- Protein items will be dynamically added here -->
                    </div>
                </div>
                
                <!-- Swallow Menu -->
                <div class="menu-content" id="swallow">
                    <div class="menu-items-grid" id="swallowItems">
                        <!-- Swallow items will be dynamically added here -->
                    </div>
                </div>
                
                <!-- Bulk Orders Menu -->
                <div class="menu-content" id="bulk-orders">
                    <div class="menu-items-grid" id="bulkOrdersItems">
                        <!-- Bulk order items will be dynamically added here -->
                    </div>
                </div>
                
                <!-- Breakfast Menu -->
                <div class="menu-content" id="breakfast">
                    <div class="menu-items-grid" id="breakfastItems">
                        <!-- Breakfast items will be dynamically added here -->
                    </div>
                </div>
                
                <!-- Lunch Menu -->
                <div class="menu-content" id="lunch">
                    <div class="menu-items-grid" id="lunchItems">
                        <!-- Lunch items will be dynamically added here -->
                    </div>
                </div>
                
                <!-- Dinner Menu -->
                <div class="menu-content" id="dinner">
                    <div class="menu-items-grid" id="dinnerItems">
                        <!-- Dinner items will be dynamically added here -->
                    </div>
                </div>
                
                <!-- Drinks Menu -->
                <div class="menu-content" id="drinks">
                    <div class="menu-items-grid" id="drinksItems">
                        <!-- Drink items will be dynamically added here -->
                    </div>
                </div>
            </div>

            <div class="footer">
                <p>&copy; 2025 Joseph's Pot Admin Dashboard. All rights reserved | Developed by ERIBS Tech</p>
            </div>
        </div>
    </div>

    <!-- Add/Edit Item Modal -->
    <div class="modal" id="itemModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add Menu Item</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="itemForm">
                    <div class="form-group">
                        <label for="itemName">Item Name</label>
                        <input type="text" id="itemName" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="itemDescription">Description</label>
                        <textarea id="itemDescription" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="itemPrice">Price (₦)</label>
                            <input type="number" id="itemPrice" class="form-control" min="0" step="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="itemCategory">Category</label>
                            <select id="itemCategory" class="form-control" required>
                                <option value="">Select Category</option>
                                <option value="main-course">Main Course</option>
                                <option value="proteins">Proteins</option>
                                <option value="swallow">Swallow</option>
                                <option value="bulk-orders">Bulk Orders</option>
                                <option value="breakfast">Breakfast</option>
                                <option value="lunch">Lunch</option>
                                <option value="dinner">Dinner</option>
                                <option value="drinks">Drinks</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="itemPrepTime">Preparation Time (mins)</label>
                            <input type="number" id="itemPrepTime" class="form-control" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="itemCalories">Calories</label>
                            <input type="number" id="itemCalories" class="form-control" min="0">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="itemImage">Image</label>
                        <div class="image-upload-container">
                            <div class="image-upload-box" id="imageUploadBox">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload an image</p>
                                <small>JPG, PNG, or GIF (Max: 5MB)</small>
                                <input type="file" id="itemImage" class="image-upload-input" accept="image/*">
                                <img id="imagePreview" class="image-preview" src="" alt="Image preview">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="itemIngredients">Ingredients (comma separated)</label>
                        <input type="text" id="itemIngredients" class="form-control" placeholder="Ingredient 1, Ingredient 2, ...">
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="isSpecial" class="form-control">
                        <label for="isSpecial">Mark as Special Item</label>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="isAvailable" class="form-control" checked>
                        <label for="isAvailable">Item is Available</label>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="hasTakeawayPrice" class="form-control">
                        <label for="hasTakeawayPrice">Has Takeaway Price</label>
                    </div>
                    
                    <div class="form-group" id="takeawayPriceGroup" style="display: none;">
                        <label for="takeawayPrice">Takeaway Price (₦)</label>
                        <input type="number" id="takeawayPrice" class="form-control" min="0" step="0.01">
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Item</button>
                    </div>
                </form>
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

        // Sample notification data
        const notifications = [
            {
                id: 1,
                title: 'New Menu Item Added',
                message: 'Customer added "Egusi Delight" to favorites',
                time: '10 minutes ago',
                unread: true
            },
            {
                id: 2,
                title: 'Item Unavailable',
                message: 'Palm Wine is running low in stock',
                time: '1 hour ago',
                unread: true
            },
            {
                id: 3,
                title: 'Menu Update',
                message: 'Breakfast menu items have been updated',
                time: '2 hours ago',
                unread: false
            },
            {
                id: 4,
                title: 'New Special Item',
                message: 'Added "Joe\'s Secret" as special item',
                time: '1 day ago',
                unread: false
            },
            {
                id: 5,
                title: 'Price Update',
                message: 'Updated prices for bulk order items',
                time: '2 days ago',
                unread: false
            }
        ];

        // Sample menu data with availability status
        const menuData = {
            "main-course": [
                {
                    id: 1001,
                    name: "Joe's Secret",
                    description: "Flavorful, bold full chicken red pepper base, seasoned with our signature blend of spices and served with salad and chips",
                    price: 25000,
                    prepTime: 40,
                    calories: 750,
                    image: "./images/default-food.jpg",
                    ingredients: "Chicken, Red Peppers, Spices, Salad, Chips",
                    isSpecial: true,
                    tags: ["Popular", "Chef's Special"],
                    hasTakeaway: false,
                    isAvailable: true
                },
                {
                    id: 1002,
                    name: "Jollof Rice",
                    description: "Flavorful, bold tomato and red pepper base, seasoned with our signature blend of spices",
                    price: 3900,
                    prepTime: 30,
                    calories: 450,
                    image: "./images/default-food.jpg",
                    ingredients: "Rice, Tomatoes, Red Peppers, Onions, Spices",
                    isSpecial: true,
                    tags: ["Popular", "Chef's Special"],
                    hasTakeaway: false,
                    isAvailable: true
                }
            ],
            "proteins": [
                {
                    id: 2001,
                    name: "Chicken",
                    description: "Flame-grilled or fried chicken pieces coated in our signature, fiery red pepper sauce",
                    price: 5000,
                    prepTime: 25,
                    calories: 420,
                    image: "./images/default-food.jpg",
                    ingredients: "Chicken, Red Pepper Sauce, Spices",
                    isSpecial: false,
                    tags: [],
                    hasTakeaway: false,
                    isAvailable: false  // Example: This item is currently unavailable
                },
                {
                    id: 2002,
                    name: "Cowtail",
                    description: "Rich, deep beef flavor and succulent, gelatinous texture with Joseph's traditional spices",
                    price: 7000,
                    prepTime: 40,
                    calories: 480,
                    image: "./images/default-food.jpg",
                    ingredients: "Cowtail, Spices",
                    isSpecial: false,
                    tags: [],
                    hasTakeaway: false,
                    isAvailable: true
                }
            ],
            "swallow": [
                {
                    id: 3001,
                    name: "Semolina",
                    description: "Smooth, moldable dough-like staple made from coarsely ground durum wheat semolina",
                    price: 1500,
                    prepTime: 10,
                    calories: 280,
                    image: "./images/default-food.jpg",
                    ingredients: "Semolina, Water",
                    isSpecial: false,
                    tags: [],
                    hasTakeaway: false,
                    isAvailable: true
                },
                {
                    id: 3002,
                    name: "Garri",
                    description: "Traditional, smooth, and stretchy dough-like staple made from fermented cassava",
                    price: 1500,
                    prepTime: 5,
                    calories: 320,
                    image: "./images/default-food.jpg",
                    ingredients: "Garri, Water",
                    isSpecial: false,
                    tags: [],
                    hasTakeaway: false,
                    isAvailable: true
                }
            ],
            "bulk-orders": [
                {
                    id: 4001,
                    name: "1.5 liter of Ofe-Owerri",
                    description: "Traditional Owerri soup with assorted meats",
                    price: 27000,
                    prepTime: 60,
                    calories: 650,
                    image: "./images/default-food.jpg",
                    ingredients: "Assorted Meats, Vegetables, Spices",
                    isSpecial: true,
                    tags: ["Bulk Order"],
                    hasTakeaway: true,
                    takeawayPrice: 29000,
                    isAvailable: false  // Example: This item is currently unavailable
                },
                {
                    id: 4002,
                    name: "1.5 liter of Ofe-Anara",
                    description: "Traditional Anara soup with assorted proteins",
                    price: 20000,
                    prepTime: 55,
                    calories: 600,
                    image: "./images/default-food.jpg",
                    ingredients: "Proteins, Vegetables, Spices",
                    isSpecial: false,
                    tags: ["Bulk Order"],
                    hasTakeaway: true,
                    takeawayPrice: 22000,
                    isAvailable: true
                }
            ],
            "breakfast": [
                {
                    id: 5001,
                    name: "Yam and Egg Sauce",
                    description: "Freshly pounded yam served with specially prepared egg sauce with tomatoes and peppers.",
                    price: 2500,
                    prepTime: 20,
                    calories: 420,
                    image: "./images/default-food.jpg",
                    ingredients: "Yam, Eggs, Tomatoes, Onions, Pepper, Oil",
                    isSpecial: false,
                    tags: [],
                    hasTakeaway: false,
                    isAvailable: true
                },
                {
                    id: 5002,
                    name: "Akara and Pap",
                    description: "Freshly made bean cakes served with traditional corn pap.",
                    price: 1500,
                    prepTime: 15,
                    calories: 320,
                    image: "./images/default-food.jpg",
                    ingredients: "Beans, Pepper, Onions, Corn Pap, Milk",
                    isSpecial: false,
                    tags: [],
                    hasTakeaway: false,
                    isAvailable: true
                }
            ],
            "lunch": [
                {
                    id: 6001,
                    name: "Ofe Owerri Special",
                    description: "Traditional Igbo soup made with assorted meats, fish, and fresh vegetables.",
                    price: 3200,
                    prepTime: 35,
                    calories: 380,
                    image: "./images/default-food.jpg",
                    ingredients: "Assorted Meat, Stockfish, Ugwu, Bitterleaf, Palm Oil",
                    isSpecial: false,
                    tags: [],
                    hasTakeaway: false,
                    isAvailable: false  // Example: This item is currently unavailable
                },
                {
                    id: 6002,
                    name: "Jollof Rice with Chicken",
                    description: "Flavorful Nigerian jollof rice served with grilled chicken and plantains.",
                    price: 2800,
                    prepTime: 30,
                    calories: 450,
                    image: "./images/default-food.jpg",
                    ingredients: "Rice, Tomatoes, Chicken, Plantains, Spices",
                    isSpecial: false,
                    tags: [],
                    hasTakeaway: false,
                    isAvailable: true
                }
            ],
            "dinner": [
                {
                    id: 7001,
                    name: "Nkwobi",
                    description: "Spicy cow foot delicacy served with palm wine onions.",
                    price: 3500,
                    prepTime: 25,
                    calories: 520,
                    image: "./images/default-food.jpg",
                    ingredients: "Cow Foot, Utazi, Palm Oil, Spices, Onions",
                    isSpecial: false,
                    tags: [],
                    hasTakeaway: false,
                    isAvailable: true
                },
                {
                    id: 7002,
                    name: "Grilled Fish with Vegetables",
                    description: "Fresh fish grilled to perfection with assorted vegetables.",
                    price: 4200,
                    prepTime: 40,
                    calories: 380,
                    image: "./images/default-food.jpg",
                    ingredients: "Fresh Fish, Bell Peppers, Onions, Tomatoes, Spices",
                    isSpecial: false,
                    tags: [],
                    hasTakeaway: false,
                    isAvailable: true
                }
            ],
            "drinks": [
                {
                    id: 8001,
                    name: "Palm Wine",
                    description: "Fresh traditional palm wine served chilled in calabash.",
                    price: 800,
                    prepTime: 5,
                    calories: 180,
                    image: "./images/default-food.jpg",
                    ingredients: "Palm Wine",
                    isSpecial: false,
                    tags: [],
                    hasTakeaway: false,
                    isAvailable: false  // Example: This item is currently unavailable
                },
                {
                    id: 8002,
                    name: "Zobo Drink",
                    description: "Refreshing hibiscus drink with pineapple and ginger.",
                    price: 500,
                    prepTime: 10,
                    calories: 120,
                    image: "./images/default-food.jpg",
                    ingredients: "Hibiscus, Pineapple, Ginger, Sugar",
                    isSpecial: false,
                    tags: [],
                    hasTakeaway: false,
                    isAvailable: true
                }
            ]
        };

        // DOM Elements
        const loadingOverlay = document.getElementById('loadingOverlay');
        const menuTabs = document.querySelectorAll('.menu-tab');
        const menuContents = document.querySelectorAll('.menu-content');
        const addItemBtn = document.getElementById('addItemBtn');
        const itemModal = document.getElementById('itemModal');
        const closeModal = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const itemForm = document.getElementById('itemForm');
        const modalTitle = document.getElementById('modalTitle');
        const imageUploadBox = document.getElementById('imageUploadBox');
        const imageInput = document.getElementById('itemImage');
        const imagePreview = document.getElementById('imagePreview');
        const hasTakeawayCheckbox = document.getElementById('hasTakeawayPrice');
        const takeawayPriceGroup = document.getElementById('takeawayPriceGroup');
        const takeawayPriceInput = document.getElementById('takeawayPrice');
        const searchInput = document.getElementById('searchInput');
        const filterButtons = document.querySelectorAll('.filter-btn');
        const isAvailableCheckbox = document.getElementById('isAvailable');
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebar = document.getElementById('sidebar');
        
        // Notification and User Menu elements
        const notificationIcon = document.getElementById('notificationIcon');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationList = document.getElementById('notificationList');
        const markAllReadBtn = document.getElementById('markAllRead');
        const notificationBadge = document.querySelector('.notification-badge');
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userMenuDropdown = document.getElementById('userMenuDropdown');

        // Current filter state
        let currentFilter = 'all';

        // Show loading initially
        loadingOverlay.style.display = 'flex';

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

        // User Menu functionality
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userMenuDropdown.classList.toggle('active');
            // Close notification dropdown if open
            notificationDropdown.classList.remove('active');
        });

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
            // Close user menu dropdown if open
            userMenuDropdown.classList.remove('active');
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            // Close notification dropdown
            if (!notificationIcon.contains(e.target) && !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.remove('active');
            }
            
            // Close user menu dropdown
            if (!userMenuBtn.contains(e.target) && !userMenuDropdown.contains(e.target)) {
                userMenuDropdown.classList.remove('active');
            }
        });

        // Mark all as read button
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                markAllAsRead();
            });
        }

        // Tab switching functionality
        menuTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetTab = tab.getAttribute('data-tab');
                
                // Update active tab
                menuTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                // Show corresponding content
                menuContents.forEach(content => {
                    content.classList.remove('active');
                    if (content.id === targetTab) {
                        content.classList.add('active');
                    }
                });
            });
        });

        // Filter button functionality
        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Update active filter button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                // Get filter type
                currentFilter = button.getAttribute('data-filter');
                
                // Re-render items with current filter
                renderMenuItems();
            });
        });

        // Modal functionality
        addItemBtn.addEventListener('click', () => {
            modalTitle.textContent = 'Add Menu Item';
            itemForm.reset();
            resetImagePreview();
            takeawayPriceGroup.style.display = 'none';
            isAvailableCheckbox.checked = true; // Default to available
            itemModal.style.display = 'flex';
        });

        closeModal.addEventListener('click', () => {
            itemModal.style.display = 'none';
        });

        cancelBtn.addEventListener('click', () => {
            itemModal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', (event) => {
            if (event.target === itemModal) {
                itemModal.style.display = 'none';
            }
        });

        // Toggle takeaway price field
        hasTakeawayCheckbox.addEventListener('change', function() {
            takeawayPriceGroup.style.display = this.checked ? 'block' : 'none';
        });

        // Image upload functionality
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Check file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size exceeds 5MB limit. Please choose a smaller file.');
                    resetImagePreview();
                    return;
                }
                
                // Check file type
                if (!file.type.match('image.*')) {
                    alert('Please select an image file (JPG, PNG, or GIF).');
                    resetImagePreview();
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

        // Reset image preview
        function resetImagePreview() {
            imagePreview.src = '';
            imagePreview.style.display = 'none';
            imageInput.value = '';
        }

        // Format price with commas
        function formatPrice(price) {
            if (price === 0) return 'Price on request';
            return `₦${price.toLocaleString()}`;
        }

        // Get availability status text
        function getAvailabilityText(isAvailable) {
            return isAvailable ? 'Available' : 'Unavailable';
        }

        // Get availability icon
        function getAvailabilityIcon(isAvailable) {
            return isAvailable ? 'fas fa-check-circle' : 'fas fa-times-circle';
        }

        // Toggle item availability
        function toggleAvailability(itemId, category) {
            const item = menuData[category].find(item => item.id === itemId);
            if (item) {
                item.isAvailable = !item.isAvailable;
                renderMenuItems();
                
                const action = item.isAvailable ? 'available' : 'unavailable';
                alert(`"${item.name}" is now marked as ${action}.`);
            }
        }

        // Filter items based on current filter
        function filterItemsByAvailability(items) {
            if (currentFilter === 'all') {
                return items;
            } else if (currentFilter === 'available') {
                return items.filter(item => item.isAvailable);
            } else if (currentFilter === 'unavailable') {
                return items.filter(item => !item.isAvailable);
            }
            return items;
        }

        // Render menu items with current filter
        function renderMenuItems() {
            // Show loading
            loadingOverlay.style.display = 'flex';
            loadingOverlay.style.opacity = '1';
            
            // Clear existing items
            document.querySelectorAll('.menu-items-grid').forEach(grid => {
                grid.innerHTML = '';
            });
            
            // Render items for each category
            Object.keys(menuData).forEach(category => {
                const container = document.getElementById(`${category}Items`);
                if (!container) return; // Skip if container doesn't exist
                
                // Filter items
                const filteredItems = filterItemsByAvailability(menuData[category]);
                
                if (!filteredItems || filteredItems.length === 0) {
                    let message = '';
                    if (currentFilter === 'available') {
                        message = `No available ${category.replace('-', ' ')} items`;
                    } else if (currentFilter === 'unavailable') {
                        message = `No unavailable ${category.replace('-', ' ')} items`;
                    } else {
                        message = `No ${category.replace('-', ' ')} items yet`;
                    }
                    
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-utensils"></i>
                            <h4>${message}</h4>
                            <p>${currentFilter === 'all' ? 'Add your first item to get started' : 'All items are ' + (currentFilter === 'available' ? 'unavailable' : 'available')}</p>
                        </div>
                    `;
                    return;
                }
                
                // Create document fragment for better performance
                const fragment = document.createDocumentFragment();
                
                filteredItems.forEach(item => {
                    const itemCard = document.createElement('div');
                    itemCard.className = `menu-item-card ${!item.isAvailable ? 'unavailable' : ''}`;
                    
                    // Generate unavailable badge if needed
                    const unavailableBadge = !item.isAvailable ? `
                        <div class="unavailable-badge">
                            <i class="fas fa-ban"></i> Currently Unavailable
                        </div>
                    ` : '';
                    
                    // Generate tags HTML
                    const tagsHTML = item.isSpecial ? `
                        <div class="special-item-indicator">
                            <i class="fas fa-crown"></i> Special
                        </div>
                    ` : '';
                    
                    // Generate price display
                    let priceDisplay = formatPrice(item.price);
                    if (item.hasTakeaway && item.takeawayPrice > 0) {
                        priceDisplay = `${formatPrice(item.price)}<br><small style="color: var(--text-light); font-size: 0.9rem;">Takeaway: ${formatPrice(item.takeawayPrice)}</small>`;
                    } else if (item.price === 0) {
                        priceDisplay = '<span style="color: var(--text-light);">Price on request</span>';
                    }
                    
                    // Generate availability button text and icon
                    const availabilityText = item.isAvailable ? 'Mark as Unavailable' : 'Mark as Available';
                    const availabilityIcon = item.isAvailable ? 'fas fa-ban' : 'fas fa-check';
                    const availabilityBtnClass = item.isAvailable ? 'availability-btn' : 'availability-btn unavailable';
                    
                    itemCard.innerHTML = `
                        <div class="menu-item-image">
                            <img src="${item.image}" alt="${item.name}" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 300 200\"%3E%3Crect width=\"300\" height=\"200\" fill=\"%23f5f5f5\"/%3E%3Ctext x=\"50%25\" y=\"50%25\" font-family=\"Arial\" font-size=\"16\" fill=\"%23666\" text-anchor=\"middle\" dy=\".3em\"%3E${encodeURIComponent(item.name)}%3C/text%3E%3C/svg%3E'">
                            ${tagsHTML}
                            ${unavailableBadge}
                            <div class="menu-item-badge">${category.replace('-', ' ').toUpperCase()}</div>
                        </div>
                        <div class="menu-item-details">
                            <div class="menu-item-header">
                                <div class="menu-item-name">${item.name}</div>
                                <div class="menu-item-price">${priceDisplay}</div>
                            </div>
                            <div class="menu-item-description">${item.description}</div>
                            <div class="menu-item-meta">
                                ${item.prepTime > 0 ? `<span><i class="fas fa-clock"></i> ${item.prepTime} mins</span>` : ''}
                                ${item.calories > 0 ? `<span><i class="fas fa-fire"></i> ${item.calories} kcal</span>` : ''}
                                <span><i class="${getAvailabilityIcon(item.isAvailable)}" style="color: ${item.isAvailable ? 'var(--success)' : 'var(--danger)'};"></i> ${getAvailabilityText(item.isAvailable)}</span>
                            </div>
                            ${item.ingredients ? `<div style="font-size: 0.8rem; color: var(--text-light); margin-bottom: 10px;"><strong>Ingredients:</strong> ${item.ingredients}</div>` : ''}
                            <div class="menu-item-actions">
                                <button class="action-btn edit-btn" data-id="${item.id}" data-category="${category}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="action-btn ${availabilityBtnClass}" data-id="${item.id}" data-category="${category}">
                                    <i class="${availabilityIcon}"></i> ${availabilityText}
                                </button>
                                <button class="action-btn delete-btn" data-id="${item.id}" data-category="${category}">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    `;
                    fragment.appendChild(itemCard);
                });
                
                container.appendChild(fragment);
            });
            
            // Add event listeners
            setTimeout(() => {
                addEventListeners();
                
                // Hide loading overlay after a short delay
                setTimeout(() => {
                    loadingOverlay.style.opacity = '0';
                    setTimeout(() => {
                        loadingOverlay.style.display = 'none';
                    }, 300);
                }, 500);
            }, 100);
        }

        // Add event listeners to action buttons
        function addEventListeners() {
            // Edit buttons
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(btn.getAttribute('data-id'));
                    const category = btn.getAttribute('data-category');
                    editMenuItem(id, category);
                });
            });
            
            // Delete buttons
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(btn.getAttribute('data-id'));
                    const category = btn.getAttribute('data-category');
                    deleteMenuItem(id, category);
                });
            });
            
            // Availability toggle buttons
            document.querySelectorAll('.availability-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const id = parseInt(btn.getAttribute('data-id'));
                    const category = btn.getAttribute('data-category');
                    toggleAvailability(id, category);
                });
            });
        }

        // Edit menu item
        function editMenuItem(id, category) {
            const item = menuData[category].find(item => item.id === id);
            if (!item) return;
            
            // Fill form with item data
            document.getElementById('itemName').value = item.name;
            document.getElementById('itemDescription').value = item.description;
            document.getElementById('itemPrice').value = item.price;
            document.getElementById('itemCategory').value = category;
            document.getElementById('itemPrepTime').value = item.prepTime;
            document.getElementById('itemCalories').value = item.calories;
            document.getElementById('itemIngredients').value = item.ingredients || '';
            document.getElementById('isSpecial').checked = item.isSpecial || false;
            document.getElementById('isAvailable').checked = item.isAvailable !== false; // Default to true if undefined
            document.getElementById('hasTakeawayPrice').checked = item.hasTakeaway || false;
            
            if (item.hasTakeaway) {
                takeawayPriceGroup.style.display = 'block';
                document.getElementById('takeawayPrice').value = item.takeawayPrice || 0;
            } else {
                takeawayPriceGroup.style.display = 'none';
            }
            
            // Set image preview if available
            if (item.image && item.image.startsWith('data:')) {
                imagePreview.src = item.image;
                imagePreview.style.display = 'block';
            } else if (item.image) {
                // If it's a path to an image
                imagePreview.src = item.image;
                imagePreview.style.display = 'block';
            } else {
                resetImagePreview();
            }
            
            // Update modal title
            modalTitle.textContent = 'Edit Menu Item';
            
            // Store the item being edited
            itemForm.setAttribute('data-editing-id', id);
            itemForm.setAttribute('data-editing-category', category);
            
            // Show modal
            itemModal.style.display = 'flex';
        }

        // Delete menu item
        function deleteMenuItem(id, category) {
            if (confirm('Are you sure you want to delete this menu item?')) {
                menuData[category] = menuData[category].filter(item => item.id !== id);
                renderMenuItems();
                alert('Menu item deleted successfully!');
            }
        }

        // Search functionality
        searchInput.addEventListener('input', debounce(function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            
            if (searchTerm === '') {
                renderMenuItems();
                return;
            }
            
            // Show loading for search
            loadingOverlay.style.display = 'flex';
            loadingOverlay.style.opacity = '1';
            
            // Filter items across all categories
            setTimeout(() => {
                Object.keys(menuData).forEach(category => {
                    const container = document.getElementById(`${category}Items`);
                    if (!container) return;
                    
                    // First filter by availability
                    let filteredItems = filterItemsByAvailability(menuData[category]);
                    
                    // Then filter by search term
                    filteredItems = filteredItems.filter(item => 
                        item.name.toLowerCase().includes(searchTerm) ||
                        item.description.toLowerCase().includes(searchTerm) ||
                        (item.ingredients && item.ingredients.toLowerCase().includes(searchTerm))
                    );
                    
                    if (filteredItems.length === 0) {
                        container.innerHTML = `
                            <div class="empty-state">
                                <i class="fas fa-search"></i>
                                <h4>No results found</h4>
                                <p>No ${category.replace('-', ' ')} items match your search</p>
                            </div>
                        `;
                    } else {
                        const fragment = document.createDocumentFragment();
                        
                        filteredItems.forEach(item => {
                            const itemCard = document.createElement('div');
                            itemCard.className = `menu-item-card ${!item.isAvailable ? 'unavailable' : ''}`;
                            
                            // Generate unavailable badge if needed
                            const unavailableBadge = !item.isAvailable ? `
                                <div class="unavailable-badge">
                                    <i class="fas fa-ban"></i> Currently Unavailable
                                </div>
                            ` : '';
                            
                            // Generate tags HTML
                            const tagsHTML = item.isSpecial ? `
                                <div class="special-item-indicator">
                                    <i class="fas fa-crown"></i> Special
                                </div>
                            ` : '';
                            
                            // Generate price display
                            let priceDisplay = formatPrice(item.price);
                            if (item.hasTakeaway && item.takeawayPrice > 0) {
                                priceDisplay = `${formatPrice(item.price)}<br><small style="color: var(--text-light); font-size: 0.9rem;">Takeaway: ${formatPrice(item.takeawayPrice)}</small>`;
                            } else if (item.price === 0) {
                                priceDisplay = '<span style="color: var(--text-light);">Price on request</span>';
                            }
                            
                            // Generate availability button text and icon
                            const availabilityText = item.isAvailable ? 'Mark as Unavailable' : 'Mark as Available';
                            const availabilityIcon = item.isAvailable ? 'fas fa-ban' : 'fas fa-check';
                            const availabilityBtnClass = item.isAvailable ? 'availability-btn' : 'availability-btn unavailable';
                            
                            itemCard.innerHTML = `
                                <div class="menu-item-image">
                                    <img src="${item.image}" alt="${item.name}" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 300 200\"%3E%3Crect width=\"300\" height=\"200\" fill=\"%23f5f5f5\"/%3E%3Ctext x=\"50%25\" y=\"50%25\" font-family=\"Arial\" font-size=\"16\" fill=\"%23666\" text-anchor=\"middle\" dy=\".3em\"%3E${encodeURIComponent(item.name)}%3C/text%3E%3C/svg%3E'">
                                    ${tagsHTML}
                                    ${unavailableBadge}
                                    <div class="menu-item-badge">${category.replace('-', ' ').toUpperCase()}</div>
                                </div>
                                <div class="menu-item-details">
                                    <div class="menu-item-header">
                                        <div class="menu-item-name">${item.name}</div>
                                        <div class="menu-item-price">${priceDisplay}</div>
                                    </div>
                                    <div class="menu-item-description">${item.description}</div>
                                    <div class="menu-item-meta">
                                        ${item.prepTime > 0 ? `<span><i class="fas fa-clock"></i> ${item.prepTime} mins</span>` : ''}
                                        ${item.calories > 0 ? `<span><i class="fas fa-fire"></i> ${item.calories} kcal</span>` : ''}
                                        <span><i class="${getAvailabilityIcon(item.isAvailable)}" style="color: ${item.isAvailable ? 'var(--success)' : 'var(--danger)'};"></i> ${getAvailabilityText(item.isAvailable)}</span>
                                    </div>
                                    ${item.ingredients ? `<div style="font-size: 0.8rem; color: var(--text-light); margin-bottom: 10px;"><strong>Ingredients:</strong> ${item.ingredients}</div>` : ''}
                                    <div class="menu-item-actions">
                                        <button class="action-btn edit-btn" data-id="${item.id}" data-category="${category}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="action-btn ${availabilityBtnClass}" data-id="${item.id}" data-category="${category}">
                                            <i class="${availabilityIcon}"></i> ${availabilityText}
                                        </button>
                                        <button class="action-btn delete-btn" data-id="${item.id}" data-category="${category}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            `;
                            fragment.appendChild(itemCard);
                        });
                        
                        container.innerHTML = '';
                        container.appendChild(fragment);
                    }
                });
                
                // Re-attach event listeners
                addEventListeners();
                
                // Hide loading
                setTimeout(() => {
                    loadingOverlay.style.opacity = '0';
                    setTimeout(() => {
                        loadingOverlay.style.display = 'none';
                    }, 300);
                }, 500);
            }, 300);
        }, 500));

        // Debounce function for search
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Form submission
        itemForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const editingId = itemForm.getAttribute('data-editing-id');
            const editingCategory = itemForm.getAttribute('data-editing-category');
            
            // Show loading
            loadingOverlay.style.display = 'flex';
            loadingOverlay.style.opacity = '1';
            
            setTimeout(() => {
                if (editingId) {
                    // Editing existing item
                    const itemIndex = menuData[editingCategory].findIndex(item => item.id === parseInt(editingId));
                    if (itemIndex !== -1) {
                        // Get form values
                        const name = document.getElementById('itemName').value;
                        const description = document.getElementById('itemDescription').value;
                        const price = parseFloat(document.getElementById('itemPrice').value) || 0;
                        const category = document.getElementById('itemCategory').value;
                        const prepTime = parseInt(document.getElementById('itemPrepTime').value) || 0;
                        const calories = parseInt(document.getElementById('itemCalories').value) || 0;
                        const ingredients = document.getElementById('itemIngredients').value;
                        const isSpecial = document.getElementById('isSpecial').checked;
                        const isAvailable = document.getElementById('isAvailable').checked;
                        const hasTakeaway = document.getElementById('hasTakeawayPrice').checked;
                        const takeawayPrice = hasTakeaway ? parseFloat(document.getElementById('takeawayPrice').value) || 0 : 0;
                        
                        // Handle image
                        let image = menuData[editingCategory][itemIndex].image;
                        if (imageInput.files && imageInput.files[0]) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                image = e.target.result;
                                
                                // Update item with new image
                                updateMenuItem(
                                    editingId, editingCategory, itemIndex,
                                    name, description, price, category, 
                                    prepTime, calories, image, ingredients,
                                    isSpecial, isAvailable, hasTakeaway, takeawayPrice
                                );
                            };
                            reader.readAsDataURL(imageInput.files[0]);
                        } else {
                            // No new image, update without changing image
                            updateMenuItem(
                                editingId, editingCategory, itemIndex,
                                name, description, price, category, 
                                prepTime, calories, image, ingredients,
                                isSpecial, isAvailable, hasTakeaway, takeawayPrice
                            );
                        }
                    }
                } else {
                    // Adding new item
                    const name = document.getElementById('itemName').value;
                    const description = document.getElementById('itemDescription').value;
                    const price = parseFloat(document.getElementById('itemPrice').value) || 0;
                    const category = document.getElementById('itemCategory').value;
                    const prepTime = parseInt(document.getElementById('itemPrepTime').value) || 0;
                    const calories = parseInt(document.getElementById('itemCalories').value) || 0;
                    const ingredients = document.getElementById('itemIngredients').value;
                    const isSpecial = document.getElementById('isSpecial').checked;
                    const isAvailable = document.getElementById('isAvailable').checked;
                    const hasTakeaway = document.getElementById('hasTakeawayPrice').checked;
                    const takeawayPrice = hasTakeaway ? parseFloat(document.getElementById('takeawayPrice').value) || 0 : 0;
                    
                    // Handle image
                    if (imageInput.files && imageInput.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const image = e.target.result;
                            
                            // Create new item
                            const newItem = {
                                id: Date.now(),
                                name,
                                description,
                                price,
                                prepTime,
                                calories,
                                image,
                                ingredients,
                                isSpecial,
                                tags: isSpecial ? ["Special"] : [],
                                isAvailable,
                                hasTakeaway,
                                takeawayPrice
                            };
                            
                            // Add to menu data
                            if (!menuData[category]) menuData[category] = [];
                            menuData[category].push(newItem);
                            
                            // Update UI
                            renderMenuItems();
                            
                            // Close modal
                            itemModal.style.display = 'none';
                            
                            // Show success message
                            alert(`"${name}" has been added to the ${category} menu!`);
                        };
                        reader.readAsDataURL(imageInput.files[0]);
                    } else {
                        // No image provided, use default
                        const newItem = {
                            id: Date.now(),
                            name,
                            description,
                            price,
                            prepTime,
                            calories,
                            image: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 200"%3E%3Crect width="300" height="200" fill="%23f5f5f5"/%3E%3Ctext x="50%25" y="50%25" font-family="Arial" font-size="16" fill="%23666" text-anchor="middle" dy=".3em"%3E' + encodeURIComponent(name) + '%3C/text%3E%3C/svg%3E',
                            ingredients,
                            isSpecial,
                            tags: isSpecial ? ["Special"] : [],
                            isAvailable,
                            hasTakeaway,
                            takeawayPrice
                        };
                        
                        // Add to menu data
                        if (!menuData[category]) menuData[category] = [];
                        menuData[category].push(newItem);
                        
                        // Update UI
                        renderMenuItems();
                        
                        // Close modal
                        itemModal.style.display = 'none';
                        
                        // Show success message
                        alert(`"${name}" has been added to the ${category} menu!`);
                    }
                }
            }, 300);
        });

        // Helper function to update menu item
        function updateMenuItem(id, oldCategory, itemIndex, name, description, price, newCategory, prepTime, calories, image, ingredients, isSpecial, isAvailable, hasTakeaway, takeawayPrice) {
            // Create updated item
            const updatedItem = {
                id: parseInt(id),
                name,
                description,
                price,
                prepTime,
                calories,
                image,
                ingredients,
                isSpecial,
                tags: isSpecial ? ["Special"] : [],
                isAvailable,
                hasTakeaway,
                takeawayPrice
            };
            
            // If category changed, move item to new category
            if (newCategory !== oldCategory) {
                // Remove from old category
                menuData[oldCategory].splice(itemIndex, 1);
                // Add to new category
                if (!menuData[newCategory]) menuData[newCategory] = [];
                menuData[newCategory].push(updatedItem);
            } else {
                // Update in same category
                menuData[oldCategory][itemIndex] = updatedItem;
            }
            
            // Update UI
            renderMenuItems();
            
            // Close modal and reset form
            itemModal.style.display = 'none';
            itemForm.reset();
            resetImagePreview();
            itemForm.removeAttribute('data-editing-id');
            itemForm.removeAttribute('data-editing-category');
            takeawayPriceGroup.style.display = 'none';
            
            // Show success message
            alert(`"${name}" has been updated successfully!`);
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize notifications
            renderNotifications();
            
            // Initial render
            renderMenuItems();
        });
    </script>
</body>
</html>