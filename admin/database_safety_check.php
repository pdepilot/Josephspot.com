<?php
/**
 * Database Safety Check
 * 
 * This file should be included in all database connection files
 * to prevent accidental use of the wrong database name.
 */

// Define the ONLY valid database name
define('REQUIRED_DB_NAME', 'joseph_pot_admin');

// List of INVALID database names that should never be used
$INVALID_DB_NAMES = [
    'josep_pot_admin',  // Old incorrect name
    'joseph_pot',       // Common mistake
    'josephspot',       // Common mistake
];

/**
 * Validate database name
 * @param string $dbname The database name to validate
 * @throws Exception if database name is invalid
 */
function validateDatabaseName($dbname) {
    global $INVALID_DB_NAMES;
    
    if ($dbname !== REQUIRED_DB_NAME) {
        if (in_array($dbname, $INVALID_DB_NAMES)) {
            error_log("SECURITY: Attempted to use invalid database: $dbname");
            throw new Exception("Invalid database name: '$dbname'. Only '" . REQUIRED_DB_NAME . "' is allowed.");
        }
        // Warn about unknown database names
        error_log("WARNING: Unknown database name used: $dbname");
    }
}

/**
 * Safe PDO connection with database name validation
 * @param string $host Database host
 * @param string $dbname Database name (will be validated)
 * @param string $username Database username
 * @param string $password Database password
 * @return PDO Database connection
 * @throws Exception if database name is invalid or connection fails
 */
function getSafePDOConnection($host, $dbname, $username, $password) {
    // Validate database name
    validateDatabaseName($dbname);
    
    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch(PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

// Auto-validate if dbname variable exists in global scope
if (isset($dbname)) {
    validateDatabaseName($dbname);
}

// Auto-validate if DB_NAME constant exists
if (defined('DB_NAME')) {
    validateDatabaseName(DB_NAME);
}
?>

