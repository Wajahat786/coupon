<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Setting
{
    private static ?array $cache = null;

    public static function get(string $key, string $default = ''): string
    {
        if (self::$cache === null) {
            self::$cache = [];
            foreach (Database::run('SELECT s_key, s_value FROM settings')->fetchAll() as $row) {
                self::$cache[$row['s_key']] = $row['s_value'];
            }
        }
        return self::$cache[$key] ?? $default;
    }

    public static function set(string $key, string $value): void
    {
        Database::run(
            'INSERT INTO settings (s_key, s_value) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE s_value = VALUES(s_value)',
            [':k' => mb_substr($key, 0, 64), ':v' => mb_substr($value, 0, 255)]
        );
        self::$cache = null;
    }

    public static function all(): array
    {
        return Database::run('SELECT s_key, s_value FROM settings ORDER BY s_key')->fetchAll();
    }
}
