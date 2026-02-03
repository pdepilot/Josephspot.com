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
// For valid roles: Returns EXACTLY as provided (no changes)
// For legacy roles: Maps to standard format
// NO FALLBACKS - empty roles remain empty
function mapRoleToDB($role) {
    // Trim whitespace
    $role = trim($role);
    
    // If empty, return empty (don't default to Manager)
    if (empty($role)) {
        return '';
    }
    
    // Define valid roles list
    $valid_roles = ['Super Admin', 'Manager', 'Chef', 'Supervisor', 'Support', 'Admin'];
    
    // CRITICAL: If role is in valid list, return it EXACTLY as provided (case-sensitive, no changes)
    if (in_array($role, $valid_roles)) {
        return $role; // Return exact value - no mapping, no transformation
    }
    
    // Only map legacy/normalized values if NOT in valid list (for backward compatibility with old data)
    $roleMap = [
        // Legacy lowercase versions
        'super_admin' => 'Super Admin',
        'manager' => 'Manager',
        'chef' => 'Chef',
        'supervisor' => 'Supervisor',
        'support' => 'Support',
        'admin' => 'Admin',
        
        // Legacy deprecated roles
        'moderator' => 'Support',
        'content_manager' => 'Chef',
        'content_editor' => 'Chef'
    ];
    
    // Normalize for comparison (only for legacy mapping)
    $role_normalized = strtolower($role);
    
    // Map legacy values
    if (isset($roleMap[$role_normalized])) {
        return $roleMap[$role_normalized];
    }
    
    // If not in valid list and not in legacy map, return as-is (don't default to Manager)
    // This preserves the exact value that was selected
    return $role;
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
// Returns EXACT role from database for valid roles
// Only maps legacy values for backward compatibility
// NO FALLBACKS - empty roles remain empty
function mapRoleFromDB($role) {
    // Handle null
    if ($role === null) {
        return '';
    }
    
    // Trim whitespace
    $role = trim($role);
    
    // If empty, return empty string (don't default to Manager)
    if (empty($role)) {
        return '';
    }
    
    // Define valid roles - if role is valid, return it EXACTLY as stored
    $valid_roles = ['Super Admin', 'Manager', 'Chef', 'Supervisor', 'Support', 'Admin'];
    if (in_array($role, $valid_roles)) {
        return $role; // Return exact value from database - no mapping
    }
    
    // Only map legacy values (for backward compatibility with old data)
    $legacyMap = [
        'super_admin' => 'Super Admin',
        'super admin' => 'Super Admin',
        'admin' => 'Admin',  // Map old lowercase 'admin' to 'Admin'
        'manager' => 'Manager',
        'chef' => 'Chef',
        'supervisor' => 'Supervisor',
        'support' => 'Support',
        'moderator' => 'Support',
        'content_manager' => 'Chef',
        'content_editor' => 'Chef',
        'content manager' => 'Chef'
    ];
    
    $role_normalized = strtolower($role);
    
    // Map legacy values only
    if (isset($legacyMap[$role_normalized])) {
        return $legacyMap[$role_normalized];
    }
    
    // Return the role exactly as stored in database (no changes, no defaults)
    return $role;
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
            if (empty($name) || empty($email)) {
                echo json_encode(['success' => false, 'message' => 'Name and email are required']);
                exit;
            }
            
            // CRITICAL: Validate role is provided and not empty
            if (empty($role) || trim($role) === '') {
                error_log("ERROR api/manage-admin.php create: Role is empty or missing!");
                echo json_encode(['success' => false, 'message' => 'Role is required. Please select a role.']);
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
            
            // Validate role BEFORE mapping - use original role for validation
            $allowed_roles = ['Super Admin', 'Manager', 'Chef', 'Supervisor', 'Support', 'Admin'];
            if (!in_array($role, $allowed_roles)) {
                error_log("ERROR api/manage-admin.php create: Invalid role '$role' not in allowed list");
                echo json_encode(['success' => false, 'message' => 'Invalid role selected: ' . $role]);
                exit;
            }
            
            // Ensure role is not empty
            if (empty($role)) {
                error_log("ERROR api/manage-admin.php create: Role is empty!");
                echo json_encode(['success' => false, 'message' => 'Role cannot be empty. Please select a valid role.']);
                exit;
            }
            
            // Map role to database format - for valid roles, this should return the role as-is
            $dbRole = mapRoleToDB($role);
            
            // URGENT DEBUG: Log role mapping in detail
            error_log("===========================================");
            error_log("DEBUG api/manage-admin.php create: ROLE MAPPING");
            error_log("  - Original role from POST: '$role'");
            error_log("  - Role length: " . strlen($role));
            error_log("  - Role in allowed list: " . (in_array($role, $allowed_roles) ? 'YES' : 'NO'));
            error_log("  - Mapped role (dbRole): '$dbRole'");
            error_log("  - dbRole length: " . strlen($dbRole));
            error_log("  - Roles match: " . ($role === $dbRole ? 'YES' : 'NO'));
            error_log("===========================================");
            
            // Final validation - ensure dbRole matches original (for valid roles, they should match)
            if ($dbRole !== $role && in_array($role, $allowed_roles)) {
                error_log("WARNING api/manage-admin.php create: Role was changed during mapping! Original: '$role', Mapped: '$dbRole'");
                // Use original role instead of mapped one for valid roles
                $dbRole = $role;
            }
            
            // Ensure dbRole is not empty
            if (empty($dbRole)) {
                error_log("ERROR api/manage-admin.php create: dbRole is empty after mapping! Original role was: '$role'");
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
            
            // CRITICAL: Verify role column exists before inserting
            $testRoleColumn = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'role'");
            $hasRoleColumn = $testRoleColumn && $testRoleColumn->num_rows > 0;
            
            if (!$hasRoleColumn) {
                error_log("ERROR api/manage-admin.php create: role column does not exist in admin_users table!");
                // Try to add the column
                $addRoleColumn = "ALTER TABLE `admin_users` ADD COLUMN `role` VARCHAR(50) DEFAULT NULL AFTER `full_name`";
                if ($conn->query($addRoleColumn)) {
                    error_log("SUCCESS: Added role column to admin_users table");
                    $hasRoleColumn = true;
                } else {
                    error_log("ERROR: Failed to add role column: " . $conn->error);
                    echo json_encode(['success' => false, 'message' => 'Database error: role column missing. Please run admin/ensure_role_column.php']);
                    exit;
                }
            } else {
                // Check if role column is ENUM (which might not support all role values)
                $columnInfo = $conn->query("SHOW COLUMNS FROM admin_users WHERE Field = 'role'");
                if ($columnInfo && $columnInfo->num_rows > 0) {
                    $info = $columnInfo->fetch_assoc();
                    $columnType = strtolower($info['Type']);
                    
                    // If column is ENUM, change it to VARCHAR to support all role names
                    if (strpos($columnType, 'enum') !== false) {
                        error_log("WARNING: role column is ENUM type. Changing to VARCHAR(50) to support all role names...");
                        $modifyColumn = "ALTER TABLE `admin_users` MODIFY COLUMN `role` VARCHAR(50) DEFAULT NULL";
                        if ($conn->query($modifyColumn)) {
                            error_log("SUCCESS: Changed role column from ENUM to VARCHAR(50)");
                        } else {
                            error_log("ERROR: Failed to modify role column: " . $conn->error);
                            // Continue anyway - might still work if role is in ENUM list
                        }
                    }
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
            error_log("DEBUG api/manage-admin.php create: dbRole value check - empty: " . (empty($dbRole) ? 'YES' : 'NO') . ", length: " . strlen($dbRole));
            
            // CRITICAL: Verify dbRole is not empty before binding
            if (empty($dbRole) || trim($dbRole) === '') {
                error_log("ERROR api/manage-admin.php create: dbRole is empty! Cannot insert admin without role.");
                echo json_encode(['success' => false, 'message' => 'Role cannot be empty. Please select a valid role.']);
                exit;
            }
            
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
            
            // Log the actual bound values for debugging
            error_log("DEBUG api/manage-admin.php create: About to execute INSERT with role='$dbRole'");
            
            if ($stmt->execute()) {
                $adminId = $conn->insert_id;
                
                // CRITICAL: Verify the role was actually saved
                $verifyStmt = $conn->prepare("SELECT role, full_name, email FROM admin_users WHERE id = ?");
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
                    error_log("  - Saved role (from DB): '" . ($savedAdmin['role'] ?? 'NULL') . "'");
                    error_log("  - Role is NULL: " . (($savedAdmin['role'] ?? null) === null ? 'YES' : 'NO'));
                    error_log("  - Role is empty: " . (empty($savedAdmin['role']) ? 'YES' : 'NO'));
                    error_log("  - Match: " . (($savedAdmin['role'] ?? '') === $dbRole ? 'YES' : 'NO'));
                    
                    if (empty($savedAdmin['role']) || ($savedAdmin['role'] ?? '') !== $dbRole) {
                        error_log("  - ERROR: ROLE NOT SAVED CORRECTLY!");
                        error_log("  - Expected: '$dbRole' (length: " . strlen($dbRole) . ")");
                        error_log("  - Saved: '" . ($savedAdmin['role'] ?? 'NULL') . "' (length: " . strlen($savedAdmin['role'] ?? '') . ")");
                        
                        // Try to fix it by updating the role directly
                        $fixStmt = $conn->prepare("UPDATE admin_users SET role = ? WHERE id = ?");
                        $fixStmt->bind_param("si", $dbRole, $adminId);
                        if ($fixStmt->execute()) {
                            error_log("  - FIXED: Updated role directly in database");
                            $savedAdmin['role'] = $dbRole;
                        } else {
                            error_log("  - ERROR: Failed to fix role: " . $conn->error);
                        }
                        $fixStmt->close();
                    }
                    error_log("===========================================");
                } else {
                    error_log("ERROR api/manage-admin.php create: Could not verify saved admin - query returned no results!");
                }
                
                // Use the verified role from database (single source of truth)
                $verifiedRole = isset($savedAdmin['role']) && !empty($savedAdmin['role']) ? $savedAdmin['role'] : $dbRole;
                
                // Final check - if role is still empty, this is a critical error
                if (empty($verifiedRole)) {
                    error_log("CRITICAL ERROR: Role is still empty after all attempts!");
                    echo json_encode(['success' => false, 'message' => 'Failed to save role. Please check database structure.']);
                    exit;
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Admin created successfully',
                    'admin' => [
                        'id' => $adminId,
                        'username' => $username,
                        'email' => $email,
                        'role' => $verifiedRole, // Return the role as verified from database
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
            
            // Validate role BEFORE mapping - use original role for validation
            $allowed_roles = ['Super Admin', 'Manager', 'Chef', 'Supervisor', 'Support', 'Admin'];
            if (!in_array($role, $allowed_roles)) {
                echo json_encode(['success' => false, 'message' => 'Invalid role selected: ' . $role]);
                exit;
            }
            
            // Map role to database format - for valid roles, this should return the role as-is
            $dbRole = mapRoleToDB($role);
            
            // For valid roles, ensure we use the original (mapping should not change valid roles)
            if ($dbRole !== $role && in_array($role, $allowed_roles)) {
                $dbRole = $role; // Use original role for valid selections
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
            
            // CRITICAL: Verify role column exists
            $testRoleColumn = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'role'");
            $hasRoleColumn = $testRoleColumn && $testRoleColumn->num_rows > 0;
            
            if (!$hasRoleColumn) {
                error_log("ERROR api/manage-admin.php update: role column does not exist!");
                // Try to add the column
                $addRoleColumn = "ALTER TABLE `admin_users` ADD COLUMN `role` VARCHAR(50) DEFAULT NULL AFTER `full_name`";
                if ($conn->query($addRoleColumn)) {
                    error_log("SUCCESS: Added role column to admin_users table");
                    $hasRoleColumn = true;
                } else {
                    error_log("ERROR: Failed to add role column: " . $conn->error);
                    echo json_encode(['success' => false, 'message' => 'Database error: role column missing. Please run admin/ensure_role_column.php']);
                    exit;
                }
            } else {
                // Check if role column is ENUM (which might not support all role values)
                $columnInfo = $conn->query("SHOW COLUMNS FROM admin_users WHERE Field = 'role'");
                if ($columnInfo && $columnInfo->num_rows > 0) {
                    $info = $columnInfo->fetch_assoc();
                    $columnType = strtolower($info['Type']);
                    
                    // If column is ENUM, change it to VARCHAR to support all role names
                    if (strpos($columnType, 'enum') !== false) {
                        error_log("WARNING: role column is ENUM type. Changing to VARCHAR(50) to support all role names...");
                        $modifyColumn = "ALTER TABLE `admin_users` MODIFY COLUMN `role` VARCHAR(50) DEFAULT NULL";
                        if ($conn->query($modifyColumn)) {
                            error_log("SUCCESS: Changed role column from ENUM to VARCHAR(50)");
                        } else {
                            error_log("ERROR: Failed to modify role column: " . $conn->error);
                            // Continue anyway - might still work if role is in ENUM list
                        }
                    }
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
                // CRITICAL: Verify the role was actually saved
                $verifyStmt = $conn->prepare("SELECT role, full_name, email FROM admin_users WHERE id = ?");
                $verifyStmt->bind_param("i", $id);
                $verifyStmt->execute();
                $result = $verifyStmt->get_result();
                $savedAdmin = $result->fetch_assoc();
                $verifyStmt->close();
                
                if ($savedAdmin) {
                    error_log("DEBUG api/manage-admin.php update: POST-UPDATE VERIFICATION");
                    error_log("  - Admin ID: $id");
                    error_log("  - Expected role (dbRole): '$dbRole'");
                    error_log("  - Saved role (from DB): '" . ($savedAdmin['role'] ?? 'NULL') . "'");
                    
                    if (empty($savedAdmin['role']) || ($savedAdmin['role'] ?? '') !== $dbRole) {
                        error_log("  - ERROR: ROLE NOT SAVED CORRECTLY!");
                        // Try to fix it by updating the role directly
                        $fixStmt = $conn->prepare("UPDATE admin_users SET role = ? WHERE id = ?");
                        $fixStmt->bind_param("si", $dbRole, $id);
                        if ($fixStmt->execute()) {
                            error_log("  - FIXED: Updated role directly in database");
                            $savedAdmin['role'] = $dbRole;
                        } else {
                            error_log("  - ERROR: Failed to fix role: " . $conn->error);
                        }
                        $fixStmt->close();
                    }
                }
                
                // Use the verified role from database (single source of truth)
                $verifiedRole = isset($savedAdmin['role']) && !empty($savedAdmin['role']) ? $savedAdmin['role'] : $dbRole;
                
                // Final check - if role is still empty, this is a critical error
                if (empty($verifiedRole)) {
                    error_log("CRITICAL ERROR: Role is still empty after update!");
                    echo json_encode(['success' => false, 'message' => 'Failed to save role. Please check database structure.']);
                    exit;
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Admin updated successfully',
                    'admin' => [
                        'id' => $id,
                        'username' => $username,
                        'email' => $email,
                        'role' => $verifiedRole, // Return the role as verified from database
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
            
            // CRITICAL: Verify role column exists
            $checkRoleColumn = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'role'");
            $hasRoleColumn = $checkRoleColumn && $checkRoleColumn->num_rows > 0;
            
            if (!$hasRoleColumn) {
                error_log("ERROR api/manage-admin.php list: role column does not exist!");
                echo json_encode(['success' => false, 'message' => 'Database error: role column missing. Please run admin/ensure_role_column.php']);
                exit;
            }
            
            // Check which columns exist in admin_users table
            $checkPermissions = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'permissions'");
            $hasPermissions = $checkPermissions && $checkPermissions->num_rows > 0;
            
            // Build query - ALWAYS include role column (it's required)
            // Dynamically include permissions column only if it exists
            // This ensures newly created admins appear immediately
            if ($hasPermissions) {
                $sql = "SELECT id, username, email, full_name, role, permissions, last_login, created_at 
                        FROM admin_users 
                        WHERE id != ? 
                        ORDER BY created_at DESC";
            } else {
                $sql = "SELECT id, username, email, full_name, role, last_login, created_at 
                        FROM admin_users 
                        WHERE id != ? 
                        ORDER BY created_at DESC";
            }
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Error preparing admin list query: " . $conn->error);
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
                exit;
            }
            
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
                    
                    // Get role directly from database - CRITICAL: role column must exist
                    // Check if 'role' key exists in the row (column might not be selected)
                    if (!isset($row['role'])) {
                        error_log("ERROR api/manage-admin.php list: 'role' column not found in query result for admin ID {$row['id']}!");
                        // Try to get role with a separate query as fallback
                        $roleQuery = $conn->prepare("SELECT role FROM admin_users WHERE id = ?");
                        $roleQuery->bind_param("i", $row['id']);
                        $roleQuery->execute();
                        $roleResult = $roleQuery->get_result();
                        if ($roleRow = $roleResult->fetch_assoc()) {
                            $row['role'] = $roleRow['role'];
                            error_log("FIXED: Retrieved role from separate query for admin ID {$row['id']}: '{$row['role']}'");
                        } else {
                            $row['role'] = null;
                            error_log("ERROR: Could not retrieve role even from separate query for admin ID {$row['id']}");
                        }
                        $roleQuery->close();
                    }
                    
                    $rawRole = isset($row['role']) ? $row['role'] : null;
                    
                    // Log for debugging if role is missing
                    if ($rawRole === null || $rawRole === '') {
                        error_log("WARNING api/manage-admin.php list: Admin ID {$row['id']} ({$row['username']}) has no role in database! Raw value: " . var_export($rawRole, true));
                    }
                    
                    // Map role (only for legacy values, valid roles returned as-is)
                    $displayRole = mapRoleFromDB($rawRole);
                    
                    // Ensure we have a role value (even if empty, don't default)
                    // This ensures we display exactly what's in the database
                    if ($displayRole === null) {
                        $displayRole = ''; // Show empty if null
                    }
                    
                    // Log the role being returned (only for debugging - can be removed in production)
                    // error_log("DEBUG api/manage-admin.php list: Admin {$row['id']} - rawRole: '" . ($rawRole ?? 'NULL') . "', displayRole: '$displayRole'");
                    
                    // Get permissions if available (only if column exists)
                    $permissions = null;
                    if ($hasPermissions && isset($row['permissions'])) {
                        $permissions = $row['permissions'];
                    }
                    
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
            
            // Log for debugging
            error_log("Admin list query returned " . count($admins) . " admins");
            
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

