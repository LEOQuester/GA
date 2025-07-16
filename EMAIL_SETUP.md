# G-Arena Email Notification System

The G-Arena gaming center now includes automatic email notifications for booking confirmations and cancellations.

## Features

- ✅ **Booking Confirmation Emails** - Professional HTML emails sent when admin confirms a booking
- ✅ **Booking Cancellation Emails** - Informative emails sent when admin cancels a booking  
- ✅ **Beautiful Email Templates** - Gaming-themed responsive email designs
- ✅ **Automatic Triggering** - Emails sent automatically when booking status changes
- ✅ **Error Handling** - Robust error handling and logging
- ✅ **Test Functionality** - Built-in email testing for admins

## How It Works

1. **Admin Action**: When an admin confirms or cancels a booking in the admin panel
2. **Database Update**: The booking status is updated in the database
3. **Email Trigger**: The system automatically sends an email to the user
4. **Email Content**: Rich HTML email with booking details, branding, and instructions

## Email Templates

### Confirmation Email
- Gaming-themed purple gradient header
- Complete booking details
- Important reminders for the user
- Professional styling with G-Arena branding

### Cancellation Email  
- Red-themed header indicating cancellation
- Explanation and next steps
- Refund information
- Rebooking guidance

## Setup Instructions

### 1. Configure Email Settings

Edit `backend/config/email_config.php` and update the following:

```php
// For Gmail (recommended for testing)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password'); // Not your regular password!
```

### 2. Gmail Setup (Recommended)

1. Enable 2-Factor Authentication on your Gmail account
2. Go to [App Passwords](https://support.google.com/accounts/answer/185833)
3. Generate an App Password for "Mail"
4. Use this App Password in the `SMTP_PASSWORD` field

### 3. Test Email Functionality

1. Access: `backend/test/email_test.php` in your browser
2. Make sure you're logged in as an admin
3. Enter your email address
4. Select email type (confirmation or cancellation)
5. Click "Send Test Email"

### 4. Production Setup

For production environments:

- Consider using dedicated email services (SendGrid, Mailgun, AWS SES)
- Store email credentials in environment variables
- Set `EMAIL_DEBUG` to `false` in config
- Monitor email logs for delivery issues

## Email Configuration Options

| Setting | Description | Default |
|---------|-------------|---------|
| `SMTP_HOST` | SMTP server hostname | `smtp.gmail.com` |
| `SMTP_PORT` | SMTP port (587 for TLS) | `587` |
| `SMTP_USERNAME` | Your email address | - |
| `SMTP_PASSWORD` | Email password/app password | - |
| `FROM_EMAIL` | Sender email address | `noreply@g-arena.com` |
| `FROM_NAME` | Sender display name | `G-Arena Gaming Center` |
| `EMAIL_DEBUG` | Enable debug logging | `true` |
| `EMAIL_LOG_ENABLED` | Log email activities | `true` |

## Troubleshooting

### Emails Not Sending

1. **Check server logs**: Look for email-related errors in your PHP error log
2. **Verify SMTP settings**: Ensure all SMTP credentials are correct
3. **Test connectivity**: Use the email test tool to diagnose issues
4. **Check spam folder**: Emails might be delivered to spam initially

### Gmail Authentication Issues

- Ensure 2FA is enabled on your Gmail account
- Use App Password, not your regular Gmail password
- Check that "Less secure app access" is NOT enabled (use App Password instead)

### Email Styling Issues

- Most email clients support basic HTML and CSS
- Inline styles are used for better compatibility
- Templates are tested across major email providers

## Technical Details

### Files Added/Modified

- `backend/includes/email_service.php` - Main email service class
- `backend/config/email_config.php` - Email configuration
- `backend/includes/functions.php` - Modified `updateBookingStatus()` function
- `backend/test/email_test.php` - Email testing tool

### Email Flow

```
Admin Action (Confirm/Cancel) 
    ↓
Update Booking Status in DB
    ↓
Email Service Triggered
    ↓
Get Booking Details with User Info
    ↓
Generate Email Template
    ↓
Send Email via PHP mail() function
    ↓
Log Result
```

### Error Handling

- Email failures don't prevent booking status updates
- All email activities are logged
- Graceful fallback if email service is unavailable
- User-friendly error messages for admins

## Support

If you encounter issues:

1. Check the email test tool first
2. Review server error logs
3. Verify email configuration
4. Test with a simple email address first
5. Consider using a dedicated email service for production

## Security Notes

- Email credentials should be stored securely
- Use environment variables in production
- Regularly rotate email passwords
- Monitor for email delivery failures
- Implement rate limiting for email sending if needed

---

**G-Arena Gaming Center Email System** - Enhancing user experience with automated notifications! 🎮📧
