<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

$msg = '';

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

$fabrics = $pdo->query("SELECT * FROM fabrics ORDER BY id DESC")->fetchAll();

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
        <p class="text-muted small mb-0">Catalog and monitor fabric varieties, fiber specs, and weave construction.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#fabricModal">
        <i class="bi bi-plus-lg me-1"></i> Add Fabric Variety
    </button>
</div>

<?= $msg ?>

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
                    <?php foreach ($fabrics as $f): ?>
                    <tr>
                        <td class="ps-4"><span class="code-cell"><?= htmlspecialchars($f['fabric_code']) ?></span></td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($f['name']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($f['type']) ?></span></td>
                        <td class="text-secondary"><?= htmlspecialchars($f['composition']) ?></td>
                        <td><span class="badge-status badge-grade-a"><span class="badge-dot"></span> Active</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="fabricModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Register New Fabric Variety</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Fabric Code *</label>
                        <input type="text" name="fabric_code" class="form-control" required placeholder="e.g., FAB-COT-05">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Fabric Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Egyptian Giza Cotton">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Weave Construction Type</label>
                        <input type="text" name="type" class="form-control" placeholder="e.g., Plain Weave, 3/1 Twill, Satin">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Fiber Composition</label>
                        <input type="text" name="composition" class="form-control" placeholder="e.g., 100% Cotton, 60/40 Poly-Cotton">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Fabric</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
