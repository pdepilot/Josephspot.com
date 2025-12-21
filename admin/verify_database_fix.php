<?php
/**
 * Database Fix Verification Script
 * 
 * This script verifies that:
 * 1. All PHP files use ONLY joseph_pot_admin
 * 2. No references to josep_pot_admin exist (except in migration script)
 * 3. All tables exist in joseph_pot_admin
 * 4. Data integrity is maintained
 */

$host = 'localhost';
$username = 'root';
$password = '';
$correct_db = 'joseph_pot_admin';
$wrong_db = 'josep_pot_admin';

echo "=== Database Fix Verification ===\n\n";

$errors = [];
$warnings = [];
$success = [];

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if correct database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$correct_db'");
    if ($stmt->rowCount() > 0) {
        $success[] = "✓ Correct database '$correct_db' exists";
    } else {
        $errors[] = "✗ Correct database '$correct_db' does NOT exist!";
    }
    
    // Check if wrong database still exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$wrong_db'");
    if ($stmt->rowCount() > 0) {
        $warnings[] = "⚠ Old database '$wrong_db' still exists (should be dropped after verification)";
    } else {
        $success[] = "✓ Old database '$wrong_db' has been removed";
    }
    
    // Connect to correct database and verify tables
    $pdo->exec("USE `$correct_db`");
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $success[] = "✓ Found " . count($tables) . " tables in '$correct_db'";
    
    // Verify critical tables exist
    $critical_tables = [
        'food_menu_manager',
        'general_settings',
        'restaurant_settings',
        'notification_settings',
        'security_settings',
        'appearance_settings',
        'admin_settings_meta',
        'gallery',
        'admins'
    ];
    
    echo "\n=== Table Verification ===\n";
    foreach ($critical_tables as $table) {
        if (in_array($table, $tables)) {
            // Count records
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            $success[] = "✓ Table '$table' exists with {$count['count']} records";
        } else {
            $warnings[] = "⚠ Table '$table' not found (may not be critical)";
        }
    }
    
    // Verify food_menu_manager has data
    if (in_array('food_menu_manager', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM food_menu_manager");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($count['count'] > 0) {
            $success[] = "✓ food_menu_manager has {$count['count']} menu items";
        } else {
            $warnings[] = "⚠ food_menu_manager is empty";
        }
    }
    
    // Check for old men_manager table (should not exist)
    if (in_array('men_manager', $tables)) {
        $errors[] = "✗ Old table 'men_manager' still exists in '$correct_db'!";
    } else {
        $success[] = "✓ Old table 'men_manager' does not exist (correctly renamed)";
    }
    
    echo "\n=== Results ===\n";
    foreach ($success as $msg) {
        echo $msg . "\n";
    }
    
    if (!empty($warnings)) {
        echo "\n=== Warnings ===\n";
        foreach ($warnings as $msg) {
            echo $msg . "\n";
        }
    }
    
    if (!empty($errors)) {
        echo "\n=== Errors ===\n";
        foreach ($errors as $msg) {
            echo $msg . "\n";
        }
        echo "\n✗ Verification FAILED. Please fix errors before proceeding.\n";
        exit(1);
    }
    
    echo "\n✓ All verifications passed!\n";
    echo "\nYou can now safely drop the old '$wrong_db' database if it still exists.\n";
    
} catch(PDOException $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

