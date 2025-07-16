<?php
/**
 * Email Configuration for G-Arena
 * 
 * Configure your email settings here
 * For production use, consider using environment variables
 */

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com'); // Your SMTP server
define('SMTP_PORT', 587); // SMTP port (587 for TLS, 465 for SSL)
define('SMTP_USERNAME', 'mrrilyaas@gmail.com'); // Your email address
define('SMTP_PASSWORD', 'nxoz ylyd cljn nciw'); // Replace with NEW App Password (16 chars, no spaces)
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'

// Alternative Gmail settings (if above doesn't work)
// define('SMTP_PORT', 465); // Try this if 587 doesn't work
// define('SMTP_ENCRYPTION', 'ssl'); // Try this if tls doesn't work

// Email From Settings
define('FROM_EMAIL', 'mrrilyaas@gmail.com');
define('FROM_NAME', 'D-Gaming Arena');

// Support Email
define('SUPPORT_EMAIL', 'support@g-arena.com');

// Email Templates Settings
define('ENABLE_HTML_EMAILS', true);
// define('EMAIL_LOGO_URL', 'https://your-domain.com/logo/logo.png'); // Disabled - no images in emails

// Development Settings
define('EMAIL_DEBUG', true); // Set to false in production
define('EMAIL_LOG_ENABLED', true); // Log email activities

/**
 * SETUP INSTRUCTIONS:
 * ===================
 * 
 * 1. For Gmail (Recommended for testing):
 *    - Enable 2-Factor Authentication on your Gmail account
 *    - Generate an App Password: https://support.google.com/accounts/answer/185833
 *    - Use the App Password in SMTP_PASSWORD (not your regular password)
 *    - Set SMTP_HOST to 'smtp.gmail.com'
 *    - Set SMTP_PORT to 587
 * 
 * 2. For other email providers:
 *    - Check your provider's SMTP settings
 *    - Update the SMTP_HOST, SMTP_PORT accordingly
 *    - Some providers may require different authentication methods
 * 
 * 3. For production:
 *    - Consider using a dedicated email service like SendGrid, Mailgun, or AWS SES
 *    - Store sensitive credentials in environment variables
 *    - Set EMAIL_DEBUG to false
 * 
 * 4. Testing:
 *    - You can test email functionality by updating a booking status in admin panel
 *    - Check server error logs if emails are not being sent
 *    - Ensure your server has mail() function enabled or use PHPMailer for SMTP
 */
