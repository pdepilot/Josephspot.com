<?php
// admin/includes/notification_helper.php
// Helper function to create notifications

function createNotification($conn, $admin_id, $type, $title, $message, $reference_id = null) {
    // Get all active admins if admin_id is null (for system-wide notifications)
    $admin_ids = [];
    
    if ($admin_id === null) {
        // Get all active admin IDs
        $result = $conn->query("SELECT id FROM admins WHERE is_active = 1");
        while ($row = $result->fetch_assoc()) {
            $admin_ids[] = $row['id'];
        }
    } else {
        $admin_ids = [$admin_id];
    }
    
    if (empty($admin_ids)) {
        return false; // No admins to notify
    }
    
    // Insert notification for each admin
    $inserted = 0;
    foreach ($admin_ids as $aid) {
        if ($reference_id === null) {
            $stmt = $conn->prepare("
                INSERT INTO notifications (admin_id, type, title, message, is_read, created_at)
                VALUES (?, ?, ?, ?, 0, NOW())
            ");
            $stmt->bind_param("isss", $aid, $type, $title, $message);
        } else {
            $stmt = $conn->prepare("
                INSERT INTO notifications (admin_id, type, title, message, reference_id, is_read, created_at)
                VALUES (?, ?, ?, ?, ?, 0, NOW())
            ");
            $stmt->bind_param("isssi", $aid, $type, $title, $message, $reference_id);
        }
        if ($stmt->execute()) {
            $inserted++;
        }
        $stmt->close();
    }
    
    return $inserted > 0;
}
?>

