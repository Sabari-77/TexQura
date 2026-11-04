<?php
session_start();
require_once '../config/database.php';

// Strict Constraint: Only Administrators (Role 1) can view audit logs
if (!isset($_SESSION['role_id']) \vert{}\vert{}$_SESSION['role_id'] != 1) {
    die("<div style='padding:20px; color:red;'>Access Denied. Administrators only.</div>");
}

// Safety check: Create the audit_logs table dynamically just in case it was missed in the main schema
$pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;");

// Fetch the latest 100 logs
$logs =$pdo->query("SELECT a.*, u.name as user_name, u.email FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 100")->fetchAll();

include '../includes/header.php';
?>
<script>document.title = "Audit Logs - TexQura";</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">System Audit Logs</h3>
        <p class="text-muted small mb-0">Monitor user activity and system events for compliance.</p>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden rounded-4">
    <div class="card-body p-0">
        <div class="p-3 bg-light border-bottom">
            <input type="text" id="logSearch" class="form-control form-control-lg border-0 shadow-sm" placeholder="🔍 Search logs by user, action, or date..." onkeyup="filterLogs()">
        </div>
        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0" id="logTable">
                <thead class="bg-white" style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        <th class="ps-4 border-0">Timestamp</th>
                        <th class="border-0">User Account</th>
                        <th class="border-0">Action Performed</th>
                        <th class="border-0">System Details</th>
                        <th class="border-0 text-center">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($logs) === 0): ?>
                        <tr><td colspan="5" class="text-center p-5 text-muted"><i class="bi bi-shield-check fs-1 d-block mb-3 opacity-50"></i> No audit logs recorded yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($logs as$log): ?>
                    <tr>
                        <td class="ps-4 text-muted small"><?= date('d M Y, H:i:s', strtotime($log['created_at'])) ?></td>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($log['user_name'] ?? 'System Process') ?></div>
                            <small class="text-muted"><?= htmlspecialchars($log['email'] ?? 'N/A') ?></small>
                        </td>
                        <td><span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill"><?= htmlspecialchars($log['action']) ?></span></td>
                        <td class="text-muted small fst-italic"><?= htmlspecialchars($log['details'])