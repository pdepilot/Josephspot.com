<?php
/**
 * URGENT FIX: Change role column from ENUM to VARCHAR
 * Run this script once to fix the role column issue
 * Access via browser: admin/fix_role_column.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'joseph_pot_admin');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Fix Role Column - ENUM to VARCHAR</h1>";
echo "<hr>";

// Step 1: Check current role column type
echo "<h2>Step 1: Current Role Column Type</h2>";
$currentColumn = $conn->query("SHOW COLUMNS FROM admin_users WHERE Field = 'role'");
if ($currentColumn && $currentColumn->num_rows > 0) {
    $columnInfo = $currentColumn->fetch_assoc();
    echo "<p><strong>Current Type:</strong> " . htmlspecialchars($columnInfo['Type']) . "</p>";
    echo "<p><strong>Current Default:</strong> " . htmlspecialchars($columnInfo['Default'] ?? 'NULL') . "</p>";
    
    // Check if it's already VARCHAR
    if (strpos(strtolower($columnInfo['Type']), 'varchar') !== false) {
        echo "<p style='color: green;'>✓ Role column is already VARCHAR. No changes needed!</p>";
        $conn->close();
        exit;
    }
} else {
    echo "<p style='color: red;'>✗ Role column not found!</p>";
    $conn->close();
    exit;
}

// Step 2: Perform the fix
echo "<h2>Step 2: Fixing Role Column</h2>";

try {
    $conn->begin_transaction();
    
    // Change column from ENUM to VARCHAR(50)
    echo "<p>Changing role column from ENUM to VARCHAR(50)...</p>";
    $sql1 = "ALTER TABLE `admin_users` MODIFY COLUMN `role` VARCHAR(50) DEFAULT 'Manager'";
    if ($conn->query($sql1)) {
        echo "<p style='color: green;'>✓ Column type changed successfully</p>";
    } else {
        throw new Exception("Error changing column type: " . $conn->error);
    }
    
    // Update existing roles to new format
    echo "<p>Updating existing role values...</p>";
    
    $update1 = "UPDATE `admin_users` SET `role` = 'Super Admin' WHERE `role` = 'super_admin'";
    if ($conn->query($update1)) {
        $affected1 = $conn->affected_rows;
        echo "<p style='color: green;'>✓ Updated $affected1 record(s): super_admin → Super Admin</p>";
    }
    
    $update2 = "UPDATE `admin_users` SET `role` = 'Manager' WHERE `role` = 'admin' AND `role` != 'Super Admin'";
    if ($conn->query($update2)) {
        $affected2 = $conn->affected_rows;
        echo "<p style='color: green;'>✓ Updated $affected2 record(s): admin → Manager</p>";
    }
    
    $update3 = "UPDATE `admin_users` SET `role` = 'Support' WHERE `role` = 'moderator'";
    if ($conn->query($update3)) {
        $affected3 = $conn->affected_rows;
        echo "<p style='color: green;'>✓ Updated $affected3 record(s): moderator → Support</p>";
    }
    
    // Update empty/NULL roles to default 'Manager'
    $update4 = "UPDATE `admin_users` SET `role` = 'Manager' WHERE `role` IS NULL OR `role` = '' OR `role` = 'NULL'";
    if ($conn->query($update4)) {
        $affected4 = $conn->affected_rows;
        if ($affected4 > 0) {
            echo "<p style='color: green;'>✓ Updated $affected4 record(s) with empty/NULL roles → Manager (default)</p>";
        }
    }
    
    $conn->commit();
    echo "<p style='color: green; font-weight: bold;'>✓ All changes applied successfully!</p>";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 3: Verify the change
echo "<h2>Step 3: Verification</h2>";
$verifyColumn = $conn->query("SHOW COLUMNS FROM admin_users WHERE Field = 'role'");
if ($verifyColumn && $verifyColumn->num_rows > 0) {
    $newColumnInfo = $verifyColumn->fetch_assoc();
    echo "<p><strong>New Type:</strong> " . htmlspecialchars($newColumnInfo['Type']) . "</p>";
    echo "<p><strong>New Default:</strong> " . htmlspecialchars($newColumnInfo['Default'] ?? 'NULL') . "</p>";
    
    if (strpos(strtolower($newColumnInfo['Type']), 'varchar') !== false) {
        echo "<p style='color: green; font-weight: bold;'>✓ SUCCESS! Role column is now VARCHAR(50)</p>";
    } else {
        echo "<p style='color: red;'>✗ Column type was not changed correctly</p>";
    }
}

// Step 4: Show current admin roles
echo "<h2>Step 4: Current Admin Roles</h2>";
$admins = $conn->query("SELECT id, username, email, role FROM admin_users ORDER BY id DESC LIMIT 10");
if ($admins && $admins->num_rows > 0) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
    while ($admin = $admins->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($admin['id']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['username']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($admin['role'] ?? 'NULL') . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Try creating a new admin from the dashboard</li>";
echo "<li>The role should now be saved correctly</li>";
echo "<li>Check the debug_role_save.php script again to verify</li>";
echo "</ol>";

$conn->close();
?>

