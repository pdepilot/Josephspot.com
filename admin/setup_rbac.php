<?php
/**
 * RBAC Database Setup Script
 * Run this once to set up the RBAC system
 */

session_start();

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

echo "<h2>RBAC Database Setup</h2>";
echo "<pre>";

try {
    // Step 1: Create roles table
    echo "Step 1: Creating roles table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `roles` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(50) NOT NULL UNIQUE,
        `description` TEXT,
        `is_default` BOOLEAN DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        echo "✓ Roles table created successfully\n\n";
    } else {
        throw new Exception("Error creating roles table: " . $conn->error);
    }

    // Step 2: Create permissions table
    echo "Step 2: Creating permissions table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `permissions` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `module` VARCHAR(50) NOT NULL,
        `action` VARCHAR(50) NOT NULL,
        `description` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `module_action` (`module`, `action`),
        INDEX `idx_module` (`module`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        echo "✓ Permissions table created successfully\n\n";
    } else {
        throw new Exception("Error creating permissions table: " . $conn->error);
    }

    // Step 3: Create role_permissions junction table
    echo "Step 3: Creating role_permissions table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `role_permissions` (
        `role_id` INT(11) NOT NULL,
        `permission_id` INT(11) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`role_id`, `permission_id`),
        FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE,
        INDEX `idx_role_id` (`role_id`),
        INDEX `idx_permission_id` (`permission_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        echo "✓ Role_permissions table created successfully\n\n";
    } else {
        throw new Exception("Error creating role_permissions table: " . $conn->error);
    }

    // Step 4: Modify admin_users table
    echo "Step 4: Modifying admin_users table...\n";
    
    // Check if role_id column exists
    $checkColumn = $conn->query("SHOW COLUMNS FROM `admin_users` LIKE 'role_id'");
    if ($checkColumn->num_rows == 0) {
        // Add role_id column
        $sql = "ALTER TABLE `admin_users` ADD COLUMN `role_id` INT(11) NULL AFTER `email`";
        if (!$conn->query($sql)) {
            throw new Exception("Error adding role_id column: " . $conn->error);
        }
        echo "✓ Added role_id column\n";
    } else {
        echo "✓ role_id column already exists\n";
    }
    
    // Check if status column exists
    $checkStatus = $conn->query("SHOW COLUMNS FROM `admin_users` LIKE 'status'");
    if ($checkStatus->num_rows == 0) {
        // Add status column
        $sql = "ALTER TABLE `admin_users` ADD COLUMN `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active' AFTER `role_id`";
        if (!$conn->query($sql)) {
            throw new Exception("Error adding status column: " . $conn->error);
        }
        echo "✓ Added status column\n";
    } else {
        echo "✓ status column already exists\n";
    }
    
    // Add foreign key constraint for role_id if it doesn't exist
    $checkFK = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                             WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
                             AND TABLE_NAME = 'admin_users' 
                             AND COLUMN_NAME = 'role_id' 
                             AND CONSTRAINT_NAME != 'PRIMARY'");
    if ($checkFK->num_rows == 0) {
        $sql = "ALTER TABLE `admin_users` ADD CONSTRAINT `fk_admin_users_role` 
                FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL";
        if (!$conn->query($sql)) {
            // Foreign key might fail if there's existing data, that's okay
            echo "⚠ Foreign key constraint might already exist or cannot be added (this is okay)\n";
        } else {
            echo "✓ Added foreign key constraint for role_id\n";
        }
    } else {
        echo "✓ Foreign key constraint already exists\n";
    }
    
    echo "\n";

    // Step 5: Insert default roles
    echo "Step 5: Inserting default roles...\n";
    $defaultRoles = [
        ['Super Admin', 'Full system access with all permissions', 1],
        ['Manager', 'Can manage orders, reservations, and content', 0],
        ['Content Manager', 'Can manage menu, gallery, events, and reviews', 0],
        ['Support', 'Can view orders and reservations, respond to messages', 0]
    ];
    
    foreach ($defaultRoles as $role) {
        $stmt = $conn->prepare("INSERT IGNORE INTO `roles` (`name`, `description`, `is_default`) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $role[0], $role[1], $role[2]);
        if ($stmt->execute()) {
            echo "✓ Role '{$role[0]}' inserted\n";
        }
        $stmt->close();
    }
    echo "\n";

    // Step 6: Insert default permissions
    echo "Step 6: Inserting default permissions...\n";
    $permissions = [
        // Dashboard
        ['dashboard', 'view', 'View dashboard'],
        
        // Orders
        ['orders', 'view', 'View orders'],
        ['orders', 'create', 'Create orders'],
        ['orders', 'edit', 'Edit orders'],
        ['orders', 'delete', 'Delete orders'],
        ['orders', 'export', 'Export orders'],
        
        // Reservations
        ['reservations', 'view', 'View reservations'],
        ['reservations', 'create', 'Create reservations'],
        ['reservations', 'edit', 'Edit reservations'],
        ['reservations', 'delete', 'Delete reservations'],
        
        // Menu
        ['menu', 'view', 'View menu items'],
        ['menu', 'create', 'Create menu items'],
        ['menu', 'edit', 'Edit menu items'],
        ['menu', 'delete', 'Delete menu items'],
        
        // Customers
        ['customers', 'view', 'View customers'],
        ['customers', 'edit', 'Edit customers'],
        ['customers', 'delete', 'Delete customers'],
        
        // Reviews
        ['reviews', 'view', 'View reviews'],
        ['reviews', 'edit', 'Edit reviews'],
        ['reviews', 'delete', 'Delete reviews'],
        ['reviews', 'approve', 'Approve reviews'],
        
        // Gallery
        ['gallery', 'view', 'View gallery'],
        ['gallery', 'create', 'Upload images'],
        ['gallery', 'delete', 'Delete images'],
        
        // Events
        ['events', 'view', 'View events'],
        ['events', 'create', 'Create events'],
        ['events', 'edit', 'Edit events'],
        ['events', 'delete', 'Delete events'],
        
        // Contact Messages
        ['messages', 'view', 'View contact messages'],
        ['messages', 'reply', 'Reply to messages'],
        ['messages', 'delete', 'Delete messages'],
        
        // Settings
        ['settings', 'view', 'View settings'],
        ['settings', 'edit', 'Edit settings'],
        
        // Admin Management
        ['admins', 'view', 'View admins'],
        ['admins', 'create', 'Create admins'],
        ['admins', 'edit', 'Edit admins'],
        ['admins', 'delete', 'Delete admins'],
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO `permissions` (`module`, `action`, `description`) VALUES (?, ?, ?)");
    foreach ($permissions as $perm) {
        $stmt->bind_param("sss", $perm[0], $perm[1], $perm[2]);
        if ($stmt->execute()) {
            echo "✓ Permission '{$perm[0]}.{$perm[1]}' inserted\n";
        }
    }
    $stmt->close();
    echo "\n";

    // Step 7: Assign permissions to roles
    echo "Step 7: Assigning permissions to roles...\n";
    
    // Get role IDs
    $roleIds = [];
    $result = $conn->query("SELECT id, name FROM roles");
    while ($row = $result->fetch_assoc()) {
        $roleIds[$row['name']] = $row['id'];
    }
    
    // Get all permissions
    $allPerms = [];
    $result = $conn->query("SELECT id, module, action FROM permissions");
    while ($row = $result->fetch_assoc()) {
        $key = $row['module'] . '.' . $row['action'];
        $allPerms[$key] = $row['id'];
    }
    
    // Super Admin gets ALL permissions
    if (isset($roleIds['Super Admin'])) {
        $stmt = $conn->prepare("INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`) VALUES (?, ?)");
        foreach ($allPerms as $permId) {
            $stmt->bind_param("ii", $roleIds['Super Admin'], $permId);
            $stmt->execute();
        }
        echo "✓ All permissions assigned to Super Admin\n";
        $stmt->close();
    }
    
    // Manager permissions
    if (isset($roleIds['Manager'])) {
        $managerPerms = [
            'dashboard.view', 'orders.view', 'orders.edit', 'orders.export',
            'reservations.view', 'reservations.edit', 'reservations.delete',
            'menu.view', 'menu.create', 'menu.edit', 'menu.delete',
            'customers.view', 'reviews.view', 'reviews.edit', 'reviews.approve',
            'messages.view', 'messages.reply'
        ];
        $stmt = $conn->prepare("INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`) VALUES (?, ?)");
        foreach ($managerPerms as $permKey) {
            if (isset($allPerms[$permKey])) {
                $stmt->bind_param("ii", $roleIds['Manager'], $allPerms[$permKey]);
                $stmt->execute();
            }
        }
        echo "✓ Permissions assigned to Manager\n";
        $stmt->close();
    }
    
    // Content Manager permissions
    if (isset($roleIds['Content Manager'])) {
        $contentPerms = [
            'dashboard.view', 'menu.view', 'menu.create', 'menu.edit', 'menu.delete',
            'gallery.view', 'gallery.create', 'gallery.delete',
            'events.view', 'events.create', 'events.edit', 'events.delete',
            'reviews.view', 'reviews.edit', 'reviews.approve'
        ];
        $stmt = $conn->prepare("INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`) VALUES (?, ?)");
        foreach ($contentPerms as $permKey) {
            if (isset($allPerms[$permKey])) {
                $stmt->bind_param("ii", $roleIds['Content Manager'], $allPerms[$permKey]);
                $stmt->execute();
            }
        }
        echo "✓ Permissions assigned to Content Manager\n";
        $stmt->close();
    }
    
    // Support permissions
    if (isset($roleIds['Support'])) {
        $supportPerms = [
            'dashboard.view', 'orders.view', 'reservations.view',
            'messages.view', 'messages.reply', 'customers.view'
        ];
        $stmt = $conn->prepare("INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`) VALUES (?, ?)");
        foreach ($supportPerms as $permKey) {
            if (isset($allPerms[$permKey])) {
                $stmt->bind_param("ii", $roleIds['Support'], $allPerms[$permKey]);
                $stmt->execute();
            }
        }
        echo "✓ Permissions assigned to Support\n";
        $stmt->close();
    }
    
    echo "\n";

    // Step 8: Migrate existing admin_users role data to role_id
    echo "Step 8: Migrating existing admin role data...\n";
    
    // Check if role column still exists
    $checkOldRole = $conn->query("SHOW COLUMNS FROM `admin_users` LIKE 'role'");
    if ($checkOldRole->num_rows > 0) {
        // Get Super Admin role ID
        $result = $conn->query("SELECT id FROM roles WHERE name = 'Super Admin' LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            $superAdminRoleId = $row['id'];
            
            // Update existing admins to Super Admin role (migration default)
            $sql = "UPDATE `admin_users` SET `role_id` = $superAdminRoleId WHERE `role_id` IS NULL";
            if ($conn->query($sql)) {
                echo "✓ Migrated existing admins to Super Admin role\n";
            }
        }
    } else {
        echo "✓ No old role column found (already migrated)\n";
    }
    
    echo "\n";
    
    echo "<h3 style='color: green;'>✓ RBAC Setup Complete!</h3>\n";
    echo "You can now use the RBAC system. Make sure to include 'includes/rbac.php' in your admin pages.\n";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ Error: " . $e->getMessage() . "</h3>\n";
}

echo "</pre>";

$conn->close();
?>

