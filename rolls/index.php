<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll_number = trim($_POST['roll_number']);
    $batch_id = $_POST['batch_id'];
    $roll_length = $_POST['roll_length'];
    $received_date = $_POST['received_date'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO fabric_rolls (roll_number, batch_id, roll_length, received_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$roll_number, $batch_id, $roll_length, $received_date]);
        $msg = "<div class='alert alert-success shadow-sm rounded-3'><i class='bi bi-check-circle-fill me-2'></i> Roll registered successfully!</div>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { 
            $msg = "<div class='alert alert-danger shadow-sm rounded-3'><i class='bi bi-exclamation-triangle-fill me-2'></i> Error: Roll Number must be unique.</div>";
        } else {
            $msg = "<div class='alert alert-danger shadow-sm rounded-3'><i class='bi bi-x-circle-fill me-2'></i> System Error: Ensure roll length is strictly greater than zero.</div>";
        }
    }
}

$rolls = $pdo->query("SELECT r.*, b.batch_number, f.name as fabric_name FROM fabric_rolls r JOIN batches b ON r.batch_id = b.id JOIN fabrics f ON b.fabric_id = f.id ORDER BY r.id DESC")->fetchAll();
$batches = $pdo->query("SELECT id, batch_number FROM batches")->fetchAll();

include '../includes/header.php';
?>
<script>document.title = "Fabric Rolls - TexQura";</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">Fabric Rolls Inventory</h3>
        <p class="text-muted small mb-0">Register and monitor individual fabric rolls for inspection.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 shadow-sm text-white" data-bs-toggle="modal" data-bs-target="#rollModal">
        <i class="bi bi-plus-lg me-1"></i> Register Roll
    </button>
</div>

<?= $msg ?>

<div class="card overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 border-0">Roll No.</th>
                        <th class="border-0">Batch Reference</th>
                        <th class="border-0">Fabric Quality</th>
                        <th class="border-0">Length (m)</th>
                        <th class="border-0">Inspection Status</th>
                        <th class="border-0">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($rolls) === 0): ?>
                        <tr><td colspan="6" class="text-center p-4 text-muted">No rolls registered in inventory.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($rolls as $r): 
                        $status_color = match($r['inspection_status']) {
                            'Approved' => 'success',
                            'Rejected' => 'danger',
                            'Pending Approval' => 'warning',
                            default => 'secondary'
                        };
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold text-primary"><?= htmlspecialchars($r['roll_number']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($r['batch_number']) ?></span></td>
                        <td><?= htmlspecialchars($r['fabric_name']) ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($r['roll_length']) ?></td>
                        <td><span class="badge bg-<?= $status_color ?> bg-opacity-10 text-<?= $status_color ?> px-3 py-2 rounded-pill"><?= $r['inspection_status'] ?></span></td>
                        <td>
                            <?php if ($r['inspection_status'] === 'Awaiting Inspection'): ?>
                                <a href="../inspections/create.php?roll_id=<?= $r['id'] ?>" class="btn btn-sm btn-primary rounded-pill px-3"><i class="bi bi-search me-1"></i> Inspect</a>
                            <?php else: ?>
                                <span class="text-muted small"><i class="bi bi-lock-fill"></i> Locked</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Roll Modal -->
<div class="modal fade" id="rollModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Register Individual Roll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Roll Number *</label>
                        <input type="text" name="roll_number" class="form-control" required placeholder="e.g., RL-2026-010">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Assign to Batch *</label>
                        <select name="batch_id" class="form-select" required>
                            <option value="">-- Select Parent Batch --</option>
                            <?php foreach($batches as $b) echo "<option value='{$b['id']}'>".htmlspecialchars($b['batch_number'])."</option>"; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Roll Length (m) *</label>
                            <input type="number" step="0.01" min="0.1" name="roll_length" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Date Received *</label>
                            <input type="date" name="received_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Register Roll</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>