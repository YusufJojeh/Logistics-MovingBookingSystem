-- Logistics & Moving Booking System - Sample Database Schema & Demo Data
-- Compatible with procedural MySQLi

-- Drop tables if they exist
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS translations;

-- Users table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('client','provider','admin') NOT NULL DEFAULT 'client',
  phone VARCHAR(30),
  company_name VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('active','inactive') DEFAULT 'active',
  rating DECIMAL(2,1) DEFAULT 0.0
);

-- Demo users
INSERT INTO users (name, email, password, role, company_name, rating) VALUES
('Admin User', 'admin@logistics.com', '$2y$10$demoAdminHash', 'admin', NULL, 0),
('Ahmed Client', 'ahmed@client.com', '$2y$10$demoClientHash', 'client', NULL, 4.8),
('Sara Provider', 'sara@provider.com', '$2y$10$demoProviderHash', 'provider', 'Sara Movers', 4.9),
('LogiPro', 'info@logipro.com', '$2y$10$demoProviderHash2', 'provider', 'LogiPro', 4.7);

-- Services table
CREATE TABLE services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  provider_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  type VARCHAR(50),
  price DECIMAL(10,2) NOT NULL,
  city_from VARCHAR(100),
  city_to VARCHAR(100),
  available_from DATE,
  available_to DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('active','inactive') DEFAULT 'active',
  FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Demo services
INSERT INTO services (provider_id, title, description, type, price, city_from, city_to, available_from, available_to) VALUES
(3, 'Home Moving', 'Professional home moving with packing and insurance.', 'furniture', 250.00, 'Cairo', 'Alexandria', '2024-06-01', '2024-12-31'),
(4, 'Express Parcel Delivery', 'Fast parcel delivery between cities.', 'parcel', 50.00, 'Cairo', 'Giza', '2024-06-01', '2024-12-31'),
(3, 'Office Relocation', 'Complete office relocation service.', 'equipment', 500.00, 'Cairo', 'Mansoura', '2024-06-01', '2024-12-31');

-- Bookings table
CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  service_id INT NOT NULL,
  client_id INT NOT NULL,
  provider_id INT NOT NULL,
  booking_date DATE NOT NULL,
  scheduled_time DATETIME,
  status ENUM('pending','confirmed','in_progress','completed','cancelled') DEFAULT 'pending',
  details TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
  FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Demo bookings
INSERT INTO bookings (service_id, client_id, provider_id, booking_date, scheduled_time, status, details) VALUES
(1, 2, 3, '2024-06-10', '2024-06-10 09:00:00', 'confirmed', 'Moving 2-bedroom apartment.'),
(2, 2, 4, '2024-06-12', '2024-06-12 14:00:00', 'pending', 'Deliver 3 parcels.');

-- Reviews table
CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  reviewer_id INT NOT NULL,
  provider_id INT NOT NULL,
  rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Demo reviews
INSERT INTO reviews (booking_id, reviewer_id, provider_id, rating, comment) VALUES
(1, 2, 3, 5, 'Excellent service, very professional!'),
(2, 2, 4, 4, 'Fast delivery, but a bit expensive.');

-- Translations table (for multilanguage)
CREATE TABLE translations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lang VARCHAR(5) NOT NULL,
  `key` VARCHAR(100) NOT NULL,
  value TEXT NOT NULL,
  UNIQUE KEY (lang, `key`)
);

-- Demo translations
INSERT INTO translations (lang, `key`, value) VALUES
('en', 'welcome', 'Welcome to Logistics & Moving Booking System'),
('ar', 'welcome', 'مرحبًا بكم في منصة حجز خدمات النقل والنقل اللوجستي'),
('en', 'login', 'Login'),
('ar', 'login', 'تسجيل الدخول'),
('en', 'register', 'Register'),
('ar', 'register', 'إنشاء حساب');