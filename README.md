# BOUESTI Off-Campus Accommodation System

A minimalistic web platform where verified landlords can register properties and students can view authentic listings with original prices directly from landlords.

## Features

### Core Features
- **User Registration & Authentication**: Separate registration for students and landlords
- **Property Management**: Landlords can add, edit, and manage their properties
- **Property Browsing**: Students can search and filter available properties
- **Contact System**: Students can send inquiries to landlords
- **Admin Dashboard**: Comprehensive admin management system
- **Verification System**: Admin approval for landlords and properties

### User Types
1. **Students**: Can browse properties, send inquiries, and contact landlords
2. **Landlords**: Can list properties, manage listings, and receive inquiries
3. **Administrators**: Can verify users, approve properties, and manage the system

## Technology Stack

- **Backend**: Pure PHP (No framework)
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: Tailwind CSS
- **Database**: MySQL
- **Server**: Apache/Nginx (XAMPP recommended for development)

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP (recommended for local development)

### Step 1: Clone/Download the Project
```bash
# If using git
git clone <repository-url>
cd accomodation

# Or download and extract the ZIP file
```

### Step 2: Set Up Database
1. Open your MySQL client (phpMyAdmin, MySQL Workbench, or command line)
2. Create a new database or use the provided SQL script:
   ```sql
   -- Option 1: Run the complete setup script
   source database_setup.sql
   
   -- Option 2: Create database manually
   CREATE DATABASE bouesti_housing;
   USE bouesti_housing;
   ```

3. Import the database structure from `database_setup.sql`

### Step 3: Configure Database Connection
1. Open `includes/config.php`
2. Update the database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'bouesti_housing');
   ```

### Step 4: Set Up File Permissions
1. Create the uploads directory:
   ```bash
   mkdir uploads
   mkdir uploads/properties
   ```

2. Set proper permissions (on Linux/Mac):
   ```bash
   chmod 755 uploads
   chmod 755 uploads/properties
   ```

### Step 5: Configure Web Server
1. **For XAMPP**: Place the project in `htdocs` folder
2. **For Apache**: Configure virtual host or place in web root
3. **For Nginx**: Configure server block

### Step 6: Access the Application
1. Start your web server and MySQL
2. Open your browser and navigate to:
   ```
   http://localhost/accomodation/
   ```

## Default Login Credentials

### Admin Account
- **Email**: admin@bouesti.edu.ng
- **Password**: admin123

### Sample Accounts (for testing)
- **Landlord**: landlord@example.com / admin123
- **Student**: student@example.com / admin123

## Project Structure

```
accomodation/
├── index.php                 # Main landing page
├── includes/
│   ├── config.php           # Database and app configuration
│   └── functions.php        # Core functions and utilities
├── css/
│   └── style.css            # Custom styles
├── js/
│   └── main.js              # JavaScript functionality
├── pages/
│   ├── register.php         # User registration
│   ├── login.php            # User login
│   ├── logout.php           # User logout
│   ├── dashboard/           # User dashboards
│   └── properties/          # Property pages
│       ├── list.php         # Property listing
│       └── details.php      # Property details
├── admin/                   # Admin panel (to be implemented)
├── uploads/                 # File uploads
│   └── properties/          # Property images
├── database_setup.sql       # Database setup script
└── README.md               # This file
```

## Usage Guide

### For Students
1. **Register**: Create a student account
2. **Browse Properties**: Search and filter available properties
3. **View Details**: Click on properties to see full information
4. **Contact Landlords**: Send inquiries or use contact information
5. **Track Inquiries**: View your inquiry history in dashboard

### For Landlords
1. **Register**: Create a landlord account
2. **Wait for Verification**: Admin will verify your account
3. **Add Properties**: List your available properties
4. **Upload Images**: Add photos of your properties
5. **Manage Listings**: Edit or remove properties
6. **Respond to Inquiries**: Reply to student inquiries

### For Administrators
1. **Login**: Use admin credentials
2. **Verify Users**: Approve or reject landlord registrations
3. **Approve Properties**: Review and approve property listings
4. **Monitor System**: View system statistics and logs
5. **Manage Content**: Update system settings

## Security Features

- **Password Hashing**: All passwords are securely hashed
- **SQL Injection Prevention**: Input sanitization and prepared statements
- **Session Management**: Secure session handling
- **Access Control**: Role-based access control
- **File Upload Security**: Image validation and secure storage

## Customization

### Styling
- Modify `css/style.css` for custom styles
- Update Tailwind classes in HTML files
- Customize color scheme and branding

### Configuration
- Edit `includes/config.php` for database and app settings
- Update system settings in database for dynamic configuration

### Features
- Add new user types in database and functions
- Extend property types and amenities
- Implement additional search filters

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `includes/config.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **File Upload Issues**
   - Check upload directory permissions
   - Verify PHP upload settings in `php.ini`
   - Ensure directory exists and is writable

3. **Page Not Found**
   - Check web server configuration
   - Verify file paths and permissions
   - Ensure URL rewriting is configured (if using)

4. **Session Issues**
   - Check PHP session configuration
   - Verify session directory permissions
   - Clear browser cookies

### Error Logs
- Check PHP error logs for detailed error messages
- Enable error reporting in development (already configured)
- Monitor MySQL error logs for database issues

## Development

### Adding New Features
1. Create new PHP files in appropriate directories
2. Add database tables if needed
3. Update functions in `includes/functions.php`
4. Add JavaScript functionality in `js/main.js`
5. Style new components in `css/style.css`

### Code Standards
- Use consistent indentation (4 spaces)
- Follow PHP PSR standards
- Comment complex functions
- Validate all user inputs
- Sanitize database outputs

## Deployment

### Production Checklist
1. **Security**
   - Disable error reporting in `includes/config.php`
   - Set secure session configuration
   - Use HTTPS
   - Configure proper file permissions

2. **Performance**
   - Enable PHP OPcache
   - Configure MySQL query cache
   - Optimize images
   - Enable gzip compression

3. **Backup**
   - Set up regular database backups
   - Backup uploaded files
   - Document configuration changes

## Support

For technical support or questions:
- Check the troubleshooting section above
- Review the code comments
- Contact the development team

## License

This project is developed for BOUESTI (Bamidele Olumilua University of Education, Science and Technology, Ikere-Ekiti).

## Version History

- **v1.0.0**: Initial release with core features
  - User registration and authentication
  - Property management
  - Basic admin functionality
  - Contact system

---

**Note**: This is a minimal build focusing on core functionality. Future enhancements may include booking systems, payment integration, advanced search, and mobile applications.
#   b o u e s t i _ a c c o m o d a t i o n  
 # bouesti_accomodation
# bouesti_accomodation
