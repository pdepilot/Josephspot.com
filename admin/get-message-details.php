<?php
// get-message-details.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'joseph_pot_admin';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get message ID from request
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid message ID']);
    exit;
}

$messageId = (int)$_GET['id'];

// Fetch message details
$stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $messageId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $message = $result->fetch_assoc();
        
        // Format the message for display
        $formattedMessage = [
            'id' => $message['id'],
            'name' => htmlspecialchars($message['name']),
            'email' => htmlspecialchars($message['email']),
            'phone' => !empty($message['phone']) ? htmlspecialchars($message['phone']) : 'Not provided',
            'subject' => htmlspecialchars($message['subject']),
            'message' => nl2br(htmlspecialchars($message['message'])),
            'status' => $message['status'],
            'ip_address' => !empty($message['ip_address']) ? $message['ip_address'] : 'Unknown',
            'country' => !empty($message['country']) ? $message['country'] : 'Unknown',
            'user_agent' => !empty($message['user_agent']) ? htmlspecialchars(substr($message['user_agent'], 0, 100)) : 'Unknown',
            'created_at' => date('F j, Y g:i A', strtotime($message['created_at']))
        ];
        
        // Mark as read if currently unread
        if ($message['status'] === 'unread') {
            $updateStmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
            $updateStmt->bind_param("i", $messageId);
            $updateStmt->execute();
            $formattedMessage['status'] = 'read';
        }
        
        echo json_encode(['success' => true, 'message' => $formattedMessage]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Message not found']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$conn->close();
?>