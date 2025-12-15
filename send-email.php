<?php
// send-email.php
session_start();
require_once 'vendor/autoload.php'; // For PHPMailer

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = $_POST['to'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    // Use PHPMailer to send email
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@gmail.com';
    $mail->Password = 'your-password';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    
    $mail->setFrom('noreply@josephspot.com', 'Joseph\'s Pot');
    $mail->addAddress($to);
    $mail->Subject = $subject;
    $mail->Body = $message;
    
    if ($mail->send()) {
        echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email']);
    }
}
?>