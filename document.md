# BOUESTI Off-Campus Accommodation System - Minimal Build Plan

## Project Overview
**Objective**: Create a simple web platform where verified landlords can register properties and students can view authentic listings with original prices directly from landlords.

## Core Features (Minimal)
1. Landlord Registration & Verification
2. Property Listing Management
3. Student Browse & Search
4. Basic Contact System
5. **Comprehensive Admin Management Dashboard**
6. **User Creation & Management**
7. **Property Approval Workflow**
8. **System Analytics & Reporting**

---

## PHASE 8: Advanced Admin Features (Week 8)

### 8.1 Analytics & Reporting
- **User Statistics**: Registration trends, active users, user growth
- **Property Analytics**: Listing trends, approval rates, popular property types
- **Activity Reports**: System usage, most viewed properties, inquiry patterns
- **Export Reports**: Generate CSV/PDF reports for university administration

### 8.2 Bulk Management Operations
- **Bulk User Actions**: Mass approve/reject users, send notifications
- **Bulk Property Actions**: Mass approve/reject properties
- **Data Import/Export**: Import user data from CSV, export system data
- **Database Maintenance**: Clean up old data, optimize database performance

### 8.3 System Configuration
- **Platform Settings**: Set system-wide preferences and limits
- **Email Templates**: Customize notification emails for users
- **Content Management**: Manage static pages, terms of service, privacy policy
- **Maintenance Mode**: Enable/disable system for maintenance

### 8.4 Advanced Security Features
- **Role-Based Access Control**: Different admin permission levels
- **Security Audit Trail**: Detailed logging of all admin activities
- **Failed Login Monitoring**: Track and block suspicious login attempts
- **Data Backup Automation**: Schedule regular database backups

---

## PHASE 1: Foundation Setup (Week 1)

### 1.1 Environment Setup
```bash
# Development Stack
- Frontend: HTML5, CSS3, JavaScript, Bootstrap
- Backend: PHP (Laravel framework)
- Database: MySQL
- Local Environment: XAMPP
```

### 1.2 Enhanced Database Design
```sql
-- Core Tables (Minimal)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    user_type ENUM('student', 'landlord', 'admin'),
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(20),
    student_id VARCHAR(50), -- For students
    business_name VARCHAR(255), -- For landlords
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE properties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    landlord_id INT,
    title VARCHAR(255),
    description TEXT,
    address TEXT,
    rent_amount DECIMAL(10,2),
    property_type ENUM('single_room', 'shared_room', 'apartment'),
    amenities TEXT, -- JSON or comma-separated list
    is_available BOOLEAN DEFAULT TRUE,
    is_approved BOOLEAN DEFAULT FALSE,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (landlord_id) REFERENCES users(id)
);

CREATE TABLE property_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT,
    image_path VARCHAR(255),
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id)
);

-- Admin management tables
CREATE TABLE admin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    action VARCHAR(255),
    target_type ENUM('user', 'property', 'system'),
    target_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

CREATE TABLE inquiries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    property_id INT,
    landlord_id INT,
    message TEXT,
    admin_response TEXT,
    status ENUM('pending', 'responded', 'closed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (property_id) REFERENCES properties(id),
    FOREIGN KEY (landlord_id) REFERENCES users(id)
);

CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE,
    setting_value TEXT,
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);
```

### 1.3 Enhanced File Structure
```
/bouesti-housing/
├── public/
│   ├── css/
│   │   ├── style.css
│   │   └── admin.css
│   ├── js/
│   │   ├── main.js
│   │   └── admin.js
│   ├── images/
│   ├── uploads/
│   │   └── properties/
│   └── index.php
├── includes/
│   ├── config.php
│   ├── functions.php
│   ├── auth.php
│   └── admin_functions.php
├── pages/
│   ├── register.php
│   ├── login.php
│   ├── dashboard/
│   │   ├── student_dashboard.php
│   │   └── landlord_dashboard.php
│   └── properties/
│       ├── list.php
│       ├── details.php
│       └── add.php
├── admin/
│   ├── index.php
│   ├── users/
│   │   ├── list.php
│   │   ├── create.php
│   │   └── edit.php
│   ├── properties/
│   │   ├── list.php
│   │   ├── approve.php
│   │   └── details.php
│   ├── reports/
│   └── settings/
└── api/
    ├── users.php
    └── properties.php
```

---

## PHASE 2: User Authentication System (Week 2)

### 2.1 Registration Forms
- **Student Registration**: Email, Name, Phone, Student ID
- **Landlord Registration**: Email, Name, Phone, Business Details
- **Basic Validation**: Email format, required fields

### 2.2 Login System
```php
// Simple login logic
function authenticateUser($email, $password) {
    // Verify credentials
    // Set session
    // Redirect to appropriate dashboard
}
```

### 2.3 User Dashboards
- **Student Dashboard**: Browse properties, view contact details
- **Landlord Dashboard**: Add/edit properties, view inquiries
- **Admin Dashboard**: Approve landlords and properties

---

## PHASE 3: Property Management (Week 3)

### 3.1 Landlord Property Submission
```html
<!-- Simple Property Form -->
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Property Title" required>
    <textarea name="description" placeholder="Description" required></textarea>
    <input type="text" name="address" placeholder="Full Address" required>
    <input type="number" name="rent" placeholder="Monthly Rent (₦)" required>
    <select name="type" required>
        <option value="single_room">Single Room</option>
        <option value="shared_room">Shared Room</option>
        <option value="apartment">Apartment</option>
    </select>
    <input type="file" name="images[]" multiple accept="image/*" required>
    <button type="submit">Submit Property</button>
</form>
```

### 3.2 Image Upload Handler
```php
function uploadPropertyImages($files, $property_id) {
    $upload_dir = "uploads/properties/";
    foreach($files['tmp_name'] as $key => $tmp_name) {
        $filename = time() . "_" . $files['name'][$key];
        move_uploaded_file($tmp_name, $upload_dir . $filename);
        // Save to database
    }
}
```

---

## PHASE 4: Student Interface (Week 4)

### 4.1 Property Listing Page
```php
// Display approved properties
function getApprovedProperties($search = '') {
    $sql = "SELECT p.*, u.first_name, u.last_name, u.phone 
            FROM properties p 
            JOIN users u ON p.landlord_id = u.id 
            WHERE p.is_approved = 1 AND p.is_available = 1";
    if($search) {
        $sql .= " AND (p.title LIKE '%$search%' OR p.address LIKE '%$search%')";
    }
    return mysqli_query($connection, $sql);
}
```

### 4.2 Property Detail View
- Property photos carousel
- Full description
- Rent amount (original price from landlord)
- Landlord contact information
- Location details

### 4.3 Basic Search/Filter
```javascript
// Simple client-side filtering
function filterProperties() {
    const searchTerm = document.getElementById('search').value.toLowerCase();
    const maxRent = document.getElementById('maxRent').value;
    
    document.querySelectorAll('.property-card').forEach(card => {
        const title = card.querySelector('.property-title').textContent.toLowerCase();
        const rent = parseInt(card.querySelector('.rent-amount').textContent);
        
        if (title.includes(searchTerm) && (!maxRent || rent <= maxRent)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
```

---

## PHASE 5: Comprehensive Admin Management System (Week 5)

### 5.1 Enhanced Database for Admin Features
```sql
-- Add admin activity logs
CREATE TABLE admin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    action VARCHAR(255),
    target_type ENUM('user', 'property', 'system'),
    target_id INT,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Add inquiries table for contact system
CREATE TABLE inquiries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    property_id INT,
    landlord_id INT,
    message TEXT,
    status ENUM('pending', 'responded', 'closed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (property_id) REFERENCES properties(id),
    FOREIGN KEY (landlord_id) REFERENCES users(id)
);
```

### 5.2 User Management Functions
```php
// Create new users (admin function)
function createUser($email, $password, $user_type, $first_name, $last_name, $phone, $additional_data = []) {
    global $connection;
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $email = sanitizeInput($email);
    $first_name = sanitizeInput($first_name);
    $last_name = sanitizeInput($last_name);
    $phone = sanitizeInput($phone);
    
    $sql = "INSERT INTO users (email, password, user_type, first_name, last_name, phone, is_verified) 
            VALUES ('$email', '$hashed_password', '$user_type', '$first_name', '$last_name', '$phone', 1)";
    
    if(mysqli_query($connection, $sql)) {
        $user_id = mysqli_insert_id($connection);
        logAdminAction($_SESSION['admin_id'], 'create_user', 'user', $user_id, "Created $user_type: $first_name $last_name");
        return $user_id;
    }
    return false;
}

// Get all users with filtering
function getAllUsers($user_type = '', $search = '') {
    global $connection;
    
    $sql = "SELECT * FROM users WHERE 1=1";
    if($user_type) {
        $sql .= " AND user_type = '$user_type'";
    }
    if($search) {
        $sql .= " AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%')";
    }
    $sql .= " ORDER BY created_at DESC";
    
    return mysqli_query($connection, $sql);
}

// Update user status
function updateUserStatus($user_id, $is_verified) {
    global $connection;
    
    $sql = "UPDATE users SET is_verified = $is_verified WHERE id = $user_id";
    if(mysqli_query($connection, $sql)) {
        $status = $is_verified ? 'verified' : 'unverified';
        logAdminAction($_SESSION['admin_id'], 'update_user_status', 'user', $user_id, "Set user status to $status");
        return true;
    }
    return false;
}

// Delete user
function deleteUser($user_id) {
    global $connection;
    
    // First delete related data
    mysqli_query($connection, "DELETE FROM properties WHERE landlord_id = $user_id");
    mysqli_query($connection, "DELETE FROM inquiries WHERE student_id = $user_id OR landlord_id = $user_id");
    
    $sql = "DELETE FROM users WHERE id = $user_id";
    if(mysqli_query($connection, $sql)) {
        logAdminAction($_SESSION['admin_id'], 'delete_user', 'user', $user_id, "Deleted user and related data");
        return true;
    }
    return false;
}
```

### 5.3 Property Management Functions
```php
// Get all properties with detailed info
function getAllProperties($status = '', $search = '') {
    global $connection;
    
    $sql = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.is_verified as landlord_verified
            FROM properties p 
            JOIN users u ON p.landlord_id = u.id 
            WHERE 1=1";
    
    if($status === 'approved') {
        $sql .= " AND p.is_approved = 1";
    } elseif($status === 'pending') {
        $sql .= " AND p.is_approved = 0";
    }
    
    if($search) {
        $sql .= " AND (p.title LIKE '%$search%' OR p.address LIKE '%$search%' OR u.first_name LIKE '%$search%')";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    return mysqli_query($connection, $sql);
}

// Approve/Reject property
function updatePropertyStatus($property_id, $is_approved) {
    global $connection;
    
    $sql = "UPDATE properties SET is_approved = $is_approved WHERE id = $property_id";
    if(mysqli_query($connection, $sql)) {
        $status = $is_approved ? 'approved' : 'rejected';
        logAdminAction($_SESSION['admin_id'], 'update_property_status', 'property', $property_id, "Property $status");
        return true;
    }
    return false;
}

// Delete property
function deleteProperty($property_id) {
    global $connection;
    
    // Delete property images first
    $images = mysqli_query($connection, "SELECT image_path FROM property_images WHERE property_id = $property_id");
    while($image = mysqli_fetch_assoc($images)) {
        if(file_exists($image['image_path'])) {
            unlink($image['image_path']);
        }
    }
    
    mysqli_query($connection, "DELETE FROM property_images WHERE property_id = $property_id");
    mysqli_query($connection, "DELETE FROM inquiries WHERE property_id = $property_id");
    
    $sql = "DELETE FROM properties WHERE id = $property_id";
    if(mysqli_query($connection, $sql)) {
        logAdminAction($_SESSION['admin_id'], 'delete_property', 'property', $property_id, "Deleted property and related data");
        return

---

## PHASE 6: Contact & Communication (Week 6)

### 6.1 Simple Contact System
```php
// Store student inquiries
function logInquiry($student_id, $property_id, $message) {
    $sql = "INSERT INTO inquiries (student_id, property_id, message, created_at) 
            VALUES ($student_id, $property_id, '$message', NOW())";
    return mysqli_query($connection, $sql);
}
```

### 6.2 Contact Information Display
- Show verified landlord phone number
- Show verified landlord email
- Simple contact form for inquiries

---

## PHASE 7: Testing & Deployment (Week 7)

### 7.1 Testing Checklist
- [ ] Student can register and login
- [ ] Landlord can register and add properties
- [ ] Admin can verify landlords and approve properties
- [ ] Students can view approved properties only
- [ ] Contact information is displayed correctly
- [ ] Basic security (SQL injection prevention, password hashing)

### 7.2 Security Essentials
```php
// Basic security measures
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
```

### 7.3 Deployment Steps
1. Set up web hosting
2. Create production database
3. Upload files via FTP
4. Configure database connection
5. Test all functionality
6. Create admin account

---

## Key Success Metrics (Minimal)

1. **Landlord Registration**: Landlords can easily register and submit properties
2. **Verification Process**: Admin can approve legitimate landlords and properties
3. **Student Access**: Students can view only verified, approved properties
4. **Authentic Pricing**: Students see original prices directly from landlords
5. **Basic Contact**: Students can contact verified landlords directly

---

## Future Enhancements (Post-Minimal)

- Booking system with payments
- Rating and review system
- Mobile app
- Advanced search filters
- Email notifications
- Property availability calendar
- Virtual property tours

---

## Development Tips for AI Implementation

1. **Start Simple**: Build one feature completely before moving to the next
2. **Test Frequently**: Test each component as you build it
3. **Security First**: Always sanitize inputs and validate data
4. **User Experience**: Keep interfaces simple and intuitive
5. **Documentation**: Comment code clearly for future maintenance

This minimal build achieves the core objective: **verified landlords can list authentic properties with original prices, and students can trust they're dealing with legitimate property owners registered through BOUESTI.**