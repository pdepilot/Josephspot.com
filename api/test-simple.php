<?php
// api/test-simple.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

$host = "localhost";
$dbname = "joseph_pot_admin";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Simple test query
    $stmt = $pdo->query("SELECT id, customer_name, rating, status FROM reviews LIMIT 5");
    $reviews = $stmt->fetchAll();
    
    echo json_encode([
        "success" => true,
        "message" => "Database connection successful",
        "count" => count($reviews),
        "data" => $reviews
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error",
        "error" => $e->getMessage()
    ]);
}
?>