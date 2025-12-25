<?php

session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'joseph_pot_admin');

// Debug mode
define('DEBUG', false); 

// Create database connection and setup
function setupDatabase() {
    // First connect without database to create it
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE=utf8mb4_unicode_ci";
    if ($conn->query($sql) === FALSE) {
        die("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db(DB_NAME);
    
    return $conn;
}

// Get database connection
function getDBConnection() {
    static $conn = null;
    if ($conn === null) {
        $conn = setupDatabase();
    }
    return $conn;
}

// Initialize database table
function initAdminTable() {
    $conn = getDBConnection();
    
    $sql = "CREATE TABLE IF NOT EXISTS admins (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(100) NOT NULL UNIQUE,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        remember_token VARCHAR(255) NULL,
        reset_token VARCHAR(64) NULL,
        reset_expires_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($sql)) {
        die("Error creating table: " . $conn->error);
    }
}

// Create login activity table
function initLoginActivityTable() {
    $conn = getDBConnection();
    
    // First create the table if it doesn't exist with basic structure
    $sql = "CREATE TABLE IF NOT EXISTS login_activity (
        id INT PRIMARY KEY AUTO_INCREMENT,
        admin_id INT NOT NULL,
        username VARCHAR(100) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT NOT NULL,
        device_type VARCHAR(50) NULL,
        browser VARCHAR(100) NULL,
        platform VARCHAR(50) NULL,
        login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('success', 'failed') NOT NULL,
        INDEX idx_admin_id (admin_id),
        INDEX idx_login_time (login_time),
        INDEX idx_ip_address (ip_address),
        FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($sql)) {
        die("Error creating login_activity table: " . $conn->error);
    }
    
    // Now check and add the new location columns if they don't exist
    $columns_to_add = [
        'country' => "ALTER TABLE login_activity ADD COLUMN country VARCHAR(100) NULL AFTER platform",
        'city' => "ALTER TABLE login_activity ADD COLUMN city VARCHAR(100) NULL AFTER country",
        'region' => "ALTER TABLE login_activity ADD COLUMN region VARCHAR(100) NULL AFTER city",
        'latitude' => "ALTER TABLE login_activity ADD COLUMN latitude DECIMAL(10, 8) NULL AFTER region",
        'longitude' => "ALTER TABLE login_activity ADD COLUMN longitude DECIMAL(11, 8) NULL AFTER latitude"
    ];
    
    foreach ($columns_to_add as $column_name => $alter_sql) {
        // Check if column exists
        $check_sql = "SHOW COLUMNS FROM login_activity LIKE '$column_name'";
        $result = $conn->query($check_sql);
        
        if ($result->num_rows === 0) {
            // Column doesn't exist, add it
            if (!$conn->query($alter_sql)) {
                error_log("Error adding column $column_name: " . $conn->error);
            }
        }
    }
    
    // Add index for country if it doesn't exist
    $index_check = $conn->query("SHOW INDEX FROM login_activity WHERE Key_name = 'idx_country'");
    if ($index_check->num_rows === 0) {
        $conn->query("CREATE INDEX idx_country ON login_activity (country)");
    }
}

// Create a default admin (run once)
function createDefaultAdmin() {
    $conn = getDBConnection();
    
    // First ensure the table exists
    initAdminTable();
    
    // Check if admin already exists
    $result = $conn->query("SELECT id FROM admins WHERE username = 'admin'");
    if ($result->num_rows === 0) {
        $password = 'admin123';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Use INSERT IGNORE to avoid duplicate errors
        $stmt = $conn->prepare("INSERT IGNORE INTO admins (username, email, password) VALUES (?, ?, ?)");
        
        if ($stmt) {
            $username = 'admin';
            $email = 'admin@josephspot.com';
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                // Verify the admin was created
                $verify_result = $conn->query("SELECT id FROM admins WHERE username = 'admin'");
                if ($verify_result->num_rows === 1) {
                    return "Default admin created: username='admin', password='admin123'";
                } else {
                    return "Admin creation failed - user not found after creation";
                }
            } else {
                return "Error creating admin: " . $stmt->error;
            }
        } else {
            return "Error preparing statement: " . $conn->error;
        }
    }
    return "Admin user already exists";
}

// Get client IP address
function getClientIP() {
    $ipaddress = '';
    
    // Check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && validateIP($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    
    // Check for IPs passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Check if multiple IPs exist in var
        $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($iplist as $ip) {
            if (validateIP($ip)) {
                return $ip;
            }
        }
    }
    
    if (!empty($_SERVER['HTTP_X_FORWARDED']) && validateIP($_SERVER['HTTP_X_FORWARDED'])) {
        return $_SERVER['HTTP_X_FORWARDED'];
    }
    
    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validateIP($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    }
    
    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validateIP($_SERVER['HTTP_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_FORWARDED_FOR'];
    }
    
    if (!empty($_SERVER['HTTP_FORWARDED']) && validateIP($_SERVER['HTTP_FORWARDED'])) {
        return $_SERVER['HTTP_FORWARDED'];
    }
    
    // Return unreliable IP since all else failed
    if (!empty($_SERVER['REMOTE_ADDR']) && validateIP($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    }
    
    return 'UNKNOWN';
}

// Validate IP address
function validateIP($ip) {
    if (strtolower($ip) === 'unknown') {
        return false;
    }
    
    // Generate IPv4 and IPv6 versions
    $ip = trim($ip);
    
    // Check for IPv4
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return true;
    }
    
    // Check for IPv6
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return true;
    }
    
    return false;
}

// Get REAL client IP address
function getRealClientIP() {
    $ip_keys = [
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    
    foreach ($ip_keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ips = explode(',', $_SERVER[$key]);
            foreach ($ips as $ip) {
                $ip = trim($ip);
                
                // Validate IPv4
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    // Skip private IP ranges
                    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
                        continue;
                    }
                    return $ip;
                }
                
                // Validate IPv6 (convert to IPv4 if possible)
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    // If it's IPv6 localhost, try to get IPv4
                    if ($ip === '::1') {
                        continue; // Skip IPv6 localhost
                    }
                    return $ip;
                }
            }
        }
    }
    
    // Final fallback - get server's REMOTE_ADDR
    $final_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // If it's IPv6 localhost, provide a meaningful message
    if ($final_ip === '::1') {
        return '127.0.0.1 (localhost)';
    }
    
    return $final_ip;
}

// Get location information from IP address
function getLocationFromIP($ip) {
    // Handle local IPs and IPv6 localhost
    if ($ip === '127.0.0.1' || $ip === '::1' || $ip === '127.0.0.1 (localhost)' || 
        strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0 || 
        strpos($ip, '172.') === 0) {
        return [
            'country' => 'Local',
            'city' => 'Local Network',
            'region' => 'Internal',
            'latitude' => null,
            'longitude' => null
        ];
    }
    
    try {
        // Method 1: Using ipapi.co (free, no API key required for limited use)
        $url = "http://ipapi.co/{$ip}/json/";
        $context = stream_context_create([
            'http' => [
                'timeout' => 3, // 3 second timeout
                'ignore_errors' => true
            ]
        ]);
        $response = @file_get_contents($url, false, $context);
        
        if ($response !== false) {
            $data = json_decode($response, true);
            
            if (isset($data['country_name']) && $data['country_name']) {
                return [
                    'country' => $data['country_name'] ?? 'Unknown',
                    'city' => $data['city'] ?? 'Unknown',
                    'region' => $data['region'] ?? 'Unknown',
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null
                ];
            }
        }
        
        // Method 2: Using ipinfo.io (fallback)
        $url = "http://ipinfo.io/{$ip}/json";
        $response = @file_get_contents($url, false, $context);
        
        if ($response !== false) {
            $data = json_decode($response, true);
            
            if (isset($data['country'])) {
                $location = isset($data['loc']) ? explode(',', $data['loc']) : [null, null];
                return [
                    'country' => $data['country'] ?? 'Unknown',
                    'city' => $data['city'] ?? 'Unknown',
                    'region' => $data['region'] ?? 'Unknown',
                    'latitude' => $location[0] ?? null,
                    'longitude' => $location[1] ?? null
                ];
            }
        }
    } catch (Exception $e) {
        // Silent fail - we don't want location errors to break login
        error_log("Location lookup failed: " . $e->getMessage());
    }
    
    return [
        'country' => 'Unknown',
        'city' => 'Unknown',
        'region' => 'Unknown',
        'latitude' => null,
        'longitude' => null
    ];
}

// Get device information
function getDeviceInfo() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Detect device type
    $device_type = 'Desktop';
    if (preg_match('/(android|webos|iphone|ipad|ipod|blackberry|windows phone)/i', $user_agent)) {
        $device_type = 'Mobile';
    } elseif (preg_match('/(tablet|ipad|playbook|silk)/i', $user_agent)) {
        $device_type = 'Tablet';
    }
    
    // Detect browser
    $browser = 'Unknown';
    if (preg_match('/MSIE|Trident/i', $user_agent)) {
        $browser = 'Internet Explorer';
    } elseif (preg_match('/Firefox/i', $user_agent)) {
        $browser = 'Firefox';
    } elseif (preg_match('/Chrome/i', $user_agent)) {
        $browser = 'Chrome';
    } elseif (preg_match('/Safari/i', $user_agent)) {
        $browser = 'Safari';
    } elseif (preg_match('/Edge/i', $user_agent)) {
        $browser = 'Edge';
    } elseif (preg_match('/Opera|OPR/i', $user_agent)) {
        $browser = 'Opera';
    }
    
    // Detect platform
    $platform = 'Unknown';
    if (preg_match('/windows/i', $user_agent)) {
        $platform = 'Windows';
    } elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
        $platform = 'Mac';
    } elseif (preg_match('/linux/i', $user_agent)) {
        $platform = 'Linux';
    } elseif (preg_match('/android/i', $user_agent)) {
        $platform = 'Android';
    } elseif (preg_match('/iphone|ipad|ipod/i', $user_agent)) {
        $platform = 'iOS';
    }
    
    return [
        'device_type' => $device_type,
        'browser' => $browser,
        'platform' => $platform,
        'user_agent' => $user_agent
    ];
}

// Log login activity
function logLoginActivity($admin_id, $username, $status) {
    $conn = getDBConnection();
    
    // Ensure login activity table exists
    initLoginActivityTable();
    
    // FIX: Ensure admin_id exists in admins table for foreign key constraint
    // We need to find the correct admin_id that exists in admins table
    $actual_admin_id_for_fk = $admin_id; // Default to original admin_id
    
    // Check if admin exists in admins table by ID
    $check_stmt = $conn->prepare("SELECT id FROM admins WHERE id = ?");
    if ($check_stmt) {
        $check_stmt->bind_param("i", $admin_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_stmt->close();
        
        // If admin doesn't exist in admins table by ID, try to find by email or sync
        if ($check_result->num_rows === 0 && $admin_id > 0) {
            // Get admin data from admin_users table
            $user_stmt = $conn->prepare("SELECT username, email, password_hash FROM admin_users WHERE id = ?");
            if ($user_stmt) {
                $user_stmt->bind_param("i", $admin_id);
                $user_stmt->execute();
                $user_result = $user_stmt->get_result();
                
                if ($user_row = $user_result->fetch_assoc()) {
                    // First, check if email already exists in admins table (different ID scenario)
                    $email_check_stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
                    if ($email_check_stmt) {
                        $email_check_stmt->bind_param("s", $user_row['email']);
                        $email_check_stmt->execute();
                        $email_result = $email_check_stmt->get_result();
                        
                        if ($email_row = $email_result->fetch_assoc()) {
                            // Email exists with different ID - use that ID for foreign key
                            $actual_admin_id_for_fk = $email_row['id'];
                            if (DEBUG) {
                                error_log("Admin email {$user_row['email']} exists in admins table with ID {$actual_admin_id_for_fk}. Using that ID for foreign key.");
                            }
                        } else {
                            // Email doesn't exist - try to insert new admin
                            // Use INSERT IGNORE to handle any race conditions
                            $sync_stmt = $conn->prepare("INSERT IGNORE INTO admins (id, username, email, password, created_at) VALUES (?, ?, ?, ?, NOW())");
                            if ($sync_stmt) {
                                $sync_stmt->bind_param("isss", $admin_id, $user_row['username'], $user_row['email'], $user_row['password_hash']);
                                $sync_stmt->execute();
                                
                                // Check if insert succeeded by checking affected rows or re-querying
                                if ($sync_stmt->affected_rows > 0) {
                                    // Successfully inserted, use the admin_id we tried to insert
                                    $actual_admin_id_for_fk = $admin_id;
                                } else {
                                    // INSERT IGNORE skipped (duplicate) - check what ID exists now
                                    $recheck_stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
                                    $recheck_stmt->bind_param("s", $user_row['email']);
                                    $recheck_stmt->execute();
                                    $recheck_result = $recheck_stmt->get_result();
                                    if ($recheck_row = $recheck_result->fetch_assoc()) {
                                        $actual_admin_id_for_fk = $recheck_row['id'];
                                    }
                                    $recheck_stmt->close();
                                }
                                $sync_stmt->close();
                            }
                        }
                        $email_check_stmt->close();
                    }
                }
                $user_stmt->close();
            }
        }
    }
    
    // Use the actual admin_id for foreign key constraint
    $admin_id = $actual_admin_id_for_fk;
    
    $ip_address = getRealClientIP();
    $device_info = getDeviceInfo();
    $location_info = getLocationFromIP($ip_address);
    
    // Debug: Log the IP address for testing
    if (DEBUG) {
        error_log("Captured IP: " . $ip_address);
        error_log("Location info: " . print_r($location_info, true));
    }
    
    // First try the full query with all columns
    $stmt = $conn->prepare("INSERT INTO login_activity (admin_id, username, ip_address, user_agent, device_type, browser, platform, country, city, region, latitude, longitude, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param(
            "isssssssssdds", 
            $admin_id, 
            $username, 
            $ip_address, 
            $device_info['user_agent'],
            $device_info['device_type'],
            $device_info['browser'],
            $device_info['platform'],
            $location_info['country'],
            $location_info['city'],
            $location_info['region'],
            $location_info['latitude'],
            $location_info['longitude'],
            $status
        );
        
        if ($stmt->execute()) {
            $stmt->close();
            return;
        } else {
            // If full insert fails, check if it's a foreign key constraint error
            $error_code = $conn->errno;
            $error_msg = $stmt->error;
            
            // MySQL error 1452 = Cannot add or update a child row: a foreign key constraint fails
            if ($error_code == 1452) {
                // Try to sync admin again (in case it failed earlier)
                $sync_stmt = $conn->prepare("SELECT username, email, password_hash FROM admin_users WHERE id = ?");
                if ($sync_stmt) {
                    $sync_stmt->bind_param("i", $admin_id);
                    $sync_stmt->execute();
                    $sync_result = $sync_stmt->get_result();
                    if ($sync_row = $sync_result->fetch_assoc()) {
                        // Check if admin already exists by ID or email before inserting
                        $check_both = $conn->prepare("SELECT id FROM admins WHERE id = ? OR email = ?");
                        $check_both->bind_param("is", $admin_id, $sync_row['email']);
                        $check_both->execute();
                        $check_both_result = $check_both->get_result();
                        $check_both->close();
                        
                        // Only insert if doesn't exist
                        if ($check_both_result->num_rows === 0) {
                            $insert_sync = $conn->prepare("INSERT IGNORE INTO admins (id, username, email, password, created_at) VALUES (?, ?, ?, ?, NOW())");
                            if ($insert_sync) {
                                $insert_sync->bind_param("isss", $admin_id, $sync_row['username'], $sync_row['email'], $sync_row['password_hash']);
                                $insert_sync->execute();
                                $insert_sync->close();
                            }
                        }
                        
                        // Retry the insert (even if sync didn't insert, the admin might exist by email now)
                        // Check if we can use existing ID from admins table
                        $find_existing = $conn->prepare("SELECT id FROM admins WHERE email = ?");
                        $find_existing->bind_param("s", $sync_row['email']);
                        $find_existing->execute();
                        $existing_result = $find_existing->get_result();
                        if ($existing_row = $existing_result->fetch_assoc()) {
                            // Use existing admin ID for foreign key
                            $admin_id_for_fk = $existing_row['id'];
                        } else {
                            $admin_id_for_fk = $admin_id;
                        }
                        $find_existing->close();
                        
                        // Update the statement to use correct admin_id
                        $stmt->close();
                        $stmt = $conn->prepare("INSERT INTO login_activity (admin_id, username, ip_address, user_agent, device_type, browser, platform, country, city, region, latitude, longitude, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        if ($stmt) {
                            $stmt->bind_param(
                                "isssssssssdds", 
                                $admin_id_for_fk, 
                                $username, 
                                $ip_address, 
                                $device_info['user_agent'],
                                $device_info['device_type'],
                                $device_info['browser'],
                                $device_info['platform'],
                                $location_info['country'],
                                $location_info['city'],
                                $location_info['region'],
                                $location_info['latitude'],
                                $location_info['longitude'],
                                $status
                            );
                            if ($stmt->execute()) {
                                $stmt->close();
                                return;
                            }
                        }
                    }
                    $sync_stmt->close();
                }
            }
            
            // If full insert fails, try without location columns
            error_log("Full login activity insert failed, trying fallback: " . $error_msg);
            $stmt->close();
        }
    }
    
    // Fallback: Insert without location columns
    $stmt_fallback = $conn->prepare("INSERT INTO login_activity (admin_id, username, ip_address, user_agent, device_type, browser, platform, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt_fallback) {
        $stmt_fallback->bind_param(
            "isssssss", 
            $admin_id, 
            $username, 
            $ip_address, 
            $device_info['user_agent'],
            $device_info['device_type'],
            $device_info['browser'],
            $device_info['platform'],
            $status
        );
        
        if (!$stmt_fallback->execute()) {
            $error_code = $conn->errno;
            // If foreign key constraint error, try to sync admin
            if ($error_code == 1452) {
                $sync_stmt = $conn->prepare("SELECT username, email, password_hash FROM admin_users WHERE id = ?");
                if ($sync_stmt) {
                    $sync_stmt->bind_param("i", $admin_id);
                    $sync_stmt->execute();
                    $sync_result = $sync_stmt->get_result();
                    if ($sync_row = $sync_result->fetch_assoc()) {
                        // Check if admin already exists by ID or email before inserting
                        $check_both = $conn->prepare("SELECT id FROM admins WHERE id = ? OR email = ?");
                        $check_both->bind_param("is", $admin_id, $sync_row['email']);
                        $check_both->execute();
                        $check_both_result = $check_both->get_result();
                        $check_both->close();
                        
                        // Only insert if doesn't exist
                        if ($check_both_result->num_rows === 0) {
                            $insert_sync = $conn->prepare("INSERT IGNORE INTO admins (id, username, email, password, created_at) VALUES (?, ?, ?, ?, NOW())");
                            if ($insert_sync) {
                                $insert_sync->bind_param("isss", $admin_id, $sync_row['username'], $sync_row['email'], $sync_row['password_hash']);
                                $insert_sync->execute();
                                $insert_sync->close();
                            }
                        }
                        
                        // Find existing admin ID by email if sync didn't work
                        $find_existing = $conn->prepare("SELECT id FROM admins WHERE email = ?");
                        $find_existing->bind_param("s", $sync_row['email']);
                        $find_existing->execute();
                        $existing_result = $find_existing->get_result();
                        if ($existing_row = $existing_result->fetch_assoc()) {
                            // Use existing admin ID for foreign key
                            $admin_id_for_fk = $existing_row['id'];
                        } else {
                            $admin_id_for_fk = $admin_id;
                        }
                        $find_existing->close();
                        
                        // Retry with correct admin_id
                        $stmt_fallback->close();
                        $stmt_fallback = $conn->prepare("INSERT INTO login_activity (admin_id, username, ip_address, user_agent, device_type, browser, platform, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        if ($stmt_fallback) {
                            $stmt_fallback->bind_param(
                                "isssssss", 
                                $admin_id_for_fk, 
                                $username, 
                                $ip_address, 
                                $device_info['user_agent'],
                                $device_info['device_type'],
                                $device_info['browser'],
                                $device_info['platform'],
                                $status
                            );
                            $stmt_fallback->execute();
                            $stmt_fallback->close();
                        }
                    }
                    $sync_stmt->close();
                }
            } else {
                error_log("Fallback login activity insert also failed: " . $stmt_fallback->error);
            }
        }
        
        $stmt_fallback->close();
    } else {
        error_log("Failed to prepare fallback login activity statement: " . $conn->error);
    }
}

// Security Functions
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function cleanInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Brute Force Protection
function checkBruteForce($username) {
    $max_attempts = 5;
    $lockout_time = 300; // 5 minutes
    
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }
    
    if (!isset($_SESSION['lockout_time'])) {
        $_SESSION['lockout_time'] = 0;
    }
    
    if (time() < $_SESSION['lockout_time']) {
        $remaining = ceil(($_SESSION['lockout_time'] - time()) / 60);
        return "Too many failed login attempts. Try again in $remaining minutes.";
    }
    
    if ($_SESSION['login_attempts'] >= $max_attempts) {
        $_SESSION['lockout_time'] = time() + $lockout_time;
        $_SESSION['login_attempts'] = 0;
        return "Too many failed login attempts. Account locked for 5 minutes.";
    }
    
    return null;
}

function recordFailedAttempt() {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }
    $_SESSION['login_attempts']++;
}

function clearFailedAttempts() {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lockout_time'] = 0;
}

// Auto-login via Remember Me cookie
function checkRememberMe() {
    if (isset($_SESSION['admin_id'])) {
        return true;
    }
    
    if (isset($_COOKIE['remember_admin'])) {
        $cookie_data = explode(':', $_COOKIE['remember_admin']);
        if (count($cookie_data) === 2) {
            $admin_id = intval($cookie_data[0]);
            $token = $cookie_data[1];
            
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $admin_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $admin = $result->fetch_assoc();
                    if (password_verify($token, $admin['remember_token'])) {
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['admin_username'] = $admin['username'];
                        $_SESSION['admin_logged_in'] = true;
                        
                        // Log successful auto-login
                        logLoginActivity($admin['id'], $admin['username'], 'success');
                        
                        return true;
                    }
                }
            }
        }
        // Invalid cookie, remove it
        setcookie("remember_admin", "", time() - 3600, "/");
    }
    
    return false;
}

// Login Function - UPDATED VERSION WITH SESSION FIX
function handleLogin($username, $password, $remember = false) {
    $conn = getDBConnection();
    
    // Check admin_users table first (used by dashboard admin management)
    $stmt = $conn->prepare("SELECT id, username, email, password_hash as password, role, full_name FROM admin_users WHERE username = ?");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = null;
    $table_used = 'admin_users';
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
    } else {
        // Fallback to admins table (legacy)
        $stmt->close();
        $stmt = $conn->prepare("SELECT id, username, email, password, role FROM admins WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $table_used = 'admins';
            
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
            }
        }
    }
    
    if ($admin) {
        // Determine password column name based on table
        $password_column = ($table_used === 'admin_users') ? 'password_hash' : 'password';
        $stored_password = $admin['password'];
        
        if (DEBUG) {
            error_log("Login attempt for user: " . $username);
            error_log("Table used: " . $table_used);
            error_log("Stored hash: " . $stored_password);
            error_log("Input password: " . $password);
            error_log("Password verify result: " . (password_verify($password, $stored_password) ? 'true' : 'false'));
        }
        
        if (password_verify($password, $stored_password)) {
            // Set ALL session variables for consistency across all admin pages
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_logged_in'] = true; // ADDED FOR CONSISTENCY
            $_SESSION['admin_email'] = $admin['email'];
            
            clearFailedAttempts();
            
            // Log successful login
            logLoginActivity($admin['id'], $admin['username'], 'success');
            
            // Handle Remember Me (only for admins table, admin_users doesn't have remember_token)
            if ($remember && $table_used === 'admins') {
                $token = bin2hex(random_bytes(16));
                $hashed_token = password_hash($token, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("UPDATE admins SET remember_token = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("si", $hashed_token, $admin['id']);
                    $stmt->execute();
                    $stmt->close();
                    
                    setcookie("remember_admin", $admin['id'] . ':' . $token, 
                        time() + (30 * 24 * 60 * 60), "/", "", false, true);
                }
            }
            
            return ['success' => true, 'message' => 'Login successful'];
        } else {
            if (DEBUG) {
                error_log("Password verification failed for user: " . $username);
            }
            
            // Log failed login attempt
            logLoginActivity(0, $username, 'failed');
        }
    } else {
        // No user found in either table
        if (DEBUG) {
            error_log("No user found with username: " . $username . " in admin_users or admins table");
        }
        
        // Log failed login attempt for non-existent user
        logLoginActivity(0, $username, 'failed');
    }
    
    recordFailedAttempt();
    return ['success' => false, 'message' => 'Invalid username or password'];
}

// Forgot Password Function
function handleForgotPassword($email) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id, username FROM admins WHERE email = ?");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Database error'];
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        // Generate reset token
        $reset_token = bin2hex(random_bytes(32));
        $reset_expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
        
        $stmt = $conn->prepare("UPDATE admins SET reset_token = ?, reset_expires_at = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssi", $reset_token, $reset_expires, $admin['id']);
            
            if ($stmt->execute()) {
                // In a real application, you would send an email here
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=" . $reset_token;
                
                return [
                    'success' => true, 
                    'message' => "Password reset link has been generated.",
                    'reset_link' => $reset_link
                ];
            }
        }
    }
    
    return ['success' => true, 'message' => 'If the email exists in our system, a password reset link will be sent.'];
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_id']) || isset($_SESSION['admin_logged_in']);
}

// Get login history for a user
function getLoginHistory($admin_id, $limit = 10) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT * FROM login_activity WHERE admin_id = ? ORDER BY login_time DESC LIMIT ?");
    if ($stmt) {
        $stmt->bind_param("ii", $admin_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        
        return $history;
    }
    
    return [];
}

// Initialize the application
function initApp() {
    initAdminTable();
    initLoginActivityTable();
    generateCSRFToken();
    
    // Check for auto-login via remember me
    checkRememberMe();
}

// Handle incoming requests
function handleRequest() {
    $response = ['success' => false, 'message' => ''];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'login':
                if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                    $response['message'] = 'Invalid security token. Please refresh the page and try again.';
                    break;
                }
                
                $brute_force_check = checkBruteForce($_POST['username'] ?? '');
                if ($brute_force_check) {
                    $response['message'] = $brute_force_check;
                    break;
                }
                
                $username = cleanInput($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                $remember = isset($_POST['remember']);
                
                if (empty($username) || empty($password)) {
                    $response['message'] = 'All fields are required';
                } else {
                    $response = handleLogin($username, $password, $remember);
                    
                    // If login successful, redirect to admin dashboard
                    if ($response['success']) {
                        // Set a session variable to show success message on redirect
                        $_SESSION['login_success'] = true;
                        
                        if (DEBUG) {
                            error_log("Login successful, redirecting to dashboard.php");
                        }
                        
                        // Redirect to main dashboard
                        header("Location: dashboard.php");
                        exit;
                    }
                }
                break;
                
            case 'forgot_password':
                $email = cleanInput($_POST['email'] ?? '');
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $response['message'] = 'Please enter a valid email address';
                } else {
                    $response = handleForgotPassword($email);
                }
                break;
                
            default:
                $response['message'] = 'Invalid action';
        }
    }
    
    return $response;
}

// Initialize the application
initApp();

// Handle the request if it's a POST
$response = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = handleRequest();
    
    // If it's an AJAX request, return JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// For demo purposes - create default admin on first run
$init_message = '';
if (isset($_GET['init']) && $_GET['init'] == '1') {
    $init_message = createDefaultAdmin();
}

// Check if user is already logged in and redirect
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="../images/logo3.png" />
    <title>Joseph's Pot - Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Exo+2:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <style>
        /* Your existing CSS styles remain exactly the same */
        :root {
            --brown: #8b4513;
            --brown-light: #a0522d;
            --brown-dark: #654321;
            --white: #ffffff;
            --pale-orange: #ffe4b5;
            --pale-orange-light: #fff8dc;
            --accent: #d2691e;
            --text: #333333;
            --text-light: #666666;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--brown-dark) 0%, var(--brown) 50%, var(--brown-light) 100%);
            color: var(--pale-orange-light);
            font-family: "Exo 2", sans-serif;
            line-height: 1.6;
            overflow: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            display: flex;
            width: 90%;
            max-width: 1200px;
            height: 90vh;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(255, 228, 181, 0.2);
            box-shadow: 0 0 50px rgba(210, 105, 30, 0.3);
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg,
                    rgba(210, 105, 30, 0.2),
                    rgba(101, 67, 33, 0.7)),
                url("https://res.cloudinary.com/dl4hjr1p2/image/upload/v1762379534/unnamed_2_j7h5xd.webp") center/cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 50%,
                    rgba(255, 228, 181, 0.2) 0%,
                    transparent 50%),
                radial-gradient(circle at 70% 20%,
                    rgba(255, 255, 255, 0.1) 0%,
                    transparent 50%),
                radial-gradient(circle at 40% 80%,
                    rgba(210, 105, 30, 0.2) 0%,
                    transparent 50%);
            z-index: 0;
        }

        .logo-container {
            text-align: center;
            z-index: 1;
            margin-bottom: 40px;
        }

        .logo-container img {
            width: 120px;
            height: 120px;
            filter: drop-shadow(0 0 10px var(--accent));
            margin-bottom: 20px;
            border-radius: 50%;
            background: var(--pale-orange);
            padding: 10px;
        }

        .logo-container h1 {
            font-family: "Orbitron", sans-serif;
            font-size: 3rem;
            margin-bottom: 10px;
            background: linear-gradient(45deg, var(--pale-orange), var(--accent));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 0 20px rgba(210, 105, 30, 0.5);
            letter-spacing: 2px;
        }

        .logo-container p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 400px;
            color: var(--pale-orange-light);
        }

        .features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 40px;
            z-index: 1;
        }

        .feature {
            display: flex;
            align-items: center;
            background: rgba(101, 67, 33, 0.4);
            padding: 15px;
            border-radius: 10px;
            border-left: 3px solid var(--accent);
            transition: var(--transition);
        }

        .feature:hover {
            transform: translateY(-5px);
            background: rgba(160, 82, 45, 0.4);
        }

        .feature i {
            font-size: 1.5rem;
            margin-right: 15px;
            color: var(--pale-orange);
        }

        .feature p {
            font-size: 0.9rem;
            color: var(--pale-orange-light);
        }

        .login-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            position: relative;
            background: rgba(101, 67, 33, 0.2);
        }

        .login-form-container {
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h2 {
            font-family: "Orbitron", sans-serif;
            font-size: 2.2rem;
            margin-bottom: 10px;
            background: linear-gradient(45deg, var(--pale-orange), var(--accent));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .login-header p {
            opacity: 0.8;
            font-size: 1rem;
            color: var(--pale-orange-light);
        }

        .login-form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--pale-orange);
            font-size: 0.9rem;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent);
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .form-control {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 228, 181, 0.3);
            border-radius: 8px;
            color: var(--pale-orange-light);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control::placeholder {
            color: rgba(255, 228, 181, 0.5);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 10px rgba(210, 105, 30, 0.3);
            background: rgba(255, 255, 255, 0.15);
        }

        .form-control:focus+i {
            color: var(--pale-orange);
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--pale-orange-light);
            cursor: pointer;
            opacity: 0.7;
            transition: var(--transition);
        }

        .password-toggle:hover {
            opacity: 1;
            color: var(--pale-orange);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
        }

        .remember-me input {
            margin-right: 8px;
            accent-color: var(--accent);
        }

        .remember-me label {
            color: var(--pale-orange-light);
        }

        .forgot-password {
            color: var(--pale-orange);
            text-decoration: none;
            transition: var(--transition);
        }

        .forgot-password:hover {
            color: var(--accent);
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(45deg, var(--brown-light), var(--accent));
            color: var(--pale-orange-light);
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(210, 105, 30, 0.4);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(210, 105, 30, 0.6);
            background: linear-gradient(45deg, var(--accent), var(--brown-light));
        }

        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            border: 1px solid rgba(46, 204, 113, 0.5);
            color: #2ecc71;
        }
        
        .alert-error {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid rgba(231, 76, 60, 0.5);
            color: #e74c3c;
        }
        
        .alert-info {
            background: rgba(52, 152, 219, 0.2);
            border: 1px solid rgba(52, 152, 219, 0.5);
            color: #3498db;
        }

        .debug-info {
            background: rgba(0,0,0,0.3);
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 12px;
            color: #ccc;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            width: 90%;
            position: relative;
        }

        .modal-content h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .modal-content input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .modal-content button {
            width: 100%;
            padding: 10px;
            border: none;
            background: #d2691e;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
        }

        .close-modal {
            position: absolute;
            top: 10px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-left">
            <div class="logo-container">
                <img src="../images/logo3.png" alt="Joseph's Pot Logo" onerror="this.style.display='none'" />
                <h1>JOSEPH'S POT</h1>
                <p>Premium Restaurant Management System</p>
            </div>
            <div class="features">
                <div class="feature">
                    <i class="fas fa-shield-alt"></i>
                    <p>Secure Admin Access</p>
                </div>
                <div class="feature">
                    <i class="fas fa-chart-line"></i>
                    <p>Real-time Analytics</p>
                </div>
                <div class="feature">
                    <i class="fas fa-cogs"></i>
                    <p>Menu Management</p>
                </div>
                <div class="feature">
                    <i class="fas fa-users"></i>
                    <p>Staff Management</p>
                </div>
            </div>
        </div>

        <div class="login-right">
            <div class="login-form-container">
                <div class="login-header">
                    <h2>ADMIN LOGIN</h2>
                    <p>Access your restaurant dashboard</p>
                </div>

                <?php if ($init_message): ?>
                    <div class="alert alert-info">
                        <?php echo $init_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($response['message']) && $response['message']): ?>
                    <div class="alert <?php echo $response['success'] ? 'alert-success' : 'alert-error'; ?>">
                        <?php echo $response['message']; ?>
                        <?php if (isset($response['reset_link'])): ?>
                            <br><small>Reset Link: <?php echo $response['reset_link']; ?></small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Debug info -->
                <?php if (DEBUG): ?>
                <div class="debug-info">
                    <strong>Debug Info:</strong><br>
                    Current script: <?php echo $_SERVER['PHP_SELF']; ?><br>
                    Dashboard path: dashboard.php (same folder)<br>
                    Logged in: <?php echo isLoggedIn() ? 'Yes' : 'No'; ?><br>
                    Current folder: <?php echo __DIR__; ?>
                </div>
                <?php endif; ?>

                <form class="login-form" id="loginForm" method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter username: admin" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : 'admin'; ?>" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password: admin123" required value="admin123" />
                            <button type="button" class="password-toggle" id="passwordToggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" name="remember" id="remember" />
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password" id="forgotPasswordLink">Forgot Password?</a>
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i> SIGN IN
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotModal" class="modal">
        <div class="modal-content">
            <button class="close-modal" id="closeModal">&times;</button>
            <h3>Reset Password</h3>
            <p>Enter your registered email:</p>
            <input type="email" id="resetEmail" placeholder="admin@josephspot.com">
            <button id="resetBtn">Send Reset Link</button>
            <div id="resetMsg" style="margin-top:10px;"></div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Password toggle functionality
            const passwordToggle = document.getElementById("passwordToggle");
            const passwordInput = document.getElementById("password");

            if (passwordToggle && passwordInput) {
                passwordToggle.addEventListener("click", function() {
                    const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
                    passwordInput.setAttribute("type", type);
                    this.innerHTML = type === "password" ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
                });
            }

            // Form submission loading state
            const loginForm = document.getElementById("loginForm");
            if (loginForm) {
                loginForm.addEventListener("submit", function() {
                    const loginBtn = this.querySelector('.login-btn');
                    if (loginBtn) {
                        loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> AUTHENTICATING...';
                        loginBtn.disabled = true;
                    }
                });
            }

            // Forgot Password Modal JS
            const forgotLink = document.getElementById('forgotPasswordLink');
            const modal = document.getElementById('forgotModal');
            const closeModal = document.getElementById('closeModal');
            const resetBtn = document.getElementById('resetBtn');
            const resetEmail = document.getElementById('resetEmail');
            const resetMsg = document.getElementById('resetMsg');

            forgotLink.addEventListener('click', function(e){
                e.preventDefault();
                modal.style.display = 'flex';
                resetMsg.innerHTML = '';
                resetEmail.value = 'admin@josephspot.com';
            });

            closeModal.addEventListener('click', function(){ 
                modal.style.display = 'none'; 
            });

            // Close modal when clicking outside
            modal.addEventListener('click', function(e){
                if(e.target === modal){
                    modal.style.display = 'none';
                }
            });

            resetBtn.addEventListener('click', function(){
                const email = resetEmail.value.trim();
                if(email === ''){
                    resetMsg.innerHTML = '<span style="color:red;">Please enter your email.</span>';
                    return;
                }

                resetBtn.disabled = true;
                resetBtn.innerHTML = 'Sending...';
                resetMsg.innerHTML = '';

                // Create form data
                const formData = new FormData();
                formData.append('action', 'forgot_password');
                formData.append('email', email);
                formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');

                // Add XMLHttpRequest header to ensure PHP treats this as AJAX
                fetch('', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    if(data.success){
                        resetMsg.innerHTML = '<span style="color:green;">' + data.message + '</span>';
                        if(data.reset_link){
                            resetMsg.innerHTML += '<br><small><strong>Reset Link:</strong> ' + data.reset_link + '</small>';
                            console.log('Password reset link:', data.reset_link);
                        }
                        resetEmail.value = '';
                    } else {
                        resetMsg.innerHTML = '<span style="color:red;">' + data.message + '</span>';
                    }
                    resetBtn.disabled = false;
                    resetBtn.innerHTML = 'Send Reset Link';
                })
                .catch(err => {
                    console.error('Error:', err);
                    resetMsg.innerHTML = '<span style="color:red;">Error sending request. Please check console for details.</span>';
                    resetBtn.disabled = false;
                    resetBtn.innerHTML = 'Send Reset Link';
                });
            });

            // Allow pressing Enter in the email field to trigger reset
            resetEmail.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    resetBtn.click();
                }
            });
        });
    </script>
</body>
</html>