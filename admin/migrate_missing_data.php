<?php
/**
 * Migrate Missing Data Script
 * 
 * Compares old and new tables and migrates any missing records
 */

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'joseph_pot_admin';

echo "=== Migrate Missing Data ===\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if both tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'men_manager'");
    $old_exists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'food_menu_manager'");
    $new_exists = $stmt->rowCount() > 0;
    
    if (!$old_exists) {
        echo "✓ Old table 'men_manager' does not exist. Nothing to migrate.\n";
        exit(0);
    }
    
    if (!$new_exists) {
        echo "✗ New table 'food_menu_manager' does not exist!\n";
        exit(1);
    }
    
    // Get all records from old table
    $stmt = $pdo->query("SELECT * FROM men_manager");
    $old_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all records from new table
    $stmt = $pdo->query("SELECT * FROM food_menu_manager");
    $new_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create a map of new records by name (to check for duplicates)
    $new_by_name = [];
    foreach ($new_records as $record) {
        $new_by_name[$record['name']] = $record;
    }
    
    // Find records in old table that don't exist in new table
    $missing = [];
    foreach ($old_records as $old_record) {
        if (!isset($new_by_name[$old_record['name']])) {
            $missing[] = $old_record;
        }
    }
    
    echo "Old table has: " . count($old_records) . " records\n";
    echo "New table has: " . count($new_records) . " records\n";
    echo "Missing records: " . count($missing) . "\n\n";
    
    if (count($missing) > 0) {
        echo "Migrating missing records...\n";
        
        $pdo->beginTransaction();
        
        $insert_sql = "INSERT INTO food_menu_manager 
                       (name, description, category, price, display_price, icon, tags, is_special, is_available, created_at, updated_at)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $pdo->prepare($insert_sql);
        
        $migrated = 0;
        foreach ($missing as $record) {
            try {
                $insert_stmt->execute([
                    $record['name'],
                    $record['description'],
                    $record['category'],
                    $record['price'],
                    $record['display_price'],
                    $record['icon'],
                    $record['tags'],
                    $record['is_special'],
                    $record['is_available'],
                    $record['created_at'],
                    $record['updated_at']
                ]);
                $migrated++;
                echo "  ✓ Migrated: {$record['name']}\n";
            } catch(PDOException $e) {
                echo "  ✗ Failed to migrate: {$record['name']} - {$e->getMessage()}\n";
            }
        }
        
        $pdo->commit();
        
        echo "\n✓ Migrated $migrated records\n";
        
        // Now verify counts
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM food_menu_manager");
        $final_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "New table now has: $final_count records\n";
        
        // Drop old table if counts match or new is greater
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM men_manager");
        $old_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($final_count >= $old_count) {
            echo "\n✓ New table has all records. Dropping old table...\n";
            $pdo->exec("DROP TABLE IF EXISTS `men_manager`");
            echo "✓ Dropped old table 'men_manager'\n";
        }
    } else {
        echo "✓ No missing records. All data already migrated.\n";
        echo "\nDropping old table...\n";
        $pdo->exec("DROP TABLE IF EXISTS `men_manager`");
        echo "✓ Dropped old table 'men_manager'\n";
    }
    
    echo "\n✓ Migration complete!\n";
    
} catch(PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

