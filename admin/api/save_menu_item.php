<?php
// Start output buffering FIRST to catch any errors
ob_start();

// Suppress any output before JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
header('Content-Type: application/json');

// Authentication check
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

// Check if data is coming via POST or JSON
if(!empty($_POST)) {
    $data = $_POST;
    file_put_contents('debug_log.txt', "Data via POST\n", FILE_APPEND);
} else {
    $input = file_get_contents('php://input');
    file_put_contents('debug_log.txt', "Raw input: " . $input . "\n", FILE_APPEND);
    $data = json_decode($input, true);
    if(json_last_error() !== JSON_ERROR_NONE) {
        $data = $_REQUEST;
    }
}

file_put_contents('debug_log.txt', "Processed data: " . print_r($data, true) . "\n", FILE_APPEND);

if(!$data) {
    ob_clean(); // Clear any output
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid input',
        'debug' => 'No data received'
    ]);
    exit;
}

try {
    // Validate required fields
    $required = ['name', 'category', 'price', 'description'];
    $missing = [];
    foreach($required as $field) {
        if(empty($data[$field])) {
            $missing[] = $field;
        }
    }
    
    if(!empty($missing)) {
        ob_clean(); // Clear any output
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: ' . implode(', ', $missing),
            'debug' => $data
        ]);
        exit;
    }
    
    $id = isset($data['id']) ? (int)$data['id'] : null;
    $name = trim($data['name']);
    $category = trim($data['category']);
    $price = (float)$data['price'];
    $display_price = isset($data['displayPrice']) ? trim($data['displayPrice']) : null;
    $description = trim($data['description']);
    $icon = isset($data['icon']) ? trim($data['icon']) : null;
    
    // Handle tags
    if(isset($data['tags'])) {
        if(is_array($data['tags'])) {
            $tags = implode(',', array_map('trim', $data['tags']));
        } else {
            $tags = trim($data['tags']);
        }
    } else {
        $tags = '';
    }
    
    $is_special = isset($data['isSpecial']) ? (int)$data['isSpecial'] : 0;
    $is_available = isset($data['isAvailable']) ? (int)$data['isAvailable'] : 1;
    
    // Log data being saved
    file_put_contents('debug_log.txt', "Data to save:\n", FILE_APPEND);
    file_put_contents('debug_log.txt', "ID: $id\n", FILE_APPEND);
    file_put_contents('debug_log.txt', "Name: $name\n", FILE_APPEND);
    file_put_contents('debug_log.txt', "Category: $category\n", FILE_APPEND);
    file_put_contents('debug_log.txt', "Price: $price\n", FILE_APPEND);
    file_put_contents('debug_log.txt', "Description: $description\n", FILE_APPEND);
    
    if($id) {
        // Update existing item
        $sql = "UPDATE food_menu_manager SET 
                name = ?, 
                category = ?, 
                price = ?, 
                display_price = ?, 
                description = ?, 
                icon = ?, 
                tags = ?, 
                is_special = ?, 
                is_available = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        
        file_put_contents('debug_log.txt', "SQL: $sql\n", FILE_APPEND);
        
        $stmt = $pdo->prepare($sql);
        $params = [
            $name, $category, $price, $display_price, $description, 
            $icon, $tags, $is_special, $is_available, $id
        ];
        
        file_put_contents('debug_log.txt', "Params: " . print_r($params, true) . "\n", FILE_APPEND);
        
        $success = $stmt->execute($params);
        $message = 'Menu item updated successfully';
    } else {
        // Insert new item
        $sql = "INSERT INTO food_menu_manager 
                (name, category, price, display_price, description, icon, tags, is_special, is_available) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        file_put_contents('debug_log.txt', "SQL: $sql\n", FILE_APPEND);
        
        $stmt = $pdo->prepare($sql);
        $params = [
            $name, $category, $price, $display_price, $description, 
            $icon, $tags, $is_special, $is_available
        ];
        
        file_put_contents('debug_log.txt', "Params: " . print_r($params, true) . "\n", FILE_APPEND);
        
        $success = $stmt->execute($params);
        $id = $pdo->lastInsertId();
        $message = 'Menu item added successfully';
    }
    
    if($success) {
        ob_clean(); // Clear any output before JSON
        echo json_encode([
            'success' => true,
            'message' => $message,
            'id' => $id
        ]);
    } else {
        $errorInfo = $stmt->errorInfo();
        ob_clean(); // Clear any output
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save menu item',
            'error' => $errorInfo
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