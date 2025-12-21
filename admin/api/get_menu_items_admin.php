<?php
// Start output buffering FIRST to catch any errors
ob_start();

// Suppress any output before JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
header('Content-Type: application/json');

// Add authentication check
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

try {
    $category = isset($_GET['category']) && $_GET['category'] != 'all' ? $_GET['category'] : null;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    $sql = "SELECT * FROM food_menu_manager WHERE 1=1";
    $params = [];
    
    if($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    
    if($search) {
        $sql .= " AND (name LIKE ? OR description LIKE ? OR tags LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $menu_items = $stmt->fetchAll();
    
    // Format data for frontend (convert snake_case to camelCase)
    $formatted_items = [];
    foreach($menu_items as $item) {
        // Convert tags string to array
        $tags = !empty($item['tags']) ? array_map('trim', explode(',', $item['tags'])) : [];
        
        $formatted_items[] = [
            'id' => (int)$item['id'],
            'name' => $item['name'],
            'description' => $item['description'],
            'category' => $item['category'],
            'price' => (float)$item['price'],
            'displayPrice' => $item['display_price'] ?: '₦' . number_format($item['price']),
            'icon' => $item['icon'] ?: '',
            'tags' => $tags,
            'isSpecial' => (bool)$item['is_special'],
            'isAvailable' => (bool)$item['is_available']
        ];
    }
    
    ob_clean(); // Clear any output before JSON
    echo json_encode([
        'success' => true,
        'data' => $formatted_items,
        'count' => count($formatted_items)
    ]);
    
} catch(PDOException $e) {
    ob_clean(); // Clear any output
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}