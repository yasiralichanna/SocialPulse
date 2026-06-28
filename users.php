<?php
/**
 * Team Management Page — User Roles & Permissions
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();
requireRole('admin');

$pageTitle = 'Team';
$db = getDB();

// Handle add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_user') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role  = $_POST['role'] ?? 'viewer';
        $pass  = password_hash('password123', PASSWORD_DEFAULT); // Default password

        if ($name && $email) {
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $pass, $role]);
            header('Location: users.php?msg=added');
            exit;
        }
    } elseif ($_POST['action'] === 'delete_user') {
        $id = (int)$_POST['user_id'];
        if ($id !== (int)$_SESSION['user_id']) { // Can't delete yourself
            $db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        }
        header('Location: users.php?msg=deleted');
        exit;
    } elseif ($_POST['action'] === 'update_role') {
        $id   = (int)$_POST['user_id'];
        $role = $_POST['role'];
        $db->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$role, $id]);
        header('Location: users.php?msg=updated');
        exit;
    }
}

$users = $db->query("SELECT * FROM users ORDER BY created_at ASC")->fetchAll();
include __DIR__ . '/includes/header.php';
?>

<section class="page active" id="page-users">
    <div class="page-header">
        <div><h1>Team Management</h1><p class="page-subtitle">Manage roles & permissions</p></div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="document.getElementById('addUserModal').classList.add('active')"><i class="fas fa-user-plus"></i> Add Member</button>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
    <div style="background:rgba(0,206,201,0.1);border:1px solid rgba(0,206,201,0.3);color:var(--success);padding:0.8rem 1rem;border-radius:var(--radius-sm);margin-bottom:1rem">
        <i class="fas fa-check-circle"></i> User <?= htmlspecialchars($_GET['msg']) ?> successfully.
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="roles-legend">
                <span class="role-badge admin">Admin — Full access</span>
                <span class="role-badge editor">Editor — Create & edit posts</span>
                <span class="role-badge viewer">Viewer — View analytics only</span>
            </div>
            <table class="data-table">
                <thead><tr><th>Member</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div class="user-avatar" style="width:32px;height:32px;font-size:0.65rem"><?= strtoupper(substr($u['name'], 0, 2)) ?></div>
                                <?= htmlspecialchars($u['name']) ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="update_role">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <select name="role" class="select-styled" style="width:auto;padding:4px 8px;font-size:0.8rem" onchange="this.form.submit()">
                                    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="editor" <?= $u['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
                                    <option value="viewer" <?= $u['role'] === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                                </select>
                            </form>
                        </td>
                        <td><span class="status-badge <?= $u['status'] === 'active' ? 'active' : 'inactive' ?>"><?= ucfirst($u['status']) ?></span></td>
                        <td>
                            <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this user?')">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal-overlay" id="addUserModal">
        <div class="modal">
            <div class="modal-header"><h3>Add Team Member</h3><button class="modal-close" onclick="document.getElementById('addUserModal').classList.remove('active')"><i class="fas fa-times"></i></button></div>
            <form method="POST">
                <input type="hidden" name="action" value="add_user">
                <div class="modal-body">
                    <div class="form-group"><label>Full Name</label><input type="text" name="name" class="input-styled" placeholder="John Doe" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="input-styled" placeholder="john@example.com" required></div>
                    <div class="form-group"><label>Role</label>
                        <select name="role" class="select-styled"><option value="viewer">Viewer</option><option value="editor">Editor</option><option value="admin">Admin</option></select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('addUserModal').classList.remove('active')">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Add Member</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
