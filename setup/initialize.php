<?php
$host = '127.0.0.1';
$root_user = 'root';
$root_pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $root_user, $root_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // --- THIS LINE CLEARS THE OLD DATABASE TO PREVENT THE 'TABLE EXISTS' ERROR ---
    $pdo->exec("DROP DATABASE IF EXISTS texqura"); 
    
    // Create the fresh database
    $pdo->exec("CREATE DATABASE texqura CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE texqura");
    
    // Import the schema
    $schema = file_get_contents('../database/schema.sql');
    $schema = preg_replace('/CREATE DATABASE.*?USE texqura;/s', '', $schema);
    $pdo->exec($schema);
    
    // Insert Roles & Default Users
    $rolesCheck = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
    if ($rolesCheck == 0) {
        $pdo->exec("INSERT INTO roles (id, role_name) VALUES (1, 'Administrator'), (2, 'Quality Inspector'), (3, 'Quality Manager')");
        $pdo->exec("INSERT INTO grade_rules (grade, max_percentage) VALUES ('Grade A', 1.00), ('Grade B', 3.00)");
        
        $hashAdmin = password_hash('Admin@123', PASSWORD_BCRYPT);
        $pdo->exec("INSERT INTO users (role_id, name, email, password_hash) VALUES (1, 'System Admin', 'admin@texqura.local', '$hashAdmin')");
        
        $hashInsp = password_hash('Role@123', PASSWORD_BCRYPT);
        $pdo->exec("INSERT INTO users (role_id, name, email, password_hash) VALUES (2, 'Quality Inspector', 'inspector@texqura.local', '$hashInsp')");
        $pdo->exec("INSERT INTO users (role_id, name, email, password_hash) VALUES (3, 'Quality Manager', 'manager@texqura.local', '$hashInsp')");
        
        echo "<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>";
        echo "<h2 style='color:#10b981;'>TexQura System Initialized Successfully!</h2>";
        echo "<a href='../login.php' style='padding:10px 20px; background:#6366f1; color:white; text-decoration:none; border-radius:5px;'>Go to Login</a>";
        echo "</div>";
    } else {
        header("Location: ../login.php");
    }
} catch (PDOException $e) {
    die("Setup failed: " . $e->getMessage());
}
?>