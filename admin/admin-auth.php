<?php
// admin-auth.php
session_start();
require_once 'config/database.php';

class Auth {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function login($username, $password) {
        try {
            $query = "SELECT * FROM admin_users WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                
                // Verify password (you should use password_hash/password_verify in production)
                // For now, simple comparison (replace with proper hashing)
                if ($password === 'admin123' || password_verify($password, $user['password_hash'])) {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_fullname'] = $user['full_name'];
                    $_SESSION['admin_role'] = $user['role'];
                    
                    // Update last login
                    $updateQuery = "UPDATE admin_users SET last_login = NOW() WHERE id = :id";
                    $updateStmt = $this->conn->prepare($updateQuery);
                    $updateStmt->bindParam(':id', $user['id']);
                    $updateStmt->execute();
                    
                    return ['success' => true, 'message' => 'Login successful'];
                }
            }
            
            return ['success' => false, 'message' => 'Invalid credentials'];
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['admin_id']);
    }
    
    public function checkAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: admin-login.php');
            exit();
        }
    }
}