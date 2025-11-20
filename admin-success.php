<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit;
}

// Debug: Check if change-password.php exists
$change_pass_file = 'change-password.php';
$file_exists = file_exists($change_pass_file);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Successful</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(135deg, #654321 0%, #8b4513 50%, #a0522d 100%);
            color: #ffe4b5; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .success-box { 
            background: rgba(255,255,255,0.1); 
            padding: 40px; 
            border-radius: 10px; 
            text-align: center; 
            backdrop-filter: blur(10px);
            max-width: 500px;
            width: 90%;
        }
        .menu-links {
            margin-top: 20px;
        }
        .menu-links a {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            background: #d2691e;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .menu-links a:hover {
            background: #a0522d;
        }
        .debug-info {
            background: rgba(0,0,0,0.3);
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="success-box">
        <h1>✅ Login Successful!</h1>
        <p>Welcome, <?php echo $_SESSION['admin_username']; ?>!</p>
        <p>You are now logged in to the admin panel.</p>
        
        <div class="menu-links">
            <a href="change-password.php" id="changePassLink">Change Password</a>
            <a href="admin-login.php?logout=1">Logout</a>
        </div>

        <!-- Debug Information -->
        <div class="debug-info">
            <strong>Debug Info:</strong><br>
            Change Password File: <?php echo $change_pass_file; ?><br>
            File Exists: <?php echo $file_exists ? 'YES' : 'NO'; ?><br>
            Current Directory: <?php echo __DIR__; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const changePassLink = document.getElementById('changePassLink');
            
            changePassLink.addEventListener('click', function(e) {
                console.log('Change Password clicked');
                // Let the link work normally
            });
        });
    </script>
</body>
</html>