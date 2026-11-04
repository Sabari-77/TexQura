<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$stats = [
    'suppliers' => $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn(),
    'fabrics' => $pdo->query("SELECT COUNT(*) FROM fabrics")->fetchColumn(),
    'rolls' => $pdo->query("SELECT COUNT(*) FROM fabric_rolls")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM inspections WHERE status = 'Pending Approval'")->fetchColumn()
];

$roleNames = [1 => 'Administrator', 2 => 'Quality Inspector', 3 => 'Quality Manager'];
$userRole = $roleNames[$_SESSION['role_id']] ?? 'User';

include 'includes/header.php';
?>
<script>document.title = "Dashboard - TexQura";</script>

<div class="row mb-4">
    <div class="col-12">
        <div class="card p-4 text-white shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e1b4b 0%, #8b5cf6 100%); border-radius: 20px;">
            <div class="position-relative z-1">
                <span class="badge bg-white text-dark mb-3 px-3 py-1 rounded-pill fw-bold shadow-sm"><?= $userRole ?></span>
                <h2 class="fw-bold mb-1">Welcome, <?= htmlspecialchars($_SESSION['name']) ?>! <span class="fs-4">👋</span></h2>
                <p class="mb-0 text-white-75">Your dynamic quality control workspace is ready.</p>
            </div>
            <i class="bi bi-layers-half position-absolute text-white-50 opacity-25" style="font-size: 10rem; bottom: -40px; right: 20px; filter: blur(2px);"></i>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-4 h-100" style="border-left-color: var(--primary);">
            <span class="text-muted text-uppercase fw-bold small">Total Suppliers</span>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <h2 class="fw-bold mb-0 text-dark"><?= $stats['suppliers'] ?></h2>
                <div class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary"><i class="bi bi-truck fs-3"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-4 h-100" style="border-left-color: var(--secondary);">
            <span class="text-muted text-uppercase fw-bold small">Fabric Types</span>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <h2 class="fw-bold mb-0 text-dark"><?= $stats['fabrics'] ?></h2>
                <div class="p-3 rounded-circle bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-palette fs-3"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-4 h-100" style="border-left-color: var(--success);">
            <span class="text-muted text-uppercase fw-bold small">Fabric Rolls</span>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <h2 class="fw-bold mb-0 text-dark"><?= $stats['rolls'] ?></h2>
                <div class="p-3 rounded-circle bg-success bg-opacity-10 text-success"><i class="bi bi-record-circle fs-3"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-4 h-100" style="border-left-color: var(--warning);">
            <span class="text-muted text-uppercase fw-bold small">Pending Approvals</span>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <h2 class="fw-bold mb-0 text-dark"><?= $stats['pending'] ?></h2>
                <div class="p-3 rounded-circle bg-warning bg-opacity-10 text-warning"><i class="bi bi-hourglass-split fs-3"></i></div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>