<?php
session_start();
require_once '../config/database.php';

// Strict Role Constraint: Only Inspectors (2) and Admins (1)
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2)) { 
    die("<div style='padding:20px; font-family:sans-serif; color:red;'>Access Denied. Only Quality Inspectors can perform this action.</div>"); 
}

$msg = '';
$roll_id = $_GET['roll_id'] ?? null;

if (!$roll_id) {
    header("Location: ../rolls/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $defects = $_POST['defects'] ?? [];
    $total_roll_length = floatval($_POST['total_roll_length']);
    $total_affected = 0;
    
    // Server-side validation
    foreach ($defects as $defect) {
        $length = floatval($defect['affected_length']);
        $position = floatval($defect['position']);
        if ($length > 0 && ($position + $length) <= $total_roll_length) {
            $total_affected += $length;
        }
    }
    
    $defect_percentage = ($total_roll_length > 0) ? round(($total_affected / $total_roll_length) * 100, 2) : 0;
    
    // Server-Side Grade Calculation
    $rules = $pdo->query("SELECT grade, max_percentage FROM grade_rules ORDER BY max_percentage ASC")->fetchAll();
    $final_grade = 'Grade C'; // Fallback
    foreach ($rules as $rule) {
        if ($defect_percentage <= $rule['max_percentage']) {
            $final_grade = $rule['grade'];
            break;
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO inspections (roll_id, inspector_id, total_affected_length, defect_percentage, grade) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$roll_id, $_SESSION['user_id'], $total_affected, $defect_percentage, $final_grade]);
        $inspection_id = $pdo->lastInsertId();
        
        $def_stmt = $pdo->prepare("INSERT INTO inspection_defects (inspection_id, defect_type, position_m, affected_length, remarks) VALUES (?, ?, ?, ?, ?)");
        foreach ($defects as $defect) {
            if (floatval($defect['affected_length']) > 0) {
                $def_stmt->execute([$inspection_id, $defect['type'], $defect['position'], $defect['affected_length'], $defect['remarks']]);
            }
        }
        
        $pdo->prepare("UPDATE fabric_rolls SET inspection_status = 'Pending Approval' WHERE id = ?")->execute([$roll_id]);
        
        $pdo->commit();
        header("Location: view.php?id=$inspection_id&success=1");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = "<div class='alert alert-danger shadow-sm'>Error saving inspection: " . $e->getMessage() . "</div>";
    }
}

$roll = $pdo->prepare("SELECT r.*, f.name as fabric_name, b.batch_number, s.name as supplier_name FROM fabric_rolls r JOIN batches b ON r.batch_id = b.id JOIN fabrics f ON b.fabric_id = f.id JOIN suppliers s ON b.supplier_id = s.id WHERE r.id = ?");
$roll->execute([$roll_id]);
$rollData = $roll->fetch();

if (!$rollData || $rollData['inspection_status'] !== 'Awaiting Inspection') {
    die("<div style='padding:20px; font-family:sans-serif;'>Invalid Roll or Roll already inspected.</div>");
}

include '../includes/header.php';
?>
<script>document.title = "Inspect Roll - TexQura";</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">Fabric Quality Inspection</h3>
        <p class="text-muted small mb-0">Record defects to automatically calculate quality grade.</p>
    </div>
    <a href="../rolls/index.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<?= $msg ?>

<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(to right, #f8fafc, #f1f5f9); border-left: 5px solid var(--primary) !important;">
    <div class="card-body row g-3 align-items-center">
        <div class="col-md-3">
            <small class="text-muted text-uppercase fw-bold">Roll Number</small>
            <h5 class="fw-bold text-primary mb-0"><?= htmlspecialchars($rollData['roll_number']) ?></h5>
        </div>
        <div class="col-md-3">
            <small class="text-muted text-uppercase fw-bold">Fabric Quality</small>
            <h5 class="mb-0 text-dark"><?= htmlspecialchars($rollData['fabric_name']) ?></h5>
        </div>
        <div class="col-md-3">
            <small class="text-muted text-uppercase fw-bold">Batch Ref</small>
            <h5 class="mb-0 text-dark"><?= htmlspecialchars($rollData['batch_number']) ?></h5>
        </div>
        <div class="col-md-3 text-end">
            <small class="text-muted text-uppercase fw-bold">Total Roll Length</small>
            <h4 class="fw-bold text-dark mb-0"><span id="rollLength"><?= $rollData['roll_length'] ?></span> m</h4>
        </div>
    </div>
</div>

<form method="POST" id="inspectionForm">
    <input type="hidden" name="total_roll_length" value="<?= $rollData['roll_length'] ?>">
    
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
            <h5 class="fw-bold mb-0 text-dark">Defect Records</h5>
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" onclick="addDefectRow()"><i class="bi bi-plus-lg"></i> Add Defect</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="defectTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 border-0">Defect Type</th>
                            <th class="border-0">Position (m)</th>
                            <th class="border-0">Affected Length (m)</th>
                            <th class="border-0">Remarks</th>
                            <th class="border-0 text-center">Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamic Rows Here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 bg-primary bg-opacity-10 text-primary">
                <div class="card-body text-center">
                    <small class="text-uppercase fw-bold">Total Affected Length</small>
                    <h2 class="fw-bold mt-2 mb-0"><span id="totalAffected">0.00</span> m</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 bg-secondary bg-opacity-10 text-secondary">
                <div class="card-body text-center">
                    <small class="text-uppercase fw-bold">Defect Percentage</small>
                    <h2 class="fw-bold mt-2 mb-0"><span id="defectPercentage">0.00</span> %</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 bg-dark text-white">
                <div class="card-body text-center">
                    <small class="text-uppercase fw-bold text-white-50">Calculated Grade</small>
                    <h2 class="fw-bold mt-2 mb-0" id="previewGrade">Grade A</h2>
                </div>
            </div>
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 shadow-sm fw-bold">Submit Inspection for Manager Approval <i class="bi bi-check2-circle ms-2"></i></button>
</form>

<script>
let rowIdx = 0;
const totalLength = parseFloat(document.getElementById('rollLength').innerText);

function addDefectRow() {
    const tbody = document.querySelector('#defectTable tbody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="ps-4">
            <select class="form-select border-0 bg-light" name="defects[${rowIdx}][type]" required>
                <option value="Hole">Hole</option>
                <option value="Stain">Stain</option>
                <option value="Broken Yarn">Broken Yarn</option>
                <option value="Colour Variation">Colour Variation</option>
            </select>
        </td>
        <td><input type="number" step="0.01" min="0" max="${totalLength}" class="form-control border-0 bg-light pos-input" name="defects[${rowIdx}][position]" required onchange="calculate()"></td>
        <td><input type="number" step="0.01" min="0.01" class="form-control border-0 bg-light len-input" name="defects[${rowIdx}][affected_length]" required onkeyup="calculate()" onchange="calculate()"></td>
        <td><input type="text" class="form-control border-0 bg-light" name="defects[${rowIdx}][remarks]" placeholder="Optional remarks"></td>
        <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm rounded-pill" onclick="this.closest('tr').remove(); calculate()"><i class="bi bi-trash"></i></button></td>
    `;
    tbody.appendChild(row);
    rowIdx++;
}

function calculate() {
    let totalAffected = 0;
    document.querySelectorAll('.len-input').forEach(input => {
        let val = parseFloat(input.value) || 0;
        totalAffected += val;
    });
    
    if (totalAffected > totalLength) {
        alert("Constraint Error: Total affected length cannot exceed the roll length (" + totalLength + "m).");
        totalAffected = totalLength; 
    }
    
    let percentage = (totalLength > 0) ? (totalAffected / totalLength) * 100 : 0;
    
    document.getElementById('totalAffected').innerText = totalAffected.toFixed(2);
    document.getElementById('defectPercentage').innerText = percentage.toFixed(2);
    
    let gradeObj = document.getElementById('previewGrade');
    if(percentage <= 1.00) { gradeObj.innerText = 'Grade A'; gradeObj.className = 'fw-bold mt-2 mb-0 text-success'; }
    else if(percentage <= 3.00) { gradeObj.innerText = 'Grade B'; gradeObj.className = 'fw-bold mt-2 mb-0 text-warning'; }
    else { gradeObj.innerText = 'Grade C (Reject)'; gradeObj.className = 'fw-bold mt-2 mb-0 text-danger'; }
}

addDefectRow(); // Init first row
</script>

<?php include '../includes/footer.php'; ?>