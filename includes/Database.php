<?php
// includes/Database.php

class Database {
    private $host = 'localhost';
    private $db_name = 'josephspot';
    private $username = 'root';
    private $password = '';
    private $conn;
    
    public function __construct() {
        // You can also load these from a config file
        $config = $this->loadConfig();
        
        if ($config) {
            $this->host = $config['host'];
            $this->db_name = $config['db_name'];
            $this->username = $config['username'];
            $this->password = $config['password'];
        }
    }
    
    private function loadConfig() {
        $configPath = __DIR__ . '/../config/database_config.php';
        if (file_exists($configPath)) {
            return require $configPath;
        }
        return null;
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Connection failed: " . $e->getMessage());
        }
        
        return $this->conn;
    }
    
    // Test connection
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            return [
                'success' => true,
                'message' => 'Database connection successful',
                'database' => $this->db_name
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
?>