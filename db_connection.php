<?php
// db_connection.php
// Include safety check to prevent wrong database usage
require_once __DIR__ . '/admin/database_safety_check.php';

$host = 'localhost';
$dbname = 'joseph_pot_admin'; // ONLY valid database name
$username = 'root'; // Change this to your database username
$password = ''; // Change this to your database password

// Safety check: Validate database name
validateDatabaseName($dbname);

try {
    $pdo = getSafePDOConnection($host, $dbname, $username, $password);
} catch(Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>