<?php
// includes/EmailService.php - COMPLETE VERSION FOR MANUAL PHPMailer

// Manual PHPMailer installation check
$phpmailer_files = [
    'Exception.php' => __DIR__ . '/PHPMailer/src/Exception.php',
    'PHPMailer.php' => __DIR__ . '/PHPMailer/src/PHPMailer.php',
    'SMTP.php' => __DIR__ . '/PHPMailer/src/SMTP.php'
];

// Check if all PHPMailer files exist
$phpmailer_available = true;
foreach ($phpmailer_files as $file => $path) {
    if (!file_exists($path)) {
        $phpmailer_available = false;
        error_log("PHPMailer file missing: $path");
        break;
    }
}

// Load PHPMailer files if available
if ($phpmailer_available) {
    require_once $phpmailer_files['Exception.php'];
    require_once $phpmailer_files['PHPMailer.php'];
    require_once $phpmailer_files['SMTP.php'];
} else {
    // Fallback to basic mail function
    define('PHPMailer_MISSING', true);
    error_log("PHPMailer not available - using basic mail() function");
}

require_once __DIR__ . '/../config/smtp_config.php';

class EmailService {
    private $mail;
    private $lastError;
    
    public function __construct() {
        // If PHPMailer is not available, use basic mail function
        if (defined('PHPMailer_MISSING')) {
            $this->mail = null;
            return;
        }
        
        try {
            // Use fully qualified class name instead of use statement
            $this->mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $this->setupSMTP();
        } catch (Exception $e) {
            $this->lastError = "Mailer Creation Error: " . $e->getMessage();
            error_log($this->lastError);
            $this->mail = null;
        }
    }
    
    private function setupSMTP() {
        if (!$this->mail) return;
        
        try {
            // Server settings
            $this->mail->SMTPDebug = SMTP_DEBUG;
            $this->mail->isSMTP();
            $this->mail->Host       = SMTP_HOST;
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = SMTP_USER;
            $this->mail->Password   = SMTP_PASS;
            $this->mail->SMTPSecure = SMTP_SECURE;
            $this->mail->Port       = SMTP_PORT;
            
            // Encoding
            $this->mail->CharSet = 'UTF-8';
            
            // Default from address
            $this->mail->setFrom(COMPANY_EMAIL, COMPANY_NAME);
            $this->mail->addReplyTo(COMPANY_EMAIL, COMPANY_NAME);
            
        } catch (Exception $e) {
            $this->lastError = "SMTP Setup Error: " . $e->getMessage();
            error_log($this->lastError);
        }
    }
    
    public function sendAdminReservationNotification($reservationData) {
        // If SMTP is not enabled or PHPMailer not available, use basic mail
        if (!SMTP_ENABLED || !$this->mail) {
            return $this->sendAdminNotificationBasic($reservationData);
        }
        
        try {
            // Clear all recipients
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            // Set recipients
            if (TEST_MODE) {
                $this->mail->addAddress(TEST_EMAIL, 'Test Admin');
            } else {
                $this->mail->addAddress(COMPANY_EMAIL, 'Reservations Team');
                // Optional: Add additional admin emails
                // $this->mail->addAddress('manager@josephspot.com', 'Restaurant Manager');
            }
            
            // Subject
            $this->mail->Subject = "📋 New Table Reservation #{$reservationData['id']} - Joseph's Pot";
            $this->mail->Priority = 1; // High priority
            
            // HTML body
            $this->mail->isHTML(true);
            $this->mail->Body = $this->getAdminNotificationHTML($reservationData);
            
            // Plain text alternative
            $this->mail->AltBody = $this->getAdminNotificationText($reservationData);
            
            // Send email
            $result = $this->mail->send();
            
            if ($result) {
                error_log("Admin notification sent successfully for reservation #{$reservationData['id']}");
                return true;
            } else {
                $this->lastError = "Failed to send admin notification: " . $this->mail->ErrorInfo;
                error_log($this->lastError);
                // Fallback to basic mail
                return $this->sendAdminNotificationBasic($reservationData);
            }
            
        } catch (Exception $e) {
            $this->lastError = "Admin Notification Error: " . $e->getMessage();
            error_log($this->lastError);
            // Fallback to basic mail
            return $this->sendAdminNotificationBasic($reservationData);
        }
    }
    
    public function sendUserReservationConfirmation($reservationData) {
        // If SMTP is not enabled or PHPMailer not available, use basic mail
        if (!SMTP_ENABLED || !$this->mail) {
            return $this->sendUserConfirmationBasic($reservationData);
        }
        
        try {
            // Clear all recipients
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            // Set recipient
            $toEmail = TEST_MODE ? TEST_EMAIL : $reservationData['email'];
            $toName = $reservationData['name'];
            
            $this->mail->addAddress($toEmail, $toName);
            
            // Subject
            $this->mail->Subject = "✅ Your Table Reservation at Joseph's Pot";
            $this->mail->Priority = 3; // Normal priority
            
            // HTML body
            $this->mail->isHTML(true);
            $this->mail->Body = $this->getUserConfirmationHTML($reservationData);
            
            // Plain text alternative
            $this->mail->AltBody = $this->getUserConfirmationText($reservationData);
            
            // Send email
            $result = $this->mail->send();
            
            if ($result) {
                error_log("User confirmation sent successfully to {$reservationData['email']}");
                return true;
            } else {
                $this->lastError = "Failed to send user confirmation: " . $this->mail->ErrorInfo;
                error_log($this->lastError);
                // Fallback to basic mail
                return $this->sendUserConfirmationBasic($reservationData);
            }
            
        } catch (Exception $e) {
            $this->lastError = "User Confirmation Error: " . $e->getMessage();
            error_log($this->lastError);
            // Fallback to basic mail
            return $this->sendUserConfirmationBasic($reservationData);
        }
    }
    
    // Basic mail fallback functions
    private function sendAdminNotificationBasic($data) {
        $formattedDate = date('F j, Y', strtotime($data['date']));
        $formattedTime = date('g:i A', strtotime($data['time']));
        
        $subject = "New Table Reservation #{$data['id']} - Joseph's Pot";
        
        $message = "NEW RESERVATION ALERT - Joseph's Pot\n\n";
        $message .= "Reservation Details #{$data['id']}:\n";
        $message .= "Customer Name: {$data['name']}\n";
        $message .= "Email: {$data['email']}\n";
        $message .= "Phone: " . ($data['phone'] ? $data['phone'] : 'Not provided') . "\n";
        $message .= "Date: $formattedDate\n";
        $message .= "Time: $formattedTime\n";
        $message .= "Party Size: {$data['guests']} " . ($data['guests'] == 1 ? 'person' : 'people') . "\n";
        if ($data['message']) {
            $message .= "Special Requests: {$data['message']}\n";
        }
        $message .= "\nNext Steps:\n";
        $message .= "- Review the reservation details\n";
        $message .= "- Contact customer to confirm within 24 hours\n";
        $message .= "- Update reservation status in admin dashboard\n\n";
        $message .= "View in Admin Dashboard: " . SITE_URL . "/admin-reservation.php\n\n";
        $message .= "This email was automatically generated by Joseph's Pot Reservation System";
        
        $headers = "From: " . NOREPLY_EMAIL . "\r\n" .
                   "Reply-To: " . $data['email'] . "\r\n" .
                   "X-Priority: 1\r\n" .
                   "X-MSMail-Priority: High\r\n" .
                   "Importance: High\r\n";
        
        $to = TEST_MODE ? TEST_EMAIL : COMPANY_EMAIL;
        
        $result = mail($to, $subject, $message, $headers);
        error_log("Basic admin notification " . ($result ? "sent" : "failed"));
        return $result;
    }
    
    private function sendUserConfirmationBasic($data) {
        $formattedDate = date('l, F j, Y', strtotime($data['date']));
        $formattedTime = date('g:i A', strtotime($data['time']));
        
        $subject = "Your Table Reservation at Joseph's Pot";
        
        $message = "RESERVATION CONFIRMATION - Joseph's Pot\n\n";
        $message .= "Dear {$data['name']},\n\n";
        $message .= "Thank you for your table reservation at Joseph's Pot! We're excited to welcome you and provide you with an authentic Nigerian dining experience.\n\n";
        $message .= "YOUR RESERVATION DETAILS:\n";
        $message .= "Date: $formattedDate\n";
        $message .= "Time: $formattedTime\n";
        $message .= "Party Size: {$data['guests']} " . ($data['guests'] == 1 ? 'person' : 'people') . "\n";
        if ($data['message']) {
            $message .= "Special Requests: {$data['message']}\n";
        }
        $message .= "\nIMPORTANT: This is an auto-confirmation only. Our team will contact you within 24 hours to confirm your reservation and discuss any special requirements.\n\n";
        $message .= "WHAT HAPPENS NEXT?\n";
        $message .= "1. Our reservation team will review your request\n";
        $message .= "2. We'll call or email you to confirm availability\n";
        $message .= "3. You'll receive a final confirmation with all details\n";
        $message .= "4. We'll prepare for your wonderful dining experience!\n\n";
        $message .= "NEED TO MAKE CHANGES?\n";
        $message .= "Phone: +234-906-429-6917\n";
        $message .= "Email: " . COMPANY_EMAIL . "\n";
        $message .= "Address: 120 Ikenegbu Layout, Owerri, Imo State\n\n";
        $message .= "We look forward to serving you the best of Nigerian cuisine!\n\n";
        $message .= "Warm regards,\n";
        $message .= "The Joseph's Pot Team\n\n";
        $message .= "Joseph's Pot - Where Taste Meets Irresistibility\n";
        $message .= "120 Ikenegbu Layout, Owerri, Imo State, Nigeria";
        
        $headers = "From: " . COMPANY_EMAIL . "\r\n" .
                   "Reply-To: " . COMPANY_EMAIL . "\r\n";
        
        $to = TEST_MODE ? TEST_EMAIL : $data['email'];
        
        $result = mail($to, $subject, $message, $headers);
        error_log("Basic user confirmation " . ($result ? "sent to {$data['email']}" : "failed for {$data['email']}"));
        return $result;
    }
    
    // HTML email template functions
    private function getAdminNotificationHTML($data) {
        $formattedDate = date('F j, Y', strtotime($data['date']));
        $formattedTime = date('g:i A', strtotime($data['time']));
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #8b4513; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
                .reservation-details { background: white; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #8b4513; }
                .detail-row { margin-bottom: 10px; }
                .label { font-weight: bold; color: #8b4513; }
                .footer { text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px; }
                .urgent { background: #fff3cd; padding: 10px; border-radius: 5px; border-left: 4px solid #ffc107; }
                .btn { background: #8b4513; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🍽️ New Reservation Alert</h1>
                    <p>Joseph's Pot Restaurant</p>
                </div>
                <div class='content'>
                    <div class='urgent'>
                        <strong>⚠️ ACTION REQUIRED:</strong> New table reservation received that requires confirmation.
                    </div>
                    
                    <div class='reservation-details'>
                        <h3>Reservation Details #{$data['id']}</h3>
                        <div class='detail-row'><span class='label'>Customer Name:</span> {$data['name']}</div>
                        <div class='detail-row'><span class='label'>Email:</span> {$data['email']}</div>
                        <div class='detail-row'><span class='label'>Phone:</span> " . ($data['phone'] ? $data['phone'] : 'Not provided') . "</div>
                        <div class='detail-row'><span class='label'>Date:</span> $formattedDate</div>
                        <div class='detail-row'><span class='label'>Time:</span> $formattedTime</div>
                        <div class='detail-row'><span class='label'>Party Size:</span> {$data['guests']} " . ($data['guests'] == 1 ? 'person' : 'people') . "</div>
                        " . ($data['message'] ? "<div class='detail-row'><span class='label'>Special Requests:</span> {$data['message']}</div>" : "") . "
                    </div>
                    
                    <p><strong>Next Steps:</strong></p>
                    <ul>
                        <li>Review the reservation details</li>
                        <li>Contact customer to confirm within 24 hours</li>
                        <li>Update reservation status in admin dashboard</li>
                    </ul>
                    
                    <p>
                        <a href='" . SITE_URL . "/admin-reservation.php' class='btn'>
                            📊 View in Admin Dashboard
                        </a>
                    </p>
                </div>
                <div class='footer'>
                    <p>This email was automatically generated by Joseph's Pot Reservation System</p>
                    <p>© " . date('Y') . " Joseph's Pot. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getAdminNotificationText($data) {
        $formattedDate = date('F j, Y', strtotime($data['date']));
        $formattedTime = date('g:i A', strtotime($data['time']));
        
        return "NEW RESERVATION ALERT - Joseph's Pot

Reservation Details #{$data['id']}:
Customer Name: {$data['name']}
Email: {$data['email']}
Phone: " . ($data['phone'] ? $data['phone'] : 'Not provided') . "
Date: $formattedDate
Time: $formattedTime
Party Size: {$data['guests']} " . ($data['guests'] == 1 ? 'person' : 'people') . "
" . ($data['message'] ? "Special Requests: {$data['message']}" : "") . "

Next Steps:
- Review the reservation details
- Contact customer to confirm within 24 hours
- Update reservation status in admin dashboard

View in Admin Dashboard: " . SITE_URL . "/admin-reservation.php

This email was automatically generated by Joseph's Pot Reservation System";
    }
    
    private function getUserConfirmationHTML($data) {
        $formattedDate = date('l, F j, Y', strtotime($data['date']));
        $formattedTime = date('g:i A', strtotime($data['time']));
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: 'Arial', sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
                .header { background: linear-gradient(135deg, #8b4513 0%, #a0522d 100%); color: white; padding: 30px 20px; text-align: center; }
                .content { padding: 30px; background: #f8f5f0; }
                .reservation-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin: 20px 0; border-left: 5px solid #8b4513; }
                .detail-item { margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #eee; }
                .label { font-weight: bold; color: #8b4513; min-width: 120px; display: inline-block; }
                .next-steps { background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .footer { background: #2c3e50; color: white; padding: 20px; text-align: center; font-size: 12px; }
                .contact-info { background: #fff8dc; padding: 15px; border-radius: 8px; margin: 15px 0; }
                .highlight { background: #fff3cd; padding: 10px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🍽️ Reservation Received!</h1>
                    <p>Thank you for choosing Joseph's Pot</p>
                </div>
                
                <div class='content'>
                    <p>Dear <strong>{$data['name']}</strong>,</p>
                    
                    <p>Thank you for your table reservation at Joseph's Pot! We're excited to welcome you and provide you with an authentic Nigerian dining experience.</p>
                    
                    <div class='reservation-card'>
                        <h3 style='color: #8b4513; margin-top: 0;'>Your Reservation Details</h3>
                        
                        <div class='detail-item'>
                            <span class='label'>Date:</span> $formattedDate
                        </div>
                        <div class='detail-item'>
                            <span class='label'>Time:</span> $formattedTime
                        </div>
                        <div class='detail-item'>
                            <span class='label'>Party Size:</span> {$data['guests']} " . ($data['guests'] == 1 ? 'person' : 'people') . "
                        </div>
                        " . ($data['message'] ? "
                        <div class='detail-item'>
                            <span class='label'>Special Requests:</span> {$data['message']}
                        </div>
                        " : "") . "
                    </div>
                    
                    <div class='highlight'>
                        <strong>📞 Important:</strong> This is an <strong>auto-confirmation</strong> only. Our team will contact you within <strong>24 hours</strong> to confirm your reservation and discuss any special requirements.
                    </div>
                    
                    <div class='next-steps'>
                        <h4 style='color: #27ae60; margin-top: 0;'>What Happens Next?</h4>
                        <ol>
                            <li>Our reservation team will review your request</li>
                            <li>We'll call or email you to confirm availability</li>
                            <li>You'll receive a final confirmation with all details</li>
                            <li>We'll prepare for your wonderful dining experience!</li>
                        </ol>
                    </div>
                    
                    <div class='contact-info'>
                        <h4 style='color: #8b4513; margin-top: 0;'>Need to Make Changes?</h4>
                        <p>If you need to modify or cancel your reservation, please contact us:</p>
                        <p>
                            📞 <strong>Phone:</strong> +234-906-429-6917<br>
                            📧 <strong>Email:</strong> " . COMPANY_EMAIL . "<br>
                            🏠 <strong>Address:</strong> 120 Ikenegbu Layout, Owerri, Imo State
                        </p>
                    </div>
                    
                    <p>We look forward to serving you the best of Nigerian cuisine!</p>
                    
                    <p>Warm regards,<br>
                    <strong>The Joseph's Pot Team</strong></p>
                </div>
                
                <div class='footer'>
                    <p>Joseph's Pot - Where Taste Meets Irresistibility</p>
                    <p>120 Ikenegbu Layout, Owerri, Imo State, Nigeria</p>
                    <p>© " . date('Y') . " Joseph's Pot. All rights reserved.</p>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getUserConfirmationText($data) {
        $formattedDate = date('l, F j, Y', strtotime($data['date']));
        $formattedTime = date('g:i A', strtotime($data['time']));
        
        return "RESERVATION CONFIRMATION - Joseph's Pot

Dear {$data['name']},

Thank you for your table reservation at Joseph's Pot! We're excited to welcome you and provide you with an authentic Nigerian dining experience.

YOUR RESERVATION DETAILS:
Date: $formattedDate
Time: $formattedTime
Party Size: {$data['guests']} " . ($data['guests'] == 1 ? 'person' : 'people') . "
" . ($data['message'] ? "Special Requests: {$data['message']}" : "") . "

IMPORTANT: This is an auto-confirmation only. Our team will contact you within 24 hours to confirm your reservation and discuss any special requirements.

WHAT HAPPENS NEXT?
1. Our reservation team will review your request
2. We'll call or email you to confirm availability
3. You'll receive a final confirmation with all details
4. We'll prepare for your wonderful dining experience!

NEED TO MAKE CHANGES?
Phone: +234-906-429-6917
Email: " . COMPANY_EMAIL . "
Address: 120 Ikenegbu Layout, Owerri, Imo State

We look forward to serving you the best of Nigerian cuisine!

Warm regards,
The Joseph's Pot Team

Joseph's Pot - Where Taste Meets Irresistibility
120 Ikenegbu Layout, Owerri, Imo State, Nigeria";
    }
    
    public function getLastError() {
        return $this->lastError;
    }
    
    public function isPHPMailerAvailable() {
        return !defined('PHPMailer_MISSING');
    }
}
?>