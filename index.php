<?php
require_once __DIR__ . '/includes/header.php';
$pdo = get_pdo();
$featuredStmt = $pdo->query("SELECT * FROM products WHERE featured = 1 ORDER BY created_at DESC LIMIT 6");
$featured = $featuredStmt->fetchAll();
?>
<div class="hero">
    <div>
        <p class="breadcrumb">Charcoal curated luxury • Since 1998</p>
        <h1>Modern luxury timepieces, curated with precision.</h1>
        <p>Shop an edited selection of Swiss icons and independent maisons. Concierge-level service, insured delivery, and transparent authenticity.</p>
        <div class="cta-group">
            <a class="btn primary" href="<?php echo base_url('catalog.php'); ?>">Browse Collection</a>
            <a class="btn ghost" href="<?php echo base_url('services.php'); ?>">Trade / Sell</a>
        </div>
        <div class="tagline">
            <span class="tag">Certified Authentic</span>
            <span class="tag">Insured Worldwide Shipping</span>
            <span class="tag">Concierge Support</span>
        </div>
    </div>
    <div class="card">
        <div class="brand">Featured Appointment</div>
        <div class="name">Private Viewing Lounge</div>
        <p class="muted">Schedule a one-on-one session with our watch specialists. Sip espresso, compare references, and finalize your grail with confidence.</p>
        <a class="btn" href="<?php echo base_url('appointment.php'); ?>">Book a visit</a>
    </div>
</div>

<div class="section-title"><h2>Featured Watches</h2><a href="<?php echo base_url('catalog.php'); ?>" class="btn small ghost">View all</a></div>
<?php if ($featured): ?>
<div class="grid">
    <?php foreach ($featured as $watch): ?>
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
                        <button class="btn small primary" type="submit">Add to Cart</button>
                    </form>
                <?php else: ?>
                    <span class="muted">Admin accounts can't purchase.</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty">No featured pieces at the moment.</div>
<?php endif; ?>

<div class="section-title"><h2>Trust & Care</h2></div>
<div class="grid">
    <div class="card card-compact">
        <div class="brand">Authenticity</div>
        <div class="name">Certified & insured</div>
        <p class="muted">Every watch is authenticated by Swiss-trained watchmakers. Full papers when available and insured delivery worldwide.</p>
    </div>
    <div class="card card-compact">
        <div class="brand">Concierge</div>
        <div class="name">Private appointments</div>
        <p class="muted">Reserve a private lounge session in NYC, LA, or Paris. Compare references and discuss trades with discretion.</p>
    </div>
    <div class="card card-compact">
        <div class="brand">Care</div>
        <div class="name">Service & warranty</div>
        <p class="muted">Timed accuracy checks, polishing on request, and 12-month in-house warranty on serviced pieces.</p>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
