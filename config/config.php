<?php
// config/config.php
declare(strict_types=1);

$envFile = __DIR__ . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v, " \t\n\r\0\x0B\"'");
        $_ENV[$k] = $v;
        putenv("$k=$v");
    }
}

$get = static fn(string $key, ?string $default = null): ?string => $_ENV[$key] ?? getenv($key) ?: $default;

return [
    'app' => [
        'name'  => $get('APP_NAME', 'Kodafirca POS'),
        'env'   => $get('APP_ENV', 'local'),
        'debug' => filter_var($get('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOL),
        'url'   => $get('APP_URL', 'http://kodafirca.local'),
        'tz'    => $get('TIMEZONE', 'Africa/Dar_es_Salaam'),
    ],
    'db' => [
        'host' => $get('DB_HOST', '127.0.0.1'),
        'port' => (int)$get('DB_PORT', '3306'),
        'name' => $get('DB_NAME', 'kodafirca_db'),
        'user' => $get('DB_USER', 'kodafirca_user'),
        'pass' => $get('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'lifetime' => (int)$get('SESSION_LIFETIME', '0'),
        'name'     => 'kodafirca_sess',
    ],
    'paths' => [
        'root'    => dirname(__DIR__),
        'storage' => dirname(__DIR__) . '/storage',
        'uploads' => dirname(__DIR__) . '/public/uploads',
    ],
];
