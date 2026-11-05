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
        $msg = "<div class='alert alert-success shadow-sm rounded-3'><i class='bi bi-check-circle-fill me-2'></i> Fabric variety registered successfully!</div>";
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

// Image map for visual fabric swatches
$swatchImages = [
    'FAB-COT-01' => 'https://images.unsplash.com/photo-1598033129183-c4f50c736f10?w=600&auto=format&fit=crop&q=80',
    'FAB-DEN-02' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=600&auto=format&fit=crop&q=80',
    'FAB-LIN-03' => 'https://images.unsplash.com/photo-1584589167171-541ce45f1eea?w=600&auto=format&fit=crop&q=80',
    'FAB-PLY-04' => 'https://images.unsplash.com/photo-1528458876861-544fd1761a91?w=600&auto=format&fit=crop&q=80',
];
$defaultSwatch = 'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=600&auto=format&fit=crop&q=80';

include '../includes/header.php';
?>
<script>document.title = "Fabric Varieties - TexQura";</script>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-dark mb-0">Fabric Master Data</h3>
        <p class="text-muted small mb-0">Manage and catalog the fabric varieties and weave specifications.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#fabricModal">
        <i class="bi bi-plus-lg me-1"></i> Add Fabric Variety
    </button>
</div>

<?= $msg ?>

<!-- Visual Swatch Showcase -->
<div class="row g-3 mb-4">
    <?php foreach ($fabrics as $f): ?>
        <?php $img = $swatchImages[$f['fabric_code']] ?? $defaultSwatch; ?>
        <div class="col-xl-3 col-md-6">
            <div class="fabric-swatch-card h-100">
                <div class="swatch-thumb-wrap">
                    <img src="<?= $img ?>" alt="<?= htmlspecialchars($f['name']) ?>" loading="lazy">
                    <span class="swatch-badge"><?= htmlspecialchars($f['fabric_code']) ?></span>
                </div>
                <div class="swatch-body">
                    <div class="swatch-name"><?= htmlspecialchars($f['name']) ?></div>
                    <div class="swatch-meta mt-1">
                        <span><?= htmlspecialchars($f['type']) ?></span>
                        <span class="fw-bold text-primary"><?= htmlspecialchars($f['composition']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Detailed Specifications Inventory Table -->
<div class="card overflow-hidden border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-table text-primary me-2"></i> Technical Fabric Specifications Table</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Fabric Code</th>
                        <th>Fabric Name</th>
                        <th>Weave / Construction Type</th>
                        <th>Fiber Composition</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($fabrics)): ?>
                        <tr><td colspan="5" class="text-center p-4 text-muted">No fabrics cataloged yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($fabrics as $f): ?>
                    <tr>
                        <td class="ps-4"><span class="code-cell"><?= htmlspecialchars($f['fabric_code']) ?></span></td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($f['name']) ?></td>
                        <td><span class="badge bg-light text-dark border px-2 py-1"><?= htmlspecialchars($f['type']) ?></span></td>
                        <td><?= htmlspecialchars($f['composition']) ?></td>
                        <td><span class="badge-status badge-grade-a"><span class="badge-dot"></span> Active Spec</span></td>
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
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Register New Fabric Variety</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Fabric Code (SKU)</label>
                        <input type="text" name="fabric_code" class="form-control" required placeholder="e.g. FAB-COT-05">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Fabric Description Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Supima Sateen 80s">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Weave / Construction Type</label>
                        <select name="type" class="form-select" required>
                            <option value="Plain Weave">Plain Weave</option>
                            <option value="Twill 2/1">Twill 2/1</option>
                            <option value="Twill 3/1">Twill 3/1</option>
                            <option value="Satin / Sateen">Satin / Sateen</option>
                            <option value="Jacquard">Jacquard</option>
                            <option value="Knitted Jersey">Knitted Jersey</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Fiber Composition</label>
                        <input type="text" name="composition" class="form-control" required placeholder="e.g. 100% Supima Cotton">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Fabric</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
