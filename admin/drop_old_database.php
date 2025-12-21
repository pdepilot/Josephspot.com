<?php
/**
 * Drop Old Database Script
 * 
 * WARNING: This script will PERMANENTLY DELETE the old database.
 * Only run this AFTER verifying that:
 * 1. All data has been migrated to joseph_pot_admin
 * 2. All PHP files use joseph_pot_admin
 * 3. The application works correctly
 * 
 * This script requires explicit confirmation.
 */

$host = 'localhost';
$username = 'root';
$password = '';
$old_db = 'josep_pot_admin';
$correct_db = 'joseph_pot_admin';

echo "=== Drop Old Database Script ===\n\n";
echo "WARNING: This will PERMANENTLY DELETE the database '$old_db'\n";
echo "Make sure you have:\n";
echo "  1. Verified all data is in '$correct_db'\n";
echo "  2. Tested the application works\n";
echo "  3. Backed up if needed\n\n";

// Require explicit confirmation
echo "Type 'DELETE' (all caps) to confirm: ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if ($line !== 'DELETE') {
    echo "\n✗ Operation cancelled. Database not deleted.\n";
    exit(0);
}

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verify correct database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$correct_db'");
    if ($stmt->rowCount() === 0) {
        echo "\n✗ ERROR: Correct database '$correct_db' does not exist!\n";
        echo "Cannot proceed. Aborting.\n";
        exit(1);
    }
    
    echo "\n✓ Verified '$correct_db' exists\n";
    
    // Check if old database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$old_db'");
    if ($stmt->rowCount() === 0) {
        echo "✓ Old database '$old_db' does not exist (already removed)\n";
        exit(0);
    }
    
    // Final confirmation
    echo "\n⚠ FINAL WARNING: About to delete '$old_db'\n";
    echo "Type 'YES DELETE' to proceed: ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    
    if ($line !== 'YES DELETE') {
        echo "\n✗ Operation cancelled. Database not deleted.\n";
        exit(0);
    }
    
    // Drop the database
    $pdo->exec("DROP DATABASE IF EXISTS `$old_db`");
    
    // Verify it's gone
    $stmt = $pdo->query("SHOW DATABASES LIKE '$old_db'");
    if ($stmt->rowCount() === 0) {
        echo "\n✓ Successfully deleted database '$old_db'\n";
        echo "✓ Migration complete. Only '$correct_db' remains.\n";
    } else {
        echo "\n✗ Error: Database still exists after deletion attempt\n";
        exit(1);
    }
    
} catch(PDOException $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

