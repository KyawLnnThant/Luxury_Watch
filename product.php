<?php
require_once __DIR__ . '/includes/header.php';
$pdo = get_pdo();
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    if (is_admin()) {
        set_flash('Admin accounts cannot purchase products.', 'error');
        header('Location: ' . base_url('product.php?id=' . $productId));
        exit;
    }
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid request. Please try again.', 'error');
        header('Location: ' . base_url('product.php?id=' . $productId));
        exit;
    }
    $qty = max(1, (int)($_POST['quantity'] ?? 1));
    add_to_cart((int)$_POST['product_id'], $qty);
    set_flash('Added to cart.', 'success');
    header('Location: ' . base_url('cart.php'));
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo '<div class="container"><div class="empty">Product not found.</div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
?>
<div class="section-title"><h2><?php echo e($product['brand']); ?> — <?php echo e($product['name']); ?></h2></div>
<div class="hero">
    <div>
        <img src="<?php echo e(image_src($product['image_url'])); ?>" alt="<?php echo e($product['name']); ?>" style="width:100%;border-radius:16px; max-height:420px; object-fit:cover;">
    </div>
    <div class="card">
        <div class="brand"><?php echo e($product['brand']); ?></div>
        <div class="name"><?php echo e($product['name']); ?></div>
        <div class="price" style="font-size:24px; margin:4px 0;"> <?php echo format_price((float)$product['price']); ?></div>
        <p class="muted"><?php echo e($product['description']); ?></p>
        <div class="badge">In stock: <?php echo (int)$product['stock']; ?></div>
        <?php if (!is_admin()): ?>
            <form method="post" class="actions" style="margin-top:12px;">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <input type="number" name="quantity" min="1" max="<?php echo (int)$product['stock']; ?>" value="1" style="padding:10px; width:120px; border-radius:10px; border:1px solid var(--line); background:#0b0c10; color:var(--text);">
                <button class="btn primary" type="submit">Add to cart</button>
                <a class="btn ghost" href="<?php echo base_url('catalog.php'); ?>">Back to catalog</a>
            </form>
        <?php else: ?>
            <div class="muted">Admin accounts cannot purchase.</div>
            <div style="margin-top:12px;">
                <a class="btn ghost" href="<?php echo base_url('catalog.php'); ?>">Back to catalog</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
