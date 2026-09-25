<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Security utilities: hardened sessions, CSRF, escaping, rate limiting,
 * slug validation, redirect sanitisation.
 */
final class Security
{
    /* ---------------- Session ---------------- */

    public static function startSession(array $secCfg): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Deterministic, readable cookie name; strict HttpOnly + SameSite=Lax.
        session_name($secCfg['cookie_name'] ?? 'dealhub_session');
        session_set_cookie_params([
            'lifetime' => 0,                 // session cookie
            'path'     => '/',
            'domain'   => '',
            'secure'   => !empty($secCfg['cookie_secure']),
            'httponly' => true,
            'samesite' => 'Lax',             // Lax so admin links open normally
        ]);

        session_start();

        // Periodic ID rotation (defence in depth against fixation).
        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = time();
            session_regenerate_id(false);
        } elseif (time() - (int) $_SESSION['_created'] > 1800) {
            $_SESSION['_created'] = time();
            session_regenerate_id(true);
        }
    }

    public static function destroySession(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    /* ---------------- CSRF ---------------- */

    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    /** Validate submitted token with timing-safe compare. Dies on failure. */
    public static function verifyCsrf(): void
    {
        $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!is_string($sent) || !hash_equals($_SESSION['_csrf'] ?? '', $sent)) {
            http_response_code(419);
            exit('CSRF token mismatch. Go back, refresh and try again.');
        }
    }

    /* ---------------- Escaping ---------------- */

    public static function e(mixed $v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }

    /* ---------------- Rate limiting (DB-backed) ---------------- */

    /**
     * Returns remaining attempts or 0 when blocked.
     * $key example: "login:1.2.3.4:admin@example.com"
     */
    public static function rateCheck(string $key, int $max, int $windowSeconds): bool
    {
        $allowed = true;
        $hits = RateLimit::attempts($key, $windowSeconds);
        if ($hits >= $max) {
            $allowed = false;
        }
        RateLimit::hit($key);
        return $allowed;
    }

    public static function rateClear(string $key): void
    {
        RateLimit::clear($key);
    }

    /* ---------------- Misc ---------------- */

    public static function clientIp(): string
    {
        // Only trust proxy headers if you configure your webserver to strip them.
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /** Strict slug whitelist — prevents XSS via slugs embedded in URLs/DOM. */
    public static function slug(string $raw): string
    {
        $s = strtolower(trim($raw));
        $s = preg_replace('/[^a-z0-9\-]/', '-', $s) ?? '';
        $s = preg_replace('/-+/', '-', $s) ?? '';
        return trim($s, '-');
    }

    /** Coupon/referral codes: uppercase alnum + dash only. */
    public static function normalizeCode(string $raw): string
    {
        $s = strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', $raw) ?? '');
        return substr($s, 0, 32);
    }
}
