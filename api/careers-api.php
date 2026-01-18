<?php
/**
 * Careers API Endpoint
 * Handles all careers-related API requests
 */

session_start();
header('Content-Type: application/json');

// CORS headers for frontend access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/careers-functions.php';

// Helper function to check admin authentication
function requireAdminAuth() {
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized - Admin login required']);
        exit;
    }
}

// Helper function to get request method
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    // Route requests based on action
    switch ($action) {
        // ===== JOBS ENDPOINTS =====
        case 'get_jobs':
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $filters = [];
            if (isset($_GET['status'])) $filters['status'] = $_GET['status'];
            if (isset($_GET['department'])) $filters['department'] = $_GET['department'];
            if (isset($_GET['job_type'])) $filters['job_type'] = $_GET['job_type'];
            if (isset($_GET['search'])) $filters['search'] = $_GET['search'];
            if (isset($_GET['active_only'])) $filters['active_only'] = ($_GET['active_only'] === 'true');
            
            $jobs = getJobs($filters);
            echo json_encode(['success' => true, 'jobs' => $jobs]);
            break;
            
        case 'get_job':
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                throw new Exception('Job ID is required');
            }
            
            $job = getJob($id);
            if (!$job) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Job not found']);
            } else {
                echo json_encode(['success' => true, 'job' => $job]);
            }
            break;
            
        case 'create_job':
            requireAdminAuth();
            
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                $data = $_POST;
            }
            
            // Validate required fields
            $required = ['title', 'department', 'job_type', 'location', 'description', 'requirements', 'responsibilities'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Field '{$field}' is required");
                }
            }
            
            $result = createJob($data);
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            break;
            
        case 'update_job':
            requireAdminAuth();
            
            if ($method !== 'PUT' && $method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $id = $_GET['id'] ?? ($_POST['id'] ?? 0);
            if (!$id) {
                throw new Exception('Job ID is required');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                $data = $_POST;
            }
            unset($data['id']); // Remove ID from data
            
            $result = updateJob($id, $data);
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            break;
            
        case 'delete_job':
            requireAdminAuth();
            
            if ($method !== 'DELETE' && $method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $id = $_GET['id'] ?? ($_POST['id'] ?? 0);
            if (!$id) {
                throw new Exception('Job ID is required');
            }
            
            $result = deleteJob($id);
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            break;
            
        // ===== APPLICATIONS ENDPOINTS =====
        case 'get_applications':
            requireAdminAuth();
            
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $filters = [];
            if (isset($_GET['job_id'])) $filters['job_id'] = $_GET['job_id'];
            if (isset($_GET['status'])) $filters['status'] = $_GET['status'];
            if (isset($_GET['search'])) $filters['search'] = $_GET['search'];
            if (isset($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
            if (isset($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];
            
            $applications = getApplications($filters);
            echo json_encode(['success' => true, 'applications' => $applications]);
            break;
            
        case 'get_application':
            requireAdminAuth();
            
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                throw new Exception('Application ID is required');
            }
            
            $application = getApplication($id);
            if (!$application) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Application not found']);
            } else {
                $history = getApplicationHistory($id);
                echo json_encode([
                    'success' => true,
                    'application' => $application,
                    'history' => $history
                ]);
            }
            break;
            
        case 'create_application':
            // Public endpoint - no auth required
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            // Rate limiting check
            $email = $_POST['applicant_email'] ?? '';
            if (!empty($email)) {
                $rateLimit = checkApplicationRateLimit($email);
                if (!$rateLimit['allowed']) {
                    http_response_code(429);
                    echo json_encode(['success' => false, 'message' => $rateLimit['message']]);
                    exit;
                }
            }
            
            // Validate required fields
            $required = ['job_id', 'applicant_name', 'applicant_email', 'applicant_phone'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field '{$field}' is required");
                }
            }
            
            // Validate email
            if (!filter_var($_POST['applicant_email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address');
            }
            
            // Prepare data
            $data = [
                'job_id' => intval($_POST['job_id']),
                'applicant_name' => trim($_POST['applicant_name']),
                'applicant_email' => trim($_POST['applicant_email']),
                'applicant_phone' => trim($_POST['applicant_phone']),
                'cover_letter' => $_POST['cover_letter'] ?? null,
                'years_experience' => isset($_POST['years_experience']) ? intval($_POST['years_experience']) : 0,
                'current_position' => $_POST['current_position'] ?? null,
                'current_company' => $_POST['current_company'] ?? null,
                'expected_salary' => $_POST['expected_salary'] ?? null,
                'notice_period' => $_POST['notice_period'] ?? null
            ];
            
            // Handle resume upload
            $resumeFile = null;
            if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
                $resumeFile = $_FILES['resume'];
            }
            
            $result = createApplication($data, $resumeFile);
            if ($result['success']) {
                http_response_code(201);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            break;
            
        case 'update_application_status':
            requireAdminAuth();
            
            if ($method !== 'PUT' && $method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $id = $_GET['id'] ?? ($_POST['id'] ?? 0);
            if (!$id) {
                throw new Exception('Application ID is required');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                $data = $_POST;
            }
            
            $status = $data['status'] ?? '';
            $notes = $data['notes'] ?? null;
            $changedBy = $_SESSION['admin_id'] ?? null;
            
            if (empty($status)) {
                throw new Exception('Status is required');
            }
            
            $result = updateApplicationStatus($id, $status, $notes, $changedBy);
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            break;
            
        // ===== DASHBOARD & STATISTICS =====
        case 'get_stats':
            requireAdminAuth();
            
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $stats = getCareerStats();
            echo json_encode(['success' => true, 'stats' => $stats]);
            break;
            
        // ===== NOTIFICATIONS =====
        case 'get_notifications':
            requireAdminAuth();
            
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            // Get notifications for current admin, or all if no specific user
            $userId = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
            $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
            
            $notifications = getNotifications($userId, $unreadOnly, $limit);
            echo json_encode(['success' => true, 'notifications' => $notifications]);
            break;
            
        case 'mark_notification_read':
            requireAdminAuth();
            
            if ($method !== 'POST' && $method !== 'PUT') {
                throw new Exception('Method not allowed');
            }
            
            $id = $_GET['id'] ?? ($_POST['id'] ?? 0);
            if (!$id) {
                throw new Exception('Notification ID is required');
            }
            
            $result = markNotificationRead($id);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Failed to update notification']);
            }
            break;
            
        // ===== APPLICATION HISTORY =====
        case 'get_application_history':
            requireAdminAuth();
            
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                throw new Exception('Application ID is required');
            }
            
            $history = getApplicationHistory($id);
            echo json_encode(['success' => true, 'history' => $history]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action. Available actions: get_jobs, get_job, create_job, update_job, delete_job, get_applications, get_application, create_application, update_application_status, get_stats, get_notifications, mark_notification_read, get_application_history'
            ]);
            break;
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Careers API Database Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
