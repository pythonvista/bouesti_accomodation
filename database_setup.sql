-- BOUESTI Off-Campus Accommodation System Database Setup
-- Run this script to create the database and all required tables

-- Create database
CREATE DATABASE IF NOT EXISTS bouesti_housing;
USE bouesti_housing;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('student', 'landlord', 'admin') NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    student_id VARCHAR(50) NULL,
    business_name VARCHAR(255) NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Properties table
CREATE TABLE properties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    landlord_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    address TEXT NOT NULL,
    rent_amount DECIMAL(10,2) NOT NULL,
    property_type ENUM('single_room', 'shared_room', 'apartment') NOT NULL,
    amenities TEXT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    is_approved BOOLEAN DEFAULT FALSE,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Property images table
CREATE TABLE property_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Inquiries table
CREATE TABLE inquiries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    property_id INT NOT NULL,
    landlord_id INT NOT NULL,
    message TEXT NOT NULL,
    admin_response TEXT NULL,
    status ENUM('pending', 'responded', 'closed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Admin logs table
CREATE TABLE admin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    target_type ENUM('user', 'property', 'system') NOT NULL,
    target_id INT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- System settings table
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NULL,
    description TEXT NULL,
    updated_by INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_user_type ON users(user_type);
CREATE INDEX idx_users_is_verified ON users(is_verified);
CREATE INDEX idx_properties_landlord_id ON properties(landlord_id);
CREATE INDEX idx_properties_is_approved ON properties(is_approved);
CREATE INDEX idx_properties_is_available ON properties(is_available);
CREATE INDEX idx_property_images_property_id ON property_images(property_id);
CREATE INDEX idx_inquiries_student_id ON inquiries(student_id);
CREATE INDEX idx_inquiries_landlord_id ON inquiries(landlord_id);
CREATE INDEX idx_inquiries_property_id ON inquiries(property_id);
CREATE INDEX idx_admin_logs_admin_id ON admin_logs(admin_id);
CREATE INDEX idx_admin_logs_created_at ON admin_logs(created_at);

-- Insert default admin user
-- Password: admin123 (hashed with password_hash)
INSERT INTO users (email, password, user_type, first_name, last_name, phone, is_verified, is_active) 
VALUES ('admin@bouesti.edu.ng', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System', 'Administrator', '08000000000', 1, 1);

-- Insert some sample system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('site_name', 'BOUESTI Off-Campus Accommodation', 'Website name'),
('site_description', 'Find your perfect off-campus accommodation near BOUESTI', 'Website description'),
('contact_email', 'housing@bouesti.edu.ng', 'Contact email address'),
('contact_phone', '08000000000', 'Contact phone number'),
('max_properties_per_landlord', '10', 'Maximum number of properties a landlord can list'),
('property_approval_required', '1', 'Whether property approval is required (1=yes, 0=no)');

-- Insert sample data for testing (optional)
-- Sample landlord
INSERT INTO users (email, password, user_type, first_name, last_name, phone, business_name, is_verified, is_active) 
VALUES ('landlord@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'landlord', 'John', 'Doe', '08012345678', 'Doe Properties', 1, 1);

-- Sample student
INSERT INTO users (email, password, user_type, first_name, last_name, phone, student_id, is_verified, is_active) 
VALUES ('student@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Jane', 'Smith', '08087654321', 'BOUESTI/2024/001', 1, 1);

-- Sample property
INSERT INTO properties (landlord_id, title, description, address, rent_amount, property_type, amenities, is_approved, is_available) 
VALUES (2, 'Cozy Single Room Near Campus', 'A comfortable single room with basic amenities, perfect for students. Located just 5 minutes walk from BOUESTI campus.', '123 Campus Road, Ikere-Ekiti', 50000.00, 'single_room', 'Electricity, Water, Security, Kitchen', 1, 1);

-- Sample property image
INSERT INTO property_images (property_id, image_path, is_primary) 
VALUES (1, 'uploads/properties/sample_room.jpg', 1);

-- Sample inquiry
INSERT INTO inquiries (student_id, property_id, landlord_id, message, status) 
VALUES (3, 1, 2, 'Hi, I am interested in viewing this property. Is it still available?', 'pending');

-- Display success message
SELECT 'Database setup completed successfully!' as message;
SELECT 'Default admin credentials:' as info;
SELECT 'Email: admin@bouesti.edu.ng' as email;
SELECT 'Password: admin123' as password;
