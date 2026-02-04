<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
$pdo = get_pdo();

$errors = [];
$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = $_POST['role'] ?? 'customer';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid request.', 'error');
        header('Location: ' . base_url('admin/users.php'));
        exit;
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $password = $_POST['password'] ?? '';
        if ($fullName === '' || $email === '' || $password === '') {
            $errors[] = 'Name, email, and password are required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email is required.';
        }
        if (!in_array($role, ['customer', 'admin'], true)) {
            $errors[] = 'Invalid role selection.';
        }

        if (empty($errors)) {
            $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $check->execute([$email]);
            if ($check->fetch()) {
                $errors[] = 'Email already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$fullName, $email, $hash, $role]);
                set_flash('User created.', 'success');
                header('Location: ' . base_url('admin/users.php'));
                exit;
            }
        }
    } elseif ($action === 'delete') {
        $userId = (int)($_POST['id'] ?? 0);
        if ($userId === (int)(current_user()['id'] ?? 0)) {
            set_flash('You cannot delete your own account.', 'error');
        } else {
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            set_flash('User removed.', 'success');
        }
        header('Location: ' . base_url('admin/users.php'));
        exit;
    }
}

$users = $pdo->query('SELECT id, full_name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
?>
<div class="section-title">
    <h2>User Management</h2>
    <a class="btn small ghost" href="<?php echo base_url('admin/dashboard.php'); ?>">Back to Dashboard</a>
</div>
<div class="grid">
    <div class="card">
        <h3 style="margin:0 0 10px;">Add User</h3>
        <?php foreach ($errors as $err): ?>
            <div class="alert error"><?php echo e($err); ?></div>
        <?php endforeach; ?>
        <form method="post" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="action" value="create">
            <div class="input-field">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo e($fullName); ?>" required>
            </div>
            <div class="input-field">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo e($email); ?>" required>
            </div>
            <div class="input-field">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="input-field">
                <label>Role</label>
                <select name="role" required>
                    <option value="customer" <?php echo $role === 'customer' ? 'selected' : ''; ?>>Customer</option>
                    <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <div style="grid-column:1 / -1; display:flex; justify-content:flex-end;">
                <button class="btn primary" type="submit">Create User</button>
            </div>
        </form>
    </div>
    <div class="card">
        <h3 style="margin:0 0 10px;">Users</h3>
        <?php if (!$users): ?>
            <div class="empty">No users found.</div>
        <?php else: ?>
            <div class="table-frame">
                <table class="table table-users">
                    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?php echo (int)$user['id']; ?></td>
                                <td><?php echo e($user['full_name']); ?></td>
                                <td><?php echo e($user['email']); ?></td>
                                <td><?php echo e($user['role']); ?></td>
                                <td><?php echo e($user['created_at']); ?></td>
                                <td>
                                    <?php if ((int)$user['id'] === (int)(current_user()['id'] ?? 0)): ?>
                                        <span class="muted">Current</span>
                                    <?php else: ?>
                                        <form method="post" onsubmit="return confirm('Remove this user?');" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
                                            <button class="btn small ghost" type="submit">Remove</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
