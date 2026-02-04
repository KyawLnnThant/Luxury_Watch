<?php
require_once __DIR__ . '/config.php';

function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function base_url(string $path = ''): string
{
    $base = rtrim(BASE_PATH, '/');
    $prefix = $base === '' ? '' : $base . '/';
    return $prefix . ltrim($path, '/');
}

function image_src(?string $path): string
{
    $path = trim((string)$path);
    if ($path === '') {
        return base_url('assets/Image/shutterstock_2193519341-1.webp');
    }
    if (preg_match('#^(https?:)?//#i', $path)) {
        return $path;
    }
    return base_url(ltrim($path, '/'));
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    return isset($_SESSION['csrf_token'], $token) && hash_equals($_SESSION['csrf_token'], $token);
}

function set_flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    $user = current_user();
    return $user && $user['role'] === 'admin';
}

function require_login(): void
{
    if (!current_user()) {
        header('Location: ' . base_url('login.php'));
        exit;
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        header('Location: ' . base_url('index.php'));
        exit;
    }
}

function cart_items(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    return array_sum(cart_items());
}

function add_to_cart(int $productId, int $quantity = 1): void
{
    if ($productId <= 0 || $quantity <= 0) {
        return;
    }
    $cart = cart_items();
    $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
    $_SESSION['cart'] = $cart;
}

function update_cart_item(int $productId, int $quantity): void
{
    $cart = cart_items();
    if ($quantity <= 0) {
        unset($cart[$productId]);
    } else {
        $cart[$productId] = $quantity;
    }
    $_SESSION['cart'] = $cart;
}

function remove_from_cart(int $productId): void
{
    $cart = cart_items();
    unset($cart[$productId]);
    $_SESSION['cart'] = $cart;
}

function clear_cart(): void
{
    unset($_SESSION['cart']);
}

function fetch_cart_details(PDO $pdo): array
{
    $items = cart_items();
    if (empty($items)) {
        return ['items' => [], 'total' => 0];
    }
    $ids = array_keys($items);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, name, brand, price, stock, image_url FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    $details = [];
    $total = 0;
    foreach ($products as $product) {
        $pid = (int)$product['id'];
        $qty = $items[$pid] ?? 0;
        $subtotal = $product['price'] * $qty;
        $total += $subtotal;
        $details[] = [
            'product' => $product,
            'quantity' => $qty,
            'subtotal' => $subtotal,
        ];
    }

    return ['items' => $details, 'total' => $total];
}

function format_price(float $amount): string
{
    return '$' . number_format($amount, 2);
}

function get_brands(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT DISTINCT brand FROM products ORDER BY brand");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
