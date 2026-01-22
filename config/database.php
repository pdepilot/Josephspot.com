<?php
/**
 * Database Configuration (mysqli-based)
 * 
 * NOTE: This file may be unused. The project primarily uses:
 * - db_connection.php (PDO-based, used by analytics and APIs)
 * - includes/Database.php (PDO-based, uses config/database_config.php)
 * 
 * If getDBConnection() is not referenced anywhere, this file can be removed.
 */

// Database Configuration for Joseph's Pot
define('DB_HOST', 'localhost');
define('DB_USER', 'your_database_username');
define('DB_PASS', 'your_database_password');
define('DB_NAME', 'joseph_pot_admin');

// Create connection function
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return false;
    }
    
    return $conn;
}

// Test database connection (optional)
function testDatabaseConnection() {
    $conn = getDBConnection();
    if ($conn) {
        echo "Database connection successful!";
        $conn->close();
        return true;
    } else {
        echo "Database connection failed!";
        return false;
    }
}

// To test database connection, call testDatabaseConnection() manually
// testDatabaseConnection();
?>