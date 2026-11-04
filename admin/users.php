<?php
session_start();
require_once '../config/database.php';

// Strict Security: Only Administrators (Role 1) can access this page
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    die("<div style='padding:20px; color:red; font-family:sans-serif;'>Access Denied. Administrator privileges required.</div>");
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = intval($_POST['role_id']);
    
    // Hash the password securely before saving it to the database
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, role_id, password_hash) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $role, $password]);
        $msg = "<div class='alert alert-success shadow-sm rounded-3'><i class='bi bi-check-circle-fill me-2'></i> New user account created successfully!</div>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $msg = "<div class='alert alert-danger shadow-sm rounded-3'><i class='bi bi-exclamation-triangle-fill me-2'></i> Error: A user with this email address already exists.</div>";
        } else {
            $msg = "<div class='alert alert-danger shadow-sm rounded-3'>Database Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Fetch all users and their roles
$users = $pdo->query("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.id DESC")->fetchAll();
// Fetch available roles for the dropdown
$roles = $pdo->query("SELECT * FROM roles")->fetchAll();

include '../includes/header.php';
?>
<script>document.title = "Manage Users - TexQura";</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">System Users</h3>
        <p class="text-muted small mb-0">Add new employees and manage role-based access.</p>
    </div>
    <button class="btn btn-dark rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#userModal">
        <i class="bi bi-person-plus-fill me-1"></i> Add New User
    </button>
</div>

<?= $msg ?>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 border-0">Full Name</th>
                        <th class="border-0">Email Address</th>
                        <th class="border-0">Assigned Role</th>
                        <th class="border-0">Account Status</th>
                        <th class="border-0">Joined Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): 
                        $role_color = match($u['role_name']) {
                            'Administrator' => 'danger',
                            'Quality Manager' => 'primary',
                            'Quality Inspector' => 'success',
                            default => 'secondary'
                        };
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold text-dark">
                            <i class="bi bi-person-circle text-muted me-2 fs-5 align-middle"></i>
                            <?= htmlspecialchars($u['name']) ?>
                        </td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="badge bg-<?= $role_color ?> bg-opacity-10 text-<?= $role_color ?> px-3 py-2 rounded-pill"><?= htmlspecialchars($u['role_name']) ?></span></td>
                        <td>
                            <?php if ($u['status'] == 'active'): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><i class="bi bi-dash-circle me-1"></i> Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Create New User Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Full Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Jane Smith">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Email Address (Login ID) *</label>
                        <input type="email" name="email" class="form-control" required placeholder="jane.smith@texqura.com">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Assign Role *</label>
                        <select name="role_id" class="form-select" required>
                            <option value="">-- Select a Role --</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small">Roles define what modules the user can access.</div>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label text-muted small fw-bold">Temporary Password *</label>
                        <input type="password" name="password" class="form-control" required minlength="6" placeholder="Enter at least 6 characters">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark px-4">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>