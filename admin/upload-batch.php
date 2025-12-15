<?php
// upload-batch.php
session_start();
require_once 'db-connection.php';

if (!isset($_SESSION['admin_id'])) {
    die('Unauthorized access');
}

// Array of your existing images and videos
$mediaItems = [
    // Images
    ['IM1.jpg', 'Signature Dish', 'Our chef\'s special creation with fresh ingredients', 'food'],
    ['IM2.jpg', 'Spicy Delight', 'A perfect blend of spices and flavors', 'food'],
    
    // Videos - add your video files here
    ['video1.mp4', 'Cooking Tutorial', 'Watch our chef prepare a special dish', 'videos'],
    ['video2.mp4', 'Restaurant Tour', 'Take a virtual tour of our restaurant', 'videos'],
    // Add more videos as needed...
];

foreach ($mediaItems as $item) {
    $filename = $item[0];
    $title = $item[1];
    $description = $item[2];
    $category = $item[3];
    
    // Determine file type based on extension
    $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $file_type = 'image';
        $file_path = '../images/' . $filename;
    } elseif (in_array($fileExtension, ['mp4', 'webm', 'ogg', 'mov', 'avi', 'wmv'])) {
        $file_type = 'video';
        $file_path = '../videos/' . $filename; // Adjust this path if your videos are in a different folder
    } else {
        echo "Skipped (unsupported file type): $filename<br>";
        continue;
    }
    
    // Check if file exists
    if (!file_exists($file_path)) {
        echo "File not found, skipping: $file_path<br>";
        continue;
    }
    
    $stmt = $conn->prepare("INSERT INTO gallery (title, description, category, file_type, file_path, upload_date) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $title, $description, $category, $file_type, $file_path);
    
    if ($stmt->execute()) {
        echo "Added: $title ($file_type)<br>";
    } else {
        echo "Error adding: $title - " . $stmt->error . "<br>";
    }
    
    $stmt->close();
}

echo "Batch upload complete!";
$conn->close();
?>