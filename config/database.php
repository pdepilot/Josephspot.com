<?php
// config/database.php

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

// Uncomment the line below to test database connection
testDatabaseConnection();
?>