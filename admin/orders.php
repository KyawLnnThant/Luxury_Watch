<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
$pdo = get_pdo();
$orders = $pdo->query('SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC')->fetchAll();
?>
<div class="section-title">
    <h2>Orders</h2>
    <a class="btn small ghost" href="<?php echo base_url('admin/dashboard.php'); ?>">Back to Dashboard</a>
</div>
<div class="card">
    <?php if (!$orders): ?>
        <div class="empty">No orders yet.</div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo (int)$order['id']; ?></td>
                        <td><?php echo e($order['full_name']); ?></td>
                        <td><?php echo format_price((float)$order['total']); ?></td>
                        <td><?php echo e($order['status']); ?></td>
                        <td><?php echo e($order['created_at']); ?></td>
                        <td><a class="btn small" href="<?php echo base_url('admin/order_view.php?id=' . $order['id']); ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
