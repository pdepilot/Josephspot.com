<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "joseph_pot_admin";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Timezone setting
date_default_timezone_set('Africa/Lagos');

// Uncomment for debugging
// error_log("Database connected successfully to: $dbname");
?>