<?php
require_once __DIR__ . '/includes/header.php';
$pdo = get_pdo();
$errors = [];
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    }
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
            session_regenerate_id(true);
            set_flash('Welcome back, ' . $user['full_name'], 'success');
            header('Location: ' . base_url('catalog.php'));
            exit;
        } else {
            $errors[] = 'Invalid credentials.';
        }
    }
}
?>
<div class="section-title"><h2>Sign In</h2></div>
<div class="card" style="max-width:480px; margin:0 auto;">
    <?php foreach ($errors as $err): ?>
        <div class="alert error"><?php echo e($err); ?></div>
    <?php endforeach; ?>
    <form method="post" class="form-grid" style="grid-template-columns:1fr;">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        <div class="input-field">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo e($email); ?>" required>
        </div>
        <div class="input-field">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button class="btn primary" type="submit">Login</button>
    </form>
    <p class="muted" style="margin-top:10px;">No account yet? <a href="<?php echo base_url('register.php'); ?>">Register</a></p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
