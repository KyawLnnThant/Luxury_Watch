<?php
require_once __DIR__ . '/includes/header.php';
require_login();
$pdo = get_pdo();

if (is_admin()) {
    set_flash('Admin accounts cannot purchase products.', 'error');
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

$cart = fetch_cart_details($pdo);

if (!$cart['items']) {
    echo '<div class="empty">Your cart is empty.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$errors = [];
$name = current_user()['full_name'] ?? '';
$email = current_user()['email'] ?? '';
$address = $_POST['address'] ?? '';
$city = $_POST['city'] ?? '';
$country = $_POST['country'] ?? '';
$zip = $_POST['zip'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request, please refresh.';
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $zip = trim($_POST['zip'] ?? '');

    foreach (['name' => $name, 'email' => $email, 'address' => $address, 'city' => $city, 'country' => $country, 'zip' => $zip] as $field => $value) {
        if ($value === '') {
            $errors[] = ucfirst($field) . ' is required';
        }
    }

    // Stock validation
    foreach ($cart['items'] as $item) {
        if ($item['quantity'] > $item['product']['stock']) {
            $errors[] = 'Insufficient stock for ' . $item['product']['name'];
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $shipping = "$name\n$address\n$city, $country $zip";
            $orderStmt = $pdo->prepare('INSERT INTO orders (user_id, total, status, shipping_address) VALUES (?, ?, ?, ?)');
            $orderStmt->execute([current_user()['id'], $cart['total'], 'processing', $shipping]);
            $orderId = (int)$pdo->lastInsertId();

            $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
            $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');

            foreach ($cart['items'] as $item) {
                $productId = (int)$item['product']['id'];
                $qty = (int)$item['quantity'];
                $itemStmt->execute([$orderId, $productId, $qty, $item['product']['price']]);
                $stockStmt->execute([$qty, $productId, $qty]);
                if ($stockStmt->rowCount() === 0) {
                    throw new RuntimeException('Stock changed, please try again.');
                }
            }

            $pdo->commit();
            clear_cart();
            header('Location: ' . base_url('order_success.php?id=' . $orderId));
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Unable to complete checkout. Please try again.';
        }
    }
}
?>
<div class="section-title"><h2>Checkout</h2></div>
<div class="grid">
    <div class="card">
        <h3 style="margin:0 0 10px;">Shipping Details</h3>
        <?php foreach ($errors as $err): ?>
            <div class="alert error"><?php echo e($err); ?></div>
        <?php endforeach; ?>
        <form method="post" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <div class="input-field">
                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo e($name); ?>" required>
            </div>
            <div class="input-field">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo e($email); ?>" required>
            </div>
            <div class="input-field">
                <label>Address</label>
                <input type="text" name="address" value="<?php echo e($address); ?>" required>
            </div>
            <div class="input-field">
                <label>City</label>
                <input type="text" name="city" value="<?php echo e($city); ?>" required>
            </div>
            <div class="input-field">
                <label>Country</label>
                <input type="text" name="country" value="<?php echo e($country); ?>" required>
            </div>
            <div class="input-field">
                <label>Postal Code</label>
                <input type="text" name="zip" value="<?php echo e($zip); ?>" required>
            </div>
            <div style="grid-column:1 / -1; display:flex; justify-content:flex-end; gap:10px;">
                <a class="btn ghost" href="<?php echo base_url('cart.php'); ?>">Back to cart</a>
                <button class="btn primary" type="submit">Place Order</button>
            </div>
        </form>
    </div>
    <div class="card">
        <h3 style="margin:0 0 10px;">Order Summary</h3>
        <?php foreach ($cart['items'] as $item): ?>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <div>
                    <div class="name"><?php echo e($item['product']['name']); ?></div>
                    <div class="muted">Qty: <?php echo (int)$item['quantity']; ?></div>
                </div>
                <div class="price"><?php echo format_price((float)$item['subtotal']); ?></div>
            </div>
        <?php endforeach; ?>
        <div style="border-top:1px solid var(--line); padding-top:10px; display:flex; justify-content:space-between;">
            <span>Total</span>
            <strong class="price"><?php echo format_price((float)$cart['total']); ?></strong>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
