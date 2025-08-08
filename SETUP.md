# BOUESTI Off-Campus Accommodation System - Setup Guide

This guide will walk you through the complete setup process for the BOUESTI Off-Campus Accommodation System.

## Prerequisites

Before starting the setup, ensure you have:

1. **Web Server**: Apache/Nginx with PHP support
2. **PHP**: Version 7.4 or higher
3. **MySQL**: Version 5.7 or higher (or MariaDB 10.2+)
4. **PHP Extensions**: 
   - mysqli
   - gd (for image processing)
   - fileinfo
   - session

## Installation Steps

### Step 1: File Upload

1. Upload all project files to your web server directory
2. Ensure the following directory structure exists:
   ```
   your-project/
   ├── includes/
   ├── pages/
   ├── admin/
   ├── css/
   ├── js/
   ├── uploads/
   ├── setup.php
   ├── database_setup.sql
   └── index.php
   ```

### Step 2: Run the Setup Script

1. Open your web browser and navigate to:
   ```
   http://your-domain.com/setup.php
   ```

2. The setup script will guide you through 4 steps:

#### Step 1: Database Configuration
- **Database Host**: Usually `localhost` or your database server address
- **Database Name**: Create a new database (e.g., `bouesti_housing`)
- **Database Username**: Your MySQL username (e.g., `root`)
- **Database Password**: Your MySQL password (leave empty if none)

**Note**: The database must exist before running the setup. Create it using phpMyAdmin or MySQL command line:
```sql
CREATE DATABASE bouesti_housing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Step 2: Database Schema Creation
- The script will automatically create all necessary tables
- Sample data will be inserted for testing
- This step cannot be undone, so ensure you have a backup if needed

#### Step 3: Admin Password Setup
- **Admin Email**: Set the admin email address
- **Admin Password**: Create a strong password (minimum 8 characters)
- **Confirm Password**: Re-enter the password

#### Step 4: Final Configuration
- File permissions are checked
- Security files are created
- Setup completion confirmation

### Step 3: Post-Setup Configuration

After successful setup:

1. **Delete the setup file**:
   ```bash
   rm setup.php
   ```

2. **Set proper file permissions**:
   ```bash
   chmod 755 uploads/properties/
   chmod 644 includes/config.php
   ```

3. **Test the system**:
   - Visit the homepage: `http://your-domain.com/`
   - Test admin login: `http://your-domain.com/admin/`
   - Test user registration and login

## Default Login Credentials

After setup, you can use these default accounts for testing:

### Admin Account
- **Email**: admin@bouesti.edu.ng
- **Password**: [The password you set during setup]
- **Access**: Full administrative privileges

### Test Student Account
- **Email**: student@example.com
- **Password**: password123
- **Access**: Student dashboard and property browsing

### Test Landlord Account
- **Email**: landlord@example.com
- **Password**: password123
- **Access**: Landlord dashboard and property management

## Manual Database Setup (Alternative)

If you prefer to set up the database manually:

1. **Create the database**:
   ```sql
   CREATE DATABASE bouesti_housing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Import the schema**:
   ```bash
   mysql -u username -p bouesti_housing < database_setup.sql
   ```

3. **Create config file**:
   Copy `includes/config.example.php` to `includes/config.php` and update the database credentials.

## Troubleshooting

### Common Issues

#### 1. Database Connection Failed
- Verify database credentials
- Ensure MySQL service is running
- Check if the database exists
- Verify user permissions

#### 2. File Permission Errors
- Set upload directory permissions: `chmod 755 uploads/properties/`
- Ensure web server can write to the directory
- Check PHP file upload settings

#### 3. Setup Script Not Accessible
- Verify file exists in the correct location
- Check web server configuration
- Ensure PHP is properly configured

#### 4. SQL Errors During Setup
- Check MySQL version compatibility
- Verify database user privileges
- Ensure proper character set settings

### Error Logs

Check these locations for error information:
- **PHP Error Log**: Usually in `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
- **MySQL Error Log**: Check MySQL configuration for log location
- **Browser Console**: Press F12 to view JavaScript errors

### Security Considerations

1. **Remove setup file**: Always delete `setup.php` after successful setup
2. **Secure config file**: Ensure `includes/config.php` is not publicly accessible
3. **Database security**: Use strong passwords and limit database user privileges
4. **File permissions**: Set appropriate permissions for upload directories
5. **HTTPS**: Use SSL/TLS encryption in production

## Production Deployment

For production deployment, consider:

1. **Environment Configuration**:
   - Set `error_reporting(0)` in production
   - Configure proper logging
   - Use environment variables for sensitive data

2. **Performance Optimization**:
   - Enable PHP OPcache
   - Configure MySQL query cache
   - Use CDN for static assets

3. **Backup Strategy**:
   - Regular database backups
   - File system backups
   - Automated backup scripts

4. **Monitoring**:
   - Set up error monitoring
   - Monitor system resources
   - Configure uptime monitoring

## Support

If you encounter issues during setup:

1. Check the troubleshooting section above
2. Review error logs for specific error messages
3. Ensure all prerequisites are met
4. Verify file permissions and server configuration

For additional support, refer to the main README.md file or contact the development team.

## File Structure After Setup

After successful setup, your project structure should look like:

```
your-project/
├── .htaccess                 # Security configuration
├── index.php                 # Homepage
├── README.md                 # Project documentation
├── SETUP.md                  # This setup guide
├── database_setup.sql        # Database schema
├── includes/
│   ├── config.php           # Database configuration
│   └── functions.php        # Core functions
├── pages/
│   ├── login.php            # Login page
│   ├── register.php         # Registration page
│   ├── logout.php           # Logout script
│   ├── dashboard/           # User dashboards
│   └── properties/          # Property management
├── admin/
│   └── index.php            # Admin dashboard
├── css/
│   └── style.css            # Custom styles
├── js/
│   └── main.js              # JavaScript functions
└── uploads/
    └── properties/          # Property images
```

## Next Steps

After successful setup:

1. **Customize the system**:
   - Update site name and branding
   - Configure email settings
   - Customize styling and themes

2. **User training**:
   - Train administrators on system usage
   - Create user guides for students and landlords
   - Set up support procedures

3. **System maintenance**:
   - Regular security updates
   - Database maintenance
   - Performance monitoring

4. **Feature enhancement**:
   - Add additional features as needed
   - Integrate with other systems
   - Implement advanced reporting

---

**Important**: Always keep backups of your database and files before making any changes to the system.
