<?php
/**
 * Database Migration Script
 * 
 * This script will:
 * 1. Create joseph_pot_admin database if it doesn't exist
 * 2. Transfer all data from josep_pot_admin to joseph_pot_admin
 * 3. Rename men_manager table to food_menu_manager
 * 
 * Run this script ONCE to migrate your database.
 */

$host = 'localhost';
$username = 'root';
$password = '';
$source_db = 'josep_pot_admin';
$target_db = 'joseph_pot_admin';

echo "=== Database Migration Script ===\n\n";

try {
    // Connect to MySQL server (without database)
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to MySQL server\n";
    
    // Check if source database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$source_db'");
    $source_exists = $stmt->rowCount() > 0;
    
    if (!$source_exists) {
        echo "⚠ Source database '$source_db' does not exist. Skipping migration.\n";
        exit;
    }
    
    echo "✓ Source database '$source_db' found\n";
    
    // Create target database if it doesn't exist
    $stmt = $pdo->query("SHOW DATABASES LIKE '$target_db'");
    $target_exists = $stmt->rowCount() > 0;
    
    if (!$target_exists) {
        $pdo->exec("CREATE DATABASE `$target_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        echo "✓ Created target database '$target_db'\n";
    } else {
        echo "✓ Target database '$target_db' already exists\n";
    }
    
    // Connect to source database
    $source_pdo = new PDO("mysql:host=$host;dbname=$source_db;charset=utf8mb4", $username, $password);
    $source_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Connect to target database
    $target_pdo = new PDO("mysql:host=$host;dbname=$target_db;charset=utf8mb4", $username, $password);
    $target_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all tables from source database
    $stmt = $source_pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n=== Migrating Tables ===\n";
    
    foreach ($tables as $table) {
        echo "\nProcessing table: $table\n";
        
        // Get table structure
        $stmt = $source_pdo->query("SHOW CREATE TABLE `$table`");
        $create_table = $stmt->fetch(PDO::FETCH_ASSOC);
        $create_sql = $create_table['Create Table'];
        
        // Rename men_manager to food_menu_manager in CREATE statement
        if ($table === 'men_manager') {
            $create_sql = str_replace('`men_manager`', '`food_menu_manager`', $create_sql);
            $table = 'food_menu_manager';
        }
        
        // Drop table if exists in target
        try {
            $target_pdo->exec("DROP TABLE IF EXISTS `$table`");
        } catch(PDOException $e) {
            // Ignore if table doesn't exist
        }
        
        // Create table in target
        $target_pdo->exec($create_sql);
        echo "  ✓ Created table structure: $table\n";
        
        // Get data from source table
        $old_table = ($table === 'food_menu_manager') ? 'men_manager' : $table;
        $stmt = $source_pdo->query("SELECT * FROM `$old_table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($rows) > 0) {
            // Get column names
            $columns = array_keys($rows[0]);
            $column_list = '`' . implode('`, `', $columns) . '`';
            $placeholders = '(' . str_repeat('?,', count($columns) - 1) . '?)';
            
            // Insert data into target table
            $insert_sql = "INSERT INTO `$table` ($column_list) VALUES $placeholders";
            $insert_stmt = $target_pdo->prepare($insert_sql);
            
            $target_pdo->beginTransaction();
            foreach ($rows as $row) {
                $insert_stmt->execute(array_values($row));
            }
            $target_pdo->commit();
            
            echo "  ✓ Migrated " . count($rows) . " rows\n";
        } else {
            echo "  ✓ Table is empty (no data to migrate)\n";
        }
    }
    
    echo "\n=== Migration Complete ===\n";
    echo "✓ All data has been migrated from '$source_db' to '$target_db'\n";
    echo "✓ Table 'men_manager' has been renamed to 'food_menu_manager'\n";
    echo "\nYou can now update your PHP files to use '$target_db' and 'food_menu_manager'.\n";
    echo "After verifying everything works, you can drop the old '$source_db' database.\n";
    
} catch(PDOException $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

