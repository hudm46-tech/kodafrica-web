<?php
// database/seed.php
// Run once: php database/seed.php

declare(strict_types=1);
require __DIR__ . '/../config/config.php';
$cfg = require __DIR__ . '/../config/config.php';

try {
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $cfg['db']['host'], $cfg['db']['port'], $cfg['db']['name'], $cfg['db']['charset']);
    $pdo = new PDO($dsn, $cfg['db']['user'], $cfg['db']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, "DB connect failed: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

// ---- AppSettings (single row) ----
$exists = (int)$pdo->query("SELECT COUNT(*) FROM app_settings")->fetchColumn();
if ($exists === 0) {
    $pdo->exec("INSERT INTO app_settings (id, business_name, language, theme, receipt_mode)
                VALUES (1, 'Kodafirca Msata Hardware', 'en', 'dark', 'BOTH')");
    echo "[seed] app_settings created\n";
}

// ---- TaxSettings (single row) ----
$exists = (int)$pdo->query("SELECT COUNT(*) FROM tax_settings")->fetchColumn();
if ($exists === 0) {
    $pdo->exec("INSERT INTO tax_settings (id, entity_type, updated_by)
                VALUES (1, 'SOLE_PROPRIETOR', 'SYSTEM')");
    echo "[seed] tax_settings created\n";
}

echo "Seed complete ✅\n";
