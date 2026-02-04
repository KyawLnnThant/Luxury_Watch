<?php
require_once __DIR__ . '/includes/header.php';
$pdo = get_pdo();

$search = trim($_GET['q'] ?? '');
$brandFilter = trim($_GET['brand'] ?? '');
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (name LIKE ? OR brand LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($brandFilter !== '') {
    $sql .= " AND brand = ?";
    $params[] = $brandFilter;
}

if ($minPrice !== '' && is_numeric($minPrice)) {
    $sql .= " AND price >= ?";
    $params[] = (float)$minPrice;
}

if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $sql .= " AND price <= ?";
    $params[] = (float)$maxPrice;
}

switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    default:
        $sql .= " ORDER BY created_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$brands = get_brands($pdo);
?>
<div class="section-title"><h2>Catalog</h2></div>
<form class="filters" method="get">
    <input type="text" name="q" placeholder="Search by brand or model" value="<?php echo e($search); ?>">
    <select name="brand">
        <option value="">All Brands</option>
        <?php foreach ($brands as $brand): ?>
            <option value="<?php echo e($brand); ?>" <?php echo $brand === $brandFilter ? 'selected' : ''; ?>><?php echo e($brand); ?></option>
        <?php endforeach; ?>
    </select>
    <input type="number" step="0.01" name="min_price" placeholder="Min price" value="<?php echo e($minPrice); ?>">
    <input type="number" step="0.01" name="max_price" placeholder="Max price" value="<?php echo e($maxPrice); ?>">
    <select name="sort">
        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
        <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price low-high</option>
        <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price high-low</option>
    </select>
    <button class="btn primary" type="submit">Apply</button>
</form>

<?php if ($products): ?>
<div class="grid">
    <?php foreach ($products as $watch): ?>
        <div class="card">
            <img src="<?php echo e(image_src($watch['image_url'])); ?>" alt="<?php echo e($watch['name']); ?>">
            <div class="brand"><?php echo e($watch['brand']); ?></div>
            <div class="name"><?php echo e($watch['name']); ?></div>
            <div class="price"><?php echo format_price((float)$watch['price']); ?></div>
            <div class="actions">
                <a class="btn small" href="<?php echo base_url('product.php?id=' . $watch['id']); ?>">View</a>
                <?php if (!is_admin()): ?>
                    <form method="post" action="<?php echo base_url('cart.php'); ?>">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo (int)$watch['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <button class="btn small primary" type="submit">Add</button>
                    </form>
                <?php else: ?>
                    <span class="muted">Admin accounts can't purchase.</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty">No watches match this search yet.</div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
