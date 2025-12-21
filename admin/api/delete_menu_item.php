<?php
// Start output buffering FIRST to catch any errors
ob_start();

session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    ob_clean(); // Clear any output
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

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
    
    $sql = "DELETE FROM food_menu_manager WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([$id]);
    
    if($success && $stmt->rowCount() > 0) {
        ob_clean(); // Clear any output before JSON
        echo json_encode([
            'success' => true,
            'message' => 'Menu item deleted successfully'
        ]);
    } else {
        ob_clean(); // Clear any output
        echo json_encode([
            'success' => false,
            'message' => 'Item not found or already deleted'
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