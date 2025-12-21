<?php
// Verify men_manager table exists and show structure
$host = 'localhost';
$dbname = 'joseph_pot_admin';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'food_menu_manager'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Table 'food_menu_manager' exists!\n\n";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE food_menu_manager");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Table structure:\n";
        echo str_repeat("-", 60) . "\n";
        printf("%-20s %-15s %-10s\n", "Column", "Type", "Null");
        echo str_repeat("-", 60) . "\n";
        foreach($columns as $col) {
            printf("%-20s %-15s %-10s\n", $col['Field'], $col['Type'], $col['Null']);
        }
        
        // Count records
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM food_menu_manager");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\nTotal records: " . $count['count'] . "\n";
    } else {
        echo "✗ Table 'food_menu_manager' does NOT exist!\n";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

