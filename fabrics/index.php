<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

$msg = '';

// Handle Form Submission (Add Fabric)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['fabric_code']);
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $composition = trim($_POST['composition']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO fabrics (fabric_code, name, type, composition) VALUES (?, ?, ?, ?)");
        $stmt->execute([$code, $name, $type, $composition]);
        $msg = "<div class='alert alert-success shadow-sm rounded-3'><i class='bi bi-check-circle-fill me-2'></i> Fabric type registered successfully!</div>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { 
            $msg = "<div class='alert alert-danger shadow-sm rounded-3'><i class='bi bi-exclamation-triangle-fill me-2'></i> Error: Fabric Code must be unique.</div>";
        } else {
            $msg = "<div class='alert alert-danger shadow-sm rounded-3'><i class='bi bi-x-circle-fill me-2'></i> Error processing request.</div>";
        }
    }
}

// Fetch all fabrics
$fabrics = $pdo->query("SELECT * FROM fabrics ORDER BY id DESC")->fetchAll();

include '../includes/header.php';
?>
<script>document.title = "Fabrics - TexQura";</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">Fabric Master Data</h3>
        <p class="text-muted small mb-0">Manage the fabric varieties configured in the system.</p>
    </div>
    <button class="btn btn-secondary rounded-pill px-4 shadow-sm text-white" style="background: var(--secondary); border: none;" data-bs-toggle="modal" data-bs-target="#fabricModal">
        <i class="bi bi-plus-lg me-1"></i> Add Fabric Type
    </button>
</div>

<?= $msg ?>

<div class="card overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 border-0">Fabric Code</th>
                        <th class="border-0">Fabric Name</th>
                        <th class="border-0">Material Type</th>
                        <th class="border-0">Composition</th>
                        <th class="border-0">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($fabrics) === 0): ?>
                        <tr><td colspan="5" class="text-center p-4 text-muted">No fabrics configured yet. Add one to continue.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($fabrics as $f): ?>
                    <tr>
                        <td class="ps-4"><span class="badge bg-light text-dark border px-2 py-1"><?= htmlspecialchars($f['fabric_code']) ?></span></td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($f['name']) ?></td>
                        <td><?= htmlspecialchars($f['type']) ?: '<span class="text-muted">-</span>' ?></td>
                        <td><?= htmlspecialchars($f['composition']) ?: '<span class="text-muted">-</span>' ?></td>
                        <td><span class="badge bg-<?= $f['status'] == 'active' ? 'success' : 'secondary' ?> bg-opacity-10 text-<?= $f['status'] == 'active' ? 'success' : 'secondary' ?> px-3 py-2 rounded-pill"><?= ucfirst($f['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Fabric Modal -->
<div class="modal fade" id="fabricModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Register New Fabric</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Fabric Code *</label>
                        <input type="text" name="fabric_code" class="form-control" required placeholder="e.g., FAB-100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Fabric Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Organic Cotton Poplin">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Type</label>
                        <input type="text" name="type" class="form-control" placeholder="e.g., Woven, Knitted">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Composition</label>
                        <input type="text" name="composition" class="form-control" placeholder="e.g., 100% Cotton, Polycotton">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary text-white px-4" style="background: var(--secondary); border: none;">Save Fabric</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>