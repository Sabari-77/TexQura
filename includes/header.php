<?php
if (session_status() == PHP_SESSION_NONE) session_start();
$base_url = (strpos($_SERVER['REQUEST_URI'], '/TexQura/') !== false) ? '/TexQura' : '';
if (!isset($_SESSION['user_id'])) { header("Location: {$base_url}/login.php"); exit; }
$current_page = basename($_SERVER['PHP_SELF']);
$folder = basename(dirname($_SERVER['PHP_SELF']));

$roleNames = [1 => 'System Admin', 2 => 'Quality Inspector', 3 => 'Quality Manager'];
$currentRoleName = $roleNames[$_SESSION['role_id']] ?? 'Staff';
$userInitials = strtoupper(substr($_SESSION['name'] ?? 'U', 0, 2));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TexQura - Enterprise Textile QC System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
</head>
<body>
    <!-- Mobile Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- Main Navigation Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <span class="brand-icon-wrap"><i class="bi bi-grid-3x3"></i></span>
            <span class="brand-text-wrap">
                <span>TexQura</span>
                <span class="brand-subtitle">Fabric QC Platform</span>
            </span>
            <span class="brand-badge">ISO QC</span>
        </div>

        <div class="menu-wrapper">
            <div class="nav-heading">Overview</div>
            <a href="<?= $base_url ?>/dashboard.php" class="<?= ($current_page == 'dashboard.php' || $folder == 'TexQura') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
            </a>
            
            <div class="nav-heading">Operational Data</div>
            <a href="<?= $base_url ?>/suppliers/index.php" class="<?= $folder == 'suppliers' ? 'active' : '' ?>">
                <i class="bi bi-truck"></i> <span>Suppliers</span>
            </a>
            <a href="<?= $base_url ?>/fabrics/index.php" class="<?= $folder == 'fabrics' ? 'active' : '' ?>">
                <i class="bi bi-layers-half"></i> <span>Fabric Varieties</span>
            </a>
            <a href="<?= $base_url ?>/batches/index.php" class="<?= $folder == 'batches' ? 'active' : '' ?>">
                <i class="bi bi-boxes"></i> <span>Delivery Batches</span>
            </a>
            <a href="<?= $base_url ?>/rolls/index.php" class="<?= $folder == 'rolls' ? 'active' : '' ?>">
                <i class="bi bi-record-circle"></i> <span>Fabric Rolls Inventory</span>
            </a>

            <div class="nav-heading">Quality Control & Audit</div>
            <?php if ($_SESSION['role_id'] == 2 || $_SESSION['role_id'] == 1): ?>
                <a href="<?= $base_url ?>/inspections/create.php" class="<?= $current_page == 'create.php' ? 'active' : '' ?>">
                    <i class="bi bi-search"></i> <span>New Inspection</span>
                </a>
            <?php endif; ?>
            <a href="<?= $base_url ?>/inspections/history.php" class="<?= $current_page == 'history.php' ? 'active' : '' ?>">
                <i class="bi bi-clock-history"></i> <span>Inspection History</span>
            </a>
            
            <?php if ($_SESSION['role_id'] == 3 || $_SESSION['role_id'] == 1): ?>
                <a href="<?= $base_url ?>/approvals/index.php" class="<?= $folder == 'approvals' ? 'active' : '' ?>">
                    <i class="bi bi-check2-square"></i> <span>Manager Approvals</span>
                </a>
            <?php endif; ?>
            
            <div class="nav-heading">Intelligence & Compliance</div>
            <a href="<?= $base_url ?>/reports/index.php" class="<?= $folder == 'reports' ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-line"></i> <span>Analytics & Reports</span>
            </a>
            <a href="<?= $base_url ?>/complaints/index.php" class="<?= $folder == 'complaints' ? 'active' : '' ?>">
                <i class="bi bi-exclamation-octagon"></i> <span>Supplier Complaints</span>
            </a>
            
            <?php if ($_SESSION['role_id'] == 1): ?>
                <div class="nav-heading">System Administration</div>
                <a href="<?= $base_url ?>/admin/users.php" class="<?= $current_page == 'users.php' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> <span>System Users & RBAC</span>
                </a>
                <a href="<?= $base_url ?>/admin/grade_rules.php" class="<?= $current_page == 'grade_rules.php' ? 'active' : '' ?>">
                    <i class="bi bi-sliders"></i> <span>Defect Grade Rules</span>
                </a>
            <?php endif; ?>
        </div>

        <div class="sidebar-footer">
            <a href="<?= $base_url ?>/logout.php" class="btn-sidebar-logout">
                <i class="bi bi-box-arrow-right"></i> <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Main Workspace Container -->
    <div class="main-content">
        <!-- Top Workspace Bar (Consistent Across Phone & Desktop) -->
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="mobile-toggle" onclick="toggleSidebar()" aria-label="Toggle Navigation">
                    <i class="bi bi-list"></i>
                </button>
                <div class="topbar-title-wrap">
                    <h5 id="pageTitle">Workspace</h5>
                </div>
                <span class="topbar-system-status d-none d-sm-inline-flex">
                    <span class="status-dot-pulse"></span> Live QC System
                </span>
            </div>

            <div class="topbar-right">
                <div class="user-profile-badge">
                    <div class="user-avatar-circle"><?= $userInitials ?></div>
                    <div class="user-info-text d-none d-md-block">
                        <div class="user-name"><?= htmlspecialchars($_SESSION['name']) ?></div>
                        <span class="user-role"><?= $currentRoleName ?></span>
                    </div>
                </div>

                <!-- Always-Visible Topbar Logout Button (Phone, Tablet & Desktop) -->
                <a href="<?= $base_url ?>/logout.php" class="btn-topbar-logout" title="Sign Out of Terminal">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="d-none d-sm-inline">Logout</span>
                </a>
            </div>
        </header>

        <!-- Main Body Content Area -->
        <main class="content-body">
