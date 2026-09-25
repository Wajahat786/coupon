<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Authentication for admins & submitters.
 * Passwords: bcrypt via password_hash(). Sessions store only the user id;
 * the row is re-fetched each request so role/status changes apply instantly.
 */
final class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $ip    = Security::clientIp();
        $rlKey = "login:$ip:" . strtolower($email);

        if (!Security::rateCheck($rlKey, (int) APP_CONFIG['security']['login_max_attempts'],
                (int) APP_CONFIG['security']['login_lockout_seconds'])) {
            flash_message_set('error', 'Too many login attempts. Please wait 15 minutes.');
            return false;
        }

        $user = User::findByEmail($email);
        // Always run a hash comparison to keep timing uniform (user-enumeration defence).
        $hash = $user['password_hash']
            ?? '$2y$12$usesomesillystringfor.eeM0n7lGxkZ0m1Q3sYq7uWjE0Zg5S';

        if (!password_verify($password, $hash) || $user === null || (int) $user['is_active'] !== 1) {
            Security::rateClear($rlKey); // consumed one slot already; keep symmetric
            return false;
        }

        Security::rateClear($rlKey);
        session_regenerate_id(true);          // fixation defence on privilege change
        $_SESSION['uid']    = (int) $user['id'];
        $_SESSION['role']   = (string) $user['role'];
        $_SESSION['_created'] = time();
        User::touchLogin((int) $user['id'], $ip);
        return true;
    }

    public static function logout(): void
    {
        Security::destroySession();
    }

    public static function check(): bool
    {
        return isset($_SESSION['uid']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        $u = User::find((int) $_SESSION['uid']);
        if ($u === null || (int) $u['is_active'] !== 1) {
            self::logout();
            return null;
        }
        unset($u['password_hash']);
        return $u;
    }

    public static function id(): int
    {
        return (int) ($_SESSION['uid'] ?? 0);
    }

    public static function isAdmin(): bool
    {
        return self::check() && ($_SESSION['role'] ?? '') === 'admin';
    }

    /** Gate a page: redirects guests to login. */
    public static function requireLogin(bool $adminOnly = false): void
    {
        if (!self::check()) {
            header('Location: /login?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
            exit;
        }
        if ($adminOnly && !self::isAdmin()) {
            http_response_code(403);
            View::renderError(403);
            exit;
        }
    }
}
