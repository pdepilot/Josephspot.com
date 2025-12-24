<?php
/**
 * API Endpoint: Menu Items CRUD
 * Handles Create, Read, Update, Delete operations for menu items
 */

session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../includes/db_connection.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Get all menu items
            $sql = "SELECT * FROM online_menu_manager ORDER BY category, name";
            $stmt = $pdo->query($sql);
            $items = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'items' => $items
            ]);
            break;
            
        case 'POST':
            // Create new menu item
            $input = json_decode(file_get_contents('php://input'), true);
            
            $name = trim($input['name'] ?? '');
            $description = trim($input['description'] ?? '');
            $price = floatval($input['price'] ?? 0);
            $category = trim($input['category'] ?? 'main');
            $image_url = trim($input['image_url'] ?? '');
            $is_available = isset($input['is_available']) ? (int)$input['is_available'] : 1;
            
            if (empty($name) || $price <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Name and price are required']);
                exit;
            }
            
            $sql = "INSERT INTO online_menu_manager (name, description, price, category, image_url, is_available) 
                    VALUES (:name, :description, :price, :category, :image_url, :is_available)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':category' => $category,
                ':image_url' => $image_url,
                ':is_available' => $is_available
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Menu item created successfully',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'PUT':
            // Update menu item
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
                exit;
            }
            
            $name = trim($input['name'] ?? '');
            $description = trim($input['description'] ?? '');
            $price = floatval($input['price'] ?? 0);
            $category = trim($input['category'] ?? 'main');
            $image_url = trim($input['image_url'] ?? '');
            $is_available = isset($input['is_available']) ? (int)$input['is_available'] : 1;
            
            $sql = "UPDATE online_menu_manager 
                    SET name = :name, description = :description, price = :price, 
                        category = :category, image_url = :image_url, is_available = :is_available
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':category' => $category,
                ':image_url' => $image_url,
                ':is_available' => $is_available
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Menu item updated successfully'
            ]);
            break;
            
        case 'DELETE':
            // Delete menu item
            $id = intval($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
                exit;
            }
            
            $sql = "DELETE FROM online_menu_manager WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Menu item deleted successfully'
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>

