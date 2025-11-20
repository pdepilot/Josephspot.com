<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit;
}
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
    </style>
</head>
<body>
    <div class="success-box">
        <h1>✅ Login Successful!</h1>
        <p>Welcome, <?php echo $_SESSION['admin_username']; ?>!</p>
        <p>You are now logged in to the admin panel.</p>
        
        <div class="menu-links">
            <a href="change-password.php">Change Password</a>
            <a href="admin-login.php?logout=1">Logout</a>
        </div>
    </div>
</body>
</html>