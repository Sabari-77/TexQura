<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

// Secure CSV Export functionality
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=TexQura_Quality_Report_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Inspection ID', 'Date', 'Roll No', 'Supplier', 'Fabric', 'Defect %', 'Grade', 'Status']);
    
    $data = $pdo->query("SELECT i.id, i.inspection_date, r.roll_number, s.name as supplier, f.name as fabric, i.defect_percentage, i.grade, i.status FROM inspections i JOIN fabric_rolls r ON i.roll_id = r.id JOIN batches b ON r.batch_id = b.id JOIN suppliers s ON b.supplier_id = s.id JOIN fabrics f ON b.fabric_id = f.id ORDER BY i.id DESC")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($data as $row) fputcsv($output, $row);
    fclose($output);
    exit;
}

$total_inspections = $pdo->query("SELECT COUNT(*) FROM inspections")->fetchColumn();
$avg_defect = $pdo->query("SELECT AVG(defect_percentage) FROM inspections")->fetchColumn() ?? 0;
$rejected = $pdo->query("SELECT COUNT(*) FROM inspections WHERE status = 'Rejected'")->fetchColumn();

include '../includes/header.php';
?>
<script>document.title = "Reports - TexQura";</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">Analytics & Reports</h3>
        <p class="text-muted small mb-0">Monitor factory quality metrics and export data.</p>
    </div>
    <a href="?export=csv" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold">
        <i class="bi bi-file-earmark-excel me-1"></i> Export to CSV
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white h-100 border-0 shadow-sm rounded-4">
            <div class="card-body text-center p-4">
                <i class="bi bi-clipboard-data fs-1 opacity-50 mb-2"></i>
                <h5 class="fw-bold text-white-75 text-uppercase small">Total Inspections</h5>
                <h1 class="display-4 fw-bold mb-0"><?= $total_inspections ?></h1>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white h-100 border-0 shadow-sm rounded-4">
            <div class="card-body text-center p-4">
                <i class="bi bi-graph-down fs-1 opacity-50 mb-2"></i>
                <h5 class="fw-bold text-white-75 text-uppercase small">Average Defect Rate</h5>
                <h1 class="display-4 fw-bold mb-0"><?= round($avg_defect, 2) ?>%</h1>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white h-100 border-0 shadow-sm rounded-4">
            <div class="card-body text-center p-4">
                <i class="bi bi-x-octagon fs-1 opacity-50 mb-2"></i>
                <h5 class="fw-bold text-white-75 text-uppercase small">Total Rejected Rolls</h5>
                <h1 class="display-4 fw-bold mb-0"><?= $rejected ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body text-center p-5">
        <i class="bi bi-cloud-arrow-down text-primary mb-3" style="font-size: 4rem;"></i>
        <h4 class="fw-bold text-dark mb-2">Download Complete Data Center</h4>
        <p class="text-muted mx-auto" style="max-width: 500px;">Use the secure export button at the top right to download your entire quality control database as a CSV file for advanced Excel or BI processing.</p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>