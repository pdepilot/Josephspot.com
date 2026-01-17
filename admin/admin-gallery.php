<?php
// Central authentication and permission check
require_once 'admin-auth.php';
checkPageAccess(); // This checks authentication and permission for current page

// Get admin user data
$admin_data = getCurrentAdmin();
$username = isset($admin_data['full_name']) ? $admin_data['full_name'] : (isset($admin_data['username']) ? $admin_data['username'] : 'Admin');
$user_initials = 'AJ';
if (isset($admin_data['full_name']) && !empty($admin_data['full_name'])) {
    $nameParts = explode(' ', $admin_data['full_name']);
    $user_initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : substr($nameParts[0], 1, 1)));
} elseif (isset($admin_data['username'])) {
    $user_initials = strtoupper(substr($admin_data['username'], 0, 2));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/logo3.png">
    <title>Gallery Management - Joseph's Pot</title>
    <link rel="stylesheet" href="../fontawesome-free-6.7.2-web/css/all.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        /* Toast notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 2000;
            pointer-events: none;
        }

        .toast {
            min-width: 260px;
            background: #ffffff;
            color: #333;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 5px solid var(--primary);
            animation: slideIn 0.25s ease, fadeOut 0.3s ease 3.2s forwards;
            pointer-events: all;
        }

        .toast.success {
            border-color: var(--success);
        }

        .toast.error {
            border-color: var(--danger);
        }

        .toast .icon {
            font-size: 1.1rem;
        }

        .toast.success .icon {
            color: var(--success);
        }

        .toast.error .icon {
            color: var(--danger);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px) translateX(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0) translateX(0);
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateY(-6px);
            }
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

        .stat-card.food::before {
            background: var(--success);
        }

        .stat-card.restaurant::before {
            background: var(--warning);
        }

        .stat-card.events::before {
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

        .stat-card.food i {
            color: var(--success);
        }

        .stat-card.restaurant i {
            color: var(--warning);
        }

        .stat-card.events i {
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

        /* Gallery Controls */
        .gallery-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .gallery-filters {
            display: flex;
            gap: 10px;
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

        .upload-btn {
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
            font-weight: 500;
        }

        .upload-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Gallery Grid */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .gallery-item {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .gallery-item-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .gallery-item-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
        }

        .gallery-item:hover .gallery-item-overlay {
            opacity: 1;
        }

        .gallery-item-actions {
            display: flex;
            gap: 10px;
        }

        .gallery-action-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            color: white;
            font-size: 1rem;
        }

        .gallery-action-btn.view {
            background: var(--info);
        }

        .gallery-action-btn.edit {
            background: var(--warning);
        }

        .gallery-action-btn.delete {
            background: var(--danger);
        }

        .gallery-action-btn:hover {
            transform: scale(1.1);
        }

        .gallery-item-info {
            padding: 15px;
        }

        .gallery-item-title {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 1rem;
        }

        .gallery-item-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .gallery-item-category {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .category-food {
            background: rgba(76, 175, 80, 0.2);
            color: var(--success);
        }

        .category-restaurant {
            background: rgba(255, 152, 0, 0.2);
            color: var(--warning);
        }

        .category-events {
            background: rgba(244, 67, 54, 0.2);
            color: var(--danger);
        }

        .category-staff {
            background: rgba(33, 150, 243, 0.2);
            color: var(--info);
        }

        /* Upload Modal */
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

        .upload-area {
            border: 2px dashed var(--gray-dark);
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 20px;
            transition: var(--transition);
            cursor: pointer;
        }

        .upload-area:hover {
            border-color: var(--primary);
            background: rgba(139, 69, 19, 0.05);
        }

        .upload-area i {
            font-size: 3rem;
            color: var(--text-light);
            margin-bottom: 15px;
        }

        .upload-area h4 {
            margin-bottom: 10px;
            color: var(--primary);
        }

        .upload-area p {
            color: var(--text-light);
            margin-bottom: 15px;
        }

        .upload-btn-browse {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }

        .upload-btn-browse:hover {
            background: var(--primary-dark);
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
            font-family: 'Poppins', sans-serif;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .preview-container {
            margin-top: 20px;
            text-align: center;
        }

        .preview-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            display: none;
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

        /* Image View Modal */
        .image-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 1100;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .image-modal-content {
            max-width: 90%;
            max-height: 90%;
            position: relative;
        }

        .image-modal-img {
            max-width: 100%;
            max-height: 90vh;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .image-modal-close {
            position: absolute;
            top: -40px;
            right: 0;
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
        }

        .image-modal-info {
            position: absolute;
            bottom: -60px;
            left: 0;
            right: 0;
            text-align: center;
            color: white;
        }

        .image-modal-title {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .image-modal-description {
            font-size: 0.9rem;
            opacity: 0.8;
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

        /* Responsive Design */
        @media (max-width: 1200px) {
            .gallery-grid {
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
            
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .gallery-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .gallery-filters {
                width: 100%;
                justify-content: center;
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
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .notification-user-container {
                align-self: flex-end;
                margin-left: auto;
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
            
            .gallery-filters {
                flex-direction: column;
            }
            
            .filter-btn {
                width: 100%;
            }
            
            .gallery-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                padding: 20px 15px;
            }
            
            .upload-area {
                padding: 30px 15px;
            }
            
            .upload-area i {
                font-size: 2.5rem;
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
            
            .gallery-item-title {
                font-size: 0.95rem;
            }
            
            .gallery-item-meta {
                flex-direction: column;
                gap: 5px;
            }
            
            .modal-header h3 {
                font-size: 1.1rem;
            }
            
            .upload-btn, .filter-btn {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
        }

        /* SweetAlert2 Custom Styling to Match Dashboard */
        .swal2-popup {
            font-family: 'Poppins', sans-serif !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
            padding: 2rem !important;
        }

        .swal2-title {
            font-family: 'Poppins', sans-serif !important;
            font-weight: 600 !important;
            font-size: 1.5rem !important;
            color: var(--text) !important;
            margin-bottom: 1rem !important;
        }

        .swal2-html-container {
            font-family: 'Poppins', sans-serif !important;
            color: var(--text) !important;
            font-size: 1rem !important;
            line-height: 1.6 !important;
        }

        .swal2-html-container strong {
            color: var(--primary) !important;
            font-weight: 600 !important;
        }

        .swal2-actions {
            margin-top: 1.5rem !important;
            gap: 10px !important;
        }

        .swal2-confirm {
            background-color: var(--danger) !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 12px 24px !important;
            font-family: 'Poppins', sans-serif !important;
            font-weight: 500 !important;
            font-size: 0.95rem !important;
            transition: var(--transition) !important;
            box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3) !important;
        }

        .swal2-confirm:hover {
            background-color: #d32f2f !important;
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4) !important;
            transform: translateY(-1px) !important;
        }

        .swal2-cancel {
            background-color: var(--gray-dark) !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 12px 24px !important;
            font-family: 'Poppins', sans-serif !important;
            font-weight: 500 !important;
            font-size: 0.95rem !important;
            color: var(--text) !important;
            transition: var(--transition) !important;
        }

        .swal2-cancel:hover {
            background-color: #d0d0d0 !important;
            transform: translateY(-1px) !important;
        }

        .swal2-loader {
            border-color: var(--danger) transparent var(--danger) transparent !important;
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
                <div class="admin-avatar"><?php echo htmlspecialchars($user_initials); ?></div>
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
                    <a href="admin-gallery.php" class="active">
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
                    <a href="admin-logout.php" onclick="return confirmLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <div class="header">
                <h2>Gallery Management</h2>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchGallery" placeholder="Search images...">
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
            
            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card total reveal">
                    <i class="fas fa-images"></i>
                    <div class="stat-value" id="totalCount">0</div>
                    <div class="stat-label">ALL</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> <span id="totalChange">0 new this week</span>
                    </div>
                </div>
                
                <div class="stat-card food reveal reveal-delay-1">
                    <i class="fas fa-utensils"></i>
                    <div class="stat-value" id="foodCount">0</div>
                    <div class="stat-label">Food</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> <span id="foodChange">0 new this week</span>
                    </div>
                </div>
                
                <div class="stat-card restaurant reveal reveal-delay-2">
                    <i class="fas fa-store"></i>
                    <div class="stat-value" id="eventsCount">0</div>
                    <div class="stat-label">EVENTS</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> <span id="eventsChange">0 new this week</span>
                    </div>
                </div>
                
                <div class="stat-card events reveal reveal-delay-3">
                    <i class="fas fa-calendar"></i>
                    <div class="stat-value" id="videosCount">0</div>
                    <div class="stat-label">VIDEOS</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> <span id="videosChange">0 new this week</span>
                    </div>
                </div>
            </div>
            
            <!-- Gallery Controls -->
            <div class="gallery-controls">
                <div class="gallery-filters">
                    <button class="filter-btn active" data-filter="all">All Images</button>
                    <button class="filter-btn" data-filter="food">Food</button>
                    <button class="filter-btn" data-filter="events">Events</button>
                    <button class="filter-btn" data-filter="staff">Videos</button>
                    <button class="filter-btn" data-filter="drinks">Drinks</button>
                </div>
                <button class="upload-btn" id="uploadImageBtn">
                    <i class="fas fa-plus"></i>
                    Upload Media
                </button>
            </div>
            
            <!-- Gallery Grid -->
            <div class="gallery-grid" id="galleryGrid">
                <!-- Gallery items will be dynamically added here -->
            </div>
            
            <div class="footer">
                <p>&copy; 2025 Joseph's Pot Admin Dashboard. All rights reserved | Developed by ERIBS Tech</p>
            </div>
        </div>
    </div>

    <!-- Upload Image Modal -->
    <div class="modal" id="uploadModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Upload New Media</h3>
                <button class="close-modal" id="closeUploadModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="upload-area" id="uploadArea">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <h4>Upload Your Media</h4>
                    <p>Drag & drop your image or video here or click to browse</p>
                    <button class="upload-btn-browse">Browse Files</button>
                    <input type="file" id="fileInput" accept="image/*,video/*" style="display: none;">
                </div>
                
                <div class="preview-container">
                    <img id="imagePreview" class="preview-image" alt="Media Preview">
                </div>
                
                <form id="imageForm">
                    <div class="form-group">
                        <label for="imageTitle">Media Title *</label>
                        <input type="text" id="imageTitle" placeholder="Enter media title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="imageDescription">Description</label>
                        <textarea id="imageDescription" placeholder="Enter media description"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="imageCategory">Category *</label>
                        <select id="imageCategory" required>
                            <option value="">Select Category</option>
                            <option value="food">Food</option>
                            <option value="event">Events</option>
                            <option value="videos">Videos</option>
                            <option value="drinks">Drinks</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="imageTags">Tags</label>
                        <input type="text" id="imageTags" placeholder="Enter tags separated by commas">
                    </div>
                </form>
                
                <div class="modal-actions">
                    <button class="btn btn-secondary" id="cancelUploadBtn">Cancel</button>
                    <button class="btn btn-primary" id="saveImageBtn">
                        <i class="fas fa-save"></i>
                        Save Media
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image View Modal -->
    <div class="image-modal" id="imageViewModal">
        <div class="image-modal-content">
            <div id="mediaContainer">
                <img class="image-modal-img" id="modalImageView" alt="Full Size Media">
            </div>
            <button class="image-modal-close" id="closeImageViewModal">&times;</button>
            <div class="image-modal-info">
                <div class="image-modal-title" id="modalImageTitle">Media Title</div>
                <div class="image-modal-description" id="modalImageDescription">Media description goes here</div>
            </div>
        </div>
    </div>

    <script>
    // Verify SweetAlert2 is loaded
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 is not loaded!');
    }

    // Logout confirmation function
    function confirmLogout() {
        return confirm('Are you sure you want to logout?');
    }

    /**
     * Reusable delete confirmation function using SweetAlert2
     * @param {Object} options - Configuration object
     * @param {string} options.title - Modal title (default: "Delete image?")
     * @param {string} options.name - Item name to display
     * @param {Function} options.onConfirm - Callback function to execute on confirmation
     */
    async function confirmDelete({ title = 'Delete image?', name, onConfirm }) {
        if (!name) {
            console.error('Item name is required for delete confirmation');
            return;
        }

        if (!onConfirm || typeof onConfirm !== 'function') {
            console.error('onConfirm callback is required');
            return;
        }

        const result = await Swal.fire({
            title: title,
            html: `
                <p style="margin-bottom: 0.5rem;">Are you sure you want to delete</p>
                <p style="margin-top: 0;"><strong>"${name}"</strong>?</p>
                <p style="margin-top: 1rem; color: #666; font-size: 0.9rem;">This action cannot be undone.</p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#F44336',
            cancelButtonColor: '#e0e0e0',
            reverseButtons: true,
            allowOutsideClick: false,
            allowEscapeKey: true,
            customClass: {
                popup: 'swal2-popup',
                title: 'swal2-title',
                htmlContainer: 'swal2-html-container',
                confirmButton: 'swal2-confirm',
                cancelButton: 'swal2-cancel'
            },
            buttonsStyling: true,
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                try {
                    await onConfirm();
                } catch (error) {
                    Swal.showValidationMessage(`Error: ${error.message}`);
                    return false;
                }
            }
        });

        return result;
    }

    // Global variables
    let galleryItems = [];
    let currentFilter = 'all';

    // Toast helper
    function showToast(message, type = 'success') {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        const icon = type === 'success' ? '✅' : '⚠️';
        toast.innerHTML = `
            <span class="icon">${icon}</span>
            <div class="text">${message}</div>
        `;

        container.appendChild(toast);

        // Remove after animation
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3400);
    }

    // DOM Elements
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const galleryGrid = document.getElementById('galleryGrid');
    const uploadModal = document.getElementById('uploadModal');
    const imageViewModal = document.getElementById('imageViewModal');
    const closeUploadModal = document.getElementById('closeUploadModal');
    const closeImageViewModal = document.getElementById('closeImageViewModal');
    const uploadImageBtn = document.getElementById('uploadImageBtn');
    const cancelUploadBtn = document.getElementById('cancelUploadBtn');
    const saveImageBtn = document.getElementById('saveImageBtn');
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const imagePreview = document.getElementById('imagePreview');
    const imageForm = document.getElementById('imageForm');
    const modalImageView = document.getElementById('modalImageView');
    const modalImageTitle = document.getElementById('modalImageTitle');
    const modalImageDescription = document.getElementById('modalImageDescription');
    const mediaContainer = document.getElementById('mediaContainer');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const searchBox = document.getElementById('searchGallery');
    
    // Stats elements
    const totalCount = document.getElementById('totalCount');
    const foodCount = document.getElementById('foodCount');
    const eventsCount = document.getElementById('eventsCount');
    const videosCount = document.getElementById('videosCount');
    const totalChange = document.getElementById('totalChange');
    const foodChange = document.getElementById('foodChange');
    const eventsChange = document.getElementById('eventsChange');
    const videosChange = document.getElementById('videosChange');

    // Load gallery items from database
    async function loadGalleryItems() {
        try {
            console.log('Loading gallery items...');
            const response = await fetch('get-gallery.php');
            const result = await response.json();
            
            if (result.success) {
                galleryItems = result.data;
                console.log('Loaded gallery items:', galleryItems);
                console.log('Categories found:', [...new Set(galleryItems.map(item => item.category))]);
                updateStats();
                filterGallery(currentFilter);
            } else {
                console.error('Error loading gallery:', result.message);
                alert('Error loading gallery: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error loading gallery items. Please check console for details.');
        }
    }

    // Update stats cards
    function updateStats() {
        const total = galleryItems.length;
        const food = galleryItems.filter(item => item.category === 'food').length;
        const events = galleryItems.filter(item => item.category === 'event').length;
        const videos = galleryItems.filter(item => item.category === 'videos').length;
        const drinks = galleryItems.filter(item => item.category === 'drinks').length;
        
        // Update stats cards
        totalCount.textContent = total;
        foodCount.textContent = food;
        eventsCount.textContent = events;
        videosCount.textContent = videos + drinks; // Combine videos and drinks for now
        
        // Calculate changes (this is simulated - you can implement real logic)
        const today = new Date();
        const oneWeekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
        
        const newThisWeek = galleryItems.filter(item => {
            const uploadDate = new Date(item.upload_date);
            return uploadDate >= oneWeekAgo;
        }).length;
        
        const newFood = galleryItems.filter(item => {
            const uploadDate = new Date(item.upload_date);
            return item.category === 'food' && uploadDate >= oneWeekAgo;
        }).length;
        
        const newEvents = galleryItems.filter(item => {
            const uploadDate = new Date(item.upload_date);
            return item.category === 'event' && uploadDate >= oneWeekAgo;
        }).length;
        
        const newVideos = galleryItems.filter(item => {
            const uploadDate = new Date(item.upload_date);
            return (item.category === 'videos' || item.category === 'drinks') && uploadDate >= oneWeekAgo;
        }).length;
        
        totalChange.textContent = newThisWeek + ' new this week';
        foodChange.textContent = newFood + ' new this week';
        eventsChange.textContent = newEvents + ' new this week';
        videosChange.textContent = newVideos + ' new this week';
        
        console.log('Stats updated:', {total, food, events, videos, drinks});
    }

    // Filter gallery items
    function filterGallery(filter) {
        currentFilter = filter;
        let filteredItems = galleryItems;
        
        console.log(`Filtering by: ${filter}`);
        
        if (filter !== 'all') {
            // Map filter values to match database categories
            const filterMap = {
                'food': 'food',
                'events': 'event',
                'staff': 'videos',
                'drinks': 'drinks'
            };
            const dbCategory = filterMap[filter] || filter;
            console.log(`Mapped ${filter} to database category: ${dbCategory}`);
            
            filteredItems = galleryItems.filter(item => {
                const matches = item.category === dbCategory;
                if (matches) {
                    console.log(`Item matched: ${item.title} (${item.category})`);
                }
                return matches;
            });
        }
        
        console.log(`Found ${filteredItems.length} items for filter ${filter}`);
        renderGallery(filteredItems);
    }

    // Render gallery items
    function renderGallery(itemsToRender) {
        galleryGrid.innerHTML = '';
        
        if (itemsToRender.length === 0) {
            galleryGrid.innerHTML = `
                <div class="no-items" style="grid-column: 1/-1; text-align: center; padding: 40px;">
                    <i class="fas fa-image" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                    <h3 style="color: #666; margin-bottom: 10px;">No gallery items found</h3>
                    <p style="color: #999;">Upload your first image or video using the "Upload Media" button</p>
                </div>
            `;
            return;
        }
        
        itemsToRender.forEach(item => {
            const galleryItem = document.createElement('div');
            galleryItem.className = 'gallery-item reveal';
            galleryItem.setAttribute('data-id', item.id);
            
            // Format date
            const uploadDate = new Date(item.upload_date);
            const formattedDate = uploadDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            
            // Category class and label
            let categoryClass = '';
            let categoryLabel = '';
            
            // Use the actual category from database
            console.log(`Rendering item: ${item.title} with category: ${item.category}`);
            
            if (item.category === 'food') {
                categoryClass = 'category-food';
                categoryLabel = 'Food';
            } else if (item.category === 'event') {
                categoryClass = 'category-events';
                categoryLabel = 'Event';
            } else if (item.category === 'videos') {
                categoryClass = 'category-staff';
                categoryLabel = 'Video';
            } else if (item.category === 'drinks') {
                categoryClass = 'category-restaurant';
                categoryLabel = 'Drinks';
            } else {
                // Default fallback
                categoryClass = 'category-food';
                categoryLabel = item.category ? item.category.charAt(0).toUpperCase() + item.category.slice(1) : 'Unknown';
                console.warn('Unknown category for item:', item);
            }
            
            // File size calculation (simulated)
            const fileSize = item.file_type === 'image' ? '2.5 MB' : '15.2 MB';
            
            // Check if it's a video
            const isVideo = item.file_type === 'video';
            
            galleryItem.innerHTML = `
                ${isVideo ? 
                    `<div class="video-thumbnail" style="width:100%;height:200px;background:#000;position:relative;overflow:hidden;">
                        <video style="width:100%;height:100%;object-fit:cover;" src="${item.file_url}" muted playsinline></video>
                        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(0,0,0,0.7);border-radius:50%;width:60px;height:60px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-play" style="color:white;font-size:24px;margin-left:5px;"></i>
                        </div>
                        <div style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,0.7);color:white;padding:5px 10px;border-radius:5px;font-size:12px;">
                            <i class="fas fa-play-circle"></i> VIDEO
                        </div>
                    </div>` : 
                    `<img src="${item.file_url}" alt="${item.title}" class="gallery-item-img" onerror="this.src='../images/placeholder.jpg'">`
                }
                <div class="gallery-item-overlay">
                    <div class="gallery-item-actions">
                        <button class="gallery-action-btn view" data-id="${item.id}" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="gallery-action-btn edit" data-id="${item.id}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="gallery-action-btn delete" data-id="${item.id}" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="gallery-item-info">
                    <span class="gallery-item-category ${categoryClass}">${categoryLabel}</span>
                    <div class="gallery-item-title">${item.title}</div>
                    <div class="gallery-item-meta">
                        <span><i class="far fa-calendar"></i> ${formattedDate}</span>
                        <span><i class="far fa-file"></i> ${fileSize}</span>
                    </div>
                </div>
            `;
            
            galleryGrid.appendChild(galleryItem);
        });
        
        // Use event delegation for action buttons
        galleryGrid.addEventListener('click', function(e) {
            const target = e.target;
            
            // Check if click is on a button or icon inside button
            const button = target.closest('.gallery-action-btn');
            if (!button) return;
            
            const itemId = parseInt(button.getAttribute('data-id'));
            console.log('Button clicked:', button.className, 'Item ID:', itemId);
            
            if (button.classList.contains('view')) {
                viewImage(itemId);
            } else if (button.classList.contains('edit')) {
                editImage(itemId);
            } else if (button.classList.contains('delete')) {
                deleteImage(itemId);
            }
        });
        
        // Initialize reveal animations for new items
        setTimeout(() => {
            const reveals = galleryGrid.querySelectorAll('.reveal');
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

    // View image/video in modal
    function viewImage(itemId) {
        console.log('Viewing item ID:', itemId);
        
        const item = galleryItems.find(i => i.id == itemId);
        
        if (!item) {
            console.error('Item not found with ID:', itemId);
            console.log('Available items:', galleryItems);
            alert('Item not found!');
            return;
        }
        
        // Clear previous content
        mediaContainer.innerHTML = '';
        
        if (item.file_type === 'video') {
            // Create video element
            const videoElement = document.createElement('video');
            videoElement.src = item.file_url;
            videoElement.controls = true;
            videoElement.autoplay = false;
            videoElement.style.maxWidth = '100%';
            videoElement.style.maxHeight = '90vh';
            videoElement.style.borderRadius = '8px';
            videoElement.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.3)';
            videoElement.id = 'modalVideo';
            videoElement.className = 'image-modal-img';
            
            mediaContainer.appendChild(videoElement);
        } else {
            // Create image element
            const imgElement = document.createElement('img');
            imgElement.src = item.file_url;
            imgElement.alt = item.title;
            imgElement.style.maxWidth = '100%';
            imgElement.style.maxHeight = '90vh';
            imgElement.style.borderRadius = '8px';
            imgElement.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.3)';
            imgElement.id = 'modalImageView';
            imgElement.className = 'image-modal-img';
            imgElement.onerror = function() {
                this.src = '../images/placeholder.jpg';
            };
            
            mediaContainer.appendChild(imgElement);
        }
        
        modalImageTitle.textContent = item.title;
        modalImageDescription.textContent = item.description || 'No description provided';
        
        imageViewModal.style.display = 'flex';
    }

    // Edit image
    async function editImage(itemId) {
        console.log('Editing item ID:', itemId);
        
        const item = galleryItems.find(i => i.id == itemId);
        
        if (!item) {
            console.error('Item not found with ID:', itemId);
            alert('Item not found!');
            return;
        }
        
        // Create edit form
        const editForm = `
            <div class="modal" id="editModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Edit Gallery Item</h3>
                        <button class="close-modal" id="closeEditModal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="editForm">
                            <div class="form-group">
                                <label for="editTitle">Title *</label>
                                <input type="text" id="editTitle" value="${item.title.replace(/"/g, '&quot;').replace(/'/g, '&#39;')}" required>
                            </div>
                            <div class="form-group">
                                <label for="editDescription">Description</label>
                                <textarea id="editDescription">${item.description ? item.description.replace(/"/g, '&quot;').replace(/'/g, '&#39;') : ''}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="editCategory">Category *</label>
                                <select id="editCategory" required>
                                    <option value="food" ${item.category === 'food' ? 'selected' : ''}>Food</option>
                                    <option value="event" ${item.category === 'event' ? 'selected' : ''}>Event</option>
                                    <option value="videos" ${item.category === 'videos' ? 'selected' : ''}>Video</option>
                                    <option value="drinks" ${item.category === 'drinks' ? 'selected' : ''}>Drinks</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Current File</label>
                                <div style="padding: 10px; background: #f5f5f5; border-radius: 5px;">
                                    ${item.file_type === 'video' ? 
                                        `<i class="fas fa-video"></i> Video file: ${item.file_path.split('/').pop()}` : 
                                        `<i class="fas fa-image"></i> Image file: ${item.file_path.split('/').pop()}`
                                    }
                                </div>
                                <small style="color: #666;">To change the file, delete this item and upload a new one.</small>
                            </div>
                            <div class="modal-actions">
                                <button type="button" class="btn btn-secondary" id="cancelEditBtn">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('editModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to DOM
        document.body.insertAdjacentHTML('beforeend', editForm);
        const editModal = document.getElementById('editModal');
        editModal.style.display = 'flex';
        
        // Close modal handlers
        document.getElementById('closeEditModal').addEventListener('click', function() {
            editModal.style.display = 'none';
            editModal.remove();
        });
        
        document.getElementById('cancelEditBtn').addEventListener('click', function() {
            editModal.style.display = 'none';
            editModal.remove();
        });
        
        // Close modal when clicking outside
        editModal.addEventListener('click', function(e) {
            if (e.target === editModal) {
                editModal.style.display = 'none';
                editModal.remove();
            }
        });
        
        // Handle form submission
        document.getElementById('editForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const title = document.getElementById('editTitle').value;
            const description = document.getElementById('editDescription').value;
            const category = document.getElementById('editCategory').value;
            
            if (!title || !category) {
                alert('Please fill in all required fields');
                return;
            }
            
            const formData = new FormData();
            formData.append('id', itemId);
            formData.append('title', title);
            formData.append('description', description);
            formData.append('category', category);
            
            try {
                const response = await fetch('update-gallery.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Gallery item updated successfully!');
                    editModal.style.display = 'none';
                    editModal.remove();
                    loadGalleryItems(); // Reload gallery items
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error updating item: ' + error.message);
            }
        });
    }

    // Delete image
    async function deleteImage(itemId) {
        const item = galleryItems.find(i => i.id == itemId);
        
        if (!item) {
            await Swal.fire({
                title: 'Error',
                text: 'Item not found!',
                icon: 'error',
                confirmButtonColor: '#F44336',
                customClass: {
                    popup: 'swal2-popup',
                    title: 'swal2-title',
                    confirmButton: 'swal2-confirm'
                }
            });
            return;
        }
        
        const result = await confirmDelete({
            title: 'Delete image?',
            name: item.title,
            onConfirm: async () => {
                const formData = new FormData();
                formData.append('id', itemId);
                
                const response = await fetch('delete-gallery.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error(result.message || 'Delete failed');
                }
                
                // Success - reload gallery items
                loadGalleryItems();
                
                // Show success toast
                showToast('Gallery item deleted successfully!', 'success');
            }
        });
        
        // If user cancelled, do nothing (modal already closed)
        // If confirmed, success is handled in onConfirm
        // If error occurred, SweetAlert shows it via showValidationMessage
    }

    // Upload area click handler
    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    // File input change handler
    fileInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
                
                // Update UI to indicate file type
                if (file.type.startsWith('video/')) {
                    imagePreview.alt = 'Video preview';
                    // You could also show a video icon or message
                }
            }
            
            reader.readAsDataURL(file);
        }
    });

    // Save image handler
    saveImageBtn.addEventListener('click', async function() {
        if (!fileInput.files[0]) {
            alert('Please select an image or video to upload');
            return;
        }
        
        const title = document.getElementById('imageTitle').value;
        const description = document.getElementById('imageDescription').value;
        const category = document.getElementById('imageCategory').value;
        
        console.log('Uploading with category:', category);
        
        if (!title || !category) {
            alert('Please fill in all required fields');
            return;
        }
        
        // Create FormData object
        const formData = new FormData();
        formData.append('title', title);
        formData.append('description', description);
        formData.append('category', category);
        formData.append('file', fileInput.files[0]);
        
        try {
            const response = await fetch('create-gallery.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            console.log('Upload response:', result);
            
            if (result.success) {
                // Reset form and close modal
                imageForm.reset();
                imagePreview.style.display = 'none';
                uploadModal.style.display = 'none';
                
                alert('Gallery item uploaded successfully!');
                loadGalleryItems(); // Reload gallery items
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Error uploading file: ' + error.message);
        }
    });

    // Open upload modal
    uploadImageBtn.addEventListener('click', function() {
        uploadModal.style.display = 'flex';
    });

    // Close modals
    closeUploadModal.addEventListener('click', function() {
        uploadModal.style.display = 'none';
    });

    closeImageViewModal.addEventListener('click', function() {
        imageViewModal.style.display = 'none';
        // Stop any playing videos
        const video = document.getElementById('modalVideo');
        if (video) {
            video.pause();
            video.currentTime = 0;
        }
    });

    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === uploadModal) {
            uploadModal.style.display = 'none';
        }
        if (event.target === imageViewModal) {
            imageViewModal.style.display = 'none';
            // Stop any playing videos
            const video = document.getElementById('modalVideo');
            if (video) {
                video.pause();
                video.currentTime = 0;
            }
        }
    });

    // Cancel upload
    cancelUploadBtn.addEventListener('click', function() {
        uploadModal.style.display = 'none';
        imageForm.reset();
        imagePreview.style.display = 'none';
    });

    // Filter buttons event listeners
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filter = this.getAttribute('data-filter');
            console.log('Filter button clicked:', filter);
            filterGallery(filter);
        });
    });

    // Search functionality
    searchBox.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        console.log('Searching for:', searchTerm);
        const filteredItems = galleryItems.filter(item => 
            item.title.toLowerCase().includes(searchTerm) ||
            (item.description && item.description.toLowerCase().includes(searchTerm))
        );
        console.log('Found items:', filteredItems.length);
        renderGallery(filteredItems);
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

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Admin Gallery Page Loaded');
        
        // Load gallery items
        loadGalleryItems();
        
        // Initialize scroll reveal
        window.addEventListener('scroll', revealOnScroll);
        // Trigger once on load to check initial position
        revealOnScroll();
    });
</script>
</body>
</html>