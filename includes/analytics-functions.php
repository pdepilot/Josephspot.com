<?php
/**
 * Analytics Helper Functions
 * Provides functions for querying analytics data from the database
 */

require_once __DIR__ . '/../db_connection.php';

// Ensure $pdo is available in global scope
if (!isset($pdo)) {
    // If $pdo is not set, create connection directly
    require_once __DIR__ . '/../admin/database_safety_check.php';
    $host = 'localhost';
    $dbname = 'joseph_pot_admin';
    $username = 'root';
    $password = '';
    validateDatabaseName($dbname);
    try {
        $pdo = getSafePDOConnection($host, $dbname, $username, $password);
    } catch(Exception $e) {
        error_log("Analytics functions: Database connection failed: " . $e->getMessage());
        $pdo = null;
    }
}

/**
 * Get visitors over time (aggregated by hour or day)
 * @param string $period 'hour' or 'day'
 * @param int $days Number of days to retrieve
 * @return array
 */
function getVisitorsOverTime($period = 'day', $days = 30) {
    global $pdo;
    if (!$pdo) {
        return [];
    }
    try {
        if ($period === 'hour') {
            $sql = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as time_period,
                        COUNT(DISTINCT session_id) as visitors,
                        COUNT(*) as page_views
                    FROM page_views
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                        AND device_type != 'bot'
                    GROUP BY time_period
                    ORDER BY time_period ASC";
        } else {
            $sql = "SELECT 
                        DATE(created_at) as time_period,
                        COUNT(DISTINCT session_id) as visitors,
                        COUNT(*) as page_views
                    FROM page_views
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                        AND device_type != 'bot'
                    GROUP BY time_period
                    ORDER BY time_period ASC";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting visitors over time: " . $e->getMessage());
        return [];
    }
}

/**
 * Get traffic sources (referrers)
 * @param int $limit Number of sources to return
 * @return array
 */
function getTrafficSources($limit = 10) {
    global $pdo;
    if (!$pdo) {
        return [];
    }
    try {
        $sql = "SELECT 
                    CASE 
                        WHEN referrer IS NULL OR referrer = '' THEN 'Direct'
                        WHEN referrer LIKE '%google%' OR referrer LIKE '%bing%' OR referrer LIKE '%yahoo%' THEN 'Search Engine'
                        WHEN referrer LIKE '%facebook%' OR referrer LIKE '%twitter%' OR referrer LIKE '%instagram%' OR referrer LIKE '%linkedin%' THEN 'Social Media'
                        ELSE 'Referral'
                    END as source,
                    COUNT(*) as visits,
                    COUNT(DISTINCT session_id) as unique_visitors
                FROM page_views
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    AND device_type != 'bot'
                GROUP BY source
                ORDER BY visits DESC
                LIMIT ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting traffic sources: " . $e->getMessage());
        return [];
    }
}

/**
 * Get most visited pages
 * @param int $limit Number of pages to return
 * @return array
 */
function getTopPages($limit = 10) {
    global $pdo;
    if (!$pdo) {
        return [];
    }
    try {
        $sql = "SELECT 
                    page_url,
                    COUNT(*) as visits,
                    COUNT(DISTINCT session_id) as unique_visitors
                FROM page_views
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    AND device_type != 'bot'
                GROUP BY page_url
                ORDER BY visits DESC
                LIMIT ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting top pages: " . $e->getMessage());
        return [];
    }
}

/**
 * Get top countries
 * @param int $limit Number of countries to return
 * @return array
 */
function getTopCountries($limit = 10) {
    global $pdo;
    if (!$pdo) {
        return [];
    }
    try {
        // First try to get countries excluding Unknown/Local
        $sql = "SELECT 
                    country,
                    country_code,
                    COUNT(DISTINCT session_id) as visitors,
                    COUNT(*) as page_views
                FROM page_views
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    AND device_type != 'bot'
                    AND country IS NOT NULL
                    AND country != ''
                    AND country != 'Unknown'
                    AND country != 'Local'
                GROUP BY country, country_code
                ORDER BY visitors DESC
                LIMIT ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        $results = $stmt->fetchAll();
        
        // If no results, try including all countries (for debugging)
        if (empty($results)) {
            $sqlAll = "SELECT 
                        country,
                        country_code,
                        COUNT(DISTINCT session_id) as visitors,
                        COUNT(*) as page_views
                    FROM page_views
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                        AND device_type != 'bot'
                        AND country IS NOT NULL
                        AND country != ''
                    GROUP BY country, country_code
                    ORDER BY visitors DESC
                    LIMIT ?";
            
            $stmtAll = $pdo->prepare($sqlAll);
            $stmtAll->execute([$limit]);
            $results = $stmtAll->fetchAll();
        }
        
        return $results;
    } catch (PDOException $e) {
        error_log("Error getting top countries: " . $e->getMessage());
        return [];
    }
}

/**
 * Get device type breakdown
 * @return array
 */
function getDeviceTypes() {
    global $pdo;
    if (!$pdo) {
        return [];
    }
    try {
        $sql = "SELECT 
                    device_type,
                    COUNT(*) as visits,
                    COUNT(DISTINCT session_id) as unique_visitors
                FROM page_views
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    AND device_type != 'bot'
                GROUP BY device_type
                ORDER BY visits DESC";
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting device types: " . $e->getMessage());
        return [];
    }
}

/**
 * Get browser usage statistics
 * @param int $limit Number of browsers to return
 * @return array
 */
function getBrowserUsage($limit = 10) {
    global $pdo;
    if (!$pdo) {
        return [];
    }
    try {
        $sql = "SELECT 
                    browser,
                    COUNT(*) as users,
                    COUNT(DISTINCT session_id) as unique_users
                FROM page_views
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    AND device_type != 'bot'
                    AND browser != 'Unknown'
                GROUP BY browser
                ORDER BY users DESC
                LIMIT ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting browser usage: " . $e->getMessage());
        return [];
    }
}

/**
 * Get real-time active users (last 5 minutes)
 * @return int
 */
function getActiveUsers() {
    global $pdo;
    if (!$pdo) {
        return 0;
    }
    try {
        $sql = "SELECT COUNT(DISTINCT session_id) as active_users
                FROM page_views
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                    AND device_type != 'bot'";
        
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch();
        return (int)($result['active_users'] ?? 0);
    } catch (PDOException $e) {
        error_log("Error getting active users: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get total sessions for a period
 * @param int $days Number of days
 * @return int
 */
function getTotalSessions($days = 30) {
    global $pdo;
    if (!$pdo) {
        return 0;
    }
    try {
        $sql = "SELECT COUNT(DISTINCT session_id) as total_sessions
                FROM page_views
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    AND device_type != 'bot'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$days]);
        $result = $stmt->fetch();
        return (int)($result['total_sessions'] ?? 0);
    } catch (PDOException $e) {
        error_log("Error getting total sessions: " . $e->getMessage());
        return 0;
    }
}
