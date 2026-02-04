<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
$pdo = get_pdo();
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?');
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    echo '<div class="empty">Order not found.</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid request.', 'error');
    } else {
        $status = trim($_POST['status'] ?? '');
        $allowed = ['processing','paid','shipped','completed','cancelled'];
        if (in_array($status, $allowed, true)) {
            $update = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $update->execute([$status, $orderId]);
            set_flash('Status updated.', 'success');
            header('Location: ' . base_url('admin/order_view.php?id=' . $orderId));
            exit;
        }
    }
}

$itemStmt = $pdo->prepare('SELECT oi.*, p.name, p.brand FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
$itemStmt->execute([$orderId]);
$items = $itemStmt->fetchAll();
?>
<div class="section-title"><h2>Order #<?php echo (int)$order['id']; ?></h2></div>
<div class="grid">
    <div class="card">
        <div class="brand">Customer</div>
        <div class="name"><?php echo e($order['full_name']); ?></div>
        <p class="muted"><?php echo e($order['email']); ?></p>
        <div class="brand" style="margin-top:10px;">Shipping</div>
        <p class="muted" style="white-space:pre-line;"><?php echo e($order['shipping_address']); ?></p>
        <div class="brand" style="margin-top:10px;">Status</div>
        <form method="post" style="display:flex; gap:10px; align-items:center;">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <select name="status" style="padding:10px; border-radius:10px; border:1px solid var(--line); background:#0b0c10; color:var(--text);">
                <?php foreach (['processing','paid','shipped','completed','cancelled'] as $opt): ?>
                    <option value="<?php echo e($opt); ?>" <?php echo $opt === $order['status'] ? 'selected' : ''; ?>><?php echo ucfirst($opt); ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn small primary" type="submit">Update</button>
        </form>
    </div>
    <div class="card">
        <div class="brand">Items</div>
        <?php foreach ($items as $item): ?>
            <div style="display:flex; justify-content:space-between; margin:6px 0;">
                <div>
                    <div class="name"><?php echo e($item['brand']); ?> — <?php echo e($item['name']); ?></div>
                    <div class="muted">Qty: <?php echo (int)$item['quantity']; ?></div>
                </div>
                <div class="price"><?php echo format_price((float)$item['unit_price'] * (int)$item['quantity']); ?></div>
            </div>
        <?php endforeach; ?>
        <div style="border-top:1px solid var(--line); padding-top:10px; display:flex; justify-content:space-between;">
            <span>Total</span>
            <strong class="price"><?php echo format_price((float)$order['total']); ?></strong>
        </div>
        <div class="muted" style="margin-top:6px;">Placed: <?php echo e($order['created_at']); ?></div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
