<?php
// database_safety_check.php
// Safety checks to prevent wrong database usage

/**
 * Validates that the database name is the correct one
 * @param string $dbname The database name to validate
 * @throws Exception if database name is invalid
 */
function validateDatabaseName($dbname) {
    $allowedDatabases = ['joseph_pot_admin'];
    
    if (!in_array($dbname, $allowedDatabases)) {
        throw new Exception("Invalid database name. Only allowed databases: " . implode(', ', $allowedDatabases));
    }
}

/**
 * Creates a safe PDO connection with validation
 * @param string $host Database host
 * @param string $dbname Database name
 * @param string $username Database username
 * @param string $password Database password
 * @return PDO The PDO connection object
 * @throws Exception if connection fails or database is invalid
 */
function getSafePDOConnection($host, $dbname, $username, $password) {
    // Validate database name before connecting
    validateDatabaseName($dbname);
    
    // Create PDO connection
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}
?>

