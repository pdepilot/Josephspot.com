<?php
/**
 * URGENT DEBUG SCRIPT: Check admin_users table structure and role column
 * Run this script directly in browser: admin/debug_role_save.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'joseph_pot_admin');

// Get database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Admin Users Table Structure Debug</h1>";
echo "<hr>";

// 1. Check if table exists
echo "<h2>1. Table Existence Check</h2>";
$tableCheck = $conn->query("SHOW TABLES LIKE 'admin_users'");
if ($tableCheck->num_rows > 0) {
    echo "<p style='color: green;'>✓ admin_users table EXISTS</p>";
} else {
    echo "<p style='color: red;'>✗ admin_users table DOES NOT EXIST</p>";
    exit;
}

// 2. Check table structure
echo "<h2>2. Table Structure</h2>";
$columns = $conn->query("SHOW COLUMNS FROM admin_users");
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $columns->fetch_assoc()) {
    echo "<tr>";
    echo "<td><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
    echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Check role column specifically
echo "<h2>3. Role Column Details</h2>";
$roleColumn = $conn->query("SHOW COLUMNS FROM admin_users WHERE Field = 'role'");
if ($roleColumn->num_rows > 0) {
    $roleInfo = $roleColumn->fetch_assoc();
    echo "<p><strong>Column Name:</strong> " . htmlspecialchars($roleInfo['Field']) . "</p>";
    echo "<p><strong>Type:</strong> " . htmlspecialchars($roleInfo['Type']) . "</p>";
    echo "<p><strong>Null:</strong> " . htmlspecialchars($roleInfo['Null']) . "</p>";
    echo "<p><strong>Default:</strong> " . htmlspecialchars($roleInfo['Default'] ?? 'NULL') . "</p>";
    
    // Check if default value might be overriding inserts
    if (!empty($roleInfo['Default'])) {
        echo "<p style='color: orange;'><strong>⚠ WARNING:</strong> Role column has a DEFAULT value: '" . htmlspecialchars($roleInfo['Default']) . "'</p>";
        echo "<p>This might be overriding your INSERT statements if you're not explicitly setting the role!</p>";
    }
    
    // CRITICAL: Check if column is ENUM with old values
    if (strpos(strtolower($roleInfo['Type']), 'enum') !== false) {
        echo "<p style='color: red; font-weight: bold; font-size: 1.2em;'><strong>🚨 CRITICAL ISSUE FOUND:</strong></p>";
        echo "<p style='color: red;'>The role column is an ENUM with old values: " . htmlspecialchars($roleInfo['Type']) . "</p>";
        echo "<p style='color: red;'>The code is trying to insert new values like 'Super Admin', 'Manager', 'Content Manager', 'Support'</p>";
        echo "<p style='color: red;'><strong>These values DO NOT MATCH the ENUM values, so the role will NOT be saved correctly!</strong></p>";
        echo "<p style='color: red;'><strong>SOLUTION: Run fix_role_column.php to change the column to VARCHAR(50)</strong></p>";
    }
} else {
    echo "<p style='color: red;'>✗ Role column DOES NOT EXIST</p>";
}

// 4. Check existing admin records
echo "<h2>4. Existing Admin Records (Last 5)</h2>";
// Check if status column exists first
$statusCheck = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'status'");
$hasStatus = $statusCheck && $statusCheck->num_rows > 0;
$statusField = $hasStatus ? ", status" : "";
$admins = $conn->query("SELECT id, username, email, role{$statusField}, created_at FROM admin_users ORDER BY id DESC LIMIT 5");
if ($admins && $admins->num_rows > 0) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th>" . ($hasStatus ? "<th>Status</th>" : "") . "<th>Created</th></tr>";
    while ($admin = $admins->fetch_assoc()) {
        $roleValue = $admin['role'];
        $roleDisplay = is_null($roleValue) ? '<span style="color: red;">NULL</span>' : htmlspecialchars($roleValue);
        echo "<tr>";
        echo "<td>" . htmlspecialchars($admin['id']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['username']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
        echo "<td>" . $roleDisplay . "</td>";
        if ($hasStatus) {
            echo "<td>" . htmlspecialchars($admin['status'] ?? 'N/A') . "</td>";
        }
        echo "<td>" . htmlspecialchars($admin['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No admin records found.</p>";
}

// 5. Test INSERT query (dry run - don't actually insert)
echo "<h2>5. Test INSERT Query Structure</h2>";
echo "<p>Testing what SQL would be generated:</p>";
$testRole = "Content Manager";
$testUsername = "test_user";
$testEmail = "test@example.com";
$testName = "Test User";
$testPasswordHash = password_hash("test123", PASSWORD_DEFAULT);

// Check which status column exists
$statusCheck = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'status'");
$hasStatus = $statusCheck && $statusCheck->num_rows > 0;

if ($hasStatus) {
    $testSql = "INSERT INTO admin_users (username, email, password_hash, full_name, role, status) VALUES (?, ?, ?, ?, ?, 'active')";
} else {
    $isActiveCheck = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'is_active'");
    $hasIsActive = $isActiveCheck && $isActiveCheck->num_rows > 0;
    if ($hasIsActive) {
        $testSql = "INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active) VALUES (?, ?, ?, ?, ?, 1)";
    } else {
        $testSql = "INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)";
    }
}

echo "<p><strong>Generated SQL:</strong></p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
echo htmlspecialchars($testSql);
echo "</pre>";

echo "<p><strong>Parameters would be:</strong></p>";
echo "<ul>";
echo "<li>username: $testUsername</li>";
echo "<li>email: $testEmail</li>";
echo "<li>password_hash: [HIDDEN]</li>";
echo "<li>full_name: $testName</li>";
echo "<li>role: <strong style='color: blue;'>$testRole</strong></li>";
echo "</ul>";

// 6. Recommendations
echo "<h2>6. Recommendations</h2>";
echo "<ol>";
echo "<li>Check PHP error logs for DEBUG messages when creating an admin</li>";
echo "<li>Check browser console (F12 → Console) for JavaScript errors</li>";
echo "<li>Check browser Network tab (F12 → Network) to see the actual POST request payload</li>";
echo "<li>Verify the role dropdown in the form has correct value attributes</li>";
echo "<li>If role column has a DEFAULT value, it should be removed or set to NULL</li>";
echo "<li>Ensure role column type is VARCHAR(50) or similar (not ENUM with limited values)</li>";
echo "</ol>";

$conn->close();
?>

