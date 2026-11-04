<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT i.*, r.roll_number, r.roll_length, b.batch_number, s.name as supplier_name, f.name as fabric_name, u.name as inspector_name, m.name as manager_name FROM inspections i JOIN fabric_rolls r ON i.roll_id = r.id JOIN batches b ON r.batch_id = b.id JOIN suppliers s ON b.supplier_id = s.id JOIN fabrics f ON b.fabric_id = f.id JOIN users u ON i.inspector_id = u.id LEFT JOIN users m ON i.manager_id = m.id WHERE i.id = ?");
$stmt->execute([$id]);
$inspection = $stmt->fetch();

if (!$inspection) die("<div style='padding:20px;'>Inspection not found.</div>");

$defects = $pdo->prepare("SELECT * FROM inspection_defects WHERE inspection_id = ?");
$defects->execute([$id]);
$defectsList = $defects->fetchAll();

include '../includes/header.php';
?>
<script>document.title = "View Inspection - TexQura";</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">Inspection #<?= str_pad($inspection['id'], 5, '0', STR_PAD_LEFT) ?></h3>
        <p class="text-muted small mb-0">Detailed view of recorded defects and manager remarks.</p>
    </div>
    <a href="history.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm"><i class="bi bi-arrow-left"></i> Back to History</a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success shadow-sm rounded-3"><i class="bi bi-check-circle-fill me-2"></i> Inspection submitted successfully! It is now pending approval.</div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom-0 fw-bold"><i class="bi bi-clipboard-data text-primary me-2"></i> Inspection Details</div>
            <div class="card-body pt-0">
                <table class="table table-borderless table-sm text-muted">
                    <tr><td width="35%">Roll Number</td><td class="fw-bold text-dark"><?= htmlspecialchars($inspection['roll_number']) ?></td></tr>
                    <tr><td>Batch & Fabric</td><td class="text-dark"><?= htmlspecialchars($inspection['batch_number']) ?> | <?= htmlspecialchars($inspection['fabric_name']) ?></td></tr>
                    <tr><td>Total Roll Length</td><td class="text-dark"><?= htmlspecialchars($inspection['roll_length']) ?> m</td></tr>
                    <tr><td>Inspection Date</td><td class="text-dark"><?= date('d M Y, h:i A', strtotime($inspection['inspection_date'])) ?></td></tr>
                    <tr><td>Inspector</td><td class="text-dark"><?= htmlspecialchars($inspection['inspector_name']) ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <?php 
            $status_color = match($inspection['status']) { 'Approved' => 'success', 'Rejected' => 'danger', 'Pending Approval' => 'warning', default => 'secondary' };
            $grade_color = match($inspection['grade']) { 'Grade A' => 'success', 'Grade B' => 'warning', default => 'danger' };
        ?>
        <div class="card border-0 shadow-sm h-100 bg-<?= $status_color ?> bg-opacity-10 text-<?= $status_color ?>">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                <span class="badge bg-<?= $status_color ?> mb-3 px-3 py-2 rounded-pill fs-6"><?= $inspection['status'] ?></span>
                <div class="d-flex w-100 justify-content-around">
                    <div>
                        <small class="text-uppercase fw-bold opacity-75">Defect Rate</small>
                        <h2 class="fw-bold mb-0 text-dark"><?= $inspection['defect_percentage'] ?>%</h2>
                    </div>
                    <div>
                        <small class="text-uppercase fw-bold opacity-75">Final Grade</small>
                        <h2 class="fw-bold mb-0 text-<?= $grade_color ?>"><?= $inspection['grade'] ?></h2>
                    </div>
                </div>
                <?php if ($inspection['manager_id']): ?>
                    <hr class="w-100 border-<?= $status_color ?> opacity-25 my-3">
                    <small class="text-dark fw-bold">Reviewed By: <?= htmlspecialchars($inspection['manager_name']) ?></small>
                    <small class="text-muted mt-1 fst-italic">"<?= htmlspecialchars($inspection['remarks'] ?? 'No remarks provided.') ?>"</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-white py-3 border-bottom-0 fw-bold d-flex justify-content-between">
        <span><i class="bi bi-bug text-danger me-2"></i> Defect Breakdown</span>
        <span class="badge bg-dark">Total Affected: <?= $inspection['total_affected_length'] ?> m</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4 border-0">Defect Type</th>
                    <th class="border-0">Position</th>
                    <th class="border-0">Affected Length</th>
                    <th class="border-0">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($defectsList) === 0): ?>
                    <tr><td colspan="4" class="text-center p-4 text-success fw-bold"><i class="bi bi-shield-check fs-4 d-block mb-2"></i> No defects recorded. Perfect roll!</td></tr>
                <?php endif; ?>
                <?php foreach ($defectsList as $d): ?>
                <tr>
                    <td class="ps-4 fw-bold text-danger"><?= htmlspecialchars($d['defect_type']) ?></td>
                    <td><?= htmlspecialchars($d['position_m']) ?> m</td>
                    <td><?= htmlspecialchars($d['affected_length']) ?> m</td>
                    <td class="text-muted fst-italic"><?= htmlspecialchars($d['remarks']) ?: '-' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>