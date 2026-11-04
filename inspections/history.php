<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

$inspections = $pdo->query("SELECT i.*, r.roll_number, s.name as supplier, f.name as fabric, u.name as inspector FROM inspections i JOIN fabric_rolls r ON i.roll_id = r.id JOIN batches b ON r.batch_id = b.id JOIN suppliers s ON b.supplier_id = s.id JOIN fabrics f ON b.fabric_id = f.id JOIN users u ON i.inspector_id = u.id ORDER BY i.inspection_date DESC")->fetchAll();

include '../includes/header.php';
?>
<script>document.title = "Inspection History - TexQura";</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">Inspection Log</h3>
        <p class="text-muted small mb-0">Complete historical record of all fabric inspections.</p>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-body p-0">
        <div class="p-3 bg-light border-bottom">
            <input type="text" id="searchInput" class="form-control form-control-lg border-0 shadow-sm" placeholder="🔍 Search by Roll, Supplier, or Status..." onkeyup="filterTable()">
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="historyTable">
                <thead class="bg-white">
                    <tr>
                        <th class="ps-4 border-0">Date</th>
                        <th class="border-0">Roll No.</th>
                        <th class="border-0">Supplier / Fabric</th>
                        <th class="border-0">Inspector</th>
                        <th class="border-0 text-center">Defect %</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($inspections) === 0): ?>
                        <tr><td colspan="7" class="text-center p-4 text-muted">No inspections found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($inspections as $row): 
                        $status_color = match($row['status']) {
                            'Approved' => 'success',
                            'Rejected' => 'danger',
                            'Pending Approval' => 'warning',
                            default => 'secondary'
                        };
                    ?>
                    <tr>
                        <td class="ps-4 text-muted small"><?= date('d M Y, H:i', strtotime($row['inspection_date'])) ?></td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($row['roll_number']) ?></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($row['supplier']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($row['fabric']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($row['inspector']) ?></td>
                        <td class="text-center"><span class="badge bg-dark px-2 py-1"><?= $row['defect_percentage'] ?>%</span><br><small class="text-<?= $row['grade'] == 'Grade A' ? 'success' : ($row['grade'] == 'Grade B' ? 'warning' : 'danger') ?> fw-bold"><?= $row['grade'] ?></small></td>
                        <td><span class="badge bg-<?= $status_color ?> bg-opacity-10 text-<?= $status_color ?> px-3 py-2 rounded-pill"><?= $row['status'] ?></span></td>
                        <td><a href="view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-light border shadow-sm rounded-circle"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterTable() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let rows = document.querySelectorAll("#historyTable tbody tr");
    rows.forEach(row => { row.style.display = row.innerText.toLowerCase().includes(input) ? "" : "none"; });
}
</script>

<?php include '../includes/footer.php'; ?>