<?php
require_once __DIR__ . '/includes/header.php';

if (is_admin()) {
    set_flash('Admin accounts cannot access customer pages.', 'error');
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

$errors = [];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    }
    if ($name === '') {
        $errors[] = 'Name is required';
    }
    if ($email === '') {
        $errors[] = 'Email is required';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email required';
    }
    if ($message === '') {
        $errors[] = 'Message is required';
    }

    if (empty($errors)) {
        set_flash('Thanks for reaching out. We will get back to you soon.', 'success');
        header('Location: ' . base_url('contact.php'));
        exit;
    }
}
?>
<div class="section-title"><h2>Contact</h2></div>
<div class="grid">
    <div class="card">
        <div class="brand">Concierge</div>
        <div class="name">Talk to a specialist</div>
        <p class="muted">Need a specific reference or want a sourcing update? Send a note and our team will reply within 1-2 business days.</p>
        <div style="display:flex; flex-wrap:wrap; gap:8px;">
            <span class="badge">concierge@luxetime.com</span>
            <span class="badge">+1 (555) 901-2233</span>
        </div>
        <div class="muted">NYC Flagship, LA Lounge, Paris Salon</div>
    </div>
    <div class="card">
        <?php foreach ($errors as $err): ?>
            <div class="alert error"><?php echo e($err); ?></div>
        <?php endforeach; ?>
        <form method="post" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <div class="input-field">
                <label>Name</label>
                <input type="text" name="name" value="<?php echo e($name); ?>" required>
            </div>
            <div class="input-field">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo e($email); ?>" required>
            </div>
            <div class="input-field" style="grid-column:1 / -1;">
                <label>Subject</label>
                <input type="text" name="subject" value="<?php echo e($subject); ?>" placeholder="Order, sourcing, or service question">
            </div>
            <div class="input-field" style="grid-column:1 / -1;">
                <label>Message</label>
                <textarea name="message" rows="5" required><?php echo e($message); ?></textarea>
            </div>
            <div style="grid-column:1 / -1; display:flex; justify-content:flex-end;">
                <button class="btn primary" type="submit">Send Message</button>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
