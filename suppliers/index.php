<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

$msg = '';

// Handle Form Submission (Add Supplier)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['supplier_code']);
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact_person']);
    $email = trim($_POST['email']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO suppliers (supplier_code, name, contact_person, email) VALUES (?, ?, ?, ?)");
        $stmt->execute([$code, $name, $contact, $email]);
        $msg = "<div class='alert alert-success shadow-sm rounded-3'><i class='bi bi-check-circle-fill me-2'></i> Supplier added successfully!</div>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Integrity constraint violation (duplicate)
            $msg = "<div class='alert alert-danger shadow-sm rounded-3'><i class='bi bi-exclamation-triangle-fill me-2'></i> Error: Supplier Code must be unique.</div>";
        } else {
            $msg = "<div class='alert alert-danger shadow-sm rounded-3'><i class='bi bi-x-circle-fill me-2'></i> Error processing request.</div>";
        }
    }
}

// Fetch all suppliers
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY id DESC")->fetchAll();

include '../includes/header.php';
?>
<script>document.title = "Suppliers - TexQura";</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">Manage Suppliers</h3>
        <p class="text-muted small mb-0">View and register your raw material vendors.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#supplierModal">
        <i class="bi bi-plus-lg me-1"></i> Add Supplier
    </button>
</div>

<?= $msg ?>

<div class="card overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 border-0">Code</th>
                        <th class="border-0">Supplier Name</th>
                        <th class="border-0">Contact Person</th>
                        <th class="border-0">Email Address</th>
                        <th class="border-0">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($suppliers) === 0): ?>
                        <tr><td colspan="5" class="text-center p-4 text-muted">No suppliers found. Add one to get started!</td></tr>
                    <?php endif; ?>
                    <?php foreach ($suppliers as $s): ?>
                    <tr>
                        <td class="ps-4"><span class="badge bg-light text-dark border px-2 py-1"><?= htmlspecialchars($s['supplier_code']) ?></span></td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($s['name']) ?></td>
                        <td><?= htmlspecialchars($s['contact_person']) ?: '<span class="text-muted">-</span>' ?></td>
                        <td><?= htmlspecialchars($s['email']) ?: '<span class="text-muted">-</span>' ?></td>
                        <td><span class="badge bg-<?= $s['status'] == 'active' ? 'success' : 'secondary' ?> bg-opacity-10 text-<?= $s['status'] == 'active' ? 'success' : 'secondary' ?> px-3 py-2 rounded-pill"><?= ucfirst($s['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Register New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Supplier Code *</label>
                        <input type="text" name="supplier_code" class="form-control" required placeholder="e.g., SUP-001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Supplier Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Premium Textiles Ltd">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" placeholder="John Doe">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="contact@supplier.com">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>