<?php
session_start();
require 'includes/db.php';
require 'includes/mail.php';

// Set content type for JSON response
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Something went wrong.'];

try {
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $email = trim($_POST['email'] ?? '');
        
        if(empty($email)){
            $response['message'] = 'Please enter your email.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Please enter a valid email address.';
        } else {
            // Check if admin exists
            $stmt = $conn->prepare("SELECT id, username FROM admins WHERE email=? LIMIT 1");
            if (!$stmt) {
                throw new Exception('Database preparation failed: ' . $conn->error);
            }
            
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows === 1){
                $admin = $result->fetch_assoc();
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 1800); // 30 minutes validity

                // Save token and expiration
                $stmt = $conn->prepare("UPDATE admins SET reset_token=?, reset_expires=? WHERE id=?");
                if (!$stmt) {
                    throw new Exception('Database preparation failed: ' . $conn->error);
                }
                
                $stmt->bind_param("ssi", $token, $expires, $admin['id']);
                
                if(!$stmt->execute()){
                    throw new Exception('Database update failed: ' . $stmt->error);
                }

                // Send email
                $resetLink = "https://yourdomain.com/reset-password.php?token=" . urlencode($token);
                $mail = new Mail();
                $mail->addAddress($email, $admin['username']);
                $mail->setSubject("Password Reset Request - Joseph's Pot");
                $mail->setBody("
                    Hello {$admin['username']},<br><br>
                    You requested a password reset. Click the link below to reset your password:<br>
                    <a href='{$resetLink}' target='_blank'>{$resetLink}</a><br><br>
                    This link expires in 30 minutes.<br><br>
                    If you did not request this, please ignore this email.
                ");

                if($mail->send()){
                    $response['status'] = 'success';
                    $response['message'] = 'Password reset link has been sent to your email.';
                } else {
                    $response['message'] = 'Failed to send email. Please try again later.';
                }

            } else {
                // For security, don't reveal if email exists or not
                $response['status'] = 'success';
                $response['message'] = 'If the email exists in our system, a password reset link has been sent.';
            }
        }
    } else {
        $response['message'] = 'Invalid request method.';
    }
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Password reset error: " . $e->getMessage());
    $response['message'] = 'An error occurred. Please try again later.';
}

echo json_encode($response);
?>