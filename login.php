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
            $error = "Account deactivated. Contact your Quality Administrator.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['name'] = $user['name'];
            header("Location: dashboard.php"); 
            exit;
        }
    } else { $error = "Invalid work email or password. Please check your credentials."; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Sign In - TexQura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --navy-dark: #0b1329;
            --navy-surface: #131e3d;
        }
        * { box-sizing: border-box; }
        body { 
            margin: 0; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background: radial-gradient(circle at 15% 15%, #1e293b 0%, #0b1329 100%);
            background-attachment: fixed;
            font-family: 'Plus Jakarta Sans', sans-serif; 
            padding: 24px 16px;
            color: #0f172a;
            position: relative;
        }
        /* Subtle Textile Weave Grid Background */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
        }
        .login-card { 
            background: #ffffff;
            border-radius: 24px; 
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1); 
            width: 100%; 
            max-width: 1020px; 
            overflow: hidden; 
            position: relative;
            z-index: 10;
        }
        .brand-sidebar { 
            background: linear-gradient(145deg, rgba(12,18,38,0.95) 0%, rgba(26,39,68,0.92) 100%), url('https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&auto=format&fit=crop&q=80') center/cover no-repeat;
            color: white; 
            padding: 48px; 
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .fabric-stat-pills { display: flex; flex-direction: column; gap: 8px; margin-top: 20px; }
        .fabric-stat-pill {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(212,160,23,0.14); color: #d4a017;
            border: 1px solid rgba(212,160,23,0.3); padding: 7px 14px;
            border-radius: 999px; font-size: 0.75rem; font-weight: 700;
            letter-spacing: 0.04em;
        }
        .fabric-stat-pill i { font-size: 0.9rem; }
        .brand-sidebar::after {
            content: '';
            position: absolute;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(2, 132, 199, 0.15) 0%, transparent 70%);
            top: -100px;
            right: -100px;
            border-radius: 50%;
            pointer-events: none;
        }
        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(2, 132, 199, 0.15);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            margin-bottom: 20px;
        }
        .feature-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            font-size: 0.8rem;
            color: #cbd5e1;
            margin-bottom: 8px;
        }
        .feature-pill i {
            color: #38bdf8;
            font-size: 1rem;
        }
        /* Demo Credential Quick Selector */
        .demo-role-btn {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            text-align: left;
            width: 100%;
        }
        .demo-role-btn:hover {
            background: rgba(2, 132, 199, 0.2);
            border-color: #38bdf8;
            transform: translateX(4px);
        }
        .demo-role-btn .role-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .demo-role-btn .role-sub {
            font-size: 0.72rem;
            color: #94a3b8;
            margin-top: 2px;
            font-family: 'JetBrains Mono', monospace;
        }
        .form-section {
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .form-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.92rem;
            border: 1px solid #cbd5e1;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.15);
        }
        .btn-signin {
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            border: none;
            color: white;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 14px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.3);
        }
        .btn-signin:hover {
            background: linear-gradient(135deg, #0369a1 0%, #1d4ed8 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(2, 132, 199, 0.4);
            color: white;
        }
        .security-badge {
            font-size: 0.74rem;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            padding-top: 24px;
            border-top: 1px solid #f1f5f9;
        }
        .security-badge span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        @media (max-width: 768px) {
            .brand-sidebar { padding: 32px 24px; }
            .form-section { padding: 32px 24px; }
        }
    </style>
</head>
<body>
    <div class="login-card row g-0">
        <!-- Left Industrial Credibility Column -->
        <div class="col-lg-5 brand-sidebar">
            <div>
                <div class="brand-badge">
                    <i class="bi bi-patch-check-fill"></i> ISO 9001:2015 QC Ready
                </div>
                <h2 class="fw-bold mb-2 d-flex align-items-center gap-2" style="letter-spacing: -0.02em;">
                    <i class="bi bi-shield-check text-info"></i> TexQura
                </h2>
                <p class="text-white-50 mb-4" style="font-size: 0.9rem; line-height: 1.5;">
                    Industrial Textile Quality Assurance & Defect Management Platform.
                </p>

                <div class="feature-pill"><i class="bi bi-rulers"></i> ASTM 4-Point Defect Measurement</div>
                <div class="feature-pill"><i class="bi bi-cpu"></i> Automated Roll Grade Calculus</div>
                <div class="feature-pill"><i class="bi bi-shield-lock"></i> Maker-Checker Anti-Fraud RBAC</div>

                <div class="fabric-stat-pills">
                    <div class="fabric-stat-pill"><i class="bi bi-patch-check-fill"></i> 98.4% Grade A Pass Rate</div>
                    <div class="fabric-stat-pill"><i class="bi bi-award-fill"></i> ISO 9001:2015 Certified</div>
                    <div class="fabric-stat-pill"><i class="bi bi-rulers"></i> ASTM 4-Point System</div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top border-white border-opacity-10">
                <div class="text-white-50 small text-uppercase fw-bold mb-2" style="font-size: 0.7rem; letter-spacing: 0.08em;">
                    ⚡ 1-Click Demo Login
                </div>

                <button type="button" class="demo-role-btn" onclick="fillCredentials('admin@texqura.local', 'Admin@123')">
                    <div class="role-title">
                        <span>🛡️ System Admin</span>
                        <span class="badge bg-primary bg-opacity-25 text-info">All Modules</span>
                    </div>
                    <div class="role-sub">admin@texqura.local</div>
                </button>

                <button type="button" class="demo-role-btn" onclick="fillCredentials('inspector@texqura.local', 'Role@123')">
                    <div class="role-title">
                        <span>🔍 Quality Inspector</span>
                        <span class="badge bg-success bg-opacity-25 text-success">Maker Role</span>
                    </div>
                    <div class="role-sub">inspector@texqura.local</div>
                </button>

                <button type="button" class="demo-role-btn" onclick="fillCredentials('manager@texqura.local', 'Role@123')">
                    <div class="role-title">
                        <span>👔 Quality Manager</span>
                        <span class="badge bg-warning bg-opacity-25 text-warning">Checker Role</span>
                    </div>
                    <div class="role-sub">manager@texqura.local</div>
                </button>
            </div>
        </div>

        <!-- Right Login Interaction Form -->
        <div class="col-lg-7 form-section">
            <div class="mb-4">
                <h3 class="fw-bold text-dark mb-1" style="letter-spacing: -0.02em;">Sign In to Workspace</h3>
                <p class="text-muted small mb-0">Enter your authorized factory terminal credentials.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 rounded-3 mb-4" role="alert" style="font-size: 0.85rem;">
                    <i class="bi bi-exclamation-triangle-fill fs-6"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="mb-3">
                    <label class="form-label">Work Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" id="emailInput" class="form-control border-start-0" required placeholder="e.g., admin@texqura.local" autocomplete="email">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label d-flex justify-content-between">
                        <span>Password</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="passwordInput" class="form-control border-start-0 border-end-0" required placeholder="Enter password">
                        <button type="button" class="input-group-text bg-white border-start-0 text-muted" onclick="togglePasswordVisibility()" aria-label="Toggle password view">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-signin w-100 d-flex align-items-center justify-content-center gap-2">
                    <span>Access Quality Terminal</span>
                    <i class="bi bi-arrow-right-short fs-5"></i>
                </button>
            </form>

            <div class="security-badge mt-4">
                <span><i class="bi bi-shield-lock-fill text-success"></i> 256-Bit SSL Encrypted</span>
                <span><i class="bi bi-journal-check text-primary"></i> Audit Logged</span>
            </div>
        </div>
    </div>

    <script>
        function fillCredentials(email, password) {
            document.getElementById('emailInput').value = email;
            document.getElementById('passwordInput').value = password;
            const form = document.getElementById('loginForm');
            form.style.transition = 'all 0.3s ease';
            form.style.transform = 'scale(1.01)';
            setTimeout(() => { form.style.transform = 'scale(1)'; }, 300);
        }

        function togglePasswordVisibility() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }
    </script>
</body>
</html>
