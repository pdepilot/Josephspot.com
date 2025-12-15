<?php
// fetch-reviews.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'joseph_pot_admin';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Fetch approved reviews only
$sql = "SELECT name, rating, review_text as text, 
               image_url as imageUrl, 
               DATE_FORMAT(created_at, '%M %e, %Y') as date 
        FROM reviews 
        WHERE status = 'approved' 
        ORDER BY created_at DESC 
        LIMIT 10";

$result = $conn->query($sql);

$reviews = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Generate initials for avatar
        $initials = '';
        $name_parts = explode(' ', $row['name']);
        foreach ($name_parts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper(substr($part, 0, 1));
                if (strlen($initials) >= 2) break;
            }
        }
        
        // Handle image URL
        $imageUrl = $row['imageUrl'];
        
        if (!empty($imageUrl)) {
            // For frontend (index.php at root level), use as-is
            // The path is already: uploads/reviews/filename.jpg
        } else {
            $imageUrl = 'https://randomuser.me/api/portraits/neutral/default.jpg';
        }
        
        $review = [
            'name' => $row['name'],
            'text' => $row['text'],
            'rating' => intval($row['rating']),
            'imageUrl' => $imageUrl, // Use as-is for frontend
            'avatarInitials' => $initials,
            'date' => $row['date']
        ];
        $reviews[] = $review;
    }
}

$conn->close();

echo json_encode($reviews);
?>