<?php
require_once __DIR__ . '/includes/header.php';

if (is_admin()) {
    set_flash('Admin accounts cannot access customer pages.', 'error');
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}
?>
<div class="section-title"><h2>About Us</h2></div>
<div class="grid">
    <div class="card">
        <div class="brand">Our story</div>
        <div class="name">Curated luxury since 1998</div>
        <p class="muted">We source exceptional timepieces from trusted collectors, authorized channels, and private estates. Every watch is inspected, authenticated, and delivered with full transparency.</p>
    </div>
    <div class="card">
        <div class="brand">Our promise</div>
        <div class="name">Authentic. Insured. Supported.</div>
        <p class="muted">Our concierge team guides each purchase, whether you are discovering a first icon or completing a grail collection. We back every sale with insured shipping and in-house service.</p>
    </div>
    <div class="card">
        <div class="brand">Our craft</div>
        <div class="name">Watchmaking expertise</div>
        <p class="muted">Swiss-trained specialists handle detailing, timing, and servicing. We focus on long-term value, precision, and a purchase experience that feels bespoke.</p>
    </div>
</div>

<div class="section-title"><h2>How We Work</h2></div>
<div class="grid">
    <div class="card card-compact">
        <div class="brand">Sourcing</div>
        <div class="name">Selective inventory</div>
        <p class="muted">We only list references that meet strict condition and provenance standards.</p>
    </div>
    <div class="card card-compact">
        <div class="brand">Verification</div>
        <div class="name">Multi-step checks</div>
        <p class="muted">Movement, case, and dial inspection with authenticity reporting.</p>
    </div>
    <div class="card card-compact">
        <div class="brand">Delivery</div>
        <div class="name">Secure handoff</div>
        <p class="muted">Insured shipping, tracked delivery, and white-glove handoff options.</p>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
