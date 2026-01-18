<?php
/**
 * Careers Database Setup Script
 * Run this script once to create all careers-related database tables
 */

// Database configuration
$host = 'localhost';
$dbname = 'joseph_pot_admin';
$username = 'root';
$password = '';

// HTML header
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Careers Database Setup</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;}";
echo "h2{color:#333;}p{margin:10px 0;padding:8px;border-radius:4px;}";
echo ".success{color:green;background:#e8f5e9;}";
echo ".warning{color:orange;background:#fff3e0;}";
echo ".error{color:red;background:#ffebee;}</style></head><body>";

try {
    // First, try to connect without database to create it if needed
    try {
        $pdo = new PDO(
            "mysql:host=$host;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p class='success'>✓ Database '$dbname' ready</p>\n";
    } catch (PDOException $e) {
        // Database might already exist, continue
    }
    
    // Now connect to the database
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "<h2>Creating Careers Database Tables...</h2>\n";
    
    // Check if admin_users table exists (needed for foreign keys)
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    $adminUsersExists = $stmt->rowCount() > 0;
    
    if (!$adminUsersExists) {
        // Check for 'admins' table as alternative
        $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
        $adminsExists = $stmt->rowCount() > 0;
        
        if ($adminsExists) {
            echo "<p class='warning'>⚠ Note: 'admins' table found. Foreign keys will reference 'admin_users' which may not exist. This is okay as foreign keys are optional (ON DELETE SET NULL).</p>\n";
        } else {
            echo "<p class='warning'>⚠ Note: No admin_users or admins table found. Foreign keys in career_notifications and application_status_history will be set to NULL.</p>\n";
        }
    }
    
    // Read and execute SQL schema
    $sqlFile = __DIR__ . '/../database/careers_schema.sql';
    
    if (!file_exists($sqlFile)) {
        die("<p class='error'>Error: SQL schema file not found at: $sqlFile</p>\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Remove comments and split SQL statements
    $sql = preg_replace('/--.*$/m', '', $sql); // Remove single-line comments
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    $errorCount = 0;
    $skippedCount = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strlen($statement) < 10) {
            continue; // Skip empty or very short statements
        }
        
        try {
            $pdo->exec($statement);
            $tableName = '';
            if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                $tableName = $matches[1];
            }
            echo "<p class='success'>✓ Created table: <strong>$tableName</strong></p>\n";
            $successCount++;
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            // Check if table already exists
            if (strpos($errorMsg, 'already exists') !== false || strpos($errorMsg, 'Duplicate table') !== false) {
                if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                    $tableName = $matches[1];
                    echo "<p class='warning'>⚠ Table already exists: <strong>$tableName</strong> (skipped)</p>\n";
                } else {
                    echo "<p class='warning'>⚠ Statement skipped (already exists)</p>\n";
                }
                $skippedCount++;
            } else {
                // Check for foreign key errors (these are okay if admin_users doesn't exist)
                if (strpos($errorMsg, 'foreign key constraint') !== false || strpos($errorMsg, 'Cannot add foreign key') !== false) {
                    echo "<p class='warning'>⚠ Foreign key constraint warning (this is okay if admin_users table doesn't exist): " . htmlspecialchars(substr($errorMsg, 0, 100)) . "...</p>\n";
                    $skippedCount++;
                } else {
                    echo "<p class='error'>✗ Error: " . htmlspecialchars($errorMsg) . "</p>\n";
                    $errorCount++;
                }
            }
        }
    }
    
    echo "<hr>";
    echo "<h2 style='color: " . ($errorCount > 0 ? 'red' : 'green') . ";'>Setup Summary</h2>\n";
    echo "<p><strong>Successfully created:</strong> $successCount table(s)</p>\n";
    echo "<p><strong>Skipped (already exists):</strong> $skippedCount table(s)</p>\n";
    if ($errorCount > 0) {
        echo "<p class='error'><strong>Errors:</strong> $errorCount</p>\n";
    }
    
    if ($errorCount == 0) {
        echo "<h2 style='color: green;'>✓ Database setup complete!</h2>\n";
        echo "<p><a href='admin-career.php' style='display:inline-block;padding:10px 20px;background:#8B4513;color:white;text-decoration:none;border-radius:5px;'>Go to Careers Dashboard</a></p>\n";
    } else {
        echo "<p class='error'>⚠ Some errors occurred. Please review the messages above.</p>\n";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Database Connection Error:</h2>";
    echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>Please check your database configuration in the setup script.</p>\n";
}

echo "</body></html>";
?>
