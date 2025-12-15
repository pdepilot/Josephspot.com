<?php
session_start();
require_once 'db-connection.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    
    try {
        // First get file paths
        $stmt = $conn->prepare("SELECT file_path, thumbnail_path FROM gallery WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Delete files from server
            if (file_exists($row['file_path'])) {
                unlink($row['file_path']);
            }
            if ($row['thumbnail_path'] && file_exists($row['thumbnail_path'])) {
                unlink($row['thumbnail_path']);
            }
        }
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Gallery item deleted successfully']);
        } else {
            throw new Exception('Database error: ' . $stmt->error);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>