-- Database: parking_system_db

CREATE DATABASE IF NOT EXISTS parking_system_db;
USE parking_system_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Locations
CREATE TABLE IF NOT EXISTS locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    description TEXT,
    image_url VARCHAR(255)
);

-- Parking Slots
CREATE TABLE IF NOT EXISTS parking_slots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    location_id INT,
    slot_number VARCHAR(20) NOT NULL,
    is_covered BOOLEAN DEFAULT FALSE,
    price_per_hour DECIMAL(10, 2) NOT NULL,
    status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
);

-- Reservations
CREATE TABLE IF NOT EXISTS reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    slot_id INT,
    entry_time DATETIME NOT NULL,
    exit_time DATETIME DEFAULT NULL,
    total_price DECIMAL(10, 2) DEFAULT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (slot_id) REFERENCES parking_slots(id)
);

-- DATA SEEDING (Johor Bahru Context)
-- 1. Locations
INSERT INTO locations (id, name, address, image_url) VALUES 
(1, 'JB City Square', '106-108, Jalan Wong Ah Fook, Bandar Johor Bahru, 80000 Johor Bahru', 'https://images.unsplash.com/photo-1573348729566-914b1ac7c2e6?auto=format&fit=crop&w=600&q=80'),
(2, 'Mid Valley Southkey', '1, Persiaran Southkey 1, Kota Southkey, 80150 Johor Bahru', 'https://images.unsplash.com/photo-1590674899505-12567c192d47?auto=format&fit=crop&w=600&q=80'),
(3, 'Paradigm Mall JB', 'Jalan Bertingkat Skudai, Taman Bukit Mewah, 81200 Johor Bahru', 'https://images.unsplash.com/photo-1470224114660-3f6686c562eb?auto=format&fit=crop&w=600&q=80'),
(4, 'KSL City Mall', '33, Jalan Seladang, Taman Abad, 80250 Johor Bahru', NULL);

-- 2. Slots (Generating multiple slots using a loop approach if in stored proc, but for SQL dump we lists many)
-- JB City Square (Loc 1)
INSERT INTO parking_slots (location_id, slot_number, price_per_hour, is_covered) VALUES 
(1, 'A-01', 5.00, 1), (1, 'A-02', 5.00, 1), (1, 'A-03', 5.00, 1), (1, 'A-04', 5.00, 1), (1, 'A-05', 5.00, 1),
(1, 'B-01', 4.00, 0), (1, 'B-02', 4.00, 0), (1, 'B-03', 4.00, 0), (1, 'B-04', 4.00, 0), (1, 'B-05', 4.00, 0);

-- Mid Valley Southkey (Loc 2)
INSERT INTO parking_slots (location_id, slot_number, price_per_hour, is_covered) VALUES 
(2, 'MV-01', 6.00, 1), (2, 'MV-02', 6.00, 1), (2, 'MV-03', 6.00, 1), (2, 'MV-04', 6.00, 1), (2, 'MV-05', 6.00, 1),
(2, 'MV-06', 6.00, 1), (2, 'MV-07', 6.00, 1), (2, 'MV-08', 6.00, 1), (2, 'MV-09', 6.00, 1), (2, 'MV-10', 6.00, 1);

-- Paradigm Mall (Loc 3)
INSERT INTO parking_slots (location_id, slot_number, price_per_hour, is_covered) VALUES 
(3, 'P-01', 3.00, 1), (3, 'P-02', 3.00, 1), (3, 'P-03', 3.00, 1), (3, 'P-04', 3.00, 1), (3, 'P-05', 3.00, 1);

-- KSL City (Loc 4)
INSERT INTO parking_slots (location_id, slot_number, price_per_hour, is_covered) VALUES 
(4, 'K-01', 2.00, 0), (4, 'K-02', 2.00, 0), (4, 'K-03', 2.00, 0), (4, 'K-04', 2.00, 0), (4, 'K-05', 2.00, 0);

-- Default Admin
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@parking.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
