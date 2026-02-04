<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid request.', 'error');
        header('Location: ' . base_url('admin/products.php'));
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);
    set_flash('Product deleted.', 'success');
    header('Location: ' . base_url('admin/products.php'));
    exit;
}

$products = $pdo->query('SELECT * FROM products ORDER BY created_at DESC')->fetchAll();
?>
<div class="section-title">
    <h2>Products</h2>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a class="btn small ghost" href="<?php echo base_url('admin/dashboard.php'); ?>">Back to Dashboard</a>
        <a class="btn small primary" href="<?php echo base_url('admin/product_edit.php'); ?>">Add Product</a>
    </div>
</div>
<div class="card">
    <?php if (!$products): ?>
        <div class="empty">No products yet.</div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>ID</th><th>Brand</th><th>Name</th><th>Price</th><th>Stock</th><th>Featured</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?php echo (int)$p['id']; ?></td>
                    <td><?php echo e($p['brand']); ?></td>
                    <td><?php echo e($p['name']); ?></td>
                    <td><?php echo format_price((float)$p['price']); ?></td>
                    <td><?php echo (int)$p['stock']; ?></td>
                    <td><?php echo $p['featured'] ? 'Yes' : 'No'; ?></td>
                    <td style="display:flex; gap:6px;">
                        <a class="btn small" href="<?php echo base_url('admin/product_edit.php?id=' . $p['id']); ?>">Edit</a>
                        <form method="post" onsubmit="return confirm('Delete product?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                            <button class="btn small ghost" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
