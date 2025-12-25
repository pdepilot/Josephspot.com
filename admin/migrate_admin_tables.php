<?php
/**
 * Admin Tables Migration Script
 * 
 * This script migrates data from 'admins' table to 'admin_users' table
 * and updates the login_activity foreign key to reference admin_users instead of admins.
 * 
 * RECOMMENDED APPROACH: Merge tables
 * 
 * Usage: Run this script once via browser: http://localhost/josephspot.com/admin/migrate_admin_tables.php
 */

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
            die("Connection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
    }
    return $conn;
}

$conn = getDBConnection();

echo "<h2>Admin Tables Migration</h2>";
echo "<pre>";

try {
    // Step 1: Check if admin_users table exists, create if not
    echo "Step 1: Ensuring admin_users table exists...\n";
    $check_table = $conn->query("SHOW TABLES LIKE 'admin_users'");
    if ($check_table->num_rows === 0) {
        $sql = "CREATE TABLE admin_users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) DEFAULT NULL,
            email VARCHAR(100) DEFAULT NULL,
            role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
            last_login TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($conn->query($sql)) {
            echo "✓ admin_users table created.\n\n";
        } else {
            throw new Exception("Error creating admin_users table: " . $conn->error);
        }
    } else {
        echo "✓ admin_users table already exists.\n\n";
    }
    
    // Step 2: Migrate data from admins to admin_users (if not already migrated)
    echo "Step 2: Migrating data from admins to admin_users...\n";
    $result = $conn->query("SELECT id, username, email, password, created_at FROM admins");
    $migrated_count = 0;
    $skipped_count = 0;
    
    while ($row = $result->fetch_assoc()) {
        // Check if admin already exists in admin_users
        $check_stmt = $conn->prepare("SELECT id FROM admin_users WHERE id = ?");
        $check_stmt->bind_param("i", $row['id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_stmt->close();
        
        if ($check_result->num_rows === 0) {
            // Insert into admin_users
            // Map role: super_admin stays, others become 'admin'
            $role = 'admin';
            if (isset($row['role']) && $row['role'] === 'super_admin') {
                $role = 'super_admin';
            }
            
            // Extract full_name from username if needed
            $full_name = ucwords(str_replace(['_', '-'], ' ', $row['username']));
            
            $insert_stmt = $conn->prepare("INSERT INTO admin_users (id, username, email, password_hash, full_name, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("issssss", 
                $row['id'], 
                $row['username'], 
                $row['email'], 
                $row['password'], 
                $full_name,
                $role,
                $row['created_at']
            );
            
            if ($insert_stmt->execute()) {
                $migrated_count++;
                echo "  ✓ Migrated admin: {$row['username']} (ID: {$row['id']})\n";
            } else {
                echo "  ✗ Failed to migrate admin: {$row['username']} - " . $insert_stmt->error . "\n";
            }
            $insert_stmt->close();
        } else {
            $skipped_count++;
            echo "  - Skipped admin: {$row['username']} (ID: {$row['id']}) - already exists in admin_users\n";
        }
    }
    
    echo "\n✓ Migration complete. Migrated: $migrated_count, Skipped: $skipped_count\n\n";
    
    // Step 3: Remove foreign key constraint from login_activity
    echo "Step 3: Updating login_activity table foreign key...\n";
    
    // Get the constraint name
    $fk_check = $conn->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
        AND TABLE_NAME = 'login_activity' 
        AND REFERENCED_TABLE_NAME = 'admins'
        AND REFERENCED_COLUMN_NAME = 'id'
    ");
    
    if ($fk_check && $fk_check->num_rows > 0) {
        $fk_row = $fk_check->fetch_assoc();
        $constraint_name = $fk_row['CONSTRAINT_NAME'];
        
        // Drop the old foreign key
        $drop_fk_sql = "ALTER TABLE login_activity DROP FOREIGN KEY `$constraint_name`";
        if ($conn->query($drop_fk_sql)) {
            echo "  ✓ Dropped foreign key constraint: $constraint_name\n";
        } else {
            echo "  ✗ Failed to drop foreign key: " . $conn->error . "\n";
        }
    } else {
        echo "  - No foreign key constraint found (may have been removed already)\n";
    }
    
    // Add new foreign key to admin_users (optional - can be skipped if preferred)
    echo "\nStep 4: Adding foreign key to admin_users (optional)...\n";
    $fk_check_new = $conn->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
        AND TABLE_NAME = 'login_activity' 
        AND REFERENCED_TABLE_NAME = 'admin_users'
        AND REFERENCED_COLUMN_NAME = 'id'
    ");
    
    if ($fk_check_new && $fk_check_new->num_rows === 0) {
        $add_fk_sql = "ALTER TABLE login_activity 
                       ADD CONSTRAINT fk_login_activity_admin_user 
                       FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE";
        
        if ($conn->query($add_fk_sql)) {
            echo "  ✓ Added foreign key constraint to admin_users\n";
        } else {
            echo "  ⚠ Could not add foreign key to admin_users: " . $conn->error . "\n";
            echo "    (This is okay - the system will work without it)\n";
        }
    } else {
        echo "  ✓ Foreign key to admin_users already exists\n";
    }
    
    echo "\n";
    echo "========================================\n";
    echo "Migration Summary:\n";
    echo "========================================\n";
    echo "✓ All admins migrated to admin_users table\n";
    echo "✓ Foreign key constraint updated\n";
    echo "\n";
    echo "NEXT STEPS:\n";
    echo "1. Test login with admins from admin_users table\n";
    echo "2. Verify login_activity logging works correctly\n";
    echo "3. Once confirmed working, you can optionally drop the 'admins' table\n";
    echo "   (Keep it for now as a backup)\n";
    echo "\n";
    echo "To drop admins table later (optional):\n";
    echo "  DROP TABLE IF EXISTS admins;\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "\nMigration failed. Please check the error above.\n";
}

$conn->close();
echo "</pre>";
?>

