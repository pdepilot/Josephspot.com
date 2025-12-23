<?php
session_start();
header('Content-Type: application/json');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'joseph_pot_admin');

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

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get action from request
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
$conn = getDBConnection();

// Helper function to map role names to database values for admin_users table
function mapRoleToDB($role) {
    // admin_users table uses: 'super_admin', 'admin', 'moderator'
    $roleMap = [
        'Super Admin' => 'super_admin',
        'Manager' => 'admin',
        'Content Manager' => 'admin',
        'Support' => 'admin'
    ];
    return isset($roleMap[$role]) ? $roleMap[$role] : 'admin';
}

// Helper function to map database role values to display names
function mapRoleFromDB($role) {
    // admin_users table uses: 'super_admin', 'admin', 'moderator'
    $roleMap = [
        'super_admin' => 'Super Admin',
        'admin' => 'Manager',
        'moderator' => 'Support'
    ];
    return isset($roleMap[$role]) ? $roleMap[$role] : 'Manager';
}

try {
    switch ($action) {
        case 'create':
            // Create new admin
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $role = isset($_POST['role']) ? trim($_POST['role']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            // Validate required fields
            if (empty($name) || empty($email) || empty($role)) {
                echo json_encode(['success' => false, 'message' => 'Name, email, and role are required']);
                exit;
            }
            
            // Generate username from name (use email if name is not suitable)
            $username = strtolower(str_replace(' ', '_', $name));
            // Ensure username is unique
            $counter = 1;
            $originalUsername = $username;
            while ($conn->query("SELECT id FROM admin_users WHERE username = '$username'")->num_rows > 0) {
                $username = $originalUsername . $counter;
                $counter++;
            }
            
            // Generate default password if not provided (first part of email + 123)
            if (empty($password)) {
                $emailParts = explode('@', $email);
                $password = $emailParts[0] . '123';
            }
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Map role to database format
            $dbRole = mapRoleToDB($role);
            
            // Insert admin into admin_users table
            $stmt = $conn->prepare("INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
                exit;
            }
            
            $stmt->bind_param("sssss", $username, $email, $password_hash, $name, $dbRole);
            
            if ($stmt->execute()) {
                $adminId = $conn->insert_id;
                echo json_encode([
                    'success' => true,
                    'message' => 'Admin created successfully',
                    'admin' => [
                        'id' => $adminId,
                        'username' => $username,
                        'email' => $email,
                        'role' => $role,
                        'name' => $name
                    ]
                ]);
            } else {
                // Check for duplicate email
                if ($conn->errno == 1062) {
                    echo json_encode(['success' => false, 'message' => 'Email already exists']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error creating admin: ' . $conn->error]);
                }
            }
            $stmt->close();
            break;
            
        case 'update':
            // Update existing admin
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $role = isset($_POST['role']) ? trim($_POST['role']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';
            
            if (empty($id) || empty($name) || empty($email) || empty($role)) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                exit;
            }
            
            // Generate username from name
            $username = strtolower(str_replace(' ', '_', $name));
            
            // Map role to database format
            $dbRole = mapRoleToDB($role);
            
            // Check if email is being changed and if it already exists
            $checkStmt = $conn->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
            $checkStmt->bind_param("si", $email, $id);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
                $checkStmt->close();
                exit;
            }
            $checkStmt->close();
            
            // Update admin (with or without password)
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE admin_users SET username = ?, email = ?, password_hash = ?, full_name = ?, role = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $username, $email, $password_hash, $name, $dbRole, $id);
            } else {
                $stmt = $conn->prepare("UPDATE admin_users SET username = ?, email = ?, full_name = ?, role = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $username, $email, $name, $dbRole, $id);
            }
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Admin updated successfully',
                    'admin' => [
                        'id' => $id,
                        'username' => $username,
                        'email' => $email,
                        'role' => $role,
                        'name' => $name
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating admin: ' . $conn->error]);
            }
            $stmt->close();
            break;
            
        case 'delete':
            // Delete admin
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'Admin ID is required']);
                exit;
            }
            
            // Prevent deleting yourself
            if ($id == $_SESSION['admin_id']) {
                echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
                exit;
            }
            
            $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Admin deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting admin: ' . $conn->error]);
            }
            $stmt->close();
            break;
            
        case 'get':
        case 'list':
            // Get all admins from admin_users table
            $result = $conn->query("SELECT id, username, email, full_name, role, created_at FROM admin_users ORDER BY created_at DESC");
            $admins = [];
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    // Use full_name if available, otherwise generate from username
                    $name = !empty($row['full_name']) ? $row['full_name'] : ucwords(str_replace('_', ' ', $row['username']));
                    $avatar = '';
                    if (!empty($name)) {
                        $nameParts = explode(' ', $name);
                        $avatar = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : substr($nameParts[0], 1, 1)));
                    } else {
                        $avatar = strtoupper(substr($row['email'], 0, 2));
                    }
                    
                    // Map role back to display format
                    $displayRole = mapRoleFromDB($row['role']);
                    
                    $admins[] = [
                        'id' => $row['id'],
                        'name' => $name,
                        'email' => $row['email'],
                        'username' => $row['username'],
                        'role' => $displayRole,
                        'avatar' => $avatar
                    ];
                }
            }
            
            echo json_encode(['success' => true, 'admins' => $admins]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>

