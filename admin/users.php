<?php
$pageTitle = 'Users';
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrf($_POST[CSRF_TOKEN_NAME])) {
        setFlash('error', 'Invalid CSRF token.');
        redirect(ADMIN_URL . '/users.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $data = [];
        if (!empty($_POST['name'])) $data['name'] = $_POST['name'];
        if (!empty($_POST['email'])) $data['email'] = $_POST['email'];
        $result = Auth::updateProfile($data);
        if ($result['success']) {
            setFlash('success', 'Profile updated successfully.');
        } else {
            setFlash('error', $result['error']);
        }
        redirect(ADMIN_URL . '/users.php');
    }

    if ($action === 'update_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (strlen($new) < 8) {
            setFlash('error', 'New password must be at least 8 characters.');
            redirect(ADMIN_URL . '/users.php');
        }
        if ($new !== $confirm) {
            setFlash('error', 'New passwords do not match.');
            redirect(ADMIN_URL . '/users.php');
        }

        $result = Auth::updatePassword($current, $new);
        if ($result['success']) {
            setFlash('success', 'Password changed successfully.');
        } else {
            setFlash('error', $result['error']);
        }
        redirect(ADMIN_URL . '/users.php');
    }

    if ($action === 'add_user') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'editor';

        if ($name === '' || $email === '' || $password === '') {
            setFlash('error', 'Name, email, and password are required.');
            redirect(ADMIN_URL . '/users.php');
        }
        if (strlen($password) < 8) {
            setFlash('error', 'Password must be at least 8 characters.');
            redirect(ADMIN_URL . '/users.php');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('error', 'Invalid email address.');
            redirect(ADMIN_URL . '/users.php');
        }

        $result = Auth::create(['name' => $name, 'email' => $email, 'password' => $password, 'role' => $role]);
        if ($result['success']) {
            setFlash('success', 'User "' . sanitize($name) . '" created successfully.');
        } else {
            setFlash('error', $result['error']);
        }
        redirect(ADMIN_URL . '/users.php');
    }

    if ($action === 'delete_user') {
        $id = (int)($_POST['user_id'] ?? 0);
        $result = Auth::delete($id);
        if ($result['success']) {
            setFlash('success', 'User deleted successfully.');
        } else {
            setFlash('error', $result['error']);
        }
        redirect(ADMIN_URL . '/users.php');
    }

    setFlash('error', 'Unknown action.');
    redirect(ADMIN_URL . '/users.php');
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$currentUser = Auth::getCurrentUser();
$admins = Auth::getAll();
?>

<div class="admin-content">
    <div class="admin-header">
        <div>
            <h1>Users</h1>
            <p style="color:var(--text-secondary); font-size:14px; margin-top:4px;">Manage admin accounts</p>
        </div>
    </div>

    <div class="admin-body">
        <ul class="nav nav-tabs" id="userTabs" style="border-bottom:1px solid var(--border-color); margin-bottom:24px; display:flex; gap:0; list-style:none; padding:0;">
            <li>
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-profile" style="display:inline-block; padding:12px 20px; font-size:14px; font-weight:500; color:#777; border-bottom:2px solid transparent; cursor:pointer; transition:all 0.2s ease;">
                    <i class="bi bi-person-fill"></i> My Profile
                </a>
            </li>
            <li>
                <a class="nav-link" data-bs-toggle="tab" href="#tab-password" style="display:inline-block; padding:12px 20px; font-size:14px; font-weight:500; color:#777; border-bottom:2px solid transparent; cursor:pointer; transition:all 0.2s ease;">
                    <i class="bi bi-lock-fill"></i> Change Password
                </a>
            </li>
            <li>
                <a class="nav-link" data-bs-toggle="tab" href="#tab-add" style="display:inline-block; padding:12px 20px; font-size:14px; font-weight:500; color:#777; border-bottom:2px solid transparent; cursor:pointer; transition:all 0.2s ease;">
                    <i class="bi bi-person-plus-fill"></i> Add User
                </a>
            </li>
            <li>
                <a class="nav-link" data-bs-toggle="tab" href="#tab-all" style="display:inline-block; padding:12px 20px; font-size:14px; font-weight:500; color:#777; border-bottom:2px solid transparent; cursor:pointer; transition:all 0.2s ease;">
                    <i class="bi bi-people-fill"></i> All Users
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- TAB: My Profile -->
            <div class="tab-pane fade show active" id="tab-profile">
                <div class="content-card">
                    <div class="content-card-header">
                        <h3>My Profile</h3>
                    </div>
                    <div class="content-card-body">
                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="update_profile">

                            <div class="form-group">
                                <label class="form-label">Name <span class="required">*</span></label>
                                <input type="text" name="name" class="form-control" value="<?= sanitize($currentUser['name'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email <span class="required">*</span></label>
                                <input type="email" name="email" class="form-control" value="<?= sanitize($currentUser['email'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?= sanitize(ucfirst($currentUser['role'] ?? 'admin')) ?>" disabled style="opacity:0.6;">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Member Since</label>
                                <input type="text" class="form-control" value="<?= sanitize(date('F j, Y', strtotime($currentUser['created_at'] ?? ''))) ?>" disabled style="opacity:0.6;">
                            </div>

                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="bi bi-save-fill"></i> Save Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB: Change Password -->
            <div class="tab-pane fade" id="tab-password">
                <div class="content-card">
                    <div class="content-card-header">
                        <h3>Change Password</h3>
                    </div>
                    <div class="content-card-body">
                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="update_password">

                            <div class="form-group">
                                <label class="form-label">Current Password <span class="required">*</span></label>
                                <input type="password" name="current_password" class="form-control" required autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label class="form-label">New Password <span class="required">*</span></label>
                                <input type="password" name="new_password" class="form-control" required minlength="8" autocomplete="off">
                                <p class="form-hint">Minimum 8 characters</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Confirm New Password <span class="required">*</span></label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="8" autocomplete="off">
                            </div>

                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="bi bi-lock-fill"></i> Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB: Add User -->
            <div class="tab-pane fade" id="tab-add">
                <div class="content-card">
                    <div class="content-card-header">
                        <h3>Add New Admin User</h3>
                    </div>
                    <div class="content-card-body">
                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="add_user">

                            <div class="form-group">
                                <label class="form-label">Name <span class="required">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email <span class="required">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Password <span class="required">*</span></label>
                                <input type="password" name="password" class="form-control" required minlength="8" autocomplete="off">
                                <p class="form-hint">Minimum 8 characters</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select" style="width:auto; min-width:200px;">
                                    <option value="editor">Editor</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <p class="form-hint">Admins have full access. Editors can manage content only.</p>
                            </div>

                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="bi bi-person-plus-fill"></i> Add User
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB: All Users -->
            <div class="tab-pane fade" id="tab-all">
                <div class="content-card">
                    <div class="content-card-header">
                        <h3>All Admin Users</h3>
                        <span style="font-size:13px; color:var(--text-secondary);"><?= count($admins) ?> user<?= count($admins) !== 1 ? 's' : '' ?></span>
                    </div>
                    <div class="content-card-body" style="padding:0;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Last Login</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($admins)): ?>
                                <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <div style="width:36px; height:36px; border-radius:50%; background:var(--primary); color:#000; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; flex-shrink:0;">
                                                <?= strtoupper(substr($admin['name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div style="font-weight:500; font-size:14px;"><?= sanitize($admin['name']) ?></div>
                                                <?php if ((int)$admin['id'] === (int)$_SESSION['admin_id']): ?>
                                                <span style="font-size:11px; color:var(--primary);">(you)</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="color:var(--text-secondary);"><?= sanitize($admin['email']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $admin['role'] === 'admin' ? 'primary' : 'secondary' ?>"><?= ucfirst($admin['role']) ?></span>
                                    </td>
                                    <td style="color:var(--text-secondary); font-size:13px;"><?= $admin['last_login'] ? timeAgo($admin['last_login']) : '<span style="color:var(--text-muted);">Never</span>' ?></td>
                                    <td style="color:var(--text-secondary); font-size:13px;"><?= date('M j, Y', strtotime($admin['created_at'])) ?></td>
                                    <td>
                                        <?php if ((int)$admin['id'] !== (int)$_SESSION['admin_id']): ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete <?= sanitize(addslashes($admin['name'])) ?>?');">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $admin['id'] ?>">
                                            <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" title="Delete user">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <span style="color:var(--text-muted); font-size:12px;">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--text-secondary);">No users found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-footer">
        &copy; <?= date('Y') ?> Portfolio Admin Panel
    </div>
</div>

<style>
.nav-tabs .nav-link.active {
    color: var(--primary) !important;
    border-bottom-color: var(--primary) !important;
    background: transparent;
}
.nav-tabs .nav-link:hover:not(.active) {
    color: #ccc !important;
    background: rgba(255,255,255,0.03);
}
.nav-tabs {
    border: none !important;
}
.tab-content .tab-pane {
    display: none;
}
.tab-content .tab-pane.active.show {
    display: block;
    animation: fadeIn 0.3s ease;
}
.form-select {
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    padding: 10px 14px;
    border-radius: var(--radius-md);
    font-size: 14px;
    outline: none;
    transition: var(--transition);
}
.form-select:focus {
    border-color: var(--primary);
}
.admin-btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}
.badge-secondary {
    background: rgba(255,255,255,0.08);
    color: #aaa;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tabs = document.querySelectorAll('#userTabs .nav-link');
    var tabPanes = document.querySelectorAll('.tab-content .tab-pane');

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();

            tabs.forEach(function(t) { t.classList.remove('active'); });
            tabPanes.forEach(function(p) {
                p.classList.remove('active', 'show');
            });

            this.classList.add('active');
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.classList.add('active', 'show');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
