<?php
/**
 * PDO database connection (singleton).
 * Usage:  $pdo = db();
 */
declare(strict_types=1);

function config(?string $key = null)
{
    static $cfg = null;
    if ($cfg === null) {
        $path = __DIR__ . '/config.php';
        if (!is_file($path)) {
            http_response_code(500);
            exit('Configuration missing. Copy app/config.example.php to app/config.php and set your values.');
        }
        $cfg = require $path;
    }
    if ($key === null) return $cfg;
    // dot access: config('db.host')
    $parts = explode('.', $key);
    $val = $cfg;
    foreach ($parts as $p) {
        if (!is_array($val) || !array_key_exists($p, $val)) return null;
        $val = $val[$p];
    }
    return $val;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $db = config('db');
    $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
    try {
        $pdo = new PDO($dsn, $db['user'], $db['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        error_log('DB connection failed: ' . $e->getMessage());
        exit('Database connection failed. Check app/config.php credentials.');
    }
    return $pdo;
}
