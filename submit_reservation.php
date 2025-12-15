<?php
// submit_reservation.php
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log file for debugging
$log_file = 'reservation_errors.log';

function logError($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

try {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "joseph_pot_admin";
    
    logError("Attempting database connection...");
    
    $conn = new mysqli($servername, $username, $password, $database);
    
    // Check connection
    if ($conn->connect_error) {
        logError("Database connection failed: " . $conn->connect_error);
        throw new Exception('Database connection failed. Please call us to make your reservation.');
    }
    
    logError("Database connected successfully");
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        logError("POST request received");
        
        // Log all POST data for debugging
        logError("POST Data: " . print_r($_POST, true));
        
        // Get and sanitize form data
        $customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
        $customer_phone = isset($_POST['customer_phone']) ? trim($_POST['customer_phone']) : '';
        $customer_email = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '';
        $reservation_date = isset($_POST['reservation_date']) ? trim($_POST['reservation_date']) : '';
        $reservation_time = isset($_POST['reservation_time']) ? trim($_POST['reservation_time']) : '';
        $party_size = isset($_POST['party_size']) ? intval($_POST['party_size']) : 1;
        $purpose = isset($_POST['purpose']) ? trim($_POST['purpose']) : 'dining';
        $special_requests = isset($_POST['special_requests']) ? trim($_POST['special_requests']) : '';
        
        logError("Form data extracted");
        
        // Convert purpose to database format
        if ($purpose === 'Dining In') $purpose = 'dining';
        else if ($purpose === 'Special Event') $purpose = 'event';
        else if ($purpose === 'Catering') $purpose = 'catering';
        else if ($purpose === 'Takeaway') $purpose = 'takeaway';
        
        // Validate required fields
        if (empty($customer_name)) {
            throw new Exception('Please enter your name.');
        }
        
        if (empty($customer_phone)) {
            throw new Exception('Please enter your phone number.');
        }
        
        if (empty($reservation_date)) {
            throw new Exception('Please select a date.');
        }
        
        if (empty($reservation_time)) {
            throw new Exception('Please select a time.');
        }
        
        if (empty($party_size) || $party_size < 1) {
            throw new Exception('Please select number of guests.');
        }
        
        if (empty($purpose)) {
            throw new Exception('Please select reservation purpose.');
        }
        
        // Validate phone number
        $phone_digits = preg_replace('/\D/', '', $customer_phone);
        if (strlen($phone_digits) < 10) {
            throw new Exception('Please enter a valid phone number (at least 10 digits).');
        }
        
        // Format phone number for database
        $formatted_phone = $customer_phone;
        
        // Format time properly (ensure it's HH:MM:SS)
        if (strlen($reservation_time) === 5) {
            $reservation_time .= ':00';
        }
        
        logError("Data validated, preparing to insert");
        
        // Prepare SQL statement
        $sql = "INSERT INTO reservations (customer_name, customer_phone, customer_email, reservation_date, reservation_time, party_size, purpose, special_requests, source, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'website', 'pending')";
        
        logError("SQL: $sql");
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            logError("Prepare failed: " . $conn->error);
            throw new Exception('Database error. Please try again.');
        }
        
        // Bind parameters
        $bind_result = $stmt->bind_param("sssssiss", 
            $customer_name,
            $formatted_phone,
            $customer_email,
            $reservation_date,
            $reservation_time,
            $party_size,
            $purpose,
            $special_requests
        );
        
        if (!$bind_result) {
            logError("Bind failed: " . $stmt->error);
            throw new Exception('Database error. Please try again.');
        }
        
        // Execute the statement
        $execute_result = $stmt->execute();
        
        if ($execute_result) {
            logError("Reservation inserted successfully. ID: " . $stmt->insert_id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Reservation submitted successfully! We will contact you shortly to confirm.',
                'reservation_id' => $stmt->insert_id
            ]);
        } else {
            logError("Execute failed: " . $stmt->error);
            throw new Exception('Error saving reservation. Please try again or call us directly.');
        }
        
        $stmt->close();
    } else {
        logError("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
        throw new Exception('Invalid request method.');
    }
    
    $conn->close();
    
} catch (Exception $e) {
    logError("Exception: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>