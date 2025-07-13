# G-Arena - Gaming Arena Booking System

A comprehensive gaming arena management system built with PHP, HTML, JavaScript, and Tailwind CSS.

## Features

### Admin Features
- **Dashboard**: Overview of stations, bookings, and revenue
- **Station Management**: Full CRUD operations for gaming stations
- **Booking Management**: View and manage all customer bookings
- **Status Updates**: Confirm, cancel, or complete bookings
- **Analytics**: Track revenue and booking statistics

### User Features
- **User Registration & Authentication**: Secure account creation and login
- **Station Booking**: Book available gaming stations for specific dates and times
- **Booking History**: View all past and current bookings
- **Real-time Availability**: Check available time slots for stations
- **Booking Summary**: Calculate costs before confirming bookings

### System Features
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Data Tables**: Advanced table functionality with search, sort, and pagination
- **Real-time Updates**: Dynamic content loading via AJAX
- **Secure Authentication**: Password hashing and session management
- **Conflict Prevention**: Prevents double-booking of time slots

## Technology Stack

- **Backend**: PHP (procedural, not class-based)
- **Frontend**: HTML5, JavaScript (ES6+), Tailwind CSS
- **Database**: MySQL/MariaDB
- **Libraries**: 
  - jQuery 3.7.0
  - DataTables 1.13.6
  - Font Awesome 6.0.0
  - Tailwind CSS (CDN)

## Project Structure

```
G-Arena/
├── backend/
│   ├── config/
│   │   ├── config.php          # Application configuration
│   │   └── database.php        # Database connection class
│   ├── includes/
│   │   ├── auth.php           # Authentication functions
│   │   └── functions.php      # Business logic functions
│   └── api/
│       ├── admin_login.php    # Admin authentication API
│       ├── user_login.php     # User authentication API
│       ├── user_register.php  # User registration API
│       ├── stations.php       # Station CRUD API
│       ├── bookings.php       # Booking management API
│       └── update_booking_status.php # Booking status update API
├── frontend/
│   ├── admin/
│   │   ├── dashboard.php      # Admin dashboard
│   │   └── logout.php         # Admin logout
│   ├── user/
│   │   ├── dashboard.php      # User dashboard
│   │   └── logout.php         # User logout
│   ├── css/
│   │   └── custom.css         # Custom styles
│   ├── js/
│   │   ├── admin-dashboard.js # Admin dashboard functionality
│   │   └── user-dashboard.js  # User dashboard functionality
│   ├── admin_login.php        # Admin login page
│   ├── login.php              # User login page
│   └── register.php           # User registration page
├── index.php                  # Landing page
└── database.sql              # Database schema
```

## Installation & Setup

### Prerequisites
- XAMPP/WAMP/LAMP server
- PHP 7.4 or higher
- MySQL/MariaDB 5.7 or higher
- Web browser with JavaScript enabled

### Step 1: Setup Project
1. Extract/clone the project to your XAMPP htdocs directory:
   ```
   C:\xampp\htdocs\G-Arena\
   ```

### Step 2: Database Setup
1. Start XAMPP and ensure Apache and MySQL are running
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Create a new database named `gaming_arena`
4. Import the `database.sql` file or run the SQL commands from it
5. The script will create all necessary tables and insert sample data

### Step 3: Configuration
1. Open `backend/config/config.php`
2. Update database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Update if you have a password
   define('DB_NAME', 'gaming_arena');
   ```

### Step 4: Access the Application
1. Open your web browser
2. Navigate to: `http://localhost/G-Arena/`
3. The landing page should load successfully

## Default Credentials

### Admin Access
- **URL**: `http://localhost/G-Arena/frontend/admin_login.php`
- **Username**: `admin`
- **Password**: `admin123`

### User Access
- Users need to register first at: `http://localhost/G-Arena/frontend/register.php`
- Then login at: `http://localhost/G-Arena/frontend/login.php`

## Usage Guide

### For Administrators

1. **Login**: Use admin credentials to access the admin dashboard
2. **Manage Stations**: 
   - Add new gaming stations with details like name, type, description, and hourly rate
   - Edit existing stations to update information or change status
   - Delete stations that are no longer available
3. **Manage Bookings**:
   - View all customer bookings in a comprehensive table
   - Confirm pending bookings
   - Mark bookings as completed when sessions end
   - Cancel bookings if necessary
4. **Monitor Analytics**: View dashboard statistics for business insights

### For Users

1. **Register**: Create an account with username, email, and personal details
2. **Login**: Access your dashboard with credentials
3. **Book Stations**:
   - Select desired gaming station
   - Choose available date and time slots
   - Review booking summary and costs
   - Confirm booking with optional notes
4. **Manage Bookings**: View booking history and track status updates

## Database Schema

### Tables

- **admins**: Admin user accounts
- **users**: Customer accounts
- **gaming_stations**: Available gaming stations
- **station_availability**: Time slot availability (auto-populated)
- **bookings**: Customer booking records

### Sample Data Included

- 1 Admin user (admin/admin123)
- 5 Sample gaming stations (PC Gaming, Console Gaming, VR Gaming)
- 30 days of availability slots (9 AM - 11 PM daily)

## Customization

### Adding New Station Types
1. Update the station type dropdown in admin dashboard
2. Modify the station creation form validation if needed

### Changing Operating Hours
1. Update the time slot generation in JavaScript files
2. Modify the database population script for different hours

### Styling Customization
1. Edit `frontend/css/custom.css` for additional styling
2. Tailwind classes can be modified throughout HTML files
3. Color scheme can be changed by updating Tailwind color classes

## Security Features

- Password hashing using PHP's password_hash()
- SQL injection prevention with prepared statements
- Session-based authentication
- CSRF protection through form validation
- Input sanitization and validation

## Browser Support

- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+
- Internet Explorer not supported

## Troubleshooting

### Common Issues

1. **Database Connection Error**:
   - Check XAMPP MySQL service is running
   - Verify database credentials in config.php
   - Ensure database `gaming_arena` exists

2. **Page Not Loading**:
   - Check Apache service is running
   - Verify project is in correct htdocs directory
   - Check for PHP syntax errors in browser/server logs

3. **JavaScript Errors**:
   - Ensure jQuery and DataTables are loading (check browser console)
   - Verify internet connection for CDN resources

4. **Login Issues**:
   - Check database contains admin/user records
   - Verify session configuration in PHP
   - Clear browser cookies/session data

## Future Enhancements

- Payment integration
- Email notifications
- Advanced reporting
- Mobile app
- Real-time chat support
- Equipment status monitoring
- Loyalty program
- Multi-location support

## License

This project is for educational/demonstration purposes. Feel free to modify and use as needed.

## Support

For technical support or questions:
1. Check the troubleshooting section
2. Review browser console for JavaScript errors
3. Check server error logs for PHP issues
4. Verify database connectivity and data integrity
