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

    // Insert default seed roles and demo users
    $pdo->exec("INSERT OR IGNORE INTO roles VALUES (1, 'Administrator'), (2, 'Quality Inspector'), (3, 'Quality Manager');");
    $pdo->exec("INSERT OR IGNORE INTO grade_rules (grade, max_percentage) VALUES ('Grade A', 1.00), ('Grade B', 3.00);");

    $hashAdmin = password_hash('Admin@123', PASSWORD_BCRYPT);
    $hashRole = password_hash('Role@123', PASSWORD_BCRYPT);
    $pdo->exec("INSERT OR IGNORE INTO users (id, role_id, name, email, password_hash) VALUES (1, 1, 'System Admin', 'admin@texqura.local', '$hashAdmin');");
    $pdo->exec("INSERT OR IGNORE INTO users (id, role_id, name, email, password_hash) VALUES (2, 2, 'Quality Inspector', 'inspector@texqura.local', '$hashRole');");
    $pdo->exec("INSERT OR IGNORE INTO users (id, role_id, name, email, password_hash) VALUES (3, 3, 'Quality Manager', 'manager@texqura.local', '$hashRole');");

} catch (PDOException $e) {
    die("<div style='color:red; font-family:sans-serif; padding:20px;'>Database error: " . $e->getMessage() . "</div>");
}
?>
