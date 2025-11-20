<?php
session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'joseph_pot_admin');

echo "<h1>Database Setup for Joseph's Pot Admin</h1>";
echo "<div style='padding:20px; background:#f0f0f0;'>";

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("<div style='color:red;'>Connection failed: " . $conn->connect_error . "</div>");
}

echo "<div style='color:green;'>✓ Connected to MySQL server</div>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === FALSE) {
    die("<div style='color:red;'>Error creating database: " . $conn->error . "</div>");
}

echo "<div style='color:green;'>✓ Database '" . DB_NAME . "' created/verified</div>";

// Select the database
$conn->select_db(DB_NAME);

// Create admin table
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
    die("<div style='color:red;'>Error creating table: " . $conn->error . "</div>");
}

echo "<div style='color:green;'>✓ Admin table created/verified</div>";

// Check if admin already exists
$result = $conn->query("SELECT id FROM admins WHERE username = 'admin'");

if ($result->num_rows === 0) {
    // Create admin user
    $password = 'admin123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $username = 'admin';
    $email = 'admin@josephspot.com';
    
    $stmt = $conn->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    
    if ($stmt->execute()) {
        echo "<div style='color:green;'>✓ Admin user created successfully!</div>";
        echo "<div style='background:lightgreen; padding:10px; margin:10px;'>";
        echo "<strong>Login Credentials:</strong><br>";
        echo "Username: <strong>admin</strong><br>";
        echo "Password: <strong>admin123</strong><br>";
        echo "Email: <strong>admin@josephspot.com</strong>";
        echo "</div>";
    } else {
        echo "<div style='color:red;'>Error creating admin: " . $stmt->error . "</div>";
    }
} else {
    echo "<div style='color:blue;'>✓ Admin user already exists</div>";
}

// Verify the admin was created
$result = $conn->query("SELECT username, email FROM admins");
echo "<div style='margin-top:20px; padding:10px; background:white;'>";
echo "<strong>Current Admin Users in Database:</strong><br>";
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "- " . $row['username'] . " (" . $row['email'] . ")<br>";
    }
} else {
    echo "No admin users found!";
}
echo "</div>";

echo "</div>";

echo "<div style='margin-top:20px;'>";
echo "<a href='admin-login.php' style='padding:10px 20px; background:blue; color:white; text-decoration:none;'>Go to Login Page</a>";
echo "</div>";

$conn->close();
?>