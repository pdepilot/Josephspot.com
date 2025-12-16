<?php
// api/get_reservations_api.php
header('Content-Type: application/json');

// Check authentication
require_once '../includes/admin_auth.php';
checkAdminLogin();

// Include the reservation model
require_once '../includes/ReservationModel.php';

try {
    $reservationModel = new ReservationModel();
    
    $tab = $_GET['tab'] ?? 'all';
    
    switch ($tab) {
        case 'today':
            $reservations = $reservationModel->getTodayReservations();
            break;
        case 'upcoming':
            $reservations = $reservationModel->getUpcomingReservations();
            break;
        case 'pending':
            $reservations = $reservationModel->getPendingReservations();
            break;
        default:
            $reservations = $reservationModel->getAllReservations();
            break;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $reservations
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>