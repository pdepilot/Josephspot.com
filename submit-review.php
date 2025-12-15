<?php
// submit-review.php

header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'joseph_pot_admin';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get form data
$name = isset($_POST['name']) ? $conn->real_escape_string(trim($_POST['name'])) : '';
$review = isset($_POST['review']) ? $conn->real_escape_string(trim($_POST['review'])) : '';
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 5;
$email = isset($_POST['email']) ? $conn->real_escape_string(trim($_POST['email'])) : '';

// Validate data
if (empty($name) || empty($review)) {
    echo json_encode(['success' => false, 'message' => 'Name and review are required']);
    exit();
}

if ($rating < 1 || $rating > 5) {
    $rating = 5;
}

// Handle image upload
$image_url = '';

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/reviews/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($_FILES['image']['tmp_name']);
    
    if (in_array($file_type, $allowed_types)) {
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $file_name = 'review_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            // Store full relative path
            $image_url = $upload_dir . $file_name; // This will be: uploads/reviews/review_...
        }
    }
}

// Insert into database
$sql = "INSERT INTO reviews (name, email, rating, review_text, image_url, status, created_at) 
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
        
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
    exit();
}

$stmt->bind_param("ssiss", $name, $email, $rating, $review, $image_url);

if ($stmt->execute()) {
    $review_id = $stmt->insert_id;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Review submitted for approval',
        'review_id' => $review_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>