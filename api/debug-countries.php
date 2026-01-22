<?php
/**
 * Debug endpoint to check country data in database
 * SECURITY: Only accessible to authenticated admins
 * TODO: Remove this file in production or add proper authentication
 */

// Require admin authentication
require_once __DIR__ . '/../admin/admin-auth.php';
checkPageAccess();

header('Content-Type: application/json');
require_once __DIR__ . '/../db_connection.php';

global $pdo;
if (!$pdo) {
    echo json_encode(['error' => 'No database connection']);
    exit;
}

try {
    // Get all countries (including Unknown/Local)
    $sql = "SELECT 
                country,
                country_code,
                COUNT(DISTINCT session_id) as visitors,
                COUNT(*) as page_views
            FROM page_views
            WHERE device_type != 'bot'
            GROUP BY country, country_code
            ORDER BY visitors DESC
            LIMIT 20";
    
    $stmt = $pdo->query($sql);
    $allCountries = $stmt->fetchAll();
    
    // Get sample records
    $sqlSample = "SELECT country, country_code, ip_address, created_at 
                  FROM page_views 
                  ORDER BY created_at DESC 
                  LIMIT 10";
    $stmtSample = $pdo->query($sqlSample);
    $samples = $stmtSample->fetchAll();
    
    echo json_encode([
        'success' => true,
        'all_countries' => $allCountries,
        'sample_records' => $samples,
        'total_records' => $pdo->query("SELECT COUNT(*) as total FROM page_views")->fetch()['total']
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
