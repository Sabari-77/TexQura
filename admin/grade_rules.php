<?php
session_start();
require_once '../config/database.php';

// Strict Constraint: Only Administrators (Role 1) can modify business rules
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    die("<div style='padding:20px; color:red;'>Access Denied. Administrators only.</div>");
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gradeA = floatval($_POST['grade_a']);
    $gradeB = floatval($_POST['grade_b']);
    
    // Strict Business Logic: Grade B limit MUST be greater than Grade A limit
    if ($gradeB <= $gradeA) {
        $msg = "<div class='alert alert-danger shadow-sm rounded-3'><i class='bi bi-exclamation-triangle-fill me-2'></i> Constraint Error: Grade B maximum percentage must be strictly greater than Grade A!</div>";
    } else {
        $pdo->prepare("UPDATE grade_rules SET max_percentage = ? WHERE grade = 'Grade A'")->execute([$gradeA]);
        $pdo->prepare("UPDATE grade_rules SET max_percentage = ? WHERE grade = 'Grade B'")->execute([$gradeB]);
        $msg = "<div class='alert alert-success shadow-sm rounded-3'><i class='bi bi-check-circle-fill me-2'></i> System grading rules updated! New inspections will use these limits.</div>";
    }
}

// Fetch current active rules
$rules = $pdo->query("SELECT * FROM grade_rules ORDER BY max_percentage ASC")->fetchAll();
$currentA = $rules[0]['max_percentage'] ?? 1.00;
$currentB = $rules[1]['max_percentage'] ?? 3.00;

include '../includes/header.php';
?>
<script>document.title = "Configure Grade Rules - TexQura";</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">Quality Grading Constraints</h3>
        <p class="text-muted small mb-0">Set the automated system thresholds for fabric quality classification.</p>
    </div>
</div>

<?= $msg ?>

<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark mb-1">Grade A Limit (%)</label>
                        <p class="text-muted small mb-2">Rolls with a defect percentage less than or equal to this value will be classified as Grade A.</p>
                        <div class="input-group shadow-sm rounded-3 overflow-hidden">
                            <input type="number" step="0.01" min="0.01" name="grade_a" class="form-control border-0 bg-light" value="<?= $currentA ?>" required>
                            <span class="input-group-text border-0 bg-success text-white fw-bold">%</span>
                        </div>
                    </div>
                    
                    <div class="mb-5">
                        <label class="form-label fw-bold text-dark mb-1">Grade B Limit (%)</label>
                        <p class="text-muted small mb-2">Rolls exceeding Grade A but below this limit will be Grade B. Anything exceeding this limit automatically becomes Grade C (Rejected).</p>
                        <div class="input-group shadow-sm rounded-3 overflow-hidden">
                            <input type="number" step="0.01" min="0.01" name="grade_b" class="form-control border-0 bg-light" value="<?= $currentB ?>" required>
                            <span class="input-group-text border-0 bg-warning text-dark fw-bold">%</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-dark w-100 rounded-pill py-2 fw-bold shadow-sm">Save Business Rules</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>