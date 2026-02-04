<?php
require_once __DIR__ . '/includes/header.php';
$pdo = get_pdo();
$errors = [];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$brand = trim($_POST['brand'] ?? '');
$model = trim($_POST['model'] ?? '');
$condition = trim($_POST['condition'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    }
    foreach ([
        'name' => $name,
        'email' => $email,
        'brand' => $brand,
        'model' => $model,
        'condition' => $condition,
    ] as $field => $value) {
        if ($value === '') {
            $errors[] = ucfirst($field) . ' is required';
        }
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email required';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO trade_requests (name, email, brand, model, watch_condition, message) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $email, $brand, $model, $condition, $message]);
        set_flash('Request received. A specialist will respond shortly.', 'success');
        header('Location: ' . base_url('services.php'));
        exit;
    }
}
?>
<div class="section-title"><h2>Trade / Sell / Consign</h2></div>
<div class="grid">
    <div class="card">
        <div class="brand">Trade In</div>
        <div class="name">Upgrade your collection</div>
        <p class="muted">Receive competitive values toward your next acquisition. We provide same-day estimates with transparent comps.</p>
    </div>
    <div class="card">
        <div class="brand">Sell Outright</div>
        <div class="name">Fast, insured payouts</div>
        <p class="muted">Wire within 24 hours after inspection. White-glove shipping labels and in-person drop-offs at any lounge.</p>
    </div>
    <div class="card">
        <div class="brand">Consign</div>
        <div class="name">Showcase to qualified buyers</div>
        <p class="muted">We authenticate, photograph, and market your piece to our global network with agreed reserves.</p>
    </div>
</div>

<div class="section-title"><h2>Submit a request</h2></div>
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
            <label>Brand</label>
            <input type="text" name="brand" value="<?php echo e($brand); ?>" required>
        </div>
        <div class="input-field">
            <label>Model</label>
            <input type="text" name="model" value="<?php echo e($model); ?>" required>
        </div>
        <div class="input-field">
            <label>Condition</label>
            <select name="condition" required>
                <option value="">Select</option>
                <?php foreach (['Mint', 'Excellent', 'Good', 'Fair'] as $opt): ?>
                    <option value="<?php echo e($opt); ?>" <?php echo $opt === $condition ? 'selected' : ''; ?>><?php echo e($opt); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="input-field" style="grid-column:1 / -1;">
            <label>Notes</label>
            <textarea name="message" placeholder="Box & papers, year, service history..."><?php echo e($message); ?></textarea>
        </div>
        <button class="btn primary" type="submit">Send request</button>
    </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
