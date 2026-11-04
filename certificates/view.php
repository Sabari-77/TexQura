<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

$cert_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT c.certificate_number, c.generated_at, i.defect_percentage, i.grade, i.total_affected_length, r.roll_number, r.roll_length, b.batch_number, s.name as supplier, f.name as fabric, u.name as inspector FROM certificates c JOIN inspections i ON c.inspection_id = i.id JOIN fabric_rolls r ON i.roll_id = r.id JOIN batches b ON r.batch_id = b.id JOIN suppliers s ON b.supplier_id = s.id JOIN fabrics f ON b.fabric_id = f.id JOIN users u ON i.inspector_id = u.id WHERE i.id = ? OR c.id = ?");
$stmt->execute([$cert_id, $cert_id]);
$cert = $stmt->fetch();

if (!$cert) die("<div style='padding:40px; font-family:sans-serif; text-align:center;'><h2>Certificate not found or Inspection not yet approved.</h2><a href='../dashboard.php'>Return Home</a></div>");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quality Certificate - <?= htmlspecialchars($cert['certificate_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; padding-top: 40px; font-family: 'Segoe UI', serif; }
        .certificate-wrapper { 
            max-width: 850px; margin: 0 auto; background: #fff; padding: 60px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1); 
            border: 15px solid #10b981; 
            outline: 2px solid #0f172a; outline-offset: -20px;
            position: relative; overflow: hidden;
        }
        .watermark {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 8rem; color: rgba(16, 185, 129, 0.05); font-weight: 900; white-space: nowrap; z-index: 0; pointer-events: none;
        }
        .cert-content { position: relative; z-index: 1; }
        .cert-header { border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; margin-bottom: 30px; }
        @media print { 
            body { background: #fff; padding: 0; } 
            .no-print { display: none !important; } 
            .certificate-wrapper { box-shadow: none; border: 10px solid #10b981; outline-offset: -15px; margin: 0; width: 100%; max-width: 100%; } 
        }
    </style>
</head>
<body>
    <div class="text-center mb-4 no-print">
        <button class="btn btn-success fw-bold px-4 me-2 shadow-sm rounded-pill" onclick="window.print()">Print Certificate</button>
        <button class="btn btn-dark fw-bold px-4 shadow-sm rounded-pill" onclick="window.close()">Close Window</button>
    </div>
    <div class="certificate-wrapper">
        <div class="watermark">PASSED</div>
        <div class="cert-content">
            <div class="cert-header text-center">
                <h1 style="color:#0f172a; font-weight:900; letter-spacing: 2px;">TEXQURA.</h1>
                <h4 class="text-muted mt-2 text-uppercase fw-bold" style="letter-spacing: 4px;">Certificate of Quality Assurance</h4>
                <p class="mt-4 mb-0 badge bg-light text-dark border fs-6"><b>Certificate No:</b> <?= htmlspecialchars($cert['certificate_number']) ?></p>
            </div>
            
            <div class="row mb-5 mt-4 g-4 text-dark">
                <div class="col-6">
                    <p class="mb-2 text-uppercase small fw-bold text-muted border-bottom pb-1">Origin Details</p>
                    <p class="mb-1"><b>Supplier:</b> <?= htmlspecialchars($cert['supplier']) ?></p>
                    <p class="mb-1"><b>Fabric Type:</b> <?= htmlspecialchars($cert['fabric']) ?></p>
                    <p class="mb-1"><b>Batch Reference:</b> <?= htmlspecialchars($cert['batch_number']) ?></p>
                </div>
                <div class="col-6 text-end">
                    <p class="mb-2 text-uppercase small fw-bold text-muted border-bottom pb-1">Product Details</p>
                    <p class="mb-1"><b>Roll ID:</b> <?= htmlspecialchars($cert['roll_number']) ?></p>
                    <p class="mb-1"><b>Total Length:</b> <?= htmlspecialchars($cert['roll_length']) ?> meters</p>
                    <p class="mb-1"><b>Issue Date:</b> <?= date('F d, Y', strtotime($cert['generated_at'])) ?></p>
                </div>
            </div>

            <div class="p-4 text-center mb-5 border border-2 border-success rounded-4" style="background: rgba(16, 185, 129, 0.05);">
                <h6 class="text-uppercase text-muted fw-bold mb-2">Final Quality Result</h6>
                <h1 class="text-success fw-bold mb-2"><?= htmlspecialchars($cert['grade']) ?> (Approved)</h1>
                <p class="mb-0 fs-5">Certified Defect Percentage: <b><?= htmlspecialchars($cert['defect_percentage']) ?>%</b></p>
            </div>

            <div class="row text-center mt-5 pt-5">
                <div class="col-6">
                    <div class="border-top border-dark pt-2 mx-4">
                        <b class="fs-5 d-block"><?= htmlspecialchars($cert['inspector']) ?></b>
                        <small class="text-muted text-uppercase fw-bold">Inspecting Officer</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border-top border-dark pt-2 mx-4">
                        <b class="fs-5 d-block">TexQura System</b>
                        <small class="text-muted text-uppercase fw-bold">Digital Authorization</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>