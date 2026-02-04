<?php
require_once __DIR__ . '/includes/header.php';
require_login();
$pdo = get_pdo();
$orderStmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$orderStmt->execute([current_user()['id']]);
$orders = $orderStmt->fetchAll();
$itemStmt = $pdo->prepare('SELECT oi.quantity, oi.unit_price, p.name, p.brand FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
?>
<div class="section-title"><h2>My Orders</h2></div>
<?php if (!$orders): ?>
    <div class="empty">No orders yet. Discover a new grail.</div>
<?php else: ?>
    <div class="grid">
    <?php foreach ($orders as $order): ?>
        <?php $itemStmt->execute([$order['id']]); $items = $itemStmt->fetchAll(); ?>
        <div class="card">
            <div class="brand">Order #<?php echo (int)$order['id']; ?> — <?php echo e($order['status']); ?></div>
            <div class="muted">Placed <?php echo e($order['created_at']); ?></div>
            <?php foreach ($items as $item): ?>
                <div style="display:flex; justify-content:space-between; margin:6px 0;">
                    <div>
                        <div class="name"><?php echo e($item['brand']); ?> — <?php echo e($item['name']); ?></div>
                        <div class="muted">Qty: <?php echo (int)$item['quantity']; ?></div>
                    </div>
                    <div class="price"><?php echo format_price((float)$item['unit_price'] * (int)$item['quantity']); ?></div>
                </div>
            <?php endforeach; ?>
            <div style="border-top:1px solid var(--line); padding-top:8px; display:flex; justify-content:space-between;">
                <span>Total</span>
                <strong class="price"><?php echo format_price((float)$order['total']); ?></strong>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
