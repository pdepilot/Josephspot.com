<?php
session_start();

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'joseph_pot_admin';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed");
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die("Unauthorized access");
}

// Get filter parameters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$countryFilter = isset($_GET['country']) ? $_GET['country'] : 'all';

// Build WHERE conditions
$whereConditions = [];
$params = [];
$types = '';

if ($statusFilter != 'all') {
    $whereConditions[] = "status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if (!empty($dateFrom)) {
    $whereConditions[] = "DATE(created_at) >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}

if (!empty($dateTo)) {
    $whereConditions[] = "DATE(created_at) <= ?";
    $params[] = $dateTo;
    $types .= 's';
}

if (!empty($searchQuery)) {
    $whereConditions[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $searchTerm = "%$searchQuery%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= str_repeat('s', 5);
}

if ($countryFilter != 'all') {
    $whereConditions[] = "country = ?";
    $params[] = $countryFilter;
    $types .= 's';
}

$whereSQL = '';
if (!empty($whereConditions)) {
    $whereSQL = "WHERE " . implode(" AND ", $whereConditions);
}

// Get messages
$query = "SELECT * FROM contact_messages $whereSQL ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=contact_messages_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'ID', 'Name', 'Email', 'Phone', 'Subject', 'Message', 
    'Status', 'IP Address', 'Country', 'Created At'
]);

// Add data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['email'],
        $row['phone'],
        $row['subject'],
        strip_tags($row['message']),
        $row['status'],
        $row['ip_address'],
        $row['country'],
        $row['created_at']
    ]);
}

fclose($output);
$conn->close();
?>