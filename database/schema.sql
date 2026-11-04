CREATE DATABASE IF NOT EXISTS texqura CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE texqura;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE fabrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fabric_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(100),
    composition VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active'
) ENGINE=InnoDB;

CREATE TABLE batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_number VARCHAR(50) NOT NULL UNIQUE,
    supplier_id INT NOT NULL,
    fabric_id INT NOT NULL,
    received_date DATE NOT NULL,
    total_rolls INT NOT NULL,
    total_length DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (fabric_id) REFERENCES fabrics(id)
) ENGINE=InnoDB;

CREATE TABLE fabric_rolls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_number VARCHAR(50) NOT NULL UNIQUE,
    batch_id INT NOT NULL,
    roll_length DECIMAL(10,2) NOT NULL CHECK (roll_length > 0),
    received_date DATE NOT NULL,
    inspection_status ENUM('Awaiting Inspection', 'Pending Approval', 'Approved', 'Rejected') DEFAULT 'Awaiting Inspection',
    FOREIGN KEY (batch_id) REFERENCES batches(id)
) ENGINE=InnoDB;

CREATE TABLE grade_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grade VARCHAR(10) NOT NULL UNIQUE,
    max_percentage DECIMAL(5,2) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE inspections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_id INT NOT NULL,
    inspector_id INT NOT NULL,
    inspection_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_affected_length DECIMAL(10,2) NOT NULL,
    defect_percentage DECIMAL(5,2) NOT NULL,
    grade VARCHAR(10) NOT NULL,
    status ENUM('Pending Approval', 'Approved', 'Rejected') DEFAULT 'Pending Approval',
    manager_id INT,
    remarks TEXT,
    FOREIGN KEY (roll_id) REFERENCES fabric_rolls(id),
    FOREIGN KEY (inspector_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE inspection_defects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inspection_id INT NOT NULL,
    defect_type VARCHAR(50) NOT NULL,
    position_m DECIMAL(10,2) NOT NULL,
    affected_length DECIMAL(10,2) NOT NULL CHECK (affected_length > 0),
    remarks TEXT,
    FOREIGN KEY (inspection_id) REFERENCES inspections(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_number VARCHAR(50) NOT NULL UNIQUE,
    inspection_id INT NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inspection_id) REFERENCES inspections(id)
) ENGINE=InnoDB;

CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_number VARCHAR(50) NOT NULL UNIQUE,
    inspection_id INT NOT NULL,
    description TEXT NOT NULL,
    status ENUM('Open', 'Resolved') DEFAULT 'Open',
    complaint_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inspection_id) REFERENCES inspections(id)
) ENGINE=InnoDB;