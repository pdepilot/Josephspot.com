<?php
// Script to create men_manager table
// Run this file once to create the table

// Database connection - create connection directly
$host = 'localhost';
$dbname = 'joseph_pot_admin';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$sql = "CREATE TABLE IF NOT EXISTS `food_menu_manager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `display_price` varchar(50) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `is_special` tinyint(1) DEFAULT 0,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_is_available` (`is_available`),
  KEY `idx_is_special` (`is_special`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

try {
    $pdo->exec($sql);
    echo "Table 'food_menu_manager' created successfully!";
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>

