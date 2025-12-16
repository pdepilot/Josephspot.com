<?php
// admin/includes/ReservationModel.php
require_once 'ReservationDatabase.php';

class ReservationModel {
    private $conn;
    
    public function __construct() {
        try {
            $db = new ReservationDatabase();
            $this->conn = $db->getConnection();
        } catch (Exception $e) {
            throw new Exception("Could not initialize reservation model: " . $e->getMessage());
        }
    }
    
    public function getAllReservations($filters = []) {
        $sql = "SELECT * FROM reservations WHERE 1=1";
        $params = [];
        
        if (isset($filters['status']) && !empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }
        
        $sql .= " ORDER BY reservation_date DESC, reservation_time DESC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("GetAllReservations Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTodayReservations() {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM reservations WHERE reservation_date = :today ORDER BY reservation_time ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':today' => $today]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("GetTodayReservations Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getUpcomingReservations() {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM reservations WHERE reservation_date > :today AND status IN ('pending', 'confirmed') ORDER BY reservation_date ASC, reservation_time ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':today' => $today]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("GetUpcomingReservations Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPendingReservations() {
        $sql = "SELECT * FROM reservations WHERE status = 'pending' ORDER BY created_at DESC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("GetPendingReservations Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getReservationStats() {
        $today = date('Y-m-d');
        $firstDayOfMonth = date('Y-m-01');
        $lastDayOfMonth = date('Y-m-t');
        
        $stats = ['today' => 0, 'upcoming' => 0, 'completed_this_month' => 0, 'cancelled_this_month' => 0];
        
        try {
            // Today's count
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE reservation_date = :today");
            $stmt->execute([':today' => $today]);
            $stats['today'] = $stmt->fetch()['count'];
            
            // Upcoming count
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE reservation_date > :today AND status IN ('pending', 'confirmed')");
            $stmt->execute([':today' => $today]);
            $stats['upcoming'] = $stmt->fetch()['count'];
            
            // Completed this month
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE status = 'completed' AND reservation_date BETWEEN :first_day AND :last_day");
            $stmt->execute([':first_day' => $firstDayOfMonth, ':last_day' => $lastDayOfMonth]);
            $stats['completed_this_month'] = $stmt->fetch()['count'];
            
            // Cancelled this month
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE status = 'cancelled' AND reservation_date BETWEEN :first_day AND :last_day");
            $stmt->execute([':first_day' => $firstDayOfMonth, ':last_day' => $lastDayOfMonth]);
            $stats['cancelled_this_month'] = $stmt->fetch()['count'];
            
        } catch (PDOException $e) {
            error_log("GetReservationStats Error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    public function getReservationById($id) {
        $sql = "SELECT * FROM reservations WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("GetReservationById Error: " . $e->getMessage());
            return null;
        }
    }
    
    public function createReservation($data) {
        $sql = "INSERT INTO reservations (
            customer_name, customer_email, customer_phone,
            reservation_date, reservation_time, party_size,
            purpose, special_requests, status
        ) VALUES (
            :customer_name, :customer_email, :customer_phone,
            :reservation_date, :reservation_time, :party_size,
            :purpose, :special_requests, :status
        )";
        
        $params = [
            ':customer_name' => $data['customer_name'],
            ':customer_email' => $data['customer_email'],
            ':customer_phone' => $data['customer_phone'],
            ':reservation_date' => $data['reservation_date'],
            ':reservation_time' => $data['reservation_time'],
            ':party_size' => $data['party_size'],
            ':purpose' => $data['purpose'],
            ':special_requests' => $data['special_requests'],
            ':status' => $data['status'] ?? 'pending'
        ];
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("CreateReservation Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateReservation($id, $data) {
        $sql = "UPDATE reservations SET
            customer_name = :customer_name,
            customer_email = :customer_email,
            customer_phone = :customer_phone,
            reservation_date = :reservation_date,
            reservation_time = :reservation_time,
            party_size = :party_size,
            purpose = :purpose,
            special_requests = :special_requests,
            status = :status,
            updated_at = NOW()
        WHERE id = :id";
        
        $params = [
            ':customer_name' => $data['customer_name'],
            ':customer_email' => $data['customer_email'],
            ':customer_phone' => $data['customer_phone'],
            ':reservation_date' => $data['reservation_date'],
            ':reservation_time' => $data['reservation_time'],
            ':party_size' => $data['party_size'],
            ':purpose' => $data['purpose'],
            ':special_requests' => $data['special_requests'],
            ':status' => $data['status'],
            ':id' => $id
        ];
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("UpdateReservation Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteReservation($id) {
        $sql = "DELETE FROM reservations WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("DeleteReservation Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function searchReservations($searchTerm) {
        $sql = "SELECT * FROM reservations WHERE 
                customer_name LIKE :search OR 
                customer_email LIKE :search OR 
                customer_phone LIKE :search OR 
                special_requests LIKE :search
                ORDER BY reservation_date DESC";
        
        $params = [':search' => "%" . $searchTerm . "%"];
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("SearchReservations Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function updateStatus($id, $status) {
        $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $sql = "UPDATE reservations SET status = :status WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':status' => $status, ':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("UpdateStatus Error: " . $e->getMessage());
            return false;
        }
    }
}
?>