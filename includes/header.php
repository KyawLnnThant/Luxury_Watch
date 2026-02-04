<?php
require_once __DIR__ . '/functions.php';
$user = current_user();
$flash = get_flash();
$cartCount = cart_count();
$showCustomerNav = !$user || !is_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxury Watch Shop</title>
    <link rel="stylesheet" href="<?php echo base_url('assets/css/styles.css'); ?>">
</head>
<body>
<header class="site-header" id="top">
    <div class="container nav-container">
        <a class="logo" href="<?php echo base_url('index.php'); ?>">LuxeTime</a>
        <nav class="main-nav">
            <a href="<?php echo base_url('catalog.php'); ?>">Shop</a>
            <a href="<?php echo base_url('services.php'); ?>">Services</a>
            <?php if ($showCustomerNav): ?>
                <a href="<?php echo base_url('about.php'); ?>">About</a>
                <a href="<?php echo base_url('contact.php'); ?>">Contact</a>
                <a href="<?php echo base_url('appointment.php'); ?>">Appointment</a>
            <?php endif; ?>
            <?php if ($showCustomerNav): ?>
                <a href="<?php echo base_url('cart.php'); ?>">Cart (<?php echo $cartCount; ?>)</a>
            <?php endif; ?>
            <?php if ($user): ?>
                <?php if (!is_admin()): ?>
                    <a href="<?php echo base_url('my_orders.php'); ?>">My Orders</a>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                    <a href="<?php echo base_url('admin/dashboard.php'); ?>">Admin</a>
                <?php endif; ?>
                <span class="user-chip"><?php echo e($user['full_name']); ?></span>
                <a href="<?php echo base_url('logout.php'); ?>">Logout</a>
            <?php else: ?>
                <a href="<?php echo base_url('login.php'); ?>">Login</a>
                <a href="<?php echo base_url('register.php'); ?>" class="pill">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<?php if ($flash): ?>
<div class="flash <?php echo e($flash['type']); ?>">
    <div class="container"><?php echo e($flash['message']); ?></div>
</div>
<?php endif; ?>
<main class="page">
<div class="container">
