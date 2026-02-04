<?php
require_once __DIR__ . '/includes/header.php';
$pdo = get_pdo();

if (is_admin()) {
    set_flash('Admin accounts cannot request appointments.', 'error');
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

$errors = [];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$location = trim($_POST['location'] ?? '');
$datetime = trim($_POST['preferred_datetime'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    }
    foreach (['name' => $name, 'email' => $email, 'location' => $location, 'preferred date' => $datetime] as $field => $value) {
        if ($value === '') {
            $errors[] = ucfirst($field) . ' is required';
        }
    }
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email required';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO appointments (name, email, phone, location, preferred_datetime, message) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $email, $phone, $location, $datetime, $message]);
        set_flash('Appointment request submitted.', 'success');
        header('Location: ' . base_url('appointment.php'));
        exit;
    }
}
?>
<div class="section-title"><h2>Private Appointment</h2></div>
<div class="grid">
    <div class="card">
        <div class="brand">Flagship Lounges</div>
        <div class="name">New York</div>
        <p class="muted">5th Avenue lounge with vault viewing room. Espresso bar and strap library.</p>
        <div class="name">Los Angeles</div>
        <p class="muted">Rodeo Drive penthouse suite with skyline terrace.</p>
        <div class="name">Paris</div>
        <p class="muted">Rue Saint-Honoré salon in partnership with Atelier Marcel.</p>
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
            <div class="input-field">
                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo e($phone); ?>">
            </div>
            <div class="input-field">
                <label>Location</label>
                <select name="location" required>
                    <option value="">Select</option>
                    <?php foreach (['NYC Flagship', 'LA Lounge', 'Paris Salon'] as $loc): ?>
                        <option value="<?php echo e($loc); ?>" <?php echo $loc === $location ? 'selected' : ''; ?>><?php echo e($loc); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-field">
                <label>Preferred Date & Time</label>
                <input type="datetime-local" name="preferred_datetime" value="<?php echo e($datetime); ?>" required>
            </div>
            <div class="input-field" style="grid-column:1 / -1;">
                <label>Notes</label>
                <textarea name="message" placeholder="Pieces you want to see, wrist size, etc."><?php echo e($message); ?></textarea>
            </div>
            <button class="btn primary" type="submit">Request Appointment</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
