<?php
/**
 * Bootstrap: loads .env (if present), config, autoloader, security headers.
 */

declare(strict_types=1);

/*
 * BASE_PATH auto-detection — supports BOTH deployment styles:
 *   A) public/ as document root (recommended):  index.php is inside public/,
 *      app/ sits one level up  ->  BASE_PATH = dirname(__DIR__)
 *   B) whole project inside public_html (shared-hosting fallback):
 *      index.php lives next to app/             ->  BASE_PATH = __DIR__
 */
$candidates = [dirname(__DIR__), __DIR__];
foreach ($candidates as $cand) {
    if (is_dir($cand . '/app') && is_file($cand . '/config/config.php')) {
        define('BASE_PATH', $cand);
        break;
    }
}
if (!defined('BASE_PATH')) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Setup error: app/ and config/ folders not found next to index.php "
       . "(or one level above it). Checked:\n - " . implode("\n - ", $candidates) . "\n";
    exit;
}

/* ---------- PHP 8.0 compat polyfills (Hostinger may run older PHP) ------ */
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}

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
// Search order covers every supported deployment layout:
//   BASE_PATH/.env                  -> app root (public/ or public_html/)
//   dirname(BASE_PATH)/.env         -> one level above app root
//   BASE_PATH/public/.env           -> whole project inside public_html,
//                                      .env left at project root by mistake
//   __DIR__/.env                    -> legacy: .env beside bootstrap in app/
$GLOBALS['ENV_LOADED_FROM'] = null;
(static function (): void {
    $candidates = [
        BASE_PATH . '/.env',
        dirname(BASE_PATH) . '/.env',
        BASE_PATH . '/public/.env',
        __DIR__ . '/.env',
    ];
    foreach ($candidates as $envFile) {
        if (!is_readable($envFile)) {
            continue;
        }
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k);
            $v = trim(trim($v), "\"'");
            if ($v === '') {
                continue; // empty value = not configured yet
            }
            if (getenv($k) === false) {
                putenv("$k=$v");
                $_ENV[$k] = $v;
            }
        }
        $GLOBALS['ENV_LOADED_FROM'] = $envFile;
        return; // first readable .env wins
    }
})();

$config = require BASE_PATH . '/config/config.php';
define('APP_CONFIG', $config);

date_default_timezone_set($config['app']['timezone']);

/* ---------- Error handling: never leak stack traces in production --------- */
error_reporting(E_ALL);
ini_set('display_errors', $config['app']['debug'] ? '1' : '0');
ini_set('log_errors', '1');
$logDir = BASE_PATH . '/storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true); // create if missing (shared-hosting uploads often skip it)
}
if (is_writable($logDir)) {
    ini_set('error_log', $logDir . '/php-error.log');
}

register_shutdown_function(static function (): void {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('[FATAL] ' . $e['message'] . ' in ' . $e['file'] . ':' . $e['line']);
        if (!headers_sent()) {
            http_response_code(500);
        }
        echo 'Server error — please check storage/logs/php-error.log';
    }
});

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
