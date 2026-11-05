<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$stats = [
    'suppliers' => $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn(),
    'fabrics'   => $pdo->query("SELECT COUNT(*) FROM fabrics")->fetchColumn(),
    'rolls'     => $pdo->query("SELECT COUNT(*) FROM fabric_rolls")->fetchColumn(),
    'pending'   => $pdo->query("SELECT COUNT(*) FROM inspections WHERE status = 'Pending Approval'")->fetchColumn(),
    'complaints'=> $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Open'")->fetchColumn()
];

$roleNames = [1 => 'System Administrator', 2 => 'Quality Inspector', 3 => 'Quality Manager'];
$userRole  = $roleNames[$_SESSION['role_id']] ?? 'Staff Member';

$recentInspections = $pdo->query("SELECT i.*, r.roll_number, f.name as fabric_name, s.name as supplier_name FROM inspections i JOIN fabric_rolls r ON i.roll_id = r.id JOIN batches b ON r.batch_id = b.id JOIN fabrics f ON b.fabric_id = f.id JOIN suppliers s ON b.supplier_id = s.id ORDER BY i.id DESC LIMIT 5")->fetchAll();

$openComplaints = $pdo->query("SELECT c.*, r.roll_number, s.name as supplier FROM complaints c JOIN inspections i ON c.inspection_id = i.id JOIN fabric_rolls r ON i.roll_id = r.id JOIN batches b ON r.batch_id = b.id JOIN suppliers s ON b.supplier_id = s.id WHERE c.status = 'Open' ORDER BY c.id DESC LIMIT 3")->fetchAll();

$gradeACnt = $pdo->query("SELECT COUNT(*) FROM inspections WHERE grade='Grade A'")->fetchColumn();
$gradeBCnt = $pdo->query("SELECT COUNT(*) FROM inspections WHERE grade='Grade B'")->fetchColumn();
$gradeRCnt = $pdo->query("SELECT COUNT(*) FROM inspections WHERE grade='Reject'")->fetchColumn();

include 'includes/header.php';
?>
<script>document.title = "Textile QC Operations - TexQura";</script>

<!-- Loom & Weave Executive Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card p-4 text-white border-0 shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, #0c1226 0%, #1a2744 60%, #152042 100%); border-radius: 20px;">
            <div class="position-relative z-1 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <span class="badge px-3 py-1 rounded-pill fw-bold mb-2" style="background:rgba(212,160,23,0.18);color:#d4a017;border:1px solid rgba(212,160,23,0.3);">
                        <i class="bi bi-grid-3x3 me-1"></i> <?= $userRole ?> Terminal
                    </span>
                    <h2 class="fw-bold mb-1" style="letter-spacing:-0.02em;">TexQura Textile Operations Center</h2>
                    <p class="mb-0 text-white-50 small">Automated ASTM 4-point defect grading &amp; traceable lot inspection.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-white-50 small d-none d-md-inline"><i class="bi bi-calendar3 me-1"></i> <?= date('l, d M Y') ?></span>
                    <a href="reports/index.php" class="btn btn-sm btn-outline-light rounded-pill px-3 ms-2">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i> Full Quality Audit
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Primary KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-kpi-card h-100">
            <div class="stat-kpi-header">
                <span class="stat-kpi-title">Raw Suppliers</span>
                <div class="stat-icon-box stat-icon-cyan"><i class="bi bi-truck"></i></div>
            </div>
            <div class="stat-kpi-value"><?= $stats['suppliers'] ?></div>
            <div class="stat-kpi-subtitle"><i class="bi bi-check2-circle text-success"></i> Active yarn &amp; fiber mills</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-kpi-card h-100">
            <div class="stat-kpi-header">
                <span class="stat-kpi-title">Fabric Varieties</span>
                <div class="stat-icon-box stat-icon-indigo"><i class="bi bi-layers-half"></i></div>
            </div>
            <div class="stat-kpi-value"><?= $stats['fabrics'] ?></div>
            <div class="stat-kpi-subtitle"><i class="bi bi-palette text-primary"></i> Weave &amp; fiber specs</div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-kpi-card h-100">
            <div class="stat-kpi-header">
                <span class="stat-kpi-title">Fabric Rolls</span>
                <div class="stat-icon-box stat-icon-gold"><i class="bi bi-record-circle"></i></div>
            </div>
            <div class="stat-kpi-value"><?= $stats['rolls'] ?></div>
            <div class="stat-kpi-subtitle">
                <span class="live-meter-counter"><i class="bi bi-activity"></i><?= $stats['rolls'] ?> <span class="meter-unit">rolls tracked</span></span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-kpi-card h-100">
            <div class="stat-kpi-header">
                <span class="stat-kpi-title">Pending Approvals</span>
                <div class="stat-icon-box <?= $stats['pending'] > 0 ? 'stat-icon-amber' : 'stat-icon-cyan' ?>"><i class="bi bi-hourglass-split"></i></div>
            </div>
            <div class="stat-kpi-value"><?= $stats['pending'] ?></div>
            <div class="stat-kpi-subtitle">
                <?= $stats['pending'] > 0 ? '<span class="text-warning fw-bold"><i class="bi bi-exclamation-circle"></i> Awaiting manager review</span>' : '<span class="text-success"><i class="bi bi-check-all"></i> All logs verified</span>' ?>
            </div>
        </div>
    </div>
</div>

<!-- Real Fabric Swatch Cards Section -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-palette-fill text-primary me-2"></i> Active Fabric Varieties</h5>
            <small class="text-muted">High-resolution textile grain &amp; fiber classification</small>
        </div>
        <a href="fabrics/index.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">Manage Varieties</a>
    </div>

    <div class="row g-3">
        <!-- Cotton -->
        <div class="col-lg-3 col-sm-6">
            <div class="fabric-swatch-card h-100">
                <div class="swatch-thumb-wrap">
                    <img src="https://images.unsplash.com/photo-1598033129183-c4f50c736f10?w=600&auto=format&fit=crop&q=80" alt="Combed Cotton Weave">
                    <span class="swatch-badge">FAB-COT-01</span>
                    <span class="swatch-grade-badge">Grade A</span>
                </div>
                <div class="swatch-body">
                    <div class="swatch-name">Combed Cotton 60s</div>
                    <div class="swatch-meta"><span>Woven Plain</span><span class="fw-bold text-primary">100% Cotton</span></div>
                    <div class="swatch-fiber">CTN-60S | 150gsm | 60"W</div>
                </div>
            </div>
        </div>

        <!-- Denim -->
        <div class="col-lg-3 col-sm-6">
            <div class="fabric-swatch-card h-100">
                <div class="swatch-thumb-wrap">
                    <img src="https://images.unsplash.com/photo-1542272604-787c3835535d?w=600&auto=format&fit=crop&q=80" alt="Indigo Denim Twill">
                    <span class="swatch-badge">FAB-DEN-02</span>
                    <span class="swatch-grade-badge">Grade A</span>
                </div>
                <div class="swatch-body">
                    <div class="swatch-name">Indigo Ring Denim</div>
                    <div class="swatch-meta"><span>Twill 3/1 Weave</span><span class="fw-bold text-primary">98% Cot / 2% Spn</span></div>
                    <div class="swatch-fiber">DEN-3/1 | 320gsm | 58"W</div>
                </div>
            </div>
        </div>

        <!-- Linen -->
        <div class="col-lg-3 col-sm-6">
            <div class="fabric-swatch-card h-100">
                <div class="swatch-thumb-wrap">
                    <img src="https://images.unsplash.com/photo-1584589167171-541ce45f1eea?w=600&auto=format&fit=crop&q=80" alt="Organic Linen Weave">
                    <span class="swatch-badge">FAB-LIN-03</span>
                    <span class="swatch-grade-badge">Grade B</span>
                </div>
                <div class="swatch-body">
                    <div class="swatch-name">European Pure Linen</div>
                    <div class="swatch-meta"><span>Natural Plain</span><span class="fw-bold text-primary">100% Linen</span></div>
                    <div class="swatch-fiber">LIN-PL | 185gsm | 56"W</div>
                </div>
            </div>
        </div>

        <!-- Silk Poly -->
        <div class="col-lg-3 col-sm-6">
            <div class="fabric-swatch-card h-100">
                <div class="swatch-thumb-wrap">
                    <img src="https://images.unsplash.com/photo-1528458876861-544fd1761a91?w=600&auto=format&fit=crop&q=80" alt="Microfiber Silk Satin">
                    <span class="swatch-badge">FAB-PLY-04</span>
                    <span class="swatch-grade-badge">Grade A</span>
                </div>
                <div class="swatch-body">
                    <div class="swatch-name">Microfiber Silk Satin</div>
                    <div class="swatch-meta"><span>Lustrous Satin</span><span class="fw-bold text-primary">85% Poly / 15% Silk</span></div>
                    <div class="swatch-fiber">SLK-SAT | 90gsm | 54"W</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interactive ASTM 4-Point Quality Grade Simulator & Roll Defect Strip -->
<div class="roll-defect-strip-container mb-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1 mb-1">Interactive Simulation Tool</span>
            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-sliders text-primary me-2"></i> Live ASTM 4-Point Grade &amp; Defect Simulator</h5>
            <small class="text-muted">Simulate dynamic grade calculation by adjusting total roll length and defect size in real-time.</small>
        </div>
        <div class="mt-2 mt-md-0 d-flex align-items-center gap-2">
            <span class="badge px-2 py-1 rounded-pill" style="background:rgba(212,160,23,0.15);color:#c47b1a;border:1px solid rgba(212,160,23,0.3);font-size:0.72rem;font-weight:700;">ASTM 4-Point System</span>
            <span id="simulatorGradeBadge" class="badge-status badge-grade-a fs-6 px-3 py-2">
                <span class="badge-dot"></span> <span id="simulatorGradeText">Grade A (Export Certified)</span>
            </span>
        </div>
    </div>

    <!-- Dual Range Sliders -->
    <div class="row g-4 align-items-center">
        <div class="col-md-5">
            <label class="form-label d-flex justify-content-between mb-1">
                <span>Roll Total Length</span>
                <span class="fw-bold text-primary" id="simRollLengthLabel">100 meters</span>
            </label>
            <input type="range" class="form-range" id="simRollLength" min="50" max="250" step="5" value="100" oninput="updateSimulator()">
        </div>

        <div class="col-md-5">
            <label class="form-label d-flex justify-content-between mb-1">
                <span>Total Affected Defect Length</span>
                <span class="fw-bold text-danger" id="simDefectLengthLabel">0.8 meters</span>
            </label>
            <input type="range" class="form-range" id="simDefectLength" min="0.1" max="10.0" step="0.1" value="0.8" oninput="updateSimulator()">
        </div>

        <div class="col-md-2 text-center bg-light p-3 rounded-3">
            <div class="text-muted small fw-bold text-uppercase" style="font-size:0.7rem;">Defect Rate</div>
            <div class="fs-4 fw-bold text-dark" id="simDefectPercent">0.80%</div>
        </div>
    </div>

    <!-- Visual Fabric Roll Defect Strip Map -->
    <div class="mt-3">
        <div class="d-flex justify-content-between small text-muted">
            <span>0 m (Roll Start)</span>
            <span class="fw-bold text-secondary">Visual Fabric Roll Defect Map Preview</span>
            <span id="simEndMeterLabel">100 m (Roll End)</span>
        </div>
        <div class="defect-canvas-strip" title="Visual inspection scan of fabric roll">
            <div id="simDefectPin" class="defect-marker-pin defect-pin-sev1" style="left:20%;width:10%;"></div>
        </div>
    </div>
</div>

<!-- Quick Defect Logger (Tap-Based, Inspired by fabricinspectionsoftware.com) -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-cursor-fill text-warning me-2"></i> Quick Defect Classifier</h6>
            <small class="text-muted">Click to simulate defect classification — real inspectors use this on the inspection floor</small>
        </div>
        <span class="badge px-2 py-1 rounded-pill" style="background:rgba(212,160,23,0.15);color:#c47b1a;border:1px solid rgba(212,160,23,0.3);font-size:0.7rem;font-weight:700;"><i class="bi bi-rulers me-1"></i> ASTM 4-Point System</span>
    </div>
    <div class="card-body">
        <div class="defect-btn-grid">
            <button class="defect-btn defect-hole" onclick="logDefect('Hole', this)" title="Log Hole defect">
                <i class="bi bi-circle-slash"></i>
                <span>Hole</span>
                <span class="defect-count-badge" style="display:none;">0</span>
            </button>
            <button class="defect-btn defect-stain" onclick="logDefect('Stain', this)" title="Log Stain defect">
                <i class="bi bi-droplet-fill"></i>
                <span>Stain</span>
                <span class="defect-count-badge" style="display:none;">0</span>
            </button>
            <button class="defect-btn defect-weave" onclick="logDefect('Weave Error', this)" title="Log Weave Error defect">
                <i class="bi bi-grid-3x3"></i>
                <span>Weave Error</span>
                <span class="defect-count-badge" style="display:none;">0</span>
            </button>
            <button class="defect-btn defect-slub" onclick="logDefect('Slub/Nep', this)" title="Log Slub/Nep defect">
                <i class="bi bi-node-plus"></i>
                <span>Slub/Nep</span>
                <span class="defect-count-badge" style="display:none;">0</span>
            </button>
            <button class="defect-btn defect-color" onclick="logDefect('Color Variation', this)" title="Log Color Variation defect">
                <i class="bi bi-palette2"></i>
                <span>Color Variation</span>
                <span class="defect-count-badge" style="display:none;">0</span>
            </button>
        </div>
        <div class="mt-3 d-flex justify-content-between align-items-center">
            <span id="defectLogOutput" class="text-muted small">No defects logged this session.</span>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" onclick="clearDefectLog()">Reset Session</button>
        </div>
    </div>
</div>

<!-- Roll Grade Classification Board -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-award-fill text-warning me-2"></i> Roll Grade Classification Board</h5>
            <small class="text-muted">ISO 4-Point grading system — live counts from inspection database</small>
        </div>
        <a href="inspections/history.php" class="btn btn-sm btn-outline-primary rounded-pill">View All Rolls</a>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="grade-board-card grade-a">
                <div class="grade-letter">A</div>
                <div class="grade-label">Export Certified</div>
                <div class="grade-desc">Defect Rate &le; 1.00%</div>
                <div class="grade-count"><?= $gradeACnt ?> Rolls</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="grade-board-card grade-b">
                <div class="grade-letter">B</div>
                <div class="grade-label">Commercial Quality</div>
                <div class="grade-desc">Defect Rate 1.01% &ndash; 3.00%</div>
                <div class="grade-count"><?= $gradeBCnt ?> Rolls</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="grade-board-card grade-reject">
                <div class="grade-letter">R</div>
                <div class="grade-label">Rejected Lot</div>
                <div class="grade-desc">Defect Rate &gt; 3.00%</div>
                <div class="grade-count"><?= $gradeRCnt ?> Rolls</div>
            </div>
        </div>
    </div>
</div>

<!-- Defect Pareto Analysis (Type Frequency) -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <div>
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-steps text-primary me-2"></i> Defect Type Frequency &mdash; Pareto Analysis</h6>
            <small class="text-muted">Top defect categories by occurrence rate — industry benchmark data</small>
        </div>
        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1 small fw-bold">Demo Data</span>
    </div>
    <div class="card-body">
        <div class="pareto-row">
            <span class="pareto-label">Weave Error</span>
            <div class="pareto-track">
                <div class="pareto-bar" style="--bar-width:42%;background:linear-gradient(90deg,#4338ca,#6366f1);"></div>
            </div>
            <span class="pareto-pct">42%</span>
        </div>
        <div class="pareto-row">
            <span class="pareto-label">Stain</span>
            <div class="pareto-track">
                <div class="pareto-bar" style="--bar-width:28%;background:linear-gradient(90deg,#c47b1a,#f59e0b);"></div>
            </div>
            <span class="pareto-pct">28%</span>
        </div>
        <div class="pareto-row">
            <span class="pareto-label">Slub / Nep</span>
            <div class="pareto-track">
                <div class="pareto-bar" style="--bar-width:14%;background:linear-gradient(90deg,#0d9488,#2dd4bf);"></div>
            </div>
            <span class="pareto-pct">14%</span>
        </div>
        <div class="pareto-row">
            <span class="pareto-label">Color Variation</span>
            <div class="pareto-track">
                <div class="pareto-bar" style="--bar-width:10%;background:linear-gradient(90deg,#7c3aed,#a78bfa);"></div>
            </div>
            <span class="pareto-pct">10%</span>
        </div>
        <div class="pareto-row mb-0">
            <span class="pareto-label">Hole</span>
            <div class="pareto-track">
                <div class="pareto-bar" style="--bar-width:6%;background:linear-gradient(90deg,#dc2626,#f87171);"></div>
            </div>
            <span class="pareto-pct">6%</span>
        </div>
    </div>
</div>

<!-- Recent Inspections & Active Defect Claims -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card h-100 border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clipboard2-pulse-fill text-primary me-2"></i> Recent Quality Inspections</h6>
                <a href="inspections/history.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">History Log</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Roll Number</th>
                                <th>Fabric &amp; Supplier</th>
                                <th>Defect %</th>
                                <th>Grade</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentInspections as $insp): ?>
                                <?php
                                    $gradeClass  = ($insp['grade'] === 'Grade A') ? 'badge-grade-a' : (($insp['grade'] === 'Grade B') ? 'badge-grade-b' : 'badge-reject');
                                    $statusClass = ($insp['status'] === 'Approved') ? 'badge-grade-a' : 'badge-reject';
                                ?>
                                <tr>
                                    <td class="ps-4"><span class="code-cell"><?= htmlspecialchars($insp['roll_number']) ?></span></td>
                                    <td>
                                        <div class="fw-bold text-dark small"><?= htmlspecialchars($insp['fabric_name']) ?></div>
                                        <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($insp['supplier_name']) ?></div>
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
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bell-fill text-danger me-2"></i> Quality Defect Claims</h6>
                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 small"><?= $stats['complaints'] ?> Open</span>
            </div>
            <div class="card-body p-3">
                <?php foreach ($openComplaints as $comp): ?>
                    <div class="p-3 mb-2 rounded-3" style="background:#fff1f2;border:1px solid rgba(244,63,94,0.2);">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="badge bg-danger text-white" style="font-size:0.7rem;"><?= htmlspecialchars($comp['complaint_number']) ?></span>
                            <span class="badge bg-white text-danger border border-danger border-opacity-25" style="font-size:0.68rem;">Roll <?= htmlspecialchars($comp['roll_number']) ?></span>
                        </div>
                        <div class="fw-bold text-dark small mt-1"><?= htmlspecialchars($comp['supplier']) ?></div>
                        <p class="text-secondary small mb-0 mt-1" style="font-size:0.78rem;line-height:1.4;">
                            <?= htmlspecialchars(substr($comp['description'], 0, 90)) ?>...
                        </p>
                    </div>
                <?php endforeach; ?>
                <a href="complaints/index.php" class="btn btn-outline-danger w-100 btn-sm rounded-pill mt-2">Manage All Complaints</a>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Simulator + Quick Defect Logger Client-Side Script -->
<script>
function updateSimulator() {
    const rollLength   = parseFloat(document.getElementById('simRollLength').value);
    const defectLength = parseFloat(document.getElementById('simDefectLength').value);

    document.getElementById('simRollLengthLabel').innerText  = rollLength + ' meters';
    document.getElementById('simDefectLengthLabel').innerText = defectLength.toFixed(1) + ' meters';
    document.getElementById('simEndMeterLabel').innerText     = rollLength + ' m (Roll End)';

    const percent = (defectLength / rollLength) * 100;
    document.getElementById('simDefectPercent').innerText = percent.toFixed(2) + '%';

    const gradeBadge = document.getElementById('simulatorGradeBadge');
    const gradeText  = document.getElementById('simulatorGradeText');
    const pin        = document.getElementById('simDefectPin');

    const pinWidth = Math.min(Math.max((defectLength / rollLength) * 100, 4), 60);
    pin.style.left  = '35%';
    pin.style.width = pinWidth + '%';

    pin.className = 'defect-marker-pin';
    if (percent <= 1.00) {
        gradeBadge.className = 'badge-status badge-grade-a fs-6 px-3 py-2';
        gradeText.innerText  = 'Grade A (Export Certified)';
        pin.classList.add('defect-pin-sev1');
    } else if (percent <= 3.00) {
        gradeBadge.className = 'badge-status badge-grade-b fs-6 px-3 py-2';
        gradeText.innerText  = 'Grade B (Commercial Quality)';
        pin.classList.add('defect-pin-sev2');
    } else {
        gradeBadge.className = 'badge-status badge-reject fs-6 px-3 py-2';
        gradeText.innerText  = 'Grade C (Rejected Lot)';
        pin.classList.add('defect-pin-sev4');
    }
}

// Quick Defect Logger
const defectCounts = {};

function logDefect(type, btn) {
    defectCounts[type] = (defectCounts[type] || 0) + 1;
    const badge = btn.querySelector('.defect-count-badge');
    badge.style.display = 'flex';
    badge.textContent   = defectCounts[type];

    // Bump animation
    badge.style.transform = 'scale(1.4)';
    setTimeout(() => { badge.style.transform = 'scale(1)'; badge.style.transition = 'transform 0.2s'; }, 200);

    updateDefectOutput();
}

function updateDefectOutput() {
    const total = Object.values(defectCounts).reduce((a,b) => a + b, 0);
    if (total === 0) {
        document.getElementById('defectLogOutput').textContent = 'No defects logged this session.';
        return;
    }
    const parts = Object.entries(defectCounts).map(([k,v]) => v + '× ' + k);
    document.getElementById('defectLogOutput').innerHTML =
        '<strong>' + total + ' defect' + (total > 1 ? 's' : '') + ' logged</strong> — ' + parts.join(', ');
}

function clearDefectLog() {
    Object.keys(defectCounts).forEach(k => delete defectCounts[k]);
    document.querySelectorAll('.defect-count-badge').forEach(b => {
        b.style.display  = 'none';
        b.textContent = '0';
    });
    document.getElementById('defectLogOutput').textContent = 'No defects logged this session.';
}
</script>

<?php include 'includes/footer.php'; ?>
