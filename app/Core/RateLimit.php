<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Tiny DB-backed sliding-window rate limiter used for login & submissions.
 */
final class RateLimit
{
    public static function hit(string $key): void
    {
        Database::run(
            'INSERT INTO rate_limits (rl_key, created_at) VALUES (:k, NOW())',
            [':k' => hash('sha256', $key)]
        );
        // Opportunistic GC (cheap because table is small & indexed).
        if (random_int(1, 50) === 1) {
            Database::run('DELETE FROM rate_limits WHERE created_at < NOW() - INTERVAL 1 DAY');
        }
    }

    public static function attempts(string $key, int $windowSeconds): int
    {
        $row = Database::run(
            'SELECT COUNT(*) c FROM rate_limits WHERE rl_key = :k AND created_at > NOW() - INTERVAL :w SECOND',
            [':k' => hash('sha256', $key), ':w' => $windowSeconds]
        )->fetch();
        return (int) ($row['c'] ?? 0);
    }

    public static function clear(string $key): void
    {
        Database::run('DELETE FROM rate_limits WHERE rl_key = :k', [':k' => hash('sha256', $key)]);
    }
}
