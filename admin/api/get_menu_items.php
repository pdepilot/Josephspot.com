<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../db_connection.php';

try {
    $sql = "SELECT * FROM food_menu_manager ORDER BY category, name";
    $stmt = $pdo->query($sql);
    $menu_items = $stmt->fetchAll();
    
    // Format the data for frontend
    $formatted_items = [];
    foreach($menu_items as $item) {
        // Convert tags string to array
        $tags = !empty($item['tags']) ? explode(',', $item['tags']) : [];
        
        $formatted_items[] = [
            'id' => (int)$item['id'],
            'name' => $item['name'],
            'description' => $item['description'],
            'category' => $item['category'],
            'price' => (float)$item['price'],
            'displayPrice' => $item['display_price'] ?: '₦' . number_format($item['price']),
            'icon' => $item['icon'],
            'tags' => $tags,
            'isSpecial' => (bool)$item['is_special'],
            'isAvailable' => (bool)$item['is_available']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formatted_items,
        'count' => count($formatted_items)
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>