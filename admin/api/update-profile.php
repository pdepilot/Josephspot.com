<?php
session_start();
header('Content-Type: application/json');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'joseph_pot_admin');

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get database connection
function getDBConnection() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die(json_encode(['success' => false, 'message' => 'Database connection failed']));
        }
        $conn->set_charset("utf8mb4");
    }
    return $conn;
}

try {
    $conn = getDBConnection();
    $admin_id = $_SESSION['admin_id'];
    
    // Get form data
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    
    // Validate required fields
    if (empty($username) || empty($email) || empty($current_password)) {
        echo json_encode(['success' => false, 'message' => 'Username, email, and current password are required']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Get current admin data - check admin_users first, then admins table
    $stmt = $conn->prepare("SELECT id, username, email, password_hash as password FROM admin_users WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = null;
    $table_used = 'admin_users';
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
    } else {
        // Fallback to admins table
        $stmt->close();
        $stmt = $conn->prepare("SELECT id, username, email, password FROM admins WHERE id = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit;
        }
        
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $table_used = 'admins';
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
        } else {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Admin not found']);
            exit;
        }
    }
    $stmt->close();
    
    // Verify current password
    if (!password_verify($current_password, $admin['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }
    
    // Check if email is being changed and if it already exists (check both tables)
    if ($email !== $admin['email']) {
        // Check admin_users table
        $checkStmt = $conn->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
        $checkStmt->bind_param("si", $email, $admin_id);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $checkStmt->close();
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }
        $checkStmt->close();
        
        // Check admins table
        $checkStmt = $conn->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
        $checkStmt->bind_param("si", $email, $admin_id);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $checkStmt->close();
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }
        $checkStmt->close();
    }
    
    // Check if username is being changed and if it already exists (check both tables)
    if ($username !== $admin['username']) {
        // Check admin_users table
        $checkStmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
        $checkStmt->bind_param("si", $username, $admin_id);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $checkStmt->close();
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            exit;
        }
        $checkStmt->close();
        
        // Check admins table
        $checkStmt = $conn->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
        $checkStmt->bind_param("si", $username, $admin_id);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $checkStmt->close();
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            exit;
        }
        $checkStmt->close();
    }
    
    // Validate new password if provided
    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters long']);
            exit;
        }
        
        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'New password and confirm password do not match']);
            exit;
        }
        
        // Hash new password
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update with new password (use appropriate table and column names)
        if ($table_used === 'admin_users') {
            $stmt = $conn->prepare("UPDATE admin_users SET username = ?, email = ?, password_hash = ? WHERE id = ?");
        } else {
            $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ?, password = ? WHERE id = ?");
        }
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit;
        }
        
        $stmt->bind_param("sssi", $username, $email, $password_hash, $admin_id);
    } else {
        // Update without changing password
        if ($table_used === 'admin_users') {
            $stmt = $conn->prepare("UPDATE admin_users SET username = ?, email = ? WHERE id = ?");
        } else {
            $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ? WHERE id = ?");
        }
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit;
        }
        
        $stmt->bind_param("ssi", $username, $email, $admin_id);
    }
    
    if ($stmt->execute()) {
        // Update session username if changed
        if ($username !== $admin['username']) {
            $_SESSION['admin_username'] = $username;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'admin' => [
                'id' => $admin_id,
                'username' => $username,
                'email' => $email
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating profile: ' . $conn->error]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>

