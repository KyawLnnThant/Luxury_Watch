<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Adjust this base path to match your virtual host (e.g., '/Assignment').
define('BASE_PATH', '/Assignment');

define('DB_HOST', 'localhost');
define('DB_NAME', 'watch_shop');
define('DB_USER', 'root');
define('DB_PASS', '');

date_default_timezone_set('UTC');

function get_pdo(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (Throwable $e) {
        die('Database connection failed.');
    }

    return $pdo;
}
