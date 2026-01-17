<?php
// admin-profile.php
// Central authentication and permission check
require_once 'admin-auth.php';

// Profile page should be accessible to all logged-in admins
// But still verify authentication
requireAuth(); // Ensures user is logged in and active

// Note: Profile page doesn't need module permission check
// as it's accessible to all authenticated admins
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Joseph's Pot</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .profile-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h1 {
            color: #8b4513;
            border-bottom: 2px solid #8b4513;
            padding-bottom: 10px;
        }
        .profile-info {
            margin-top: 30px;
        }
        .info-item {
            margin-bottom: 15px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .info-item label {
            font-weight: bold;
            color: #8b4513;
            display: inline-block;
            width: 150px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #8b4513;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-link:hover {
            background: #654321;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h1><i class="fas fa-user"></i> Admin Profile</h1>
        <div class="profile-info">
            <div class="info-item">
                <label>Name:</label>
                <span>Admin Joseph</span>
            </div>
            <div class="info-item">
                <label>Email:</label>
                <span>admin@josephspot.com</span>
            </div>
            <div class="info-item">
                <label>Role:</label>
                <span>Super Admin</span>
            </div>
            <div class="info-item">
                <label>Last Login:</label>
                <span><?php echo date('Y-m-d H:i:s'); ?></span>
            </div>
        </div>
        <a href="admin/admin-order-online.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</body>
</html>