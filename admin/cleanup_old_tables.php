<?php
/**
 * Cleanup Old Tables Script
 * 
 * Removes old table names that have been renamed in the correct database
 */

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'joseph_pot_admin';

echo "=== Cleanup Old Tables ===\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if old men_manager table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'men_manager'");
    if ($stmt->rowCount() > 0) {
        // Check if food_menu_manager exists (the new table)
        $stmt = $pdo->query("SHOW TABLES LIKE 'food_menu_manager'");
        if ($stmt->rowCount() > 0) {
            // Count records in both tables
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM men_manager");
            $old_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM food_menu_manager");
            $new_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            echo "Old table 'men_manager' has $old_count records\n";
            echo "New table 'food_menu_manager' has $new_count records\n\n";
            
            if ($new_count >= $old_count) {
                echo "✓ New table has equal or more records. Safe to drop old table.\n";
                $pdo->exec("DROP TABLE IF EXISTS `men_manager`");
                echo "✓ Dropped old table 'men_manager'\n";
            } else {
                echo "⚠ WARNING: New table has fewer records. Not dropping old table.\n";
                echo "Please verify data migration before proceeding.\n";
            }
        } else {
            echo "⚠ New table 'food_menu_manager' does not exist!\n";
            echo "Cannot safely drop old table. Aborting.\n";
            exit(1);
        }
    } else {
        echo "✓ Old table 'men_manager' does not exist (already cleaned up)\n";
    }
    
    echo "\n✓ Cleanup complete!\n";
    
} catch(PDOException $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

