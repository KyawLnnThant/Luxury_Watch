<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
$pdo = get_pdo();
$productsCount = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$ordersCount = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$usersCount = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$revenue = (float)$pdo->query('SELECT COALESCE(SUM(total),0) FROM orders')->fetchColumn();
$recentOrders = $pdo->query('SELECT o.id, o.total, o.created_at, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5')->fetchAll();
?>
<div class="section-title"><h2>Admin Dashboard</h2></div>
<div class="grid">
    <div class="card">
        <div class="brand">Products</div>
        <div class="name"><?php echo $productsCount; ?></div>
        <p class="muted">Active SKUs</p>
    </div>
    <div class="card">
        <div class="brand">Orders</div>
        <div class="name"><?php echo $ordersCount; ?></div>
        <p class="muted">Total placed</p>
    </div>
    <div class="card">
        <div class="brand">Users</div>
        <div class="name"><?php echo $usersCount; ?></div>
        <p class="muted">Active accounts</p>
    </div>
    <div class="card">
        <div class="brand">Revenue</div>
        <div class="name"><?php echo format_price($revenue); ?></div>
        <p class="muted">Gross to date</p>
    </div>
</div>

<div class="section-title"><h2>Management</h2></div>
<div class="grid">
    <div class="card card-compact">
        <div class="brand">User Management</div>
        <div class="name">Create and remove users</div>
        <p class="muted">Add customer or admin accounts and manage access.</p>
        <a class="btn small" href="<?php echo base_url('admin/users.php'); ?>">Manage Users</a>
    </div>
    <div class="card card-compact">
        <div class="brand">Product Management</div>
        <div class="name">Create, edit, remove</div>
        <p class="muted">Manage catalog listings and featured products.</p>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a class="btn small" href="<?php echo base_url('admin/product_edit.php'); ?>">Add Product</a>
            <a class="btn small ghost" href="<?php echo base_url('admin/products.php'); ?>">View Products</a>
        </div>
    </div>
    <div class="card card-compact">
        <div class="brand">Order History</div>
        <div class="name">Review orders</div>
        <p class="muted">Track order status and customer details.</p>
        <a class="btn small" href="<?php echo base_url('admin/orders.php'); ?>">View Orders</a>
    </div>
</div>
<div class="section-title"><h2>Recent Orders</h2><a class="btn small ghost" href="<?php echo base_url('admin/orders.php'); ?>">View all</a></div>
<div class="card">
    <?php if (!$recentOrders): ?>
        <div class="empty">No orders yet.</div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>ID</th><th>Customer</th><th>Total</th><th>Date</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td>#<?php echo (int)$order['id']; ?></td>
                        <td><?php echo e($order['full_name']); ?></td>
                        <td><?php echo format_price((float)$order['total']); ?></td>
                        <td><?php echo e($order['created_at']); ?></td>
                        <td><a class="btn small" href="<?php echo base_url('admin/order_view.php?id=' . $order['id']); ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
