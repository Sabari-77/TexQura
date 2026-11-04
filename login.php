<?php
session_start();
require_once 'config/database.php';
if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("SELECT id, role_id, name, password_hash, status FROM users WHERE email = ?");
    $stmt->execute([trim($_POST['email'])]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'], $user['password_hash'])) {
        if ($user['status'] == 'inactive') {
            $error = "Account deactivated. Contact your Administrator.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['name'] = $user['name'];
            header("Location: dashboard.php"); 
            exit;
        }
    } else { $error = "Invalid email or password."; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TexQura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { 
            margin: 0; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background: linear-gradient(-45deg, #ff7eb3, #ff758c, #8b5cf6, #3b82f6);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            font-family: 'Segoe UI', system-ui, sans-serif; 
            padding: 20px;
        }
        @keyframes gradientBG { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .login-wrapper { 
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(20px); 
            border-radius: 24px; 
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); 
            width: 100%; 
            max-width: 1000px; 
            overflow: hidden; 
        }
        .login-sidebar { 
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); 
            color: white; 
            padding: 50px; 
            position: relative;
        }
        .login-sidebar::after {
            content: ''; position: absolute; width: 300px; height: 300px; 
            background: rgba(255,255,255,0.03); border-radius: 50%; 
            top: -50px; right: -100px; pointer-events: none;
        }
        .login-form { padding: 50px; }
        .form-control { border-radius: 10px; padding: 12px 15px; border: 1px solid rgba(0,0,0,0.1); }
        .form-control:focus { box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15); border-color: #8b5cf6; }
        .btn-custom { 
            background: linear-gradient(to right, #8b5cf6, #f43f5e); 
            border: none; color: white; border-radius: 10px; 
            font-weight: 600; padding: 14px; transition: all 0.3s ease; 
        }
        .btn-custom:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3); color: white; }
    </style>
</head>
<body>
    <div class="login-wrapper row g-0">
        <div class="col-md-5 login-sidebar d-none d-md-flex flex-column justify-content-center">
            <h2 class="fw-bold" style="color: #34d399;"><i class="bi bi-shield-check"></i> TexQura.</h2>
            <p class="text-white-75 mt-3 mb-4" style="font-size: 1.1rem; line-height: 1.6;">Enterprise Textile Quality Control & Defect Management System.</p>
            
            <div class="mt-2 pt-4 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
                <span class="badge bg-white text-dark mb-4 px-3 py-2 rounded-pill shadow-sm">Available Demo Roles</span>
                
                <div class="mb-3">
                    <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Administrator (Full Access)</small>
                    <div class="fw-bold text-white fs-6">admin@texqura.local</div>
                    <div class="text-white-75 small"><i class="bi bi-key-fill text-warning me-1"></i> Admin@123</div>
                </div>
                
                <div class="mb-3">
                    <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Quality Inspector (Maker)</small>
                    <div class="fw-bold text-white fs-6">inspector@texqura.local</div>
                    <div class="text-white-75 small"><i class="bi bi-key-fill text-warning me-1"></i> Role@123</div>
                </div>
                
                <div>
                    <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Quality Manager (Checker)</small>
                    <div class="fw-bold text-white fs-6">manager@texqura.local</div>
                    <div class="text-white-75 small"><i class="bi bi-key-fill text-warning me-1"></i> Role@123</div>
                </div>
            </div>
        </div>
        <div class="col-md-7 col-12 login-form d-flex flex-column justify-content-center">
            <h2 class="fw-bold mb-1 text-dark">Welcome Back</h2>
            <p class="text-muted mb-4">Log in to securely access your workspace.</p>
            
            <?php if ($error) echo "<div class='alert alert-danger py-2 rounded-3 shadow-sm'><i class='bi bi-exclamation-circle-fill me-2'></i> $error</div>"; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control border-start-0" required placeholder="Enter role email">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold text-muted small">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control border-start-0" required placeholder="Enter password">
                    </div>
                </div>
                <button type="submit" class="btn btn-custom w-100 fs-5 mt-2">Sign In to Dashboard <i class="bi bi-arrow-right-circle ms-2"></i></button>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-muted">Need a new account? <b class="text-dark">Ask your System Administrator</b> to create one for you.</small>
            </div>
        </div>
    </div>
</body>
</html>