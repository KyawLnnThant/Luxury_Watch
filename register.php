<?php
require_once __DIR__ . '/includes/header.php';
$pdo = get_pdo();
$errors = [];
$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please refresh.';
    }
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($fullName === '' || $email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$fullName, $email, $hash, 'customer']);
            $userId = (int)$pdo->lastInsertId();
            $_SESSION['user'] = ['id' => $userId, 'full_name' => $fullName, 'email' => $email, 'role' => 'customer'];
            session_regenerate_id(true);
            set_flash('Welcome to LuxeTime.', 'success');
            header('Location: ' . base_url('catalog.php'));
            exit;
        }
    }
}
?>
<div class="section-title"><h2>Create Account</h2></div>
<div class="card" style="max-width:520px; margin:0 auto;">
    <?php foreach ($errors as $err): ?>
        <div class="alert error"><?php echo e($err); ?></div>
    <?php endforeach; ?>
    <form method="post" class="form-grid" style="grid-template-columns:1fr;">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
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
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
        </div>
        <button class="btn primary" type="submit">Register</button>
    </form>
    <p class="muted" style="margin-top:10px;">Already have an account? <a href="<?php echo base_url('login.php'); ?>">Login</a></p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
