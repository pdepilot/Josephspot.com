<?php
/**
 * Analytics Tracking Endpoint
 * Receives page view data from frontend and stores it in the database
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Database connection
require_once __DIR__ . '/../db_connection.php';

/**
 * Get client IP address (handles proxies and load balancers)
 */
function getClientIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Get country from IP address using free GeoIP service
 */
function getCountryFromIP($ip) {
    // Skip private/local IPs
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return ['country' => 'Local', 'country_code' => 'XX'];
    }
    
    try {
        // Using ip-api.com (free, no API key required, 45 requests/minute limit)
        $url = "http://ip-api.com/json/{$ip}?fields=status,country,countryCode";
        $context = stream_context_create([
            'http' => [
                'timeout' => 2,
                'method' => 'GET'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['status']) && $data['status'] === 'success') {
                return [
                    'country' => $data['country'] ?? 'Unknown',
                    'country_code' => $data['countryCode'] ?? 'XX'
                ];
            }
        }
    } catch (Exception $e) {
        error_log("GeoIP lookup failed: " . $e->getMessage());
    }
    
    return ['country' => 'Unknown', 'country_code' => 'XX'];
}

/**
 * Detect device type from user agent
 */
function detectDevice($userAgent) {
    if (empty($userAgent)) {
        return 'unknown';
    }
    
    $ua = strtolower($userAgent);
    
    // Bot detection
    if (preg_match('/bot|crawler|spider|crawling/i', $ua)) {
        return 'bot';
    }
    
    // Mobile detection
    if (preg_match('/mobile|android|iphone|ipod|blackberry|opera mini|iemobile|wpdesktop/i', $ua)) {
        // Check for tablet
        if (preg_match('/tablet|ipad|playbook|silk/i', $ua)) {
            return 'tablet';
        }
        return 'mobile';
    }
    
    // Tablet detection
    if (preg_match('/tablet|ipad|playbook|silk/i', $ua)) {
        return 'tablet';
    }
    
    return 'desktop';
}

/**
 * Parse browser from user agent
 */
function parseBrowser($userAgent) {
    if (empty($userAgent)) {
        return ['browser' => 'Unknown', 'version' => '', 'os' => 'Unknown'];
    }
    
    $ua = strtolower($userAgent);
    $browser = 'Unknown';
    $version = '';
    $os = 'Unknown';
    
    // Browser detection
    if (preg_match('/edg\/([0-9.]+)/i', $userAgent, $matches)) {
        $browser = 'Edge';
        $version = $matches[1];
    } elseif (preg_match('/chrome\/([0-9.]+)/i', $userAgent, $matches)) {
        $browser = 'Chrome';
        $version = $matches[1];
    } elseif (preg_match('/safari\/([0-9.]+)/i', $userAgent, $matches) && !preg_match('/chrome/i', $userAgent)) {
        $browser = 'Safari';
        $version = $matches[1];
    } elseif (preg_match('/firefox\/([0-9.]+)/i', $userAgent, $matches)) {
        $browser = 'Firefox';
        $version = $matches[1];
    } elseif (preg_match('/msie|trident/i', $userAgent)) {
        $browser = 'IE';
        if (preg_match('/rv:([0-9.]+)/i', $userAgent, $matches)) {
            $version = $matches[1];
        }
    } elseif (preg_match('/opera|opr\/([0-9.]+)/i', $userAgent, $matches)) {
        $browser = 'Opera';
        $version = isset($matches[1]) ? $matches[1] : '';
    }
    
    // OS detection
    if (preg_match('/windows nt ([0-9.]+)/i', $userAgent, $matches)) {
        $os = 'Windows ' . $matches[1];
    } elseif (preg_match('/mac os x ([0-9_]+)/i', $userAgent, $matches)) {
        $os = 'macOS';
    } elseif (preg_match('/linux/i', $userAgent)) {
        $os = 'Linux';
    } elseif (preg_match('/android ([0-9.]+)/i', $userAgent, $matches)) {
        $os = 'Android ' . $matches[1];
    } elseif (preg_match('/iphone os ([0-9_]+)/i', $userAgent, $matches)) {
        $os = 'iOS ' . str_replace('_', '.', $matches[1]);
    } elseif (preg_match('/ipad/i', $userAgent)) {
        $os = 'iPadOS';
    }
    
    return [
        'browser' => $browser,
        'version' => $version,
        'os' => $os
    ];
}

/**
 * Generate or retrieve session ID
 */
function getSessionId() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['analytics_session_id'])) {
        $_SESSION['analytics_session_id'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['analytics_session_id'];
}

// Start session for tracking
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate required fields
    if (empty($data['page_url'])) {
        throw new Exception('page_url is required');
    }
    
    // Sanitize and validate input
    $pageUrl = filter_var($data['page_url'], FILTER_SANITIZE_URL);
    if (!filter_var($pageUrl, FILTER_VALIDATE_URL) && !preg_match('/^\/[^\/]/', $pageUrl)) {
        // Allow relative URLs starting with /
        if (!preg_match('/^\/[^\/]/', $pageUrl)) {
            throw new Exception('Invalid page URL');
        }
    }
    
    $pageTitle = isset($data['page_title']) ? substr(htmlspecialchars($data['page_title'], ENT_QUOTES, 'UTF-8'), 0, 255) : null;
    $referrer = isset($data['referrer']) && !empty($data['referrer']) 
        ? filter_var($data['referrer'], FILTER_SANITIZE_URL) 
        : null;
    
    // Get server-side data
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ipAddress = getClientIP();
    $countryData = getCountryFromIP($ipAddress);
    $deviceType = detectDevice($userAgent);
    $browserData = parseBrowser($userAgent);
    $sessionId = getSessionId();
    
    // Prepare SQL statement with prepared statement to prevent SQL injection
    global $pdo;
    if (!$pdo) {
        throw new Exception('Database connection not available');
    }
    
    $sql = "INSERT INTO page_views (
        page_url, page_title, referrer, user_agent, ip_address, 
        country, country_code, device_type, browser, browser_version, os, session_id
    ) VALUES (
        :page_url, :page_title, :referrer, :user_agent, :ip_address,
        :country, :country_code, :device_type, :browser, :browser_version, :os, :session_id
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':page_url' => $pageUrl,
        ':page_title' => $pageTitle,
        ':referrer' => $referrer,
        ':user_agent' => substr($userAgent, 0, 500), // Limit length
        ':ip_address' => $ipAddress,
        ':country' => $countryData['country'],
        ':country_code' => $countryData['country_code'],
        ':device_type' => $deviceType,
        ':browser' => $browserData['browser'],
        ':browser_version' => $browserData['version'],
        ':os' => $browserData['os'],
        ':session_id' => $sessionId
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Page view tracked successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in track.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("Error in track.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
