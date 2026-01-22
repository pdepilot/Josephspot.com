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
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if user is Super Admin for admin management operations
$current_role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : '';
$current_admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0;

// Debug logging for role check
error_log("DEBUG manage-admin.php: Current role from session: '" . $current_role . "'");
error_log("DEBUG manage-admin.php: Is Super Admin: " . (isSuperAdmin($current_role) ? 'YES' : 'NO'));

// Get action from request
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

// URGENT DEBUG: Log all incoming POST data for admin creation/update
if ($action === 'create' || $action === 'update') {
    error_log("===========================================");
    error_log("DEBUG api/manage-admin.php: ACTION = $action");
    error_log("DEBUG api/manage-admin.php: All POST data: " . print_r($_POST, true));
    error_log("DEBUG api/manage-admin.php: Role field value: " . (isset($_POST['role']) ? "'{$_POST['role']}'" : 'NOT SET'));
    error_log("===========================================");
}

$conn = getDBConnection();

// Helper function to map role names to database values for admin_users table
// Stores exact role names: 'Super Admin', 'Manager', 'Chef', 'Supervisor', 'Support'
function mapRoleToDB($role) {
    // If already in valid format, return as is
    $valid_roles = ['Super Admin', 'Manager', 'Chef', 'Supervisor', 'Support', 'Admin'];
    if (in_array($role, $valid_roles)) {
        return $role; // Store exact name
    }
    
    // Map form values and legacy values to database format
    $roleMap = [
        // Form values (from dropdown) - exact matches
        'Super Admin' => 'Super Admin',
        'Manager' => 'Manager',
        'Chef' => 'Chef',
        'Supervisor' => 'Supervisor',
        'Support' => 'Support',
        
        // Legacy/normalized values
        'super_admin' => 'Super Admin',
        'manager' => 'Manager',
        'chef' => 'Chef',
        'supervisor' => 'Supervisor',
        'support' => 'Support',
        
        // Legacy mapping for backward compatibility
        'admin' => 'Manager',
        'moderator' => 'Support',
        'content_manager' => 'Chef',  // Map old content_manager to Chef
        'content_editor' => 'Chef'    // Map old content_editor to Chef
    ];
    
    // Normalize the role value (trim and lowercase for comparison)
    $role_normalized = strtolower(trim($role));
    
    // Check normalized first
    if (isset($roleMap[$role_normalized])) {
        return $roleMap[$role_normalized];
    }
    
    // Check original value
    if (isset($roleMap[$role])) {
        return $roleMap[$role];
    }
    
    // Default fallback
    return 'Manager';
}

// Helper function to check if user is Super Admin
function isSuperAdmin($role) {
    if (empty($role)) {
        return false;
    }
    $role_normalized = strtolower(trim($role));
    // Check for both formats: 'super_admin' and 'super admin' (with space)
    return ($role_normalized === 'super_admin' || 
            $role_normalized === 'super admin' || 
            $role === 'Super Admin');
}

// Helper function to map database role values to display names
function mapRoleFromDB($role) {
    // Handle empty or null roles
    if (empty($role) || $role === null || trim($role) === '') {
        return 'Manager'; // Default role
    }
    
    // If already in valid display format, return as is
    $display_roles = ['Super Admin', 'Manager', 'Chef', 'Supervisor', 'Support', 'Admin'];
    if (in_array($role, $display_roles)) {
        return $role;
    }
    
    // Legacy mapping for backward compatibility
    $roleMap = [
        'super_admin' => 'Super Admin',
        'super admin' => 'Super Admin',
        'admin' => 'Manager',
        'moderator' => 'Support',
        'content_manager' => 'Chef',
        'content_editor' => 'Chef',
        'content manager' => 'Chef'
    ];
    
    $role_normalized = strtolower(trim($role));
    return isset($roleMap[$role_normalized]) ? $roleMap[$role_normalized] : $role; // Return original if not found in map
}

try {
    switch ($action) {
        case 'create':
            // Only Super Admin can create admins
            if (!isSuperAdmin($current_role)) {
                echo json_encode(['success' => false, 'message' => 'Only Super Admin can create new admins']);
                exit;
            }
            
            // Create new admin
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $role = isset($_POST['role']) ? trim($_POST['role']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : '{}';
            
            // URGENT DEBUG: Log all received values (already logged in action check, but log again for clarity)
            error_log("DEBUG api/manage-admin.php create: Extracted values - name='$name', email='$email', role='$role', password=" . (empty($password) ? 'EMPTY' : 'SET'));
            
            // Validate required fields
            if (empty($name) || empty($email) || empty($role)) {
                echo json_encode(['success' => false, 'message' => 'Name, email, and role are required']);
                exit;
            }
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                exit;
            }
            
            // Check if username already exists
            $username = strtolower(str_replace(' ', '_', $name));
            $check_username = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
            $check_username->bind_param("s", $username);
            $check_username->execute();
            if ($check_username->get_result()->num_rows > 0) {
                $check_username->close();
                echo json_encode(['success' => false, 'message' => 'Username already exists']);
                exit;
            }
            $check_username->close();
            
            // Check if email already exists
            $check_email = $conn->prepare("SELECT id FROM admin_users WHERE email = ?");
            $check_email->bind_param("s", $email);
            $check_email->execute();
            if ($check_email->get_result()->num_rows > 0) {
                $check_email->close();
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
                exit;
            }
            $check_email->close();
            
            // Generate username from name (use email if name is not suitable)
            $username = strtolower(str_replace(' ', '_', $name));
            // Ensure username is unique
            $counter = 1;
            $originalUsername = $username;
            while ($conn->query("SELECT id FROM admin_users WHERE username = '$username'")->num_rows > 0) {
                $username = $originalUsername . $counter;
                $counter++;
            }
            
            // Validate password
            if (empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Password is required']);
                exit;
            }
            
            // Validate password strength (minimum 6 characters)
            if (strlen($password) < 6) {
                echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
                exit;
            }
            
            // Hash password with password_hash()
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Map role to database format (now stores exact role names)
            $dbRole = mapRoleToDB($role);
            
            // URGENT DEBUG: Log role mapping in detail
            error_log("===========================================");
            error_log("DEBUG api/manage-admin.php create: ROLE MAPPING");
            error_log("  - Original role from POST: '$role'");
            error_log("  - Role length: " . strlen($role));
            error_log("  - Role type check: " . (in_array($role, ['Super Admin', 'Manager', 'Content Manager', 'Support', 'Admin']) ? 'VALID' : 'NOT IN VALID LIST'));
            error_log("  - Mapped role (dbRole): '$dbRole'");
            error_log("  - dbRole length: " . strlen($dbRole));
            error_log("===========================================");
            
            // Validate role is one of the allowed values
            $allowed_roles = ['Super Admin', 'Manager', 'Chef', 'Supervisor', 'Support', 'Admin'];
            if (!in_array($dbRole, $allowed_roles)) {
                error_log("ERROR api/manage-admin.php create: Invalid role '$dbRole' not in allowed list");
                echo json_encode(['success' => false, 'message' => 'Invalid role selected: ' . $dbRole]);
                exit;
            }
            
            // Ensure dbRole is not empty
            if (empty($dbRole)) {
                error_log("ERROR api/manage-admin.php create: dbRole is empty! Original role was: '$role'");
                echo json_encode(['success' => false, 'message' => 'Role cannot be empty. Please select a valid role.']);
                exit;
            }
            
            // Validate and sanitize permissions JSON
            $permissionsJson = '{}';
            if (!empty($permissions)) {
                $decoded = json_decode($permissions, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $permissionsJson = json_encode($decoded);
                }
            }
            
            // Check which columns exist and prepare appropriate INSERT
            $testStmt = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'status'");
            $hasStatus = $testStmt && $testStmt->num_rows > 0;
            $testStmt2 = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'permissions'");
            $hasPermissions = $testStmt2 && $testStmt2->num_rows > 0;
            
            if ($hasStatus && $hasPermissions) {
                $insertSql = "INSERT INTO admin_users (username, email, password_hash, full_name, role, permissions, status) VALUES (?, ?, ?, ?, ?, ?, 'active')";
            } else if ($hasStatus) {
                $insertSql = "INSERT INTO admin_users (username, email, password_hash, full_name, role, status) VALUES (?, ?, ?, ?, ?, 'active')";
            } else {
                // Check if is_active exists
                $testStmt3 = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'is_active'");
                $hasIsActive = $testStmt3 && $testStmt3->num_rows > 0;
                
                if ($hasIsActive && $hasPermissions) {
                    $insertSql = "INSERT INTO admin_users (username, email, password_hash, full_name, role, permissions, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)";
                } else if ($hasIsActive) {
                    $insertSql = "INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active) VALUES (?, ?, ?, ?, ?, 1)";
                } else if ($hasPermissions) {
                    $insertSql = "INSERT INTO admin_users (username, email, password_hash, full_name, role, permissions) VALUES (?, ?, ?, ?, ?, ?)";
                } else {
                    // No status column, just insert without it
                    $insertSql = "INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)";
                }
            }
            
            // URGENT DEBUG: Log the SQL and bind parameters
            error_log("===========================================");
            error_log("DEBUG api/manage-admin.php create: DATABASE INSERT");
            error_log("  - SQL: $insertSql");
            error_log("  - username: '$username'");
            error_log("  - email: '$email'");
            error_log("  - password_hash: [HIDDEN]");
            error_log("  - name: '$name'");
            error_log("  - dbRole: '$dbRole' (length: " . strlen($dbRole) . ")");
            error_log("===========================================");
            
            $stmt = $conn->prepare($insertSql);
            if (!$stmt) {
                error_log("ERROR api/manage-admin.php create: Prepare failed - " . $conn->error);
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
                exit;
            }
            
            // URGENT DEBUG: Log bind parameters before execution
            error_log("DEBUG api/manage-admin.php create: Binding parameters - username='$username', email='$email', name='$name', dbRole='$dbRole'");
            
            // Bind parameters based on SQL structure
            if ($hasStatus && $hasPermissions) {
                $stmt->bind_param("ssssss", $username, $email, $password_hash, $name, $dbRole, $permissionsJson);
            } else if ($hasStatus) {
                $stmt->bind_param("sssss", $username, $email, $password_hash, $name, $dbRole);
            } else {
                $testStmt3 = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'is_active'");
                $hasIsActive = $testStmt3 && $testStmt3->num_rows > 0;
                if ($hasIsActive && $hasPermissions) {
                    $stmt->bind_param("ssssss", $username, $email, $password_hash, $name, $dbRole, $permissionsJson);
                } else if ($hasIsActive) {
                    $stmt->bind_param("sssss", $username, $email, $password_hash, $name, $dbRole);
                } else if ($hasPermissions) {
                    $stmt->bind_param("ssssss", $username, $email, $password_hash, $name, $dbRole, $permissionsJson);
                } else {
                    $stmt->bind_param("sssss", $username, $email, $password_hash, $name, $dbRole);
                }
            }
            
            if ($stmt->execute()) {
                $adminId = $conn->insert_id;
                
                // Verify the role was actually saved
                $verifyStmt = $conn->prepare("SELECT role FROM admin_users WHERE id = ?");
                $verifyStmt->bind_param("i", $adminId);
                $verifyStmt->execute();
                $result = $verifyStmt->get_result();
                $savedAdmin = $result->fetch_assoc();
                $verifyStmt->close();
                
                if ($savedAdmin) {
                    error_log("===========================================");
                    error_log("DEBUG api/manage-admin.php create: POST-INSERT VERIFICATION");
                    error_log("  - Admin ID: $adminId");
                    error_log("  - Expected role (dbRole): '$dbRole'");
                    error_log("  - Saved role (from DB): '{$savedAdmin['role']}'");
                    error_log("  - Match: " . ($savedAdmin['role'] === $dbRole ? 'YES' : 'NO'));
                    if ($savedAdmin['role'] !== $dbRole) {
                        error_log("  - ERROR: ROLE MISMATCH!");
                        error_log("  - Expected length: " . strlen($dbRole));
                        error_log("  - Saved length: " . strlen($savedAdmin['role']));
                        error_log("  - Expected bytes: " . bin2hex($dbRole));
                        error_log("  - Saved bytes: " . bin2hex($savedAdmin['role']));
                    }
                    error_log("===========================================");
                } else {
                    error_log("ERROR api/manage-admin.php create: Could not verify saved admin - query returned no results!");
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Admin created successfully',
                    'admin' => [
                        'id' => $adminId,
                        'username' => $username,
                        'email' => $email,
                        'role' => $dbRole, // Return the mapped role, not the original
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
            // Only Super Admin can update admins
            if (!isSuperAdmin($current_role)) {
                echo json_encode(['success' => false, 'message' => 'Only Super Admin can update admins']);
                exit;
            }
            
            // Update existing admin
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $role = isset($_POST['role']) ? trim($_POST['role']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';
            $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : null;
            
            if (empty($id) || empty($name) || empty($email) || empty($role)) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                exit;
            }
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                exit;
            }
            
            // Generate username from name
            $username = strtolower(str_replace(' ', '_', $name));
            
            // Map role to database format (now stores exact role names)
            $dbRole = mapRoleToDB($role);
            
            // Validate role is one of the allowed values
            $allowed_roles = ['Super Admin', 'Manager', 'Chef', 'Supervisor', 'Support', 'Admin'];
            if (!in_array($dbRole, $allowed_roles)) {
                echo json_encode(['success' => false, 'message' => 'Invalid role selected']);
                exit;
            }
            
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
            
            // Validate and sanitize permissions JSON
            $permissionsJson = null;
            if ($permissions !== null) {
                $decoded = json_decode($permissions, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $permissionsJson = json_encode($decoded);
                }
            }
            
            // Check if permissions column exists
            $testStmt = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'permissions'");
            $hasPermissions = $testStmt && $testStmt->num_rows > 0;
            
            // Update admin (with or without password)
            if (!empty($password)) {
                // Validate password strength
                if (strlen($password) < 6) {
                    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
                    exit;
                }
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                if ($hasPermissions && $permissionsJson !== null) {
                    $stmt = $conn->prepare("UPDATE admin_users SET username = ?, email = ?, password_hash = ?, full_name = ?, role = ?, permissions = ? WHERE id = ?");
                    $stmt->bind_param("ssssssi", $username, $email, $password_hash, $name, $dbRole, $permissionsJson, $id);
                } else {
                    $stmt = $conn->prepare("UPDATE admin_users SET username = ?, email = ?, password_hash = ?, full_name = ?, role = ? WHERE id = ?");
                    $stmt->bind_param("sssssi", $username, $email, $password_hash, $name, $dbRole, $id);
                }
            } else {
                if ($hasPermissions && $permissionsJson !== null) {
                    $stmt = $conn->prepare("UPDATE admin_users SET username = ?, email = ?, full_name = ?, role = ?, permissions = ? WHERE id = ?");
                    $stmt->bind_param("sssssi", $username, $email, $name, $dbRole, $permissionsJson, $id);
                } else {
                    $stmt = $conn->prepare("UPDATE admin_users SET username = ?, email = ?, full_name = ?, role = ? WHERE id = ?");
                    $stmt->bind_param("ssssi", $username, $email, $name, $dbRole, $id);
                }
            }
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Admin updated successfully',
                    'admin' => [
                        'id' => $id,
                        'username' => $username,
                        'email' => $email,
                        'role' => $dbRole, // Return the mapped role, not the original
                        'name' => $name
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating admin: ' . $conn->error]);
            }
            $stmt->close();
            break;
            
        case 'delete':
            // Only Super Admin can delete admins
            if (!isSuperAdmin($current_role)) {
                echo json_encode(['success' => false, 'message' => 'Only Super Admin can delete admins']);
                exit;
            }
            
            // Delete admin
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'Admin ID is required']);
                exit;
            }
            
            // Prevent deleting yourself
            if ($id == $current_admin_id) {
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
            // Only Super Admin can list all admins
            if (!isSuperAdmin($current_role)) {
                echo json_encode(['success' => false, 'message' => 'Only Super Admin can view admin list']);
                exit;
            }
            
            // Get all admins from admin_users table except current admin
            $stmt = $conn->prepare("SELECT id, username, email, full_name, role, permissions, last_login, created_at FROM admin_users WHERE id != ? ORDER BY created_at DESC");
            $stmt->bind_param("i", $current_admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
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
                    
                    // Map role back to display format - ensure it's not empty
                    $rawRole = isset($row['role']) ? $row['role'] : '';
                    $displayRole = mapRoleFromDB($rawRole);
                    
                    // Ensure role is never empty
                    if (empty($displayRole) || trim($displayRole) === '') {
                        $displayRole = 'Manager'; // Default fallback
                    }
                    
                    // Get permissions if available
                    $permissions = isset($row['permissions']) ? $row['permissions'] : null;
                    
                    $admins[] = [
                        'id' => $row['id'],
                        'name' => $name,
                        'email' => $row['email'],
                        'username' => $row['username'],
                        'role' => $displayRole,
                        'permissions' => $permissions,
                        'avatar' => $avatar,
                        'last_login' => isset($row['last_login']) && $row['last_login'] ? date('M j, Y g:i A', strtotime($row['last_login'])) : 'Never'
                    ];
                }
            }
            $stmt->close();
            
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

