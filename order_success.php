<?php
require_once __DIR__ . '/includes/header.php';
require_login();
$pdo = get_pdo();
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = 'SELECT * FROM orders WHERE id = ?';
$params = [$orderId];
if (!is_admin()) {
    $sql .= ' AND user_id = ?';
    $params[] = current_user()['id'];
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$order = $stmt->fetch();

if (!$order) {
    echo '<div class="empty">Order not found.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$itemStmt = $pdo->prepare('SELECT oi.quantity, oi.unit_price, p.name, p.brand FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
$itemStmt->execute([$orderId]);
$items = $itemStmt->fetchAll();
?>
<div class="hero">
    <div>
        <p class="breadcrumb">Thank you</p>
        <h1>Order confirmed.</h1>
        <p class="muted">Your order #<?php echo (int)$order['id']; ?> is processing. A concierge will confirm shipping details shortly.</p>
        <div class="tagline">
            <span class="tag">Insured shipping</span>
            <span class="tag">Authenticity guarantee</span>
        </div>
        <div class="cta-group" style="margin-top:12px;">
            <a class="btn primary" href="<?php echo base_url('my_orders.php'); ?>">View my orders</a>
            <a class="btn ghost" href="<?php echo base_url('catalog.php'); ?>">Continue shopping</a>
        </div>
    </div>
    <div class="card">
        <div class="brand">Order Summary</div>
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
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
