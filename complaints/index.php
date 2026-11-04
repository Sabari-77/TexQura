<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inspection_id = $_POST['inspection_id'];
    $desc = trim($_POST['description']);
    $complaint_no = "CMP-" . date('Ymd') . "-" . rand(1000, 9999);
    
    $stmt = $pdo->prepare("INSERT INTO complaints (complaint_number, inspection_id, description) VALUES (?, ?, ?)");
    $stmt->execute([$complaint_no, $inspection_id, $desc]);
    $msg = "<div class='alert alert-success shadow-sm rounded-3'><i class='bi bi-check-circle-fill me-2'></i> Complaint logged successfully! Ticket: <b>$complaint_no</b></div>";
}

// Fetch only rejected inspections so users can't log complaints on approved goods
$rejected = $pdo->query("SELECT i.id, r.roll_number, s.name as supplier FROM inspections i JOIN fabric_rolls r ON i.roll_id = r.id JOIN batches b ON r.batch_id = b.id JOIN suppliers s ON b.supplier_id = s.id WHERE i.status = 'Rejected'")->fetchAll();

$complaints = $pdo->query("SELECT c.*, r.roll_number, s.name as supplier FROM complaints c JOIN inspections i ON c.inspection_id = i.id JOIN fabric_rolls r ON i.roll_id = r.id JOIN batches b ON r.batch_id = b.id JOIN suppliers s ON b.supplier_id = s.id ORDER BY c.id DESC")->fetchAll();

include '../includes/header.php';
?>
<script>document.title = "Supplier Complaints - TexQura";</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">Supplier Defect Complaints</h3>
        <p class="text-muted small mb-0">Raise official complaint tickets for rejected fabric rolls.</p>
    </div>
    <button class="btn btn-danger rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#complaintModal">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> Log New Complaint
    </button>
</div>

<?= $msg ?>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 border-0">Ticket No.</th>
                        <th class="border-0">Supplier</th>
                        <th class="border-0">Roll No.</th>
                        <th class="border-0">Issue Description</th>
                        <th class="border-0">Date Logged</th>
                        <th class="border-0">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($complaints) === 0): ?>
                        <tr><td colspan="6" class="text-center p-4 text-muted">No complaints logged yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($complaints as $c): ?>
                    <tr>
                        <td class="ps-4 fw-bold text-danger"><?= htmlspecialchars($c['complaint_number']) ?></td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($c['supplier']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($c['roll_number']) ?></span></td>
                        <td class="text-muted small" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($c['description']) ?></td>
                        <td class="text-muted small"><?= date('d M Y', strtotime($c['complaint_date'])) ?></td>
                        <td><span class="badge bg-<?= $c['status'] == 'Open' ? 'danger' : 'success' ?> bg-opacity-10 text-<?= $c['status'] == 'Open' ? 'danger' : 'success' ?> px-3 py-2 rounded-pill"><?= $c['status'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Complaint Modal -->
<div class="modal fade" id="complaintModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-bottom-0">
                <h5 class="modal-title fw-bold">Issue Supplier Complaint</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body pt-4">
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">Select Rejected Roll *</label>
                        <select name="inspection_id" class="form-select bg-light border-0" required>
                            <option value="">-- View Eligible Rolls --</option>
                            <?php foreach($rejected as $r) echo "<option value='{$r['id']}'>".htmlspecialchars($r['roll_number'])." (".htmlspecialchars($r['supplier']).")</option>"; ?>
                        </select>
                        <div class="form-text small text-danger mt-1">Note: Only completely rejected rolls are eligible for complaint logging.</div>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label text-muted small fw-bold">Complaint Details *</label>
                        <textarea name="description" class="form-control bg-light border-0" rows="4" required placeholder="Describe the severe defect to the supplier clearly..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4 fw-bold">Submit Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>