<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $batch_number = trim($_POST['batch_number']);
    $supplier_id =$_POST['supplier_id'];
    $fabric_id =$_POST['fabric_id'];
    $received_date =$_POST['received_date'];
    $total_rolls =$_POST['total_rolls'];
    $total_length =$_POST['total_length'];
    
    try {
        $stmt =$pdo->prepare("INSERT INTO batches (batch_number, supplier_id, fabric_id, received_date, total_rolls, total_length) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$batch_number, $supplier_id,$fabric_id, $received_date,$total_rolls, $total_length]);$msg = "<div class='alert alert-success shadow-sm rounded-3'><i class='bi bi-check-circle-fill me-2'></i> Batch registered successfully!</div>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {$msg = "<div class='alert alert-danger shadow-sm rounded-3'><i class='bi bi-exclamation-triangle-fill me-2'></i> Error: Batch Number must be unique.</div>";
        } else {
            $msg = "<div class='alert alert-danger shadow-sm rounded-3'><i class='bi bi-x-circle-fill me-2'></i> Error processing request.</div>";
        }
    }
}

$batches =$pdo->query("SELECT b.*, s.name as supplier_name, f.name as fabric_name FROM batches b JOIN suppliers s ON b.supplier_id = s.id JOIN fabrics f ON b.fabric_id = f.id ORDER BY b.id DESC")->fetchAll();
$suppliers =$pdo->query("SELECT id, name FROM suppliers WHERE status = 'active'")->fetchAll();
$fabrics =$pdo->query("SELECT id, name FROM fabrics WHERE status = 'active'")->fetchAll();

include '../includes/header.php';
?>
<script>document.title = "Batches - TexQura";</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">Batch Management</h3>
        <p class="text-muted small mb-0">Record and track fabric batches received from suppliers.</p>
    </div>
    <button class="btn btn-dark rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#batchModal">
        <i class="bi bi-plus-lg me-1"></i> New Batch
    </button>
</div>

<?= $msg ?>

<div class="card overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 border-0">Batch No.</th>
                        <th class="border-0">Supplier</th>
                        <th class="border-0">Fabric</th>
                        <th class="border-0">Received Date</th>
                        <th class="border-0">Total Rolls</th>
                        <th class="border-0">Total Length</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($batches) === 0): ?>
                        <tr><td colspan="6" class="text-center p-4 text-muted">No batches recorded.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($batches as$b): ?>
                    <tr>
                        <td class="ps-4"><span class="badge bg-dark px-2 py-1"><?= htmlspecialchars($b['batch_number']) ?></span></td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($b['supplier_name']) ?></td>
                        <td><?= htmlspecialchars($b['fabric_name']) ?></td>
                        <td><?= date('d M Y', strtotime($b['received_date'])) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($b['total_rolls']) ?> Rolls</span></td>
                        <td><?= htmlspecialchars($b['total_length']) ?> m</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Batch Modal -->
<div class="modal fade" id="batchModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Register New Batch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body pt-3 row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Batch Number *</label>
                        <input type="text" name="batch_number" class="form-control" required placeholder="e.g., BAT-2026-001">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Received Date *</label>
                        <input type="date" name="received_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Supplier *</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">-- Select Supplier --</option>
                            <?php foreach($suppliers as$s) echo "<option value='{$s['id']}'>".htmlspecialchars($s['name'])."</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Fabric Type *</label>
                        <select name="fabric_id" class="form-select" required>
                            <option value="">-- Select Fabric --</option>
                            <?php foreach($fabrics as$f) echo "<option value='{$f['id']}'>".htmlspecialchars($f['name'])."</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Total Rolls Expected *</label>
                        <input type="number" min="1" name="total_rolls" class="form-control" required placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Total Length (m) *</label>
                        <input type="number" step="0.01" min="0.1" name="total_length" class="form-control" required placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark px-4">Save Batch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>