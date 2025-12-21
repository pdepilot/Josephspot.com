<?php
// Verify gallery table exists and show structure
$host = 'localhost';
$dbname = 'joseph_pot_admin';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'gallery'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Table 'gallery' exists!\n\n";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE gallery");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Table structure:\n";
        echo str_repeat("-", 60) . "\n";
        printf("%-20s %-20s %-10s\n", "Column", "Type", "Null");
        echo str_repeat("-", 60) . "\n";
        foreach($columns as $col) {
            printf("%-20s %-20s %-10s\n", $col['Field'], $col['Type'], $col['Null']);
        }
        
        // Count records
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\nTotal records: " . $count['count'] . "\n";
    } else {
        echo "✗ Table 'gallery' does NOT exist!\n";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

