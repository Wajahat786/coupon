<?php

declare(strict_types=1);

/**
 * DB connectivity smoke test — run in browser ONCE after setup:
 *   https://yourdomain.com/database/db-test.php
 * Then DELETE this file (it is inside the app folder, not the web root,
 * so on Hostinger you may need to temporarily copy it into public_html).
 *
 * It never prints your password; only masked diagnostics.
 */

if (PHP_SAPI !== 'cli' && !isset($_GET['key'])) {
    exit('Add ?key=<APP_KEY first 8 chars> once configured, or run via CLI.');
}

require __DIR__ . '/../app/bootstrap.php';

$cfg = APP_CONFIG['db'];
echo "PHP version      : " . PHP_VERSION . "\n";
echo ".env loaded from : " . ($GLOBALS['ENV_LOADED_FROM'] ?? '*** NOT FOUND ***') . "\n";
echo "DB host:port     : {$cfg['host']}:{$cfg['port']}\n";
echo "DB name          : {$cfg['name']}\n";
echo "DB user          : {$cfg['user']}\n";
echo "DB password      : " . ($cfg['pass'] === '' ? '(EMPTY — fill DB_PASS!)' : 'set (' . strlen($cfg['pass']) . ' chars)') . "\n";
echo "----------------------------------------\n";

try {
    $pdo = App\Core\Database::pdo();
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "CONNECTED OK ✔  tables(" . count($tables) . "): " . implode(', ', $tables) . "\n";
    if (!in_array('users', $tables, true)) {
        echo "!! Schema missing — import database/schema.sql in phpMyAdmin.\n";
    }
} catch (Throwable $e) {
    echo "FAILED ✘  " . $e->getMessage() . "\n";
    echo "Check storage/logs/php-error.log for the detailed reason.\n";
}
