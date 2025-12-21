<?php
/**
 * Appearance Settings Helper
 * 
 * Loads appearance settings from database and provides helper functions
 * for frontend pages to use dynamic logo, favicon, and theme colors.
 */

require_once __DIR__ . '/../db_connection.php';

// Default values (fallback if database fails)
$default_appearance = [
    'logo_path' => './images/logo3.png',
    'favicon_path' => './images/logo3.png',
    'primary_color' => '#8b4513',
    'theme' => 'warm_brown'
];

// Function to get appearance setting
function get_appearance_setting($key, $default = '') {
    global $pdo, $default_appearance;
    
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM appearance_settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && !empty($result['setting_value'])) {
            return $result['setting_value'];
        }
    } catch(PDOException $e) {
        error_log("Error loading appearance setting '$key': " . $e->getMessage());
    }
    
    // Return default if database value is empty or query fails
    return isset($default_appearance[$key]) ? $default_appearance[$key] : $default;
}

// Load all appearance settings
$appearance = [
    'logo_path' => get_appearance_setting('logo_path', $default_appearance['logo_path']),
    'favicon_path' => get_appearance_setting('favicon_path', $default_appearance['favicon_path']),
    'primary_color' => get_appearance_setting('primary_color', $default_appearance['primary_color']),
    'theme' => get_appearance_setting('theme', $default_appearance['theme'])
];

// Validate and fix logo path
if (!empty($appearance['logo_path'])) {
    $original_path = $appearance['logo_path'];
    
    // If path starts with 'uploads/', add './' prefix for frontend access
    if (strpos($appearance['logo_path'], 'uploads/') === 0) {
        $appearance['logo_path'] = './' . $appearance['logo_path'];
    }
    // If path starts with '../uploads/', change to './uploads/'
    if (strpos($appearance['logo_path'], '../uploads/') === 0) {
        $appearance['logo_path'] = str_replace('../uploads/', './uploads/', $appearance['logo_path']);
    }
    
    // Check if file exists
    // Build absolute path for file existence check
    $check_path = $appearance['logo_path'];
    if (strpos($check_path, './') === 0) {
        // Remove './' prefix for file check
        $file_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . substr($check_path, 2);
    } else {
        $file_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $check_path;
    }
    
    // Normalize path separators for Windows
    $file_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file_path);
    
    if (file_exists($file_path)) {
        // File exists, keep the frontend path
    } else {
        // Try alternative paths
        // 1. Try without ./ prefix
        $alt_path1 = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('./', '', $check_path);
        $alt_path1 = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $alt_path1);
        
        // 2. Try admin/uploads/settings/ (old location)
        $filename = basename($original_path);
        $alt_path2 = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . $filename;
        $alt_path2 = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $alt_path2);
        
        if (file_exists($alt_path1)) {
            // Found with alternative path 1
            $appearance['logo_path'] = './' . str_replace('./', '', $check_path);
        } elseif (file_exists($alt_path2)) {
            // Found in admin directory, copy to correct location
            $target_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR;
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $target_path = $target_dir . $filename;
            if (copy($alt_path2, $target_path)) {
                $appearance['logo_path'] = './uploads/settings/' . $filename;
            } else {
                // Use admin path as fallback
                $appearance['logo_path'] = './admin/uploads/settings/' . $filename;
            }
        } else {
            // File doesn't exist, use default but log for debugging
            error_log("Appearance: Logo file not found. Tried: $file_path, $alt_path1, $alt_path2 (original: $original_path)");
            $appearance['logo_path'] = $default_appearance['logo_path'];
        }
    }
} else {
    $appearance['logo_path'] = $default_appearance['logo_path'];
}

// Validate and fix favicon path
if (!empty($appearance['favicon_path'])) {
    $original_path = $appearance['favicon_path'];
    
    // If path starts with 'uploads/', add './' prefix for frontend access
    if (strpos($appearance['favicon_path'], 'uploads/') === 0) {
        $appearance['favicon_path'] = './' . $appearance['favicon_path'];
    }
    // If path starts with '../uploads/', change to './uploads/'
    if (strpos($appearance['favicon_path'], '../uploads/') === 0) {
        $appearance['favicon_path'] = str_replace('../uploads/', './uploads/', $appearance['favicon_path']);
    }
    
    // Check if file exists
    // Build absolute path for file existence check
    $check_path = $appearance['favicon_path'];
    if (strpos($check_path, './') === 0) {
        // Remove './' prefix for file check
        $file_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . substr($check_path, 2);
    } else {
        $file_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $check_path;
    }
    
    // Normalize path separators for Windows
    $file_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file_path);
    
    if (file_exists($file_path)) {
        // File exists, keep the frontend path
    } else {
        // Try alternative paths
        // 1. Try without ./ prefix
        $alt_path1 = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('./', '', $check_path);
        $alt_path1 = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $alt_path1);
        
        // 2. Try admin/uploads/settings/ (old location)
        $filename = basename($original_path);
        $alt_path2 = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . $filename;
        $alt_path2 = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $alt_path2);
        
        if (file_exists($alt_path1)) {
            // Found with alternative path 1
            $appearance['favicon_path'] = './' . str_replace('./', '', $check_path);
        } elseif (file_exists($alt_path2)) {
            // Found in admin directory, copy to correct location
            $target_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR;
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $target_path = $target_dir . $filename;
            if (copy($alt_path2, $target_path)) {
                // Update database with correct path
                try {
                    $update_stmt = $pdo->prepare("UPDATE appearance_settings SET setting_value = ? WHERE setting_key = 'favicon_path'");
                    $update_stmt->execute(['uploads/settings/' . $filename]);
                } catch(PDOException $e) {
                    error_log("Failed to update favicon path in database: " . $e->getMessage());
                }
                $appearance['favicon_path'] = './uploads/settings/' . $filename;
            } else {
                // Use admin path as fallback (temporary)
                $appearance['favicon_path'] = './admin/uploads/settings/' . $filename;
            }
        } else {
            // File doesn't exist, use default but log for debugging
            error_log("Appearance: Favicon file not found. Tried: $file_path, $alt_path1, $alt_path2 (original: $original_path)");
            $appearance['favicon_path'] = $default_appearance['favicon_path'];
        }
    }
} else {
    $appearance['favicon_path'] = $default_appearance['favicon_path'];
}

// Validate primary color (must be valid hex color)
if (!empty($appearance['primary_color'])) {
    // Remove # if present for validation
    $color = ltrim($appearance['primary_color'], '#');
    if (!preg_match('/^[0-9A-Fa-f]{6}$/', $color)) {
        $appearance['primary_color'] = $default_appearance['primary_color'];
    } else {
        $appearance['primary_color'] = '#' . $color;
    }
} else {
    $appearance['primary_color'] = $default_appearance['primary_color'];
}

// Calculate theme color variations from primary color
function hexToRgb($hex) {
    $hex = ltrim($hex, '#');
    return [
        'r' => hexdec(substr($hex, 0, 2)),
        'g' => hexdec(substr($hex, 2, 2)),
        'b' => hexdec(substr($hex, 4, 2))
    ];
}

function rgbToHex($r, $g, $b) {
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

function lightenColor($hex, $percent) {
    $rgb = hexToRgb($hex);
    $r = min(255, $rgb['r'] + round(255 * $percent / 100));
    $g = min(255, $rgb['g'] + round(255 * $percent / 100));
    $b = min(255, $rgb['b'] + round(255 * $percent / 100));
    return rgbToHex($r, $g, $b);
}

function darkenColor($hex, $percent) {
    $rgb = hexToRgb($hex);
    $r = max(0, $rgb['r'] - round(255 * $percent / 100));
    $g = max(0, $rgb['g'] - round(255 * $percent / 100));
    $b = max(0, $rgb['b'] - round(255 * $percent / 100));
    return rgbToHex($r, $g, $b);
}

// Generate color variations
$appearance['primary_light'] = lightenColor($appearance['primary_color'], 20);
$appearance['primary_dark'] = darkenColor($appearance['primary_color'], 20);

// Escape all values for safe output
$appearance['logo_path'] = htmlspecialchars($appearance['logo_path'], ENT_QUOTES, 'UTF-8');
$appearance['favicon_path'] = htmlspecialchars($appearance['favicon_path'], ENT_QUOTES, 'UTF-8');
$appearance['primary_color'] = htmlspecialchars($appearance['primary_color'], ENT_QUOTES, 'UTF-8');
$appearance['primary_light'] = htmlspecialchars($appearance['primary_light'], ENT_QUOTES, 'UTF-8');
$appearance['primary_dark'] = htmlspecialchars($appearance['primary_dark'], ENT_QUOTES, 'UTF-8');
?>

