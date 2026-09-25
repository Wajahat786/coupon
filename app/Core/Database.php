<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Singleton PDO connection with prepared statements ONLY.
 * Never interpolate user input into SQL anywhere in this app.
 */
final class Database
{
    private static ?PDO $pdo = null;

    private function __construct() {}
    private function __clone() {}

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            /** @var array $cfg */
            $cfg = APP_CONFIG['db'];
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $cfg['host'],
                $cfg['port'],
                $cfg['name'],
                $cfg['charset']
            );

            try {
                self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, // real server-side prepares
                    PDO::ATTR_STRINGIFY_FETCHES  => false,
                ]);
            } catch (PDOException $e) {
                // Do not leak credentials / host info to output.
                error_log('[DB] connection failed: ' . $e->getMessage());
                throw new RuntimeException('Database connection unavailable.');
            }
        }

        return self::$pdo;
    }

    /** Convenience: run a prepared statement and return the stmt. */
    public static function run(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        foreach ($params as $k => $v) {
            $key = is_int($k) ? $k + 1 : $k;
            $type = match (true) {
                is_int($v)  => PDO::PARAM_INT,
                is_bool($v) => PDO::PARAM_BOOL,
                is_null($v) => PDO::PARAM_NULL,
                default     => PDO::PARAM_STR,
            };
            $stmt->bindValue($key, $v, $type);
        }
        $stmt->execute();
        return $stmt;
    }

    public static function lastInsertId(): int
    {
        return (int) self::pdo()->lastInsertId();
    }
}
