<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/** Who did what, when — essential for an approval workflow. */
final class Audit
{
    public static function log(int $userId, string $action, string $target = ''): void
    {
        Database::run(
            'INSERT INTO audit_logs (user_id, action, target, ip, created_at)
             VALUES (:u, :a, :t, :ip, NOW())',
            [
                ':u'  => $userId ?: null,
                ':a'  => mb_substr($action, 0, 100),
                ':t'  => mb_substr($target, 0, 190),
                ':ip' => substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45),
            ]
        );
    }

    public static function recent(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));
        return Database::run(
            "SELECT a.*, u.name AS user_name FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT $limit"
        )->fetchAll();
    }
}
