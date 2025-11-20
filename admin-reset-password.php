<?php
session_start();
require 'includes/db.php';

$error = '';
$success = false;

// Validate query parameters
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

if (!$email || !$token) {
    die('Invalid password reset link');
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request. Please refresh and try again.";
    } else {
        $password = trim($_POST['password'] ?? '');
        $confirm = trim($_POST['confirm_password'] ?? '');

        if (!$password || !$confirm) {
            $error = "All fields are required";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match";
        } else {
            // Verify token
            $stmt = $conn->prepare("SELECT id, reset_token, reset_expiry FROM admins WHERE email=? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();

                if (password_verify($token, $admin['reset_token']) && strtotime($admin['reset_expiry']) > time()) {
                    // Update password and clear token
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE admins SET password=?, reset_token=NULL, reset_expiry=NULL WHERE id=?");
                    $stmt->bind_param("si", $hashed, $admin['id']);
                    $stmt->execute();
                    $success = true;
                } else {
                    $error = "Invalid or expired reset link";
                }
            } else {
                $error = "Invalid reset request";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password - Joseph's Pot</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Exo+2:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Exo 2', sans-serif;
            background: linear-gradient(135deg, #654321, #8b4513, #a0522d);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            color: #fff;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            padding: 30px;
            border-radius: 20px;
            width: 360px;
            box-shadow: 0 0 50px rgba(210, 105, 30, 0.3);
            text-align: center;
        }

        .form-container h2 {
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 20px;
            font-size: 2rem;
            background: linear-gradient(45deg, #ffe4b5, #d2691e);
            -webkit-background-clip: text;
            color: transparent;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid rgba(255, 228, 181, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: #ffe4b5;
            font-size: 1rem;
        }

        input::placeholder {
            color: rgba(255, 228, 181, 0.6);
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(45deg, #a0522d, #d2691e);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            background: linear-gradient(45deg, #d2691e, #a0522d);
        }

        .error {
            color: #ff6961;
            margin-bottom: 10px;
        }

        .success {
            color: #7fff00;
            margin-bottom: 10px;
        }

        a {
            color: #ffe4b5;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h2>Reset Password</h2>
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success">Password reset successful! <a href="login.php">Login now</a></div>
        <?php else: ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="password" name="password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>