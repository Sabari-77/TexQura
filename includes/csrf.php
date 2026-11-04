<?php
// includes/csrf.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Generate and store CSRF token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate submitted CSRF token
function verify_csrf_token($submitted_token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submitted_token)) {
        die("<div style='font-family:sans-serif; text-align:center; padding: 40px;'>
                <h3 style='color:#ef4444;'>Security Constraint Triggered</h3>
                <p>Invalid CSRF token. The form submission could not be verified.</p>
                <button onclick='window.history.back()' style='padding:10px 20px; background:#6366f1; color:white; border:none; border-radius:5px; cursor:pointer;'>Go Back & Retry</button>
             </div>");
    }
}

// Ensure token is generated when file is included
$csrf_token = generate_csrf_token();
?>