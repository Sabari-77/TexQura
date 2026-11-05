<?php
$dbFile = __DIR__ . '/texqura.sqlite';
$isNew = !file_exists($dbFile);

try {
    $pdo = new PDO("sqlite:" . $dbFile, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Ensure all 11 tables exist in the database
    $pdo->exec("CREATE TABLE IF NOT EXISTS roles (id INTEGER PRIMARY KEY, role_name TEXT UNIQUE);");
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, role_id INTEGER, name TEXT, email TEXT UNIQUE, password_hash TEXT, status TEXT DEFAULT 'active', created_at DATETIME DEFAULT CURRENT_TIMESTAMP);");
    $pdo->exec("CREATE TABLE IF NOT EXISTS suppliers (id INTEGER PRIMARY KEY AUTOINCREMENT, supplier_code TEXT UNIQUE, name TEXT, contact_person TEXT, email TEXT, status TEXT DEFAULT 'active', created_at DATETIME DEFAULT CURRENT_TIMESTAMP);");
    $pdo->exec("CREATE TABLE IF NOT EXISTS fabrics (id INTEGER PRIMARY KEY AUTOINCREMENT, fabric_code TEXT UNIQUE, name TEXT, type TEXT, composition TEXT, status TEXT DEFAULT 'active');");
    $pdo->exec("CREATE TABLE IF NOT EXISTS batches (id INTEGER PRIMARY KEY AUTOINCREMENT, batch_number TEXT UNIQUE, supplier_id INTEGER, fabric_id INTEGER, received_date DATE, total_rolls INTEGER, total_length REAL);");
    $pdo->exec("CREATE TABLE IF NOT EXISTS fabric_rolls (id INTEGER PRIMARY KEY AUTOINCREMENT, roll_number TEXT UNIQUE, batch_id INTEGER, roll_length REAL, received_date DATE, inspection_status TEXT DEFAULT 'Awaiting Inspection');");
    $pdo->exec("CREATE TABLE IF NOT EXISTS grade_rules (id INTEGER PRIMARY KEY AUTOINCREMENT, grade TEXT UNIQUE, max_percentage REAL);");
    $pdo->exec("CREATE TABLE IF NOT EXISTS inspections (id INTEGER PRIMARY KEY AUTOINCREMENT, roll_id INTEGER, inspector_id INTEGER, inspection_date DATETIME DEFAULT CURRENT_TIMESTAMP, total_affected_length REAL, defect_percentage REAL, grade TEXT, status TEXT DEFAULT 'Pending Approval', manager_id INTEGER, remarks TEXT);");
    $pdo->exec("CREATE TABLE IF NOT EXISTS inspection_defects (id INTEGER PRIMARY KEY AUTOINCREMENT, inspection_id INTEGER, defect_type TEXT, position_m REAL, affected_length REAL, remarks TEXT);");
    $pdo->exec("CREATE TABLE IF NOT EXISTS certificates (id INTEGER PRIMARY KEY AUTOINCREMENT, certificate_number TEXT UNIQUE, inspection_id INTEGER, generated_at DATETIME DEFAULT CURRENT_TIMESTAMP);");
    $pdo->exec("CREATE TABLE IF NOT EXISTS complaints (id INTEGER PRIMARY KEY AUTOINCREMENT, complaint_number TEXT UNIQUE, inspection_id INTEGER, description TEXT, status TEXT DEFAULT 'Open', complaint_date DATETIME DEFAULT CURRENT_TIMESTAMP);");

    // Default Roles & Grade Rules
    $pdo->exec("INSERT OR IGNORE INTO roles VALUES (1, 'Administrator'), (2, 'Quality Inspector'), (3, 'Quality Manager');");
    $pdo->exec("INSERT OR IGNORE INTO grade_rules (grade, max_percentage) VALUES ('Grade A', 1.00), ('Grade B', 3.00);");

    // Default Users
    $hashAdmin = password_hash('Admin@123', PASSWORD_BCRYPT);
    $hashRole = password_hash('Role@123', PASSWORD_BCRYPT);
    $pdo->exec("INSERT OR IGNORE INTO users (id, role_id, name, email, password_hash) VALUES (1, 1, 'System Admin', 'admin@texqura.local', '$hashAdmin');");
    $pdo->exec("INSERT OR IGNORE INTO users (id, role_id, name, email, password_hash) VALUES (2, 2, 'Quality Inspector', 'inspector@texqura.local', '$hashRole');");
    $pdo->exec("INSERT OR IGNORE INTO users (id, role_id, name, email, password_hash) VALUES (3, 3, 'Quality Manager', 'manager@texqura.local', '$hashRole');");

    // Permanent Suppliers
    $pdo->exec("INSERT OR IGNORE INTO suppliers (id, supplier_code, name, contact_person, email, status) VALUES 
        (1, 'SUP-101', 'Vardhman Textiles Ltd', 'Ramesh Sharma', 'ramesh@vardhman.com', 'active'),
        (2, 'SUP-102', 'Arvind Mills & Weaving', 'Priya Patel', 'priya@arvindmills.com', 'active'),
        (3, 'SUP-103', 'Raymond Luxury Fab', 'Karthik Rao', 'karthik@raymondfab.com', 'active');");

    // Permanent Fabrics
    $pdo->exec("INSERT OR IGNORE INTO fabrics (id, fabric_code, name, type, composition, status) VALUES 
        (1, 'FAB-COT-01', 'Superfine Combed Cotton', 'Woven Plain', '100% Cotton 60s', 'active'),
        (2, 'FAB-DEN-02', 'Indigo Ring Denim', 'Twill 3/1', '98% Cotton 2% Spandex', 'active'),
        (3, 'FAB-LIN-03', 'Organic European Linen', 'Plain Weave', '100% Linen', 'active'),
        (4, 'FAB-PLY-04', 'Microfiber Silk Poly', 'Satin Weave', '85% Polyester 15% Silk', 'active');");

    // Permanent Batches
    $pdo->exec("INSERT OR IGNORE INTO batches (id, batch_number, supplier_id, fabric_id, received_date, total_rolls, total_length) VALUES 
        (1, 'BAT-2026-001', 1, 1, '2026-11-01', 4, 400.00),
        (2, 'BAT-2026-002', 2, 2, '2026-11-02', 2, 250.00),
        (3, 'BAT-2026-003', 3, 3, '2026-11-03', 2, 180.00);");

    // Permanent Fabric Rolls
    $pdo->exec("INSERT OR IGNORE INTO fabric_rolls (id, roll_number, batch_id, roll_length, received_date, inspection_status) VALUES 
        (1, 'ROL-1001', 1, 100.00, '2026-11-01', 'Approved'),
        (2, 'ROL-1002', 1, 100.00, '2026-11-01', 'Rejected'),
        (3, 'ROL-1003', 1, 100.00, '2026-11-01', 'Awaiting Inspection'),
        (4, 'ROL-2001', 2, 125.00, '2026-11-02', 'Awaiting Inspection'),
        (5, 'ROL-2002', 2, 125.00, '2026-11-02', 'Awaiting Inspection'),
        (6, 'ROL-3001', 3, 90.00, '2026-11-03', 'Awaiting Inspection');");

    // Permanent Inspections
    $pdo->exec("INSERT OR IGNORE INTO inspections (id, roll_id, inspector_id, inspection_date, total_affected_length, defect_percentage, grade, status, manager_id, remarks) VALUES 
        (1, 1, 2, '2026-11-02 10:15:00', 0.80, 0.80, 'Grade A', 'Approved', 3, 'High premium quality compliant with export standards.'),
        (2, 2, 2, '2026-11-02 11:30:00', 4.50, 4.50, 'Grade C', 'Rejected', 3, 'Severe oil stains and broken threads exceeding acceptable tolerance.');");

    // Permanent Inspection Defects
    $pdo->exec("INSERT OR IGNORE INTO inspection_defects (id, inspection_id, defect_type, position_m, affected_length, remarks) VALUES 
        (1, 1, 'Stain', 15.50, 0.80, 'Isolated small oil spot cleared in finishing'),
        (2, 2, 'Hole', 25.00, 4.50, 'Severe loom damage and fabric tearing');");

    // Permanent Certificates
    $pdo->exec("INSERT OR IGNORE INTO certificates (id, certificate_number, inspection_id, generated_at) VALUES 
        (1, 'TQC-2026-0001', 1, '2026-11-02 12:00:00');");

    // Permanent Complaints
    $pdo->exec("INSERT OR IGNORE INTO complaints (id, complaint_number, inspection_id, description, status, complaint_date) VALUES 
        (1, 'CMP-20261105-9510', 2, 'Severe loom holes and fabric tearing across 4.5m of cotton fabric. Immediate replacement roll requested.', 'Open', '2026-11-05 02:25:00');");

} catch (PDOException $e) {
    die("<div style='color:red; font-family:sans-serif; padding:20px;'>Database error: " . $e->getMessage() . "</div>");
}
?>
