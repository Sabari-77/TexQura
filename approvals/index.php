<?php
session_start();
require_once '../config/database.php';

// Strict Role Constraint: Managers (3) and Admins (1) only
if (!isset($_SESSION['role_id']) || ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 3)) {
    die("<div style='padding:20px; color:red;'>Access Denied. Managers only.</div>");
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $inspection_id = $_POST['inspection_id'];
    $action = $_POST['action']; // 'Approved' or 'Rejected'
    $remarks = trim($_POST['remarks']);
    
    // Strict business logic check: You cannot approve your own inspection
    $inspCheck = $pdo->prepare("SELECT inspector_id FROM inspections WHERE id = ?");
    $inspCheck->execute([$inspection_id]);
    $creator = $inspCheck->fetchColumn();

    if ($creator == $_SESSION['user_id']) {
        $msg = "<div class='alert alert-danger shadow-sm rounded-3'><i class='bi bi-shield-lock-fill me-2'></i> Security Error: You cannot approve/reject an inspection you created.</div>";
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE inspections SET status = ?, manager_id = ?, remarks = ? WHERE id = ?");
            $stmt->execute([$action, $_SESSION['user_id'], $remarks, $inspection_id]);
            
            $stmtRoll = $pdo->prepare("UPDATE fabric_rolls SET inspection_status = ? WHERE id = (SELECT roll_id FROM inspections WHERE id = ?)");
            $stmtRoll->execute([$action, $inspection_id]);
            
            if ($action === 'Approved') {
                $cert_num = "TQC-" . date('Y') . "-" . str_pad($inspection_id, 4, '0', STR_PAD_LEFT);
                $stmtCert = $pdo->prepare("INSERT INTO certificates (certificate_number, inspection_id) VALUES (?, ?)");
                $stmtCert->execute([$cert_num, $inspection_id]);
            }
            
            $pdo->commit();
            $msg = "<div class='alert alert-success shadow-sm rounded-3'><i class='bi bi-check-circle-fill me-2'></i> Inspection successfully $action!</div>";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "<div class='alert alert-danger shadow-sm rounded-3'>Error processing approval.</div>";
        }
    }
}

$pending = $pdo->query("SELECT i.*, r.roll_number, s.name as supplier, f.name as fabric, u.name as inspector FROM inspections i JOIN fabric_rolls r ON i.roll_id = r.id JOIN batches b ON r.batch_id = b.id JOIN suppliers s ON b.supplier_id = s.id JOIN fabrics f ON b.fabric_id = f.id JOIN users u ON i.inspector_id = u.id WHERE i.status = 'Pending Approval' ORDER BY i.inspection_date DESC")->fetchAll();

include '../includes/header.php';
?>
<script>document.title = "Approvals - TexQura";</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">Quality Approvals Workspace</h3>
        <p class="text-muted small mb-0">Review pending inspections and issue quality certificates.</p>
    </div>
</div>

<?= $msg ?>

<div class="row g-4">
    <?php if (count($pending) == 0): ?>
        <div class="col-12">
            <div class="alert bg-white border-0 shadow-sm p-5 text-center text-muted rounded-4">
                <i class="bi bi-inbox fs-1 d-block mb-3 text-primary"></i>
                <h5>All Caught Up!</h5>
                <p class="mb-0">There are currently no pending inspections requiring your approval.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($pending as $row): ?>
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                <div class="card-header bg-warning bg-opacity-10 border-bottom-0 py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="bi bi-record-circle text-primary me-2"></i> Roll: <?= htmlspecialchars($row['roll_number']) ?></span>
                    <a href="../inspections/view.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill">View Full Log <i class="bi bi-box-arrow-up-right ms-1"></i></a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-6"><small class="text-muted text-uppercase fw-bold">Supplier</small><br><span class="fw-bold text-dark"><?= htmlspecialchars($row['supplier']) ?></span></div>
                        <div class="col-6 text-end"><small class="text-muted text-uppercase fw-bold">Fabric</small><br><span class="fw-bold text-dark"><?= htmlspecialchars($row['fabric']) ?></span></div>
                    </div>
                    <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center mb-4 border">
                        <div>
                            <small class="text-muted d-block text-uppercase fw-bold">Defect Rate</small>
                            <span class="fs-4 fw-bold text-<?= $row['defect_percentage'] > 3 ? 'danger' : 'dark' ?>"><?= $row['defect_percentage'] ?>%</span>
                        </div>
                        <div class="text-center">
                            <small class="text-muted d-block text-uppercase fw-bold">Inspector</small>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary"><?= htmlspecialchars($row['inspector']) ?></span>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block text-uppercase fw-bold">Suggested Grade</small>
                            <span class="badge bg-<?= $row['grade'] == 'Grade A' ? 'success' : ($row['grade'] == 'Grade B' ? 'warning' : 'danger') ?> fs-6"><?= $row['grade'] ?></span>
                        </div>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="inspection_id" value="<?= $row['id'] ?>">
                        <div class="mb-3">
                            <input type="text" name="remarks" class="form-control border-0 bg-light" placeholder="Manager Remarks (Required for rejection, optional for approval)">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="action" value="Approved" class="btn btn-success flex-fill rounded-3 fw-bold shadow-sm py-2"><i class="bi bi-check-circle me-1"></i> Approve & Issue Certificate</button>
                            <button type="submit" name="action" value="Rejected" class="btn btn-danger flex-fill rounded-3 fw-bold shadow-sm py-2"><i class="bi bi-x-circle me-1"></i> Reject Roll</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>