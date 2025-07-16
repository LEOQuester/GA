<?php
/**
 * Email Service for Gaming Arena
 * Handles email notifications for booking confirmations and cancellations
 */

// Include PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $from_email;
    private $from_name;

    public function __construct()
    {
        // Load email configuration
        $this->smtp_host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
        $this->smtp_port = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $this->smtp_username = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
        $this->smtp_password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
        $this->from_email = defined('FROM_EMAIL') ? FROM_EMAIL : 'mrrilyaas@gmail.com';
        $this->from_name = defined('FROM_NAME') ? FROM_NAME : 'G-Arena Gaming Center';
    }

    /**
     * Send booking confirmation email
     */
    public function sendBookingConfirmation($booking_id)
    {
        $booking = $this->getBookingDetails($booking_id);
        if (!$booking) {
            return ['success' => false, 'message' => 'Booking not found'];
        }

        $subject = 'Booking Confirmed - G-Arena Gaming Center';
        $html_content = $this->getConfirmationEmailTemplate($booking);
        $text_content = $this->getConfirmationEmailText($booking);

        return $this->sendEmail(
            $booking['user_email'],
            $booking['full_name'],
            $subject,
            $html_content,
            $text_content
        );
    }

    /**
     * Send booking cancellation email
     */
    public function sendBookingCancellation($booking_id)
    {
        $booking = $this->getBookingDetails($booking_id);
        if (!$booking) {
            return ['success' => false, 'message' => 'Booking not found'];
        }

        $subject = 'Booking Cancelled - G-Arena Gaming Center';
        $html_content = $this->getCancellationEmailTemplate($booking);
        $text_content = $this->getCancellationEmailText($booking);

        return $this->sendEmail(
            $booking['user_email'],
            $booking['full_name'],
            $subject,
            $html_content,
            $text_content
        );
    }

    /**
     * Get booking details with user and station information
     */
    private function getBookingDetails($booking_id)
    {
        // Include database functions if not already included
        if (!function_exists('escapeString')) {
            require_once __DIR__ . '/../config/database.php';
        }
        
        $sql = "SELECT b.*, 
                       s.station_name, s.station_type, s.description,
                       u.email as user_email, u.full_name, u.username
                FROM bookings b 
                JOIN gaming_stations s ON b.station_id = s.id 
                JOIN users u ON b.user_id = u.id 
                WHERE b.id = " . escapeString($booking_id);

        error_log("G-Arena Email Debug: Fetching booking details with SQL: " . $sql);
        
        $result = fetchSingleRow($sql);
        
        if ($result) {
            error_log("G-Arena Email Debug: Found booking {$booking_id} for user {$result['username']} with email: " . ($result['user_email'] ?: 'NO EMAIL'));
        } else {
            error_log("G-Arena Email Debug: No booking found with ID {$booking_id}");
        }
        
        return $result;
    }

    /**
     * HTML template for booking confirmation
     */
    public function getConfirmationEmailTemplate($booking)
    {
        $booking_date = date('F j, Y', strtotime($booking['booking_date']));
        $start_time = date('g:i A', strtotime($booking['start_time']));
        $end_time = date('g:i A', strtotime($booking['end_time']));
        $amount = number_format($booking['total_amount'], 2);

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Booking Confirmed</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #8B5CF6 0%, #A855F7 100%); padding: 30px; text-align: center; color: white; }
                .header h1 { margin: 0; font-size: 28px; }
                .header p { margin: 10px 0 0 0; font-size: 16px; opacity: 0.9; }
                .content { padding: 30px; }
                .booking-card { background: #f8fafc; border-left: 4px solid #8B5CF6; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0; }
                .booking-detail { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
                .booking-detail:last-child { border-bottom: none; }
                .label { font-weight: bold; color: #4a5568; }
                .value { color: #2d3748; }
                .amount { font-size: 18px; font-weight: bold; color: #059669; }
                .footer { background: #1a202c; padding: 20px; text-align: center; color: #a0aec0; }
                .footer a { color: #8B5CF6; text-decoration: none; }
                .status-badge { display: inline-block; background: #10b981; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Booking Confirmed!</h1>
                    <p>Your gaming session is ready to roll</p>
                </div>
                
                <div class='content'>
                    <p>Dear {$booking['full_name']},</p>
                    
                    <p>Great news! Your booking has been <strong>confirmed</strong> by our admin team. Get ready for an epic gaming experience!</p>
                    
                    <div class='booking-card'>
                        <h3 style='margin-top: 0; color: #8B5CF6;'>Booking Details</h3>
                        
                        <div class='booking-detail'>
                            <span class='label'>Booking Reference:</span>
                            <span class='value'><strong>{$booking['booking_reference']}</strong></span>
                        </div>
                        
                        <div class='booking-detail'>
                            <span class='label'>Status:</span>
                            <span class='value'><span class='status-badge'>Confirmed</span></span>
                        </div>
                        
                        <div class='booking-detail'>
                            <span class='label'>Gaming Station:</span>
                            <span class='value'>{$booking['station_name']}</span>
                        </div>
                        
                        <div class='booking-detail'>
                            <span class='label'>Station Type:</span>
                            <span class='value'>{$booking['station_type']}</span>
                        </div>
                        
                        <div class='booking-detail'>
                            <span class='label'>Date:</span>
                            <span class='value'>{$booking_date}</span>
                        </div>
                        
                        <div class='booking-detail'>
                            <span class='label'>Time:</span>
                            <span class='value'>{$start_time} - {$end_time}</span>
                        </div>
                        
                        <div class='booking-detail'>
                            <span class='label'>Duration:</span>
                            <span class='value'>{$booking['total_hours']} hours</span>
                        </div>
                        
                        <div class='booking-detail'>
                            <span class='label'>Total Amount:</span>
                            <span class='value amount'>LKR {$amount}</span>
                        </div>
                    </div>
                    
                    <div style='background: #eff6ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h4 style='margin-top: 0; color: #1e40af;'>Important Reminders:</h4>
                        <ul style='margin-bottom: 0; padding-left: 20px;'>
                            <li>Please arrive 10 minutes before your session</li>
                            <li>Bring a valid ID for verification</li>
                            <li>Payment is due upon arrival</li>
                            <li>Our gaming center opens at 9:00 AM and closes at 8:00 PM</li>
                        </ul>
                    </div>
                    
                    <p>We're excited to have you game with us! If you have any questions or need to make changes, please contact us immediately.</p>
                    
                    <p>Happy Gaming!<br>
                    <strong>The G-Arena Team</strong></p>
                </div>
                
                <div class='footer'>
                    <p>G-Arena Gaming Center | <a href='mailto:support@g-arena.com'>support@g-arena.com</a></p>
                    <p style='font-size: 12px; margin: 5px 0 0 0;'>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * HTML template for booking cancellation
     */
    public function getCancellationEmailTemplate($booking)
    {
        $booking_date = date('F j, Y', strtotime($booking['booking_date']));
        $start_time = date('g:i A', strtotime($booking['start_time']));
        $end_time = date('g:i A', strtotime($booking['end_time']));
        $amount = number_format($booking['total_amount'], 2);

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Booking Cancelled</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); padding: 30px; text-align: center; color: white; }
                .header h1 { margin: 0; font-size: 28px; }
                .header p { margin: 10px 0 0 0; font-size: 16px; opacity: 0.9; }
                .content { padding: 30px; }
                .booking-card { background: #fef2f2; border-left: 4px solid #EF4444; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0; }
                .booking-detail { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #fecaca; }
                .booking-detail:last-child { border-bottom: none; }
                .label { font-weight: bold; color: #4a5568; }
                .value { color: #2d3748; }
                .amount { font-size: 18px; font-weight: bold; color: #dc2626; }
                .footer { background: #1a202c; padding: 20px; text-align: center; color: #a0aec0; }
                .footer a { color: #8B5CF6; text-decoration: none; }
                .status-badge { display: inline-block; background: #ef4444; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Booking Cancelled</h1>
                    <p>Your gaming session has been cancelled</p>
                </div>
                
                <div class='content'>
                    <p>Dear {$booking['full_name']},</p>
                    
                    <p>We regret to inform you that your booking has been <strong>cancelled</strong> by our admin team. This could be due to maintenance requirements, technical issues, or other operational needs.</p>
                    
                    <div class='booking-card'>
                        <h3 style='margin-top: 0; color: #EF4444;'>Cancelled Booking Details</h3>
                        
                        <div class='booking-detail'>
                            <span class='label'>Booking Reference:</span>
                            <span class='value'><strong>{$booking['booking_reference']}</strong></span>
                        </div>
                        
                        <div class='booking-detail'>
                            <span class='label'>Status:</span>
                            <span class='value'><span class='status-badge'>Cancelled</span></span>
                        </div>
                        
                        <div class='booking-detail'>
                            <span class='label'>Gaming Station:</span>
                            <span class='value'>{$booking['station_name']}</span>
                        </div>
                        
                        <div class='booking-detail'>
                            <span class='label'>Station Type:</span>
                            <span class='value'>{$booking['station_type']}</span>
                        </div>
                        
                        <div class='booking-detail'>
                            <span class='label'>Date:</span>
                            <span class='value'>{$booking_date}</span>
                        </div>
                        
                        <div class='booking-detail'>
                            <span class='label'>Time:</span>
                            <span class='value'>{$start_time} - {$end_time}</span>
                        </div>
                        
                        <div class='booking-detail'>
                            <span class='label'>Duration:</span>
                            <span class='value'>{$booking['total_hours']} hours</span>
                        </div>
                        
                        <div class='booking-detail'>
                            <span class='label'>Amount:</span>
                            <span class='value amount'>LKR {$amount}</span>
                        </div>
                    </div>
                    
                    <div style='background: #fef3c7; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #f59e0b;'>
                        <h4 style='margin-top: 0; color: #92400e;'>What's Next?</h4>
                        <ul style='margin-bottom: 0; padding-left: 20px;'>
                            <li>You can book another available slot through our website</li>
                            <li>If you made any payment, it will be refunded within 3-5 business days</li>
                            <li>Contact us if you need assistance with rebooking</li>
                            <li>We apologize for any inconvenience caused</li>
                        </ul>
                    </div>
                    
                    <p>We sincerely apologize for the inconvenience. We value your understanding and look forward to serving you better in the future.</p>
                    
                    <p>Best Regards,<br>
                    <strong>The G-Arena Team</strong></p>
                </div>
                
                <div class='footer'>
                    <p>G-Arena Gaming Center | <a href='mailto:support@g-arena.com'>support@g-arena.com</a></p>
                    <p style='font-size: 12px; margin: 5px 0 0 0;'>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Plain text version for confirmation email
     */
    public function getConfirmationEmailText($booking)
    {
        $booking_date = date('F j, Y', strtotime($booking['booking_date']));
        $start_time = date('g:i A', strtotime($booking['start_time']));
        $end_time = date('g:i A', strtotime($booking['end_time']));
        $amount = number_format($booking['total_amount'], 2);

        return "
BOOKING CONFIRMED - G-Arena Gaming Center

Dear {$booking['full_name']},

Great news! Your booking has been CONFIRMED by our admin team. Get ready for an epic gaming experience!

BOOKING DETAILS:
================
Booking Reference: {$booking['booking_reference']}
Status: CONFIRMED
Gaming Station: {$booking['station_name']}
Station Type: {$booking['station_type']}
Date: {$booking_date}
Time: {$start_time} - {$end_time}
Duration: {$booking['total_hours']} hours
Total Amount: LKR {$amount}

IMPORTANT REMINDERS:
===================
- Please arrive 10 minutes before your session
- Bring a valid ID for verification
- Payment is due upon arrival
- Our gaming center opens at 9:00 AM and closes at 8:00 PM

We're excited to have you game with us! If you have any questions or need to make changes, please contact us immediately.

Happy Gaming!
The G-Arena Team

G-Arena Gaming Center | support@g-arena.com
This is an automated message. Please do not reply to this email.
        ";
    }

    /**
     * Plain text version for cancellation email
     */
    public function getCancellationEmailText($booking)
    {
        $booking_date = date('F j, Y', strtotime($booking['booking_date']));
        $start_time = date('g:i A', strtotime($booking['start_time']));
        $end_time = date('g:i A', strtotime($booking['end_time']));
        $amount = number_format($booking['total_amount'], 2);

        return "
BOOKING CANCELLED - G-Arena Gaming Center

Dear {$booking['full_name']},

We regret to inform you that your booking has been CANCELLED by our admin team. This could be due to maintenance requirements, technical issues, or other operational needs.

CANCELLED BOOKING DETAILS:
==========================
Booking Reference: {$booking['booking_reference']}
Status: CANCELLED
Gaming Station: {$booking['station_name']}
Station Type: {$booking['station_type']}
Date: {$booking_date}
Time: {$start_time} - {$end_time}
Duration: {$booking['total_hours']} hours
Amount: LKR {$amount}

WHAT'S NEXT?
============
- You can book another available slot through our website
- If you made any payment, it will be refunded within 3-5 business days
- Contact us if you need assistance with rebooking
- We apologize for any inconvenience caused

We sincerely apologize for the inconvenience. We value your understanding and look forward to serving you better in the future.

Best Regards,
The G-Arena Team

G-Arena Gaming Center | support@g-arena.com
This is an automated message. Please do not reply to this email.
        ";
    }

    /**
     * Send test email (public method for testing)
     */
    public function sendTestEmail($to_email, $to_name, $subject, $html_content, $text_content)
    {
        return $this->sendEmail($to_email, $to_name, $subject, $html_content, $text_content);
    }

    /**
     * Send email using PHPMailer with SMTP
     */
    private function sendEmail($to_email, $to_name, $subject, $html_content, $text_content)
    {
        try {
            error_log("G-Arena Email Debug: Starting email send to {$to_email} with subject: {$subject}");
            
            // Validate email address
            if (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
                error_log("G-Arena Email Debug: Invalid email address: {$to_email}");
                return ['success' => false, 'message' => 'Invalid email address'];
            }

            // Create a new PHPMailer instance
            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtp_username;
            $mail->Password   = $this->smtp_password;
            
            // Set encryption and port based on configuration
            if (defined('SMTP_ENCRYPTION') && SMTP_ENCRYPTION === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS (default)
            }
            
            $mail->Port       = $this->smtp_port;

            error_log("G-Arena Email Debug: SMTP configured - Host: {$this->smtp_host}, Port: {$this->smtp_port}, Username: {$this->smtp_username}");

            // Enable verbose debug output (disable in production)
            if (defined('EMAIL_DEBUG') && EMAIL_DEBUG) {
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                $mail->Debugoutput = function($str, $level) {
                    error_log("G-Arena SMTP Debug: " . $str);
                };
            }

            // Recipients
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to_email, $to_name);

            error_log("G-Arena Email Debug: Recipients set - From: {$this->from_email}, To: {$to_email}");

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html_content;
            $mail->AltBody = $text_content;

            // Additional headers for better deliverability
            $mail->XMailer = 'G-Arena Gaming Center v1.0';
            $mail->addCustomHeader('X-Priority', '3');

            error_log("G-Arena Email Debug: Attempting to send email...");

            // Send the email
            $result = $mail->send();
            
            if ($result) {
                // Log successful email if logging is enabled
                if (defined('EMAIL_LOG_ENABLED') && EMAIL_LOG_ENABLED) {
                    error_log("G-Arena Email Success: Sent to {$to_email} - Subject: {$subject}");
                }
                return ['success' => true, 'message' => 'Email sent successfully'];
            } else {
                error_log("G-Arena Email Failed: Could not send to {$to_email} - Subject: {$subject}");
                return ['success' => false, 'message' => 'Failed to send email'];
            }

        } catch (Exception $e) {
            error_log("G-Arena Email Exception: " . $e->getMessage() . " - To: {$to_email}");
            return ['success' => false, 'message' => 'Email service error: ' . $e->getMessage()];
        }
    }
}
