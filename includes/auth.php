<?php
// includes/auth.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 30-minute inactivity timeout for enterprise security
$timeout_duration = 1800; 

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: /TexQura/login.php?timeout=1");
    exit;
}

// Update last activity time stamp
$_SESSION['last_activity'] = time();

// Helper function to lock down specific pages to certain roles
function requireRole($allowed_roles) {
    if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], $allowed_roles)) {
        die("<div style='padding:40px; font-family:sans-serif; text-align:center;'>
                <h2 style='color:#ef4444;'>403 - Access Denied</h2>
                <p>You do not have the required permissions to view this module.</p>
                <a href='/TexQura/dashboard.php' style='padding:10px 20px; background:#6366f1; color:white; text-decoration:none; border-radius:5px;'>Return to Dashboard</a>
             </div>");
    }
}
?>