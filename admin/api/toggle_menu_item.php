<?php
// Start output buffering FIRST to catch any errors
ob_start();

// API Authentication and Permission Check
require_once __DIR__ . '/../api-auth.php';
requireAPIPermission('menu_management', 'edit'); // Require edit permission for menu management

header('Content-Type: application/json');

// Database connection - create connection directly to avoid die() issues
try {
    $host = 'localhost';
    $dbname = 'joseph_pot_admin';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    ob_clean(); // Clear any output
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if(!isset($data['id'])) {
    ob_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

try {
    $id = (int)$data['id'];
    
    // First get current status
    $sql = "SELECT is_available FROM food_menu_manager WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $current = $stmt->fetch();
    
    if(!$current) {
        ob_clean(); // Clear any output
        echo json_encode([
            'success' => false,
            'message' => 'Item not found'
        ]);
        exit;
    }
    
    // Toggle availability
    $new_status = $current['is_available'] ? 0 : 1;
    
    $sql = "UPDATE food_menu_manager SET is_available = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([$new_status, $id]);
    
    if($success) {
        ob_clean(); // Clear any output before JSON
        echo json_encode([
            'success' => true,
            'message' => 'Menu item availability updated successfully',
            'isAvailable' => (bool)$new_status
        ]);
    } else {
        ob_clean(); // Clear any output
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update availability'
        ]);
    }
    
} catch(PDOException $e) {
    ob_clean(); // Clear any output
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>

