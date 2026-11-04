<?php
if (session_status() == PHP_SESSION_NONE) session_start();
$base_url = (strpos($_SERVER['REQUEST_URI'], '/TexQura/') !== false) ? '/TexQura' : '';
if (!isset($_SESSION['user_id'])) { header("Location: {$base_url}/login.php"); exit; }
$current_page = basename($_SERVER['PHP_SELF']);
$folder = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TexQura Workspace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="brand"><i class="bi bi-shield-check text-primary"></i> TexQura</div>
        <div class="menu-wrapper">
            <a href="<?= $base_url ?>/dashboard.php" class="<?= ($current_page == 'dashboard.php' || $folder == 'TexQura') ? 'active' : '' ?>"><i class="bi bi-grid-1x2"></i> Dashboard</a>
            
            <div class="nav-heading">Master Data</div>
            <a href="<?= $base_url ?>/suppliers/index.php" class="<?= $folder == 'suppliers' ? 'active' : '' ?>"><i class="bi bi-truck"></i> Suppliers</a>
            <a href="<?= $base_url ?>/fabrics/index.php" class="<?= $folder == 'fabrics' ? 'active' : '' ?>"><i class="bi bi-layers"></i> Fabrics</a>
            <a href="<?= $base_url ?>/batches/index.php" class="<?= $folder == 'batches' ? 'active' : '' ?>"><i class="bi bi-boxes"></i> Batches</a>
            <a href="<?= $base_url ?>/rolls/index.php" class="<?= $folder == 'rolls' ? 'active' : '' ?>"><i class="bi bi-record-circle"></i> Fabric Rolls</a>

            <div class="nav-heading">Quality Control</div>
            <?php if ($_SESSION['role_id'] == 2 || $_SESSION['role_id'] == 1): ?>
                <a href="<?= $base_url ?>/inspections/create.php" class="<?= $current_page == 'create.php' ? 'active' : '' ?>"><i class="bi bi-search"></i> New Inspection</a>
            <?php endif; ?>
            <a href="<?= $base_url ?>/inspections/history.php" class="<?= $current_page == 'history.php' ? 'active' : '' ?>"><i class="bi bi-clock-history"></i> History</a>
            
            <?php if ($_SESSION['role_id'] == 3 || $_SESSION['role_id'] == 1): ?>
                <a href="<?= $base_url ?>/approvals/index.php" class="<?= $folder == 'approvals' ? 'active' : '' ?>"><i class="bi bi-check2-square"></i> Approvals</a>
            <?php endif; ?>
            
            <div class="nav-heading">Analytics & Admin</div>
            <a href="<?= $base_url ?>/reports/index.php" class="<?= $folder == 'reports' ? 'active' : '' ?>"><i class="bi bi-bar-chart-line"></i> Reports</a>
            <a href="<?= $base_url ?>/complaints/index.php" class="<?= $folder == 'complaints' ? 'active' : '' ?>"><i class="bi bi-exclamation-triangle"></i> Complaints</a>
            
            <?php if ($_SESSION['role_id'] == 1): ?>
                <a href="<?= $base_url ?>/admin/users.php" class="<?= $current_page == 'users.php' ? 'active' : '' ?>"><i class="bi bi-people"></i> Users</a>
                <a href="<?= $base_url ?>/admin/grade_rules.php" class="<?= $current_page == 'grade_rules.php' ? 'active' : '' ?>"><i class="bi bi-sliders"></i> Grade Rules</a>
            <?php endif; ?>
        </div>
        <div class="p-3 mt-auto border-top" style="border-color: rgba(255,255,255,0.05) !important;">
            <a href="<?= $base_url ?>/logout.php" class="btn btn-danger w-100 rounded-pill"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </div>
    <div class="main-content">
        <div class="topbar">
            <div class="d-flex align-items-center">
                <button class="mobile-toggle me-3" onclick="document.getElementById('sidebar').classList.toggle('active')"><i class="bi bi-list"></i></button>
                <h5 class="mb-0 fw-bold text-dark" id="pageTitle">Workspace</h5>
            </div>
            <div>
                <span class="badge bg-white text-dark shadow-sm p-2 rounded-pill border"><i class="bi bi-person-circle text-primary me-1"></i> <?= htmlspecialchars($_SESSION['name']) ?></span>
            </div>
        </div>
        <div class="content-body">
