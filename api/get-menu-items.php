<?php
/**
 * API Endpoint: Get Menu Items
 * Returns menu items for order-online.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../admin/includes/db_connection.php';

try {
    // Get all available menu items (is_available = 1)
    // Note: Items marked as "out of stock" in admin won't appear here
    $sql = "SELECT 
                id,
                name,
                description,
                price,
                category,
                image_url,
                is_available
            FROM online_menu_manager
            WHERE is_available = 1
            ORDER BY category, name";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Log total items in database (for debugging)
    $countSql = "SELECT COUNT(*) as total FROM online_menu_manager";
    $countStmt = $pdo->query($countSql);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $availableCount = count($items);
    
    // Transform to match frontend format
    $menuItems = [];
    foreach ($items as $item) {
        // Normalize category values to match frontend expectations
        $category = trim($item['category'] ?? '');
        if (empty($category)) {
            $category = 'main courses';
        }
        // Map "main" to "main courses" for compatibility
        if (strtolower($category) === 'main') {
            $category = 'main courses';
        }
        
        // Handle image URL - if it's a data URL (base64) or a relative path, use it as-is
        $imageUrl = trim($item['image_url'] ?? '');
        if (empty($imageUrl)) {
            $imageUrl = './images/default-food.jpg';
        } elseif (!preg_match('/^(https?:\/\/|data:|\.\/)/', $imageUrl)) {
            // If it's not a full URL, data URL, or relative path, make it relative
            $imageUrl = './' . ltrim($imageUrl, '/');
        }
        
        $menuItems[] = [
            'id' => (int)$item['id'],
            'title' => $item['name'] ?? '',
            'description' => $item['description'] ?? '',
            'price' => floatval($item['price'] ?? 0),
            'category' => $category,
            'image' => $imageUrl,
            'available' => (bool)($item['is_available'] ?? false)
        ];
    }
    
    echo json_encode([
        'success' => true,
        'items' => $menuItems,
        'count' => count($menuItems),
        'debug' => [
            'total_items_in_db' => (int)$totalCount,
            'available_items' => $availableCount,
            'message' => $availableCount == 0 ? 'No available items found. Make sure items are marked as "Available" in admin panel.' : ''
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>

