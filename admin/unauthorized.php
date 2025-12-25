<?php
session_start();

// Check if user is logged in
$is_logged_in = isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Joseph's Pot Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .unauthorized-container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
        }

        .unauthorized-icon {
            font-size: 120px;
            color: #f44336;
            margin-bottom: 30px;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .error-code {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 30px;
            font-weight: 500;
        }

        p {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #8b4513;
            color: white;
        }

        .btn-primary:hover {
            background: #654321;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 69, 19, 0.3);
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
            transform: translateY(-2px);
        }

        .permission-info {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-top: 30px;
            text-align: left;
        }

        .permission-info h3 {
            font-size: 1rem;
            color: #856404;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .permission-info p {
            font-size: 0.9rem;
            color: #856404;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="unauthorized-container">
        <div class="unauthorized-icon">
            <i class="fas fa-lock"></i>
        </div>
        <h1>Access Denied</h1>
        <div class="error-code">Error 403 - Forbidden</div>
        <p>
            You don't have permission to access this page. 
            <?php if ($is_logged_in): ?>
                Please contact your administrator if you believe this is an error.
            <?php else: ?>
                Please log in with an account that has the required permissions.
            <?php endif; ?>
        </p>
        
        <div class="action-buttons">
            <?php if ($is_logged_in): ?>
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Go to Dashboard
                </a>
                <a href="admin-logout.php" class="btn btn-secondary">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            <?php else: ?>
                <a href="admin-login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a>
            <?php endif; ?>
        </div>

        <?php if ($is_logged_in && isset($_SERVER['HTTP_REFERER'])): ?>
        <div class="permission-info">
            <h3>
                <i class="fas fa-info-circle"></i>
                Need Help?
            </h3>
            <p>
                If you need access to this page, please contact a Super Admin to grant you the required permissions.
            </p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

