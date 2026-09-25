<?php
/**
 * Bootstrap: loads .env (if present), config, autoloader, security headers.
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

/* ---------- PSR-4 style micro autoloader (no composer required) ---------- */
spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $path = BASE_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($path)) {
            require $path;
        }
    }
});

/* ---------- Load .env file (simple parser, production can use real env) --- */
(static function (): void {
    $envFile = BASE_PATH . '/.env';
    if (!is_readable($envFile)) {
        return;
    }
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim(trim($v), "\"'");
        if (getenv($k) === false) {
            putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
})();

$config = require BASE_PATH . '/config/config.php';
define('APP_CONFIG', $config);

date_default_timezone_set($config['app']['timezone']);

/* ---------- Error handling: never leak stack traces in production --------- */
error_reporting(E_ALL);
ini_set('display_errors', $config['app']['debug'] ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');

/* ---------- Hard security headers on every response ---------------------- */
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
header("Content-Security-Policy: default-src 'self'; "
     . "script-src 'self' 'unsafe-inline'; "          // inline for theme toggle; move to nonce in scale-up
     . "style-src 'self' 'unsafe-inline'; "
     . "img-src 'self' data: https:; "
     . "connect-src 'self'; "
     . "frame-ancestors 'none'; "
     . "base-uri 'self'; form-action 'self'");
if ($config['app']['env'] === 'production') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

/* ---------- Secure session ------------------------------------------------ */
\App\Core\Security::startSession($config['security']);
