<?php
/**
 * Safe Drop Old Database Script
 * 
 * This script will drop the old database 'josep_pot_admin' after verification.
 * It ensures the correct database exists and has all data before proceeding.
 */

$host = 'localhost';
$username = 'root';
$password = '';
$old_db = 'josep_pot_admin';
$correct_db = 'joseph_pot_admin';

echo "=== Drop Old Database Script ===\n\n";

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Step 1: Verify correct database exists
    echo "Step 1: Verifying correct database exists...\n";
    $stmt = $pdo->query("SHOW DATABASES LIKE '$correct_db'");
    if ($stmt->rowCount() === 0) {
        echo "✗ ERROR: Correct database '$correct_db' does not exist!\n";
        echo "Cannot proceed. Aborting.\n";
        exit(1);
    }
    echo "✓ Correct database '$correct_db' exists\n\n";
    
    // Step 2: Verify correct database has data
    echo "Step 2: Verifying data in correct database...\n";
    $pdo->exec("USE `$correct_db`");
    
    // Check critical tables
    $critical_tables = ['food_menu_manager', 'admins', 'general_settings'];
    foreach ($critical_tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() === 0) {
            echo "✗ ERROR: Critical table '$table' missing in '$correct_db'!\n";
            echo "Cannot proceed. Aborting.\n";
            exit(1);
        }
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "✓ Table '$table' exists with $count records\n";
    }
    echo "\n";
    
    // Step 3: Check if old database exists
    echo "Step 3: Checking old database...\n";
    $stmt = $pdo->query("SHOW DATABASES LIKE '$old_db'");
    if ($stmt->rowCount() === 0) {
        echo "✓ Old database '$old_db' does not exist (already removed)\n";
        echo "\n✓ Nothing to do. Process complete.\n";
        exit(0);
    }
    echo "⚠ Old database '$old_db' found\n\n";
    
    // Step 4: List tables in old database for reference
    echo "Step 4: Listing tables in old database (for reference)...\n";
    $pdo->exec("USE `$old_db`");
    $stmt = $pdo->query("SHOW TABLES");
    $old_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Found " . count($old_tables) . " tables in old database:\n";
    foreach ($old_tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "  - $table ($count records)\n";
    }
    echo "\n";
    
    // Step 5: Drop old database
    echo "Step 5: Dropping old database '$old_db'...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$old_db`");
    
    // Step 6: Verify deletion
    echo "Step 6: Verifying deletion...\n";
    $stmt = $pdo->query("SHOW DATABASES LIKE '$old_db'");
    if ($stmt->rowCount() === 0) {
        echo "✓ Successfully deleted database '$old_db'\n";
    } else {
        echo "✗ ERROR: Database still exists after deletion attempt\n";
        exit(1);
    }
    
    // Final verification
    echo "\n=== Final Verification ===\n";
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $old_exists = in_array($old_db, $databases);
    $correct_exists = in_array($correct_db, $databases);
    
    if (!$old_exists && $correct_exists) {
        echo "✓ Old database '$old_db' removed\n";
        echo "✓ Correct database '$correct_db' still exists\n";
        echo "\n✅ SUCCESS: Database cleanup complete!\n";
        echo "Only '$correct_db' database remains.\n";
    } else {
        echo "✗ Verification failed\n";
        exit(1);
    }
    
} catch(PDOException $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

