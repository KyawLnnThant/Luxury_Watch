<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
$pdo = get_pdo();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = [
    'name' => '',
    'brand' => '',
    'price' => '',
    'description' => '',
    'image_url' => '',
    'stock' => 0,
    'featured' => 0,
];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) {
        echo '<div class="empty">Product not found.</div>';
        require_once __DIR__ . '/../includes/footer.php';
        exit;
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    }
    $product['name'] = trim($_POST['name'] ?? '');
    $product['brand'] = trim($_POST['brand'] ?? '');
    $product['price'] = (float)($_POST['price'] ?? 0);
    $product['description'] = trim($_POST['description'] ?? '');
    $product['image_url'] = trim($_POST['image_url'] ?? '');
    $product['stock'] = max(0, (int)($_POST['stock'] ?? 0));
    $product['featured'] = isset($_POST['featured']) ? 1 : 0;

    $upload = $_FILES['image_file'] ?? null;
    if ($upload && $upload['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($upload['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Image upload failed.';
        } else {
            $maxBytes = 5 * 1024 * 1024;
            if ($upload['size'] > $maxBytes) {
                $errors[] = 'Image must be under 5MB.';
            }
            $ext = strtolower(pathinfo($upload['name'] ?? '', PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($ext, $allowed, true)) {
                $errors[] = 'Image must be JPG, PNG, WEBP, or GIF.';
            }
            if (empty($errors)) {
                $uploadDir = __DIR__ . '/../assets/Image';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $filename = bin2hex(random_bytes(8)) . '.' . $ext;
                $target = $uploadDir . '/' . $filename;
                if (!move_uploaded_file($upload['tmp_name'], $target)) {
                    $errors[] = 'Unable to save uploaded image.';
                } else {
                    $product['image_url'] = 'assets/Image/' . $filename;
                }
            }
        }
    }

    foreach (['name', 'brand'] as $field) {
        if ($product[$field] === '') {
            $errors[] = ucfirst($field) . ' is required';
        }
    }
    if ($product['price'] <= 0) {
        $errors[] = 'Price must be positive.';
    }

    if (empty($errors)) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE products SET name = ?, brand = ?, price = ?, description = ?, image_url = ?, stock = ?, featured = ? WHERE id = ?');
            $stmt->execute([$product['name'], $product['brand'], $product['price'], $product['description'], $product['image_url'], $product['stock'], $product['featured'], $id]);
            set_flash('Product updated.', 'success');
        } else {
            $stmt = $pdo->prepare('INSERT INTO products (name, brand, price, description, image_url, stock, featured) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$product['name'], $product['brand'], $product['price'], $product['description'], $product['image_url'], $product['stock'], $product['featured']]);
            $id = (int)$pdo->lastInsertId();
            set_flash('Product created.', 'success');
        }
        header('Location: ' . base_url('admin/products.php'));
        exit;
    }
}
?>
<div class="section-title"><h2><?php echo $id ? 'Edit Product' : 'Add Product'; ?></h2></div>
<div class="card">
    <?php foreach ($errors as $err): ?>
        <div class="alert error"><?php echo e($err); ?></div>
    <?php endforeach; ?>
    <form method="post" class="form-grid" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        <div class="input-field">
            <label>Name</label>
            <input type="text" name="name" value="<?php echo e($product['name']); ?>" required>
        </div>
        <div class="input-field">
            <label>Brand</label>
            <input type="text" name="brand" value="<?php echo e($product['brand']); ?>" required>
        </div>
        <div class="input-field">
            <label>Price</label>
            <input type="number" step="0.01" name="price" value="<?php echo e($product['price']); ?>" required>
        </div>
        <div class="input-field">
            <label>Stock</label>
            <input type="number" name="stock" value="<?php echo e($product['stock']); ?>" required>
        </div>
        <div class="input-field" style="grid-column:1 / -1;">
            <label>Description</label>
            <textarea name="description"><?php echo e($product['description']); ?></textarea>
        </div>
        <div class="input-field" style="grid-column:1 / -1;">
            <label>Image URL or asset path (e.g., assets/Image/filename.webp)</label>
            <input type="text" name="image_url" value="<?php echo e($product['image_url']); ?>">
        </div>
        <div class="input-field" style="grid-column:1 / -1;">
            <label>Upload Image (browse)</label>
            <input type="file" name="image_file" accept="image/*">
        </div>
        <div class="input-field">
            <label><input type="checkbox" name="featured" <?php echo $product['featured'] ? 'checked' : ''; ?>> Featured</label>
        </div>
        <div style="grid-column:1 / -1; display:flex; gap:10px; justify-content:flex-end;">
            <a class="btn ghost" href="<?php echo base_url('admin/products.php'); ?>">Cancel</a>
            <button class="btn primary" type="submit">Save</button>
        </div>
    </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
