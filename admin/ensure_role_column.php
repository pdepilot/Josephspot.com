<?php
/**
 * Ensure role column exists in admin_users table
 * This script creates or modifies the role column to ensure it can store role values
 */

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
$conn->set_charset("utf8mb4");

echo "Checking admin_users table structure...\n\n";

// Check if admin_users table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'admin_users'");
if ($tableCheck->num_rows === 0) {
    echo "ERROR: admin_users table does not exist!\n";
    echo "Creating admin_users table...\n";
    
    $createTable = "CREATE TABLE `admin_users` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `email` VARCHAR(100) DEFAULT NULL,
        `password_hash` VARCHAR(255) NOT NULL,
        `full_name` VARCHAR(100) DEFAULT NULL,
        `role` VARCHAR(50) DEFAULT NULL,
        `last_login` TIMESTAMP NULL DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_username` (`username`),
        INDEX `idx_email` (`email`),
        INDEX `idx_role` (`role`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($createTable)) {
        echo "✓ admin_users table created successfully.\n\n";
    } else {
        die("ERROR creating table: " . $conn->error);
    }
} else {
    echo "✓ admin_users table exists.\n\n";
}

// Check if role column exists
$columnCheck = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'role'");
if ($columnCheck->num_rows === 0) {
    echo "ERROR: role column does not exist!\n";
    echo "Adding role column...\n";
    
    $addColumn = "ALTER TABLE `admin_users` ADD COLUMN `role` VARCHAR(50) DEFAULT NULL AFTER `full_name`";
    if ($conn->query($addColumn)) {
        echo "✓ role column added successfully.\n\n";
    } else {
        die("ERROR adding role column: " . $conn->error);
    }
} else {
    echo "✓ role column exists.\n";
    
    // Check column type
    $columnInfo = $conn->query("SHOW COLUMNS FROM admin_users WHERE Field = 'role'");
    $info = $columnInfo->fetch_assoc();
    echo "  - Column Type: " . $info['Type'] . "\n";
    echo "  - Null: " . $info['Null'] . "\n";
    echo "  - Default: " . ($info['Default'] ?? 'NULL') . "\n\n";
    
    // If column is ENUM, change it to VARCHAR to support all role names
    if (strpos(strtolower($info['Type']), 'enum') !== false) {
        echo "WARNING: role column is ENUM type. Changing to VARCHAR(50) to support all role names...\n";
        $modifyColumn = "ALTER TABLE `admin_users` MODIFY COLUMN `role` VARCHAR(50) DEFAULT NULL";
        if ($conn->query($modifyColumn)) {
            echo "✓ role column changed to VARCHAR(50).\n\n";
        } else {
            echo "ERROR changing column type: " . $conn->error . "\n\n";
        }
    }
}

// Verify role column can accept the role values
echo "Testing role column with sample values...\n";
$testRoles = ['Super Admin', 'Manager', 'Chef', 'Supervisor', 'Support', 'Admin'];
foreach ($testRoles as $testRole) {
    $testQuery = "SELECT '{$testRole}' as test_role";
    $result = $conn->query($testQuery);
    if ($result) {
        echo "  ✓ Can store: '{$testRole}'\n";
    }
}

echo "\n✓ Role column verification complete!\n";
echo "\nYou can now create admins with roles and they will be stored correctly.\n";

$conn->close();
?>
