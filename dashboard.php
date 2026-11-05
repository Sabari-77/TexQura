<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$stats = [
    'suppliers' => $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn(),
    'fabrics' => $pdo->query("SELECT COUNT(*) FROM fabrics")->fetchColumn(),
    'rolls' => $pdo->query("SELECT COUNT(*) FROM fabric_rolls")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM inspections WHERE status = 'Pending Approval'")->fetchColumn(),
    'complaints' => $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Open'")->fetchColumn()
];

$roleNames = [1 => 'System Administrator', 2 => 'Quality Inspector', 3 => 'Quality Manager'];
$userRole = $roleNames[$_SESSION['role_id']] ?? 'Staff Member';

$recentInspections = $pdo->query("SELECT i.*, r.roll_number, f.name as fabric_name, s.name as supplier_name FROM inspections i JOIN fabric_rolls r ON i.roll_id = r.id JOIN batches b ON r.batch_id = b.id JOIN fabrics f ON b.fabric_id = f.id JOIN suppliers s ON b.supplier_id = s.id ORDER BY i.id DESC LIMIT 5")->fetchAll();

$openComplaints = $pdo->query("SELECT c.*, r.roll_number, s.name as supplier FROM complaints c JOIN inspections i ON c.inspection_id = i.id JOIN fabric_rolls r ON i.roll_id = r.id JOIN batches b ON r.batch_id = b.id JOIN suppliers s ON b.supplier_id = s.id WHERE c.status = 'Open' ORDER BY c.id DESC LIMIT 3")->fetchAll();

include 'includes/header.php';
?>
<script>document.title = "Executive Dashboard - TexQura";</script>

<div class="row mb-4">
    <div class="col-12">
        <div class="card p-4 text-white border-0 shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); border-radius: 20px;">
            <div class="position-relative z-1 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25 px-3 py-1 rounded-pill fw-bold mb-2">
                        <i class="bi bi-shield-lock me-1"></i> <?= $userRole ?>
                    </span>
                    <h2 class="fw-bold mb-1" style="letter-spacing: -0.02em;">Welcome, <?= htmlspecialchars($_SESSION['name']) ?> 👋</h2>
                    <p class="mb-0 text-white-50 small">Factory floor operations & automated quality metrics are live.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-white-50 small d-none d-md-inline"><i class="bi bi-calendar3 me-1"></i> <?= date('l, d M Y') ?></span>
                    <a href="reports/index.php" class="btn btn-sm btn-outline-light rounded-pill px-3 ms-2">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i> Audit Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-kpi-card h-100">
            <div class="stat-kpi-header">
                <span class="stat-kpi-title">Raw Suppliers</span>
                <div class="stat-icon-box stat-icon-blue"><i class="bi bi-truck"></i></div>
            </div>
            <div class="stat-kpi-value"><?= $stats['suppliers'] ?></div>
            <div class="stat-kpi-subtitle"><i class="bi bi-check2-circle text-success"></i> Active vendor network</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-kpi-card h-100">
            <div class="stat-kpi-header">
                <span class="stat-kpi-title">Fabric Varieties</span>
                <div class="stat-icon-box stat-icon-indigo"><i class="bi bi-layers-half"></i></div>
            </div>
            <div class="stat-kpi-value"><?= $stats['fabrics'] ?></div>
            <div class="stat-kpi-subtitle"><i class="bi bi-palette text-primary"></i> Configured weave grades</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-kpi-card h-100">
            <div class="stat-kpi-header">
                <span class="stat-kpi-title">Fabric Rolls</span>
                <div class="stat-icon-box stat-icon-emerald"><i class="bi bi-record-circle"></i></div>
            </div>
            <div class="stat-kpi-value"><?= $stats['rolls'] ?></div>
            <div class="stat-kpi-subtitle"><i class="bi bi-boxes text-emerald"></i> Tracked roll inventory</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-kpi-card h-100">
            <div class="stat-kpi-header">
                <span class="stat-kpi-title">Pending Approvals</span>
                <div class="stat-icon-box <?= $stats['pending'] > 0 ? 'stat-icon-amber' : 'stat-icon-blue' ?>"><i class="bi bi-hourglass-split"></i></div>
            </div>
            <div class="stat-kpi-value"><?= $stats['pending'] ?></div>
            <div class="stat-kpi-subtitle">
                <?= $stats['pending'] > 0 ? '<span class="text-warning fw-bold"><i class="bi bi-exclamation-circle"></i> Requires audit</span>' : '<span class="text-success"><i class="bi bi-check-all"></i> All verified</span>' ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card h-100 border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clipboard2-pulse-fill text-primary me-2"></i> Recent Quality Inspections</h6>
                <a href="inspections/history.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">History</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Roll Number</th>
                                <th>Fabric & Supplier</th>
                                <th>Defect %</th>
                                <th>Grade</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentInspections as $insp): ?>
                                <?php
                                    $gradeClass = ($insp['grade'] === 'Grade A') ? 'badge-grade-a' : (($insp['grade'] === 'Grade B') ? 'badge-grade-b' : 'badge-reject');
                                    $statusClass = ($insp['status'] === 'Approved') ? 'badge-grade-a' : 'badge-reject';
                                ?>
                                <tr>
                                    <td class="ps-4"><span class="code-cell"><?= htmlspecialchars($insp['roll_number']) ?></span></td>
                                    <td>
                                        <div class="fw-bold text-dark small"><?= htmlspecialchars($insp['fabric_name']) ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($insp['supplier_name']) ?></div>
                                    </td>
                                    <td class="fw-bold"><?= number_format($insp['defect_percentage'], 2) ?>%</td>
                                    <td><span class="badge-status <?= $gradeClass ?>"><span class="badge-dot"></span> <?= htmlspecialchars($insp['grade']) ?></span></td>
                                    <td><span class="badge-status <?= $statusClass ?>"><span class="badge-dot"></span> <?= htmlspecialchars($insp['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bell-fill text-danger me-2"></i> Quality Alerts</h6>
                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 small"><?= $stats['complaints'] ?> Open</span>
            </div>
            <div class="card-body p-3">
                <?php foreach ($openComplaints as $comp): ?>
                    <div class="p-3 mb-2 rounded-3" style="background: #fff1f2; border: 1px solid rgba(244, 63, 94, 0.2);">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="badge bg-danger text-white" style="font-size: 0.7rem;"><?= htmlspecialchars($comp['complaint_number']) ?></span>
                            <span class="badge bg-white text-danger border border-danger border-opacity-25" style="font-size: 0.68rem;">Roll <?= htmlspecialchars($comp['roll_number']) ?></span>
                        </div>
                        <div class="fw-bold text-dark small mt-1"><?= htmlspecialchars($comp['supplier']) ?></div>
                        <p class="text-secondary small mb-0 mt-1" style="font-size: 0.78rem; line-height: 1.4;">
                            <?= htmlspecialchars(substr($comp['description'], 0, 90)) ?>...
                        </p>
                    </div>
                <?php endforeach; ?>
                <a href="complaints/index.php" class="btn btn-outline-danger w-100 btn-sm rounded-pill mt-2">View All Complaints</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
