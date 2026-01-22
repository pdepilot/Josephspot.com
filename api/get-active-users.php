<?php
/**
 * Get Active Users API Endpoint
 * Returns count of active users (last 5 minutes)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../includes/analytics-functions.php';

try {
    $activeUsers = getActiveUsers();
    
    echo json_encode([
        'success' => true,
        'activeUsers' => $activeUsers,
        'timestamp' => time()
    ]);
} catch (Exception $e) {
    error_log("Error in get-active-users.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'activeUsers' => 0
    ]);
}
