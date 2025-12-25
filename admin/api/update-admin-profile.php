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

// Check if current admin is Super Admin
function isSuperAdmin($conn, $admin_id) {
    // Check admin_users table first
    $stmt = $conn->prepare("SELECT role FROM admin_users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            $stmt->close();
            return ($admin['role'] === 'super_admin');
        }
        $stmt->close();
    }
    
    // Fallback to admins table
    $stmt = $conn->prepare("SELECT role FROM admins WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            $stmt->close();
            return ($admin['role'] === 'super_admin');
        }
        $stmt->close();
    }
    
    return false;
}

try {
    $conn = getDBConnection();
    $current_admin_id = $_SESSION['admin_id'];
    $target_admin_id = isset($_POST['admin_id']) ? intval($_POST['admin_id']) : 0;
    
    // Validate target admin ID
    if (empty($target_admin_id)) {
        echo json_encode(['success' => false, 'message' => 'Admin ID is required']);
        exit;
    }
    
    // Check if current admin is Super Admin OR if they're editing their own profile
    $is_own_profile = ($current_admin_id == $target_admin_id);
    $is_super_admin = isSuperAdmin($conn, $current_admin_id);
    
    if (!$is_own_profile && !$is_super_admin) {
        echo json_encode(['success' => false, 'message' => 'Only Super Admins can edit other admin profiles']);
        exit;
    }
    
    // Get form data
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    
    // If editing own profile, require current password
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    if ($is_own_profile && empty($current_password)) {
        echo json_encode(['success' => false, 'message' => 'Current password is required to edit your own profile']);
        exit;
    }
    
    // Validate required fields
    if (empty($username) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Username and email are required']);
        exit;
    }
    
    // If Super Admin editing others, role is optional but validate if provided
    if (!$is_own_profile && !empty($role)) {
        $valid_roles = ['super_admin', 'admin', 'moderator'];
        $roleMap = [
            'Super Admin' => 'super_admin',
            'Manager' => 'admin',
            'Content Manager' => 'admin',
            'Support' => 'moderator'
        ];
        if (isset($roleMap[$role])) {
            $role = $roleMap[$role];
        }
        if (!in_array($role, $valid_roles)) {
            echo json_encode(['success' => false, 'message' => 'Invalid role']);
            exit;
        }
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Get target admin data - check admin_users first, then admins table
    $stmt = $conn->prepare("SELECT id, username, email, password_hash as password, role FROM admin_users WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("i", $target_admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = null;
    $table_used = 'admin_users';
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
    } else {
        // Fallback to admins table
        $stmt->close();
        $stmt = $conn->prepare("SELECT id, username, email, password, role FROM admins WHERE id = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit;
        }
        
        $stmt->bind_param("i", $target_admin_id);
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
    
    // Verify current password if editing own profile
    if ($is_own_profile) {
        if (!password_verify($current_password, $admin['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit;
        }
    }
    
    // Check if email is being changed and if it already exists (check both tables)
    if ($email !== $admin['email']) {
        $checkStmt = $conn->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
        $checkStmt->bind_param("si", $email, $target_admin_id);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $checkStmt->close();
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }
        $checkStmt->close();
        
        $checkStmt = $conn->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
        $checkStmt->bind_param("si", $email, $target_admin_id);
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
        $checkStmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
        $checkStmt->bind_param("si", $username, $target_admin_id);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $checkStmt->close();
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            exit;
        }
        $checkStmt->close();
        
        $checkStmt = $conn->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
        $checkStmt->bind_param("si", $username, $target_admin_id);
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
        
        // Update with new password and optionally role (if Super Admin editing others)
        if ($table_used === 'admin_users') {
            if ($is_super_admin && !$is_own_profile && !empty($role)) {
                $stmt = $conn->prepare("UPDATE admin_users SET username = ?, email = ?, password_hash = ?, role = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $username, $email, $password_hash, $role, $target_admin_id);
            } else {
                $stmt = $conn->prepare("UPDATE admin_users SET username = ?, email = ?, password_hash = ? WHERE id = ?");
                $stmt->bind_param("sssi", $username, $email, $password_hash, $target_admin_id);
            }
        } else {
            if ($is_super_admin && !$is_own_profile && !empty($role)) {
                $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ?, password = ?, role = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $username, $email, $password_hash, $role, $target_admin_id);
            } else {
                $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssi", $username, $email, $password_hash, $target_admin_id);
            }
        }
    } else {
        // Update without changing password, optionally update role if Super Admin editing others
        if ($table_used === 'admin_users') {
            if ($is_super_admin && !$is_own_profile && !empty($role)) {
                $stmt = $conn->prepare("UPDATE admin_users SET username = ?, email = ?, role = ? WHERE id = ?");
                $stmt->bind_param("sssi", $username, $email, $role, $target_admin_id);
            } else {
                $stmt = $conn->prepare("UPDATE admin_users SET username = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $username, $email, $target_admin_id);
            }
        } else {
            if ($is_super_admin && !$is_own_profile && !empty($role)) {
                $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ?, role = ? WHERE id = ?");
                $stmt->bind_param("sssi", $username, $email, $role, $target_admin_id);
            } else {
                $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $username, $email, $target_admin_id);
            }
        }
    }
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    if ($stmt->execute()) {
        // Update session username if editing own profile
        if ($is_own_profile && $username !== $admin['username']) {
            $_SESSION['admin_username'] = $username;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'admin' => [
                'id' => $target_admin_id,
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

