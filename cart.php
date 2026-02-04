<?php
require_once __DIR__ . '/includes/header.php';
$pdo = get_pdo();

if (is_admin()) {
    set_flash('Admin accounts cannot purchase products.', 'error');
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid request. Refresh and try again.', 'error');
        header('Location: ' . base_url('cart.php'));
        exit;
    }
    $action = $_POST['action'] ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);
    $qty = max(0, (int)($_POST['quantity'] ?? 0));

    switch ($action) {
        case 'add':
            $productCheck = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
            $productCheck->execute([$productId]);
            $productExists = $productCheck->fetch();
            if ($productExists && $productExists['stock'] > 0) {
                add_to_cart($productId, max(1, $qty));
                set_flash('Added to cart.', 'success');
            } else {
                set_flash('Product unavailable.', 'error');
            }
            break;
        case 'update':
            update_cart_item($productId, $qty);
            set_flash('Cart updated.', 'success');
            break;
        case 'remove':
            remove_from_cart($productId);
            set_flash('Item removed.', 'success');
            break;
    }
    header('Location: ' . base_url('cart.php'));
    exit;
}

$cart = fetch_cart_details($pdo);
?>
<div class="section-title"><h2>Your Cart</h2></div>
<?php if (!$cart['items']): ?>
    <div class="empty">Your cart is empty. Discover our collection.</div>
<?php else: ?>
    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Watch</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($cart['items'] as $item): $product = $item['product']; ?>
                <tr>
                    <td><?php echo e($product['name']); ?></td>
                    <td><?php echo e($product['brand']); ?></td>
                    <td><?php echo format_price((float)$product['price']); ?></td>
                    <td>
                        <form method="post" style="display:flex; align-items:center; gap:8px;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                            <input type="number" min="1" max="<?php echo (int)$product['stock']; ?>" name="quantity" value="<?php echo (int)$item['quantity']; ?>" style="width:80px; padding:8px; border-radius:8px; border:1px solid var(--line); background:#0b0c10; color:var(--text);">
                            <button class="btn small" type="submit">Save</button>
                        </form>
                    </td>
                    <td><?php echo format_price((float)$item['subtotal']); ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                            <button class="btn small ghost" type="submit">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px;">
            <div class="muted">Shipping & taxes calculated at checkout.</div>
            <div>
                <strong>Total: <?php echo format_price((float)$cart['total']); ?></strong>
                <a class="btn primary" href="<?php echo base_url('checkout.php'); ?>" style="margin-left:8px;">Checkout</a>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
